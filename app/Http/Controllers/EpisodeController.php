<?php

namespace App\Http\Controllers;

use App\Episode;
use App\Season;
use App\Title;
use Carbon\Carbon;
use Common\Core\BaseController;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EpisodeController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Episode
     */
    private $episode;

    /**
     * @var Season
     */
    private $season;

    public function __construct(
        Request $request,
        Episode $episode,
        Season $season
    )
    {
        $this->request = $request;
        $this->episode = $episode;
        $this->season = $season;
    }

    public function show($id)
    {
        $this->authorize('show', Title::class);

        $episode = $this->episode->with('credits')->findOrFail($id);

        return $this->success(['episode' => $episode]);
    }

    public function update($id)
    {
        $episode = $this->episode->findOrFail($id);

        $this->authorize('update', Title::class);

        $this->validate($this->request, [
            'episode_number' => [
                'integer',
                Rule::unique('episodes')
                    ->ignore($episode->episode_number, 'episode_number')
                    ->where(function (Builder $query) use($episode) {
                        $query->where('season_number', $episode->season_number)
                            ->where('title_id', $episode->title_id);
                    })
            ]
        ]);

        $episode->fill($this->request->all())->save();

        return $this->success(['episode' => $episode]);
    }

    public function store($seasonId)
    {
        $this->authorize('store', Title::class);

        $season = $this->season->withCount('episodes')->findOrFail($seasonId);

        $this->validate($this->request, [
            'episode_number' => [
                'integer',
                Rule::unique('episodes')
                    ->where(function (Builder $query) use($season) {
                        $query->where('season_number', $season->number)
                            ->where('title_id', $season->title_id);
                })
            ]
        ]);

        $epNum = $this->request->get('episode_number', $season->episode_count + 1);

        $episode = $this->episode->create(array_merge(
            $this->request->all(),
            [
                'season_number' => $season->number,
                'episode_number' => $epNum,
                'season_id' => $season->id,
                'title_id' => $season->title_id,
                'year' => Carbon::parse($this->request->get('release_date'))->year
            ]
        ));

        // increment episode_count on season
        $season->fill(['episode_count' => $epNum])->save();

        // increment episode_count on title
        $season->title->fill(['episode_count' => $epNum])->save();

        return $this->success(['episode' => $episode]);
    }

    public function destroy($id)
    {
        $this->authorize('destroy', Title::class);

        $episode = $this->episode->findOrFail($id);
        $episode->credits()->detach();
        // TODO: delete episode poster image
        $episode->delete();

        $episode->season()->decrement('episode_count');
        $episode->title()->decrement('episode_count');

        return $this->success();
    }
}
