<?php

namespace App\Services\Data\Local;

use App\Episode;
use App\Person;
use App\Services\Data\Contracts\DataProvider;
use App\Title;
use Arr;
use Carbon\Carbon;
use Common\Tags\Tag;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Str;

class LocalDataProvider implements DataProvider
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Tag
     */
    private $tag;

    public function __construct(Title $title, Tag $tag)
    {
        $this->title = $title;
        $this->tag = $tag;
    }

    public function getTitle(Title $title)
    {
        return [];
    }

    public function getPerson(Person $person)
    {
        return [];
    }

    public function getSeason(Title $title, $seasonNumber)
    {
        return [];
    }

    public function search($query, $params = [])
    {
        $titles = collect();
        $people = collect();

        $orderCol =
            config('scout.driver') === 'mysql' &&
            config('common.site.scout_mysql_mode') === 'fulltext'
                ? 'relevance'
                : 'popularity';
        if (Arr::get($params, 'type') !== 'person') {
                $titles = $this->title
                    ->search($query)
                    ->take(20)
                    ->get();

                if ($with = Arr::get($params, 'with')) {
                    $with = array_filter(explode(',', $with));
                    $titles->load($with);
                }
        }

        if (Arr::get($params, 'type') !== 'title') {
            $people = app(Person::class)
                ->search($query)
                ->take(20)
                ->get()
                ->load('popularCredits');
        }

        return $titles
            ->concat($people)
            ->slice(0, Arr::get($params, 'limit', 8))
            ->values();
    }

    public function getTitles($titleType, $titleCategory)
    {
        $titleType =
            $titleType === 'tv' ? Title::SERIES_TYPE : Title::MOVIE_TYPE;

        if ($titleCategory === 'popular') {
            return $this->title
                ->orderBy('popularity', 'desc')
                ->limit(20)
                ->where('is_series', $titleType === Title::SERIES_TYPE)
                ->get();
        } elseif ($titleCategory === 'topRated') {
            return $this->getTopRatedTitles($titleType);
        } elseif ($titleCategory === 'upcoming') {
            return $this->getMoviesReleasingBetween(
                Carbon::now(),
                Carbon::now()->addWeek(),
            );
        } elseif ($titleCategory === 'nowPlaying') {
            return $this->getMoviesReleasingBetween(
                Carbon::now(),
                Carbon::now()->subWeek(2),
            );
        } elseif ($titleCategory === 'onTheAir') {
            $this->getSeriesAiringBetween(
                Carbon::now(),
                Carbon::now()->addWeek(),
            );
        } elseif ($titleCategory === 'airingToday') {
            return $this->getSeriesAiringBetween(
                Carbon::now(),
                Carbon::now()->addDay(),
            );
        } elseif ($titleCategory === 'latestVideos') {
            return $this->title
                ->join('videos', 'titles.id', '=', 'videos.title_id')
                ->where('videos.source', 'local')
                ->where('approved', true)
                ->orderBy('videos.created_at', 'desc')
                ->select('titles.*')
                ->distinct()
                ->limit(20)
                ->get();
        } elseif ($titleCategory === 'lastAdded') {
            return $this->title
                ->where('is_series', $titleType === Title::SERIES_TYPE)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        } elseif (Str::contains($titleCategory, ['keyword', 'genre'])) {
            [$_, $tagId] = explode(':', $titleCategory);
            $tagName = $this->tag->find($tagId)->name;
            $relation = Str::startsWith($titleCategory, 'keyword')
                ? 'keywords'
                : 'genres';
            $query = $this->title
                ->whereHas($relation, function (Builder $query) use ($tagName) {
                    $query->where('name', $tagName);
                })
                ->orderBy('popularity', 'desc')
                ->limit(40);
            if ($titleType !== '*') {
                $query->where('is_series', $titleType === Title::SERIES_TYPE);
            }
            return $query->get();
        }
    }

    private function getTopRatedTitles($type)
    {
        $ratingCol = config('common.site.rating_column');

        $query = $this->title->where('is_series', $type === Title::SERIES_TYPE);

        if (Str::contains($ratingCol, 'tmdb_vote_average')) {
            $query->orderBy(DB::raw('tmdb_vote_count > 100'), 'desc');
        }

        return $query
            ->orderBy($ratingCol, 'desc')
            ->limit(20)
            ->get();
    }

    private function getMoviesReleasingBetween($from, $to)
    {
        return $this->title
            ->whereBetween('release_date', [$from, $to])
            ->orderBy('popularity', 'desc')
            ->limit(20)
            ->where('is_series', false)
            ->get(['id', 'name']);
    }

    private function getSeriesAiringBetween($from, $to)
    {
        $titleIds = app(Episode::class)
            ->whereBetween('release_date', [$from, $to])
            ->limit(300)
            ->get(['title_id'])
            ->pluck('title_id')
            ->unique()
            ->slice(0, 20);

        return $this->title->whereIn('id', $titleIds)->get(['id', 'name']);
    }
}
