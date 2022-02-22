<?php

namespace App\Services\Titles\Retrieve;

use App\Episode;
use App\Services\Titles\Store\StoreTitleData;
use App\Title;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

class ShowTitle
{
    /**
     * @param int $id
     * @param array $params
     * @return array
     */
    public function execute($id, $params)
    {
        $title = app(FindOrCreateMediaItem::class)->execute(
            $id,
            Title::MODEL_TYPE,
        );

        if ($title->needsUpdating() && !Arr::get($params, 'skipUpdating')) {
            try {
                $data = Title::dataProvider()->getTitle($title);
                $title = app(StoreTitleData::class)->execute($title, $data);
            } catch (ClientException $e) {
                //
            }
        }

        if (isset($params['minimal'])) {
            return $title;
        }

        $title->load([
            'images',
            'genres',
            'seasons' => function (HasMany $query) {
                $query->select([
                    'seasons.id',
                    'number',
                    'episode_count',
                    'title_id',
                ]);
            },
        ]);

        $this->loadVideos($title, $params);
        $this->loadCredits($title, $params);

        if (Arr::get($params, 'keywords')) {
            $title->load('keywords');
        }

        if (Arr::get($params, 'countries')) {
            $title->load('countries');
        }

        if (Arr::get($params, 'seasons')) {
            $title->load('seasons.episodes', 'seasons.credits');
        }

        // load specified season
        if ($seasonNumber = Arr::get($params, 'seasonNumber')) {
            $season = app(LoadSeasonData::class)->execute(
                $title,
                $seasonNumber,
            );
            $title->setRelation('season', $season);
        }

        // load credits for specified episode
        if (
            isset($season) &&
            ($episodeNumber = (int) Arr::get($params, 'episodeNumber'))
        ) {
            $season->episodes
                ->first(function (Episode $episode) use ($episodeNumber) {
                    return $episodeNumber === $episode->episode_number;
                })
                ->load('credits');
        }

        $response = ['title' => $title];

        // load next and last episode to air
        if ($title->is_series && !$title->series_ended) {
            $episodes = $title->getLastAndNextEpisodes();
            if ($episodes && $episodes->count() === 2) {
                $response = array_merge($response, $episodes->toArray());
            }
        }

        return $response;
    }

    private function loadVideos(Title $title, $params)
    {
        $episode = (int) Arr::get($params, 'episodeNumber');
        $season = (int) Arr::get($params, 'seasonNumber');
        $allVideos = Arr::get($params, 'allVideos') ?: false;

        $title->load([
            'videos' => function (HasMany $query) use (
                $episode,
                $season,
                $allVideos
            ) {
                $query->with([
                    'captions',
                    'latestPlay' => function (HasOne $builder) {
                        $builder
                            ->forCurrentUser()
                            ->whereNotNull('time_watched')
                            ->select(['id', 'video_id', 'time_watched']);
                    },
                ]);

                // load all videos for admin without any constraints
                if ($allVideos) {
                    return;
                }

                $query->where('approved', true);

                // load for specific season
                if ($season) {
                    $query->where('season_num', $season);
                }

                // load for specific episode
                if ($episode) {
                    $query->where('episode_num', $episode);
                }

                // load only videos that are attached to main
                // title and not a particular episode
                if (!$season && !$episode) {
                    $query->whereNull('episode_num');
                }
            },
        ]);

        // make sure videos are under same key always
        if ($title->relationLoaded('stream_videos')) {
            $title->setRelation('videos', $title->stream_videos);
            $title->setRelation('stream_videos', []);
        }
    }

    private function loadCredits(Title $title, $params)
    {
        $fullCredits = Arr::get($params, 'fullCredits');

        $title->load([
            'credits' => function (MorphToMany $query) use ($fullCredits) {
                // load full credits if needed, based on query params
                if (!$fullCredits) {
                    $query
                        ->wherePivotIn('department', [
                            'cast',
                            'writing',
                            'directing',
                            'creators',
                        ])
                        ->groupBy(['name', 'department'])
                        ->limit(50);
                }
            },
        ]);

        if (!$fullCredits) {
            $this->filterCredits($title);
        }
    }

    private function filterCredits(Title $title)
    {
        $numOfDirectors = 0;
        $numOfWriters = 0;
        $numOfCast = 0;
        $filteredCredits = $title->credits
            ->filter(function ($credit) use (
                &$numOfCast,
                &$numOfWriters,
                &$numOfDirectors
            ) {
                if (
                    $credit['pivot']['department'] === 'cast' &&
                    $numOfCast < 15
                ) {
                    $numOfCast++;
                    return true;
                }
                if (
                    $credit['pivot']['department'] === 'writing' &&
                    $numOfWriters < 3
                ) {
                    $numOfWriters++;
                    return true;
                }
                if (
                    $credit['pivot']['job'] === 'director' &&
                    $numOfDirectors < 3
                ) {
                    $numOfDirectors++;
                    return true;
                }
                if ($credit['pivot']['department'] === 'creators') {
                    return true;
                }

                return false;
            })
            ->values();

        $title->setRelation('credits', $filteredCredits);
    }
}
