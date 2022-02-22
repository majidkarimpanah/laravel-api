<?php

namespace App\Http\Controllers;

use App\Episode;
use App\Season;
use App\Title;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class SeasonController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var Episode
     */
    private $episode;

    /**
     * @var Season
     */
    private $season;

    /**
     * @param Request $request
     * @param Title $title
     * @param Episode $episode
     * @param Season $season
     */
    public function __construct(
        Request $request,
        Title $title,
        Episode $episode,
        Season $season
    )
    {
        $this->title = $title;
        $this->request = $request;
        $this->episode = $episode;
        $this->season = $season;
    }

    public function store($titleId)
    {
        $this->authorize('update', Title::class);

        /** @var Title $title */
        $title = $this->title->withCount('seasons')->findOrFail($titleId);

        $season = $title->seasons()->create([
            'number' => $title->seasons_count + 1,
            'episode_count' => 0,
        ]);

        // increment season_count on title
        $title->fill(['season_count' => $title->season_count + 1])->save();

        return $this->success(['season' => $season]);
    }

    public function destroy($seasonId)
    {
        $this->authorize('update', Title::class);

        $season = $this->season->findOrFail($seasonId);

        // decrement season_count on title
        $season->title->fill([
            'season_count' => $season->title->season_count - 1,
            'episode_count' => $season->title->episode_count - $season->episode_count
        ])->save();

        $this->episode->where('season_id', $seasonId)->delete();
        $season->delete();

        return $this->success();
    }
}
