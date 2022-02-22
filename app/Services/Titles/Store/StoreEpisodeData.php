<?php

namespace App\Services\Titles\Store;

use App\Episode;
use App\Person;
use App\Season;
use App\Services\Titles\Store\StoreCredits;
use App\Title;

class StoreEpisodeData
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var Episode
     */
    private $episode;

    /**
     * @param Person $person
     * @param Episode $episode
     */
    public function __construct(Person $person, Episode $episode)
    {
        $this->person = $person;
        $this->episode = $episode;
    }

    /**
     * @param Title $title
     * @param Season $season
     * @param array $episodes
     * @return Title
     */
    public function execute(Title $title, Season $season, $episodes)
    {
        // TODO: reduce number of queries here by doing one select and one detach query
        foreach ($episodes as $episodeData) {
            $episode = $this->persistData($title, $season, $episodeData);
            app(StoreCredits::class)->execute($episode, $episodeData['cast']);
        }

        return $this->title;
    }

    /**
     * @param Title $title
     * @param Season $season
     * @param array $episodeData
     * @return Episode
     */
    private function persistData(Title $title, Season $season, $episodeData)
    {
        $episodeData = array_filter($episodeData, function ($value) {
            return ! is_array($value) && $value !== Episode::MODEL_TYPE;
        });
        $episodeData['season_id'] = $season->id;

        return $this->episode->updateOrCreate(
            [
                'title_id' => $title->id,
                'episode_number' => $episodeData['episode_number'],
                'season_number' => $episodeData['season_number'],
            ],
            $episodeData
        );
    }
}
