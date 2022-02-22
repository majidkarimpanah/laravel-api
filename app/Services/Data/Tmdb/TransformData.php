<?php

namespace App\Services\Data\Tmdb;

use App\Episode;
use App\Person;
use App\Season;
use App\Services\Traits\HandlesTitleId;
use App\Title;
use App\Video;
use Carbon\Carbon;
use Common\Settings\Settings;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class TransformData
{
    use HandlesTitleId;

    const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/original';
    const BACKDROP_BASE_URI = 'https://image.tmdb.org/t/p/w1280';
    const PROFILE_BASE_URI = 'https://image.tmdb.org/t/p/w185';
    const YOUTUBE_BASE_URI = 'https://youtube.com/embed/';
    const SERIES_ENDED_STATUS = ['Ended', 'Canceled'];
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param array $tmdbMedia
     * @return Collection
     */
    public function execute($tmdbMedia)
    {
        return collect($tmdbMedia)->map(function ($mediaItem) {
            return $this->transformMediaItem($mediaItem);
        });
    }

    public function transformMediaItem($mediaItem)
    {
        $type = $this->getType($mediaItem);
        if ($type === Person::MODEL_TYPE) {
            return $this->transformPerson($mediaItem);
        } elseif ($type === Episode::MODEL_TYPE) {
            return $this->transformEpisode($mediaItem);
        } elseif ($type === Season::MODEL_TYPE) {
            return $this->transformSeason($mediaItem);
        } else {
            return $this->transformTitle($mediaItem, $type);
        }
    }

    private function transformTitle($data, $type)
    {
        $releaseKey =
            $type === Title::MOVIE_TYPE ? 'release_date' : 'first_air_date';
        $releaseDate = $this->getReleaseDate($releaseKey, $data);
        $year = $this->getYear($releaseDate);
        $name = $this->getTitle($data);

        $transformed = [
            'id' => $this->encodeId('tmdb', $type, $data['id']),
            'is_series' => $type === Title::SERIES_TYPE,
            'model_type' => Title::MODEL_TYPE,
            'poster' => $this->getPoster(Arr::get($data, 'poster_path')),
            'release_date' => $releaseDate,
            'cast' => $this->getCast($data),
            'name' => $name,
            'description' => $data['overview'],
            'tmdb_vote_count' => $data['vote_count'] ?: null,
            'tmdb_vote_average' => round($data['vote_average'], 1) ?: null,
            'year' => $year,
            'original_title' => $this->getOriginalName($data),
            'popularity' => Arr::get($data, 'popularity'),
            'language' => Arr::get($data, 'original_language'),
            'certification' => $this->getCertification($data, $type),
            'countries' => $this->getCountries($data),
            'tagline' => Arr::get($data, 'tagline'),
            'budget' => Arr::get($data, 'budget') ?: null,
            'revenue' => Arr::get($data, 'revenue') ?: null,
            'runtime' => $this->getRuntime($data),
            'videos' => $this->getVideos($data, $year, $name),
            'images' => $this->transformImages($data),
            'backdrop' => $this->getBackdrop($data),
            'genres' => $this->getGenres($data),
            'imdb_id' => Arr::get($data, 'external_ids.imdb_id') ?: null,
            'tmdb_id' => $data['id'],
            'keywords' => $this->getKeywords($data),
            'season_count' => Arr::get($data, 'number_of_seasons', 0),
            'episode_count' => Arr::get($data, 'number_of_episodes', 0),
            'series_ended' =>
                (bool) (array_search(
                    Arr::get($data, 'status'),
                    self::SERIES_ENDED_STATUS,
                ) !== false),
            'adult' => Arr::get($data, 'adult', false),
        ];

        if (Arr::get($data, 'seasons')) {
            $transformed['seasons'] = $this->getSeasons($data);
        }

        return $transformed;
    }

    /**
     * Get US certification for title.
     *
     * @param array $data
     * @param $type
     * @return string|null
     */
    private function getCertification($data, $type)
    {
        if ($type === Title::SERIES_TYPE) {
            $firstKey = 'content_ratings.results';
            $secondKey = 'rating';
        } else {
            $firstKey = 'release_dates.results';
            $secondKey = 'release_dates.*.certification';
        }

        $rating = collect(Arr::get($data, $firstKey, []))
            ->where('iso_3166_1', 'US')
            ->pluck($secondKey)
            ->flatten()
            ->filter()
            ->first();

        return $rating ? str_replace('tv-', '', strtolower($rating)) : null;
    }

    private function getCountries($data)
    {
        return array_map(function ($country) {
            return [
                'name' => strtolower($country['iso_3166_1']),
                'display_name' => $country['name'],
            ];
        }, Arr::get($data, 'production_countries', []));
    }

    private function transformSeason($data)
    {
        $releaseDate = $this->getReleaseDate('first_air_date', $data);

        return [
            'id' => $this->encodeId('tmdb', Season::MODEL_TYPE, $data['id']),
            'model_type' => Season::MODEL_TYPE,
            'poster' => $this->getPoster(Arr::get($data, 'poster_path')),
            'release_date' => $releaseDate,
            'cast' => $this->getCast($data),
            'number' => Arr::get($data, 'season_number'),
            'episodes' => array_map(function ($episode) {
                return $this->transformEpisode($episode);
            }, Arr::get($data, 'episodes', [])),
        ];
    }

    private function transformEpisode($data)
    {
        $releaseDate = $this->getReleaseDate('air_date', $data);
        $epNum = Arr::get($data, 'episode_number');
        $sNum = Arr::get($data, 'season_number');

        return [
            'id' => $this->encodeId('tmdb', Episode::MODEL_TYPE, $data['id']),
            'model_type' => Episode::MODEL_TYPE,
            'poster' => $this->getPoster(Arr::get($data, 'still_path')),
            'release_date' => $releaseDate,
            'cast' => $this->getCast($data),
            // episode might not have a name sometimes, auto generate it in that case
            'name' => $this->getTitle($data) ?? "Episode #$sNum.$epNum",
            'description' => $data['overview'],
            'tmdb_vote_count' => $data['vote_count'],
            'tmdb_vote_average' => round($data['vote_average'], 1) ?: null,
            'popularity' => Arr::get($data, 'popularity'),
            'year' => $this->getYear($releaseDate),
            'episode_number' => $epNum,
            'season_number' => $sNum,
        ];
    }

    /**
     * @param $path
     * @return null|string
     */
    private function getPoster($path)
    {
        return $path ? self::TMDB_IMAGE_BASE . $path : null;
    }

    private function getBackdrop($data)
    {
        $backdrop = Arr::get($data, 'backdrop_path');
        return $backdrop ? self::BACKDROP_BASE_URI . $backdrop : null;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getSeasons($data)
    {
        if (!Arr::has($data, 'seasons')) {
            return null;
        }

        // skip "specials" season with number of "0"
        $seasons = array_filter(Arr::get($data, 'seasons', []), function (
            $season
        ) {
            return $season['season_number'] !== 0;
        });

        return array_map(function ($season) {
            return [
                'release_date' => $season['air_date'],
                'episode_count' => $season['episode_count'],
                'poster' => $season['poster_path'],
                'number' => $season['season_number'],
            ];
        }, $seasons);
    }

    private function getKeywords(array $data): array
    {
        $keywords = array_merge(
            Arr::get($data, 'keywords.results', []),
            Arr::get($data, 'keywords.keywords', []),
        );

        return array_map(function ($keyword) {
            return ['name' => $keyword['name']];
        }, $keywords);
    }

    private function getRuntime(array $data): ?int
    {
        $runtime = Arr::get(
            $data,
            'runtime',
            Arr::get($data, 'episode_run_time'),
        );

        if (is_array($runtime)) {
            $runtime = !empty($runtime) ? min($runtime) : null;
        }

        return $runtime;
    }

    private function transformPerson($tmdbPerson)
    {
        if (!isset($tmdbPerson['id'])) {
            return null;
        }

        $syncCredits = $this->settings->get('content.automate_filmography');
        $hasCredits = Arr::has($tmdbPerson, 'combined_credits') && $syncCredits;
        $hasKnownForCredits =
            Arr::has($tmdbPerson, 'known_for') && $syncCredits;

        $taggedStills = Arr::get($tmdbPerson, 'tagged_images.results', []);
        $taggedStills = array_filter($taggedStills, function ($img) {
            return $img['image_type'] === 'still';
        });

        $data = [
            'id' => $this->encodeId(
                'tmdb',
                Person::MODEL_TYPE,
                $tmdbPerson['id'],
            ),
            'name' => $tmdbPerson['name'],
            'tmdb_id' => $tmdbPerson['id'],
            'imdb_id' => Arr::get($tmdbPerson, 'imdb_id'),
            'gender' => $this->transformGender(Arr::get($tmdbPerson, 'gender')),
            'poster' => $this->getPoster($tmdbPerson['profile_path']),
            //'images' => $this->transformImages($taggedStills),
            'model_type' => Person::MODEL_TYPE,
            'adult' => Arr::get($tmdbPerson, 'adult', false),
            'fully_synced' => Arr::get($tmdbPerson, 'fully_synced') ?: false,
            'relation_data' => [
                'character' => Arr::get($tmdbPerson, 'character') ?: null,
                'order' => Arr::get($tmdbPerson, 'order', 0),
                'department' => strtolower(
                    Arr::get($tmdbPerson, 'department', 'cast'),
                ),
                'job' => strtolower(Arr::get($tmdbPerson, 'job', 'cast')),
            ],
        ];

        // "known_for" credits will only be returned from "search" tmdb api call.
        if (!$hasCredits && $hasKnownForCredits) {
            $data['popular_credits'] = array_map(function ($credit) {
                return $this->transformMediaItem($credit);
            }, array_slice($tmdbPerson['known_for'], 0, 1));
        }

        if ($hasCredits) {
            $credits = array_merge(
                Arr::get($tmdbPerson, 'combined_credits.cast'),
                Arr::get($tmdbPerson, 'combined_credits.crew'),
            );

            $credits = array_map(function ($credit) {
                $title = array_filter(
                    $this->transformMediaItem($credit),
                    function ($value) {
                        return !is_array($value);
                    },
                );

                $title['relation_data'] = [
                    'department' => strtolower(
                        Arr::get($credit, 'department', 'cast'),
                    ),
                    'job' => strtolower(Arr::get($credit, 'job', 'cast')),
                    'character' => Arr::get($credit, 'character') ?: null,
                    'order' => Arr::get($credit, 'order', 0),
                ];

                return $title;
            }, $credits);

            $credits = array_filter($credits, function ($credit) {
                return !Arr::get($credit, 'adult') ||
                    $this->settings->get('tmdb.includeAdult');
            });

            $data['credits'] = $credits;
        }

        $optionalProps = [
            'biography' => 'description',
            'birthday' => 'birth_date',
            'deathday' => 'death_date',
            'place_of_birth' => 'birth_place',
            'known_for_department' => 'known_for',
            'popularity' => 'popularity',
        ];

        // can't set these as "null" as some data might not be contained
        // when getting people via movie/series filmography
        foreach ($optionalProps as $tmdbKey => $localKey) {
            if (Arr::has($tmdbPerson, $tmdbKey)) {
                $data[$localKey] = $tmdbPerson[$tmdbKey];
            }
        }

        return $data;
    }

    /**
     * @param array $tmdbTitle
     * @return array
     */
    private function getCast($tmdbTitle)
    {
        // cast/crew from series, movies and episodes
        $credits = array_merge(
            Arr::get($tmdbTitle, 'credits.cast', []),
            Arr::get($tmdbTitle, 'credits.crew', []),
            Arr::get($tmdbTitle, 'crew', []),
            Arr::get($tmdbTitle, 'guest_stars', []),
        );

        if ($createdBy = Arr::get($tmdbTitle, 'created_by')) {
            $creators = array_map(function ($person) {
                $person['job'] = 'creator';
                $person['department'] = 'creators';
                return $person;
            }, $createdBy);
            $credits = array_merge($credits, $creators);
        }

        $transformedCredits = array_map(function ($person) use ($tmdbTitle) {
            return $this->transformPerson($person);
        }, $credits);

        return array_filter($transformedCredits);
    }

    /**
     * @param int|null $gender
     * @return null|string
     */
    private function transformGender($gender)
    {
        if ($gender === 1) {
            return 'female';
        } elseif ($gender === 2) {
            return 'male';
        } else {
            return null;
        }
    }

    private function transformImages(array $tmdbTitle): array
    {
        $images = Arr::get($tmdbTitle, 'images.backdrops', []);

        return array_map(function ($image) {
            return [
                'type' => 'backdrop',
                'source' => 'tmdb',
                'url' => self::TMDB_IMAGE_BASE . $image['file_path'],
            ];
        }, $images);
    }

    /**
     * @param $tmdbTitle
     * @return array
     */
    private function getGenres($tmdbTitle)
    {
        return array_map(function ($genre) {
            return ['name' => $genre['name']];
        }, Arr::get($tmdbTitle, 'genres', []));
    }

    /**
     * @param array $tmdbTitle
     * @param string $titleName
     * @param int $year
     * @return array
     */
    private function getVideos($tmdbTitle, $year, $titleName)
    {
        $videos = Arr::get($tmdbTitle, 'videos.results', []);
        $videos = array_slice($videos, 0, 10);

        return array_map(function ($video) use ($titleName, $year) {
            return [
                'name' => trim($video['name']),
                'url' => self::YOUTUBE_BASE_URI . $video['key'],
                'type' => Video::VIDEO_TYPE_EMBED,
                'source' => 'tmdb',
                'category' => strtolower(Arr::get($video, 'type', 'trailer')),
            ];
        }, $videos);
    }

    /**
     * @param array $data
     * @return string
     */
    private function getType($data)
    {
        $hasSeasonNumber = Arr::get($data, 'season_number');
        $hasEpisodeNumber = Arr::get($data, 'episode_number');

        if ($hasEpisodeNumber && $hasSeasonNumber) {
            return Episode::MODEL_TYPE;
        } elseif ($hasSeasonNumber) {
            return Season::MODEL_TYPE;
        } elseif (
            Arr::get($data, 'media_type') === 'person' ||
            Arr::has($data, 'birthday')
        ) {
            return Person::MODEL_TYPE;
        } elseif (Arr::has($data, 'first_air_date')) {
            return Title::SERIES_TYPE;
        } else {
            return Title::MOVIE_TYPE;
        }
    }

    /**
     * @param Carbon $releaseDate
     * @return int|null
     */
    private function getYear($releaseDate)
    {
        if (!$releaseDate) {
            return null;
        }
        return $releaseDate->year;
    }

    private function getReleaseDate($key, $data)
    {
        if (!isset($data[$key])) {
            return null;
        }
        return Carbon::parse($data[$key]);
    }

    /**
     * @param array $tmdbTitle
     * @return string|null
     */
    private function getTitle($tmdbTitle)
    {
        if (isset($tmdbTitle['title'])) {
            return $tmdbTitle['title'];
        } elseif (isset($tmdbTitle['name'])) {
            return $tmdbTitle['name'];
        } else {
            return null;
        }
    }

    /**
     * @param array $tmdbTitle
     * @return string|null
     */
    private function getOriginalName($tmdbTitle)
    {
        if (isset($tmdbTitle['original_title'])) {
            return $tmdbTitle['original_title'];
        } elseif (isset($tmdbTitle['original_name'])) {
            return $tmdbTitle['original_name'];
        } else {
            return null;
        }
    }
}
