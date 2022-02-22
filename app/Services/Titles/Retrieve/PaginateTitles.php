<?php

namespace App\Services\Titles\Retrieve;

use App\Title;
use Carbon\Carbon;
use Common\Database\Datasource\MysqlDataSource;
use Common\Settings\Settings;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaginateTitles
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Builder
     */
    private $builder;

    public function __construct(Title $title, Settings $settings)
    {
        $this->title = $title;
        $this->settings = $settings;
    }

    public function execute(array $params): LengthAwarePaginator
    {
        $this->builder = $this->title->newQuery();

        if (!$this->settings->get('tmdb.includeAdult')) {
            $this->builder->where('adult', false);
        }

        if ($this->settings->get('streaming.show_label')) {
            $this->builder->withCount('stream_videos');
        }

        if ($type = Arr::get($params, 'type')) {
            $this->builder->where('is_series', $type === Title::SERIES_TYPE);
        }

        if ($genre = Arr::get($params, 'genre')) {
            $genres = explode(',', $genre);
            $genres = array_map(function ($genre) {
                return slugify($genre);
            }, $genres);
            $this->builder->whereIn('titles.id', function (
                \Illuminate\Database\Query\Builder $query
            ) use ($genres) {
                $query
                    ->from('taggables')
                    ->where('taggables.taggable_type', Title::class)
                    ->join('tags', 'tags.id', '=', 'taggables.tag_id')
                    ->whereIn('tags.name', $genres)
                    ->select('taggables.taggable_id');
            });
        }

        if ($released = Arr::get($params, 'released')) {
            $this->byReleaseDate($released);
        }

        if ($runtime = Arr::get($params, 'runtime')) {
            $this->byRuntime($runtime);
        }

        if ($score = Arr::get($params, 'score')) {
            $this->byRating($score);
        }

        if ($language = Arr::get($params, 'language')) {
            $this->builder->where('language', $language);
        }

        if ($certification = Arr::get($params, 'certification')) {
            $this->builder->where('certification', $certification);
        }

        if ($country = Arr::get($params, 'country')) {
            $this->builder->whereHas('countries', function (
                Builder $query
            ) use ($country) {
                $query->where('name', $country);
            });
        }

        if (Arr::get($params, 'onlyStreamable') === 'true') {
            // $this->$this->builder->whereHas('stream_videos');
            $this->builder->whereIn('titles.id', function ($query) {
                $query
                    ->from('videos')
                    ->select('videos.title_id')
                    ->where('approved', true)
                    ->where('category', 'full');
            });
        }

        if (Arr::get($params, 'order')) {
            $params['order'] = str_replace(
                'user_score',
                config('common.site.rating_column'),
                $params['order'],
            );

            // show titles with less then 50 votes on tmdb last, regardless of their average
            if (Str::contains($params['order'], 'tmdb_vote_average')) {
                $this->builder->orderBy(
                    DB::raw('tmdb_vote_count > 100'),
                    'desc',
                );
            }
        } else {
            $params['order'] = 'popularity:desc';
        }

        $datasource = new MysqlDataSource($this->builder, $params);
        return $datasource->paginate();
    }

    private function byRuntime($runtimes)
    {
        $parts = explode(',', $runtimes);
        if (count($parts) !== 2) {
            return;
        }

        $this->builder
            ->where('runtime', '>=', $parts[0])
            ->where('runtime', '<=', $parts[1]);
    }

    private function byReleaseDate($dates)
    {
        $parts = explode(',', $dates);
        if (count($parts) !== 2) {
            return;
        }

        // convert year to full date, otherwise same year range would not work
        // 2019,2019 => 2019-01-01,2019-12-31
        $from = Carbon::create($parts[0])->firstOfYear();
        $to = Carbon::create($parts[1])->lastOfYear();

        $this->builder
            ->where('release_date', '>=', $from)
            ->where('release_date', '<=', $to);
    }

    private function byRating($scores)
    {
        $parts = explode(',', $scores);
        if (count($parts) !== 2) {
            return;
        }

        if (
            $this->settings->get('content.title_provider') !==
            TITLE::LOCAL_PROVIDER
        ) {
            $this->builder
                ->where('tmdb_vote_average', '>=', $parts[0])
                ->where('tmdb_vote_average', '<=', $parts[1])
                ->where('tmdb_vote_count', '>=', 50);
        } else {
            $this->builder
                ->where('local_vote_average', '>=', $parts[0])
                ->where('local_vote_average', '<=', $parts[1]);
        }
    }
}
