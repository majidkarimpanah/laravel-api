<?php

namespace App\Services\Data\Tmdb;

use App\Person;
use App\Services\Data\Contracts\DataProvider;
use App\Title;
use Arr;
use Carbon\Carbon;
use Common\Core\HttpClient;
use Common\Settings\Settings;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Str;

class TmdbApi implements DataProvider
{
    const TMDB_BASE = 'https://api.themoviedb.org/3/';
    const DEFAULT_TMDB_LANGUAGE = 'en';

    protected $includeAdult;
    protected $language;

    /**
     * @var HttpClient
     */
    protected $http;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->http = new HttpClient(['exceptions' => true]);
        $this->settings = $settings;

        $this->language = $this->settings->get(
            'tmdb.language',
            self::DEFAULT_TMDB_LANGUAGE,
        );
        $this->includeAdult = $this->settings->get('tmdb.includeAdult', false);
    }

    public function getPerson(Person $person)
    {
        $appends = ['images', 'tagged_images'];

        // only import filmography if it's set by user
        if ($this->settings->get('content.automate_filmography')) {
            $appends[] = 'combined_credits';
        }

        $response = $this->call("person/{$person->tmdb_id}", [
            'append_to_response' => implode(',', $appends),
        ]);
        $response['fully_synced'] = true;

        return app(TransformData::class)
            ->execute([$response])
            ->first();
    }

    public function getSeason(Title $title, $seasonNumber)
    {
        if (!$title->tmdb_id) {
            return [];
        }

        $response = $this->call("tv/{$title->tmdb_id}/season/{$seasonNumber}", [
            'append_to_response' => 'credits',
        ]);

        $data = app(TransformData::class)
            ->execute([$response])
            ->first();
        $data['fully_synced'] = true;

        return $data;
    }

    public function getTitle(Title $title)
    {
        if (!$title->tmdb_id) {
            return [];
        }

        $appends = [
            'credits',
            'external_ids',
            'images',
            'content_ratings',
            'keywords',
            'release_dates',
            'videos',
            'seasons',
        ];

        $uri = $title->is_series ? 'tv' : 'movie';

        $response = $this->call("$uri/{$title->tmdb_id}", [
            'append_to_response' => implode(',', $appends),
        ]);
        $data = app(TransformData::class)
            ->execute([$response])
            ->first();
        $data['fully_synced'] = true;
        return $data;
    }

    /**
     * @param $query
     * @param array $params
     * @return Collection
     */
    public function search($query, $params = [])
    {
        $response = $this->call('search/multi', ['query' => $query]);
        $results = app(TransformData::class)->execute($response['results']);

        $type = Arr::get($params, 'type');
        $limit = Arr::get($params, 'limit', 8);

        if ($type) {
            $results = $results->filter(function ($result) use ($type) {
                return $result['type'] === $type;
            });
        }

        return $results
            ->sortByDesc('popularity')
            ->slice(0, $limit)
            ->values();
    }

    public function getTitles($titleType, $titleCategory)
    {
        $titleCategory = Str::snake($titleCategory);
        $uri = $titleType . '/' . $titleCategory;

        switch ($uri) {
            case 'movie/popular':
                $from = Carbon::now()
                    ->subMonths(6)
                    ->format('Y-m-d');
                $titleFilters = [
                    'sort_by' => 'popularity.desc',
                    'primary_release_date.gte' => $from,
                ];
                break;
            case 'movie/top_rated':
                $titleFilters = [
                    'sort_by' => 'vote_average.desc',
                    'vote_count.gte' => 600,
                ];
                break;
            case 'movie/upcoming':
                $from = Carbon::now()
                    ->subDay()
                    ->format('Y-m-d');
                $to = Carbon::now()
                    ->addMonth()
                    ->format('Y-m-d');
                $titleFilters = [
                    'sort_by' => 'popularity.desc',
                    'with_release_type' => '2|3',
                    'primary_release_date.gte' => $from,
                    'primary_release_date.lte' => $to,
                ];
                break;
            case 'movie/now_playing':
                $from = Carbon::now()
                    ->subMonths(2)
                    ->format('Y-m-d');
                $to = Carbon::now()
                    ->subDays(2)
                    ->format('Y-m-d');
                $titleFilters = [
                    'sort_by' => 'popularity.desc',
                    'with_release_type' => '2|3',
                    'primary_release_date.gte' => $from,
                    'primary_release_date.lte' => $to,
                ];
                break;
            case 'tv/popular':
                $titleFilters = ['sort_by' => 'popularity.desc'];
                break;
            case 'tv/top_rated':
                $titleFilters = [
                    'sort_by' => 'vote_average.desc',
                    'vote_count.gte' => 600,
                ];
                break;
            case 'tv/on_the_air':
                $from = Carbon::now()
                    ->startOfDay()
                    ->format('Y-m-d');
                $to = Carbon::now()
                    ->startOfDay()
                    ->addDays(6)
                    ->format('Y-m-d');
                $titleFilters = [
                    'sort_by' => 'popularity.desc',
                    'air_date.gte' => $from,
                    'air_date.lte' => $to,
                ];
                break;
            case 'tv/airing_today':
                $from = Carbon::now()
                    ->startOfDay()
                    ->format('Y-m-d');
                $to = Carbon::now()
                    ->endOfDay()
                    ->format('Y-m-d');
                $titleFilters = [
                    'sort_by' => 'popularity.desc',
                    'air_date.gte' => $from,
                    'air_date.lte' => $to,
                ];
                break;
            default:
                Log::error(
                    "Trying to fetch titles from '$uri', but this uri is not valid.",
                );
        }
        if (isset($titleFilters)) {
            return $this->browse(1, $titleType, $titleFilters)['results'];
        }

        return [];
    }

    public function browse($page = 1, $type = 'movie', $queryParams = [])
    {
        if ($page > 500) {
            throw new Exception('Maximum page is 500');
        }

        $apiParams = array_merge(
            ['sort_by' => 'popularity.desc', 'page' => $page],
            $queryParams,
        );

        $response = $this->call("discover/$type", $apiParams);
        $response['results'] = app(TransformData::class)->execute(
            $response['results'],
        );

        return $response;
    }

    /**
     * @param string $uri
     * @param array $queryParams
     * @return array
     */
    protected function call($uri, $queryParams = [])
    {
        $key = config('services.tmdb.key');
        $url = self::TMDB_BASE . "$uri?api_key=$key";

        $queryParams = array_merge($queryParams, [
            // need to send "true" and not "1" otherwise tmdb will not work
            'include_adult' => $this->includeAdult ? 'true' : 'false',
            'language' => $this->language,
            'region' => 'US',
            'include_image_language' => 'en,null',
        ]);
        $url .= '&' . urldecode(http_build_query($queryParams));
        return $this->http->get($url, [
            'verify' => false,
        ]);
    }
}
