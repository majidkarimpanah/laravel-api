<?php

namespace App\Services\Titles\Store;

use App\Season;
use App\Title;

class StoreSeasonData
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Season
     */
    private $season;

    public function __construct(Season $season)
    {
        $this->season = $season;
    }

    public function execute(Title $title, array $data): Title
    {
        if (empty($data)) {
            return $title;
        }

        $this->title = $title;

        $season = $this->persistData($data);
        app(StoreCredits::class)->execute($season, $data['cast']);

        if (isset($data['episodes'])) {
            app(StoreEpisodeData::class)->execute(
                $title,
                $season,
                $data['episodes'],
            );
        }

        return $this->title;
    }

    private function persistData(array $data): Season
    {
        // remove all relation data
        $data = array_filter($data, function ($value) {
            return !is_array($value) && $value !== Season::MODEL_TYPE;
        });

        // if season data did not change then timestamps
        // will not be updated because model is not dirty
        $data['updated_at'] = now();

        return $this->season->updateOrCreate(
            [
                'title_id' => $this->title->id,
                'number' => $data['number'],
            ],
            $data,
        );
    }
}
