<?php namespace App\Services;

use App\Episode;
use App\NewsArticle;
use App\Person;
use App\Season;
use App\Title;
use Common\Core\Seo\BasePrerenderUtils;

class PrerenderUtils extends BasePrerenderUtils
{
    /**
     * Get site name setting.
     *
     * @return string
     */
    public function getSiteName()
    {
        return $this->settings->get('branding.site_name');
    }

    /**
     * @param array $movie
     * @return string
     */
    public function getMovieTitle($movie)
    {
        $title = $this->settings->get("seo.movie_title");
        $title = $this->replacePlaceholder('MOVIE_NAME', $movie['name'], $title);
        return  $this->replacePlaceholder('MOVIE_YEAR', $movie['year'], $title);
    }

    /**
     * @param array $series
     * @param $season
     * @return string
     */
    public function getSeasonTitle($series, $season)
    {
        $title = $this->settings->get("seo.season_title");
        $title = $this->replacePlaceholder('SERIES_NAME', $series['name'], $title);
        $title = $this->replacePlaceholder('SERIES_YEAR', $series['year'], $title);
        return  $this->replacePlaceholder('SEASON_NUMBER', $season['number'], $title);
    }

    /**
     * @param array $series
     * @param $episode
     * @return string
     */
    public function getEpisodeTitle($series, $episode)
    {
        $title = $this->settings->get("seo.episode_title");
        $title = $this->replacePlaceholder('SERIES_NAME', $series['name'], $title);
        $title = $this->replacePlaceholder('SERIES_YEAR', $series['year'], $title);
        return  $this->replacePlaceholder('EPISODE_NAME', $episode['name'], $title);
    }

    /**
     * Get absolute url for help center homepage.
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return url('help-center');
    }

    public function getMediaItemUrl($item)
    {
        if ($item['type'] === Title::MODEL_TYPE) {
            $base = 'titles';
        } else if ($item['type'] === Person::MODEL_TYPE) {
            $base = 'people';
        } else if ($item['type'] === NewsArticle::PAGE_TYPE) {
            $base = 'news';
        } else if ($item['type'] === Episode::MODEL_TYPE) {
            return url("titles/{$item['title_id']}/season/{$item['season_number']}/episode/{$item['episode_number']}");
        } else if ($item['type'] === Season::MODEL_TYPE) {
            return url("titles/{$item['title_id']}/season/{$item['number']}");
        }

        return url($base . '/' . $item['id']);

        //TODO: add get media image url method as well to mirror frontend
    }

    public function getMediaImage($item)
    {
        if (is_string($item)) {
            return $item;
        } else {
            return $item['poster'] ?: $item['url'];
        }
    }
}
