<?php

namespace App\Services\Titles\Retrieve;

use App\Episode;
use App\Season;
use App\Services\Titles\Store\StoreSeasonData;
use App\Title;
use Carbon\Carbon;
use Common\Settings\Settings;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoadSeasonData
{
    /**
     * @var Episode
     */
    private $episode;

    /**
     * @var Season
     */
    private $season;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Episode $episode, Season $season, Settings $settings)
    {
        $this->episode = $episode;
        $this->season = $season;
        $this->settings = $settings;
    }

    public function execute(Title $title, int $seasonNumber): Season
    {
        $season = $this->findSeason($title->id, $seasonNumber);

        if ($this->needsUpdating($title, $season)) {
            try {
                $data = Title::dataProvider(['forSeason' => true])
                    ->getSeason($title, $seasonNumber);
                app(StoreSeasonData::class)->execute($title, $data);
                $season = $this->findSeason($title->id, $seasonNumber);
            } catch (ClientException $e) {
                //
            }
        }

        $season->load('credits');
        return $season;
    }

    private function findSeason(int $titleId, int $seasonNumber): Season
    {
        return $this->season
            ->with(['episodes' => function(HasMany $builder) use($titleId, $seasonNumber) {
                if ($this->settings->get('streaming.show_label')) {
                    $builder->withCount('stream_videos');
                }
            }])
            ->where('title_id', $titleId)
            ->where('number', $seasonNumber)
            ->first();
    }

    public function needsUpdating(Title $title, Season $season): bool
    {
        // series ended and this season is already fully updated from external site
        if ($title->series_ended && $season->fully_synced) return false;

        // season is fully synced and it's not the latest season
        if ($season->fully_synced && $title->season_count > $season->number) return false;

        return !$season->updated_at || $season->updated_at->lessThan(Carbon::now()->subWeek());
    }
}
