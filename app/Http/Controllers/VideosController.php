<?php

namespace App\Http\Controllers;

use App\Actions\Plays\LogVideoPlay;
use App\Services\Videos\CrupdateVideo;
use App\Video;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class VideosController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Video
     */
    private $video;

    public function __construct(Request $request, Video $video)
    {
        $this->request = $request;
        $this->video = $video;
    }

    public function index()
    {
        $this->authorize('index', Video::class);

        $builder = $this->video
            ->with([
                'captions',
                'title' => function (BelongsTo $query) {
                    $query
                        ->with('seasons')
                        ->select(
                            'id',
                            'name',
                            'poster',
                            'backdrop',
                            'is_series',
                            'season_count',
                        );
                },
            ])
            ->withCount(['reports', 'plays']);

        if ($titleId = $this->request->get('titleId')) {
            $builder->where('title_id', $titleId);

            if ($episode = $this->request->get('episode')) {
                $builder->where('episode_num', $episode);
            }

            if ($season = $this->request->get('season')) {
                $builder->where('season_num', $season);
            }
        }

        if ($source = $this->request->get('source')) {
            $builder->where('source', $source);
        }

        if ($userId = $this->request->get('userId')) {
            $builder->where('user_id', $userId);
        }

        // order by percentage of likes, taking into account total amount of likes and dislikes
        if ($this->request->get('orderBy') === 'score') {
            $builder->selectScore();
        }

        $datasource = new MysqlDataSource($builder, $this->request->all());

        return $this->success(['pagination' => $datasource->paginate()]);
    }

    public function store()
    {
        $this->authorize('store', Video::class);

        $this->validate(
            $this->request,
            [
                'title_id' => 'required|integer',
                'name' => ['required', 'string', 'min:3', 'max:250'],
                'url' => 'required|max:1000',
                'type' => 'required|string|min:3|max:250',
                'category' => 'required|string|min:3|max:20',
                'quality' => 'nullable|string|min:2|max:250',
                'language' => 'required|nullable|string|max:10',
                'season' => 'nullable|integer',
                'episode' => 'requiredWith:season|integer|nullable',
            ],
            [
                'title_id.*' => __(
                    'Select a title this video should be attached to.',
                ),
            ],
        );

        $video = app(CrupdateVideo::class)->execute($this->request->all());

        return $this->success(['video' => $video]);
    }

    public function update($id)
    {
        $this->authorize('update', Video::class);

        $this->validate(
            $this->request,
            [
                'name' => 'string|min:3|max:250',
                'url' => 'required|max:1000',
                'type' => 'string|min:3|max:1000',
                'quality' => 'nullable|string|min:2|max:250',
                'language' => 'required|nullable|string|max:10',
                'title_id' => 'integer',
                'season' => 'nullable|integer',
                'episode' => 'requiredWith:season|integer|nullable',
            ],
            [
                'title_id.*' => __(
                    'Select a title this video should be attached to.',
                ),
            ],
        );

        $video = app(CrupdateVideo::class)->execute($this->request->all(), $id);

        return $this->success(['video' => $video]);
    }

    public function destroy($ids)
    {
        $ids = explode(',', $ids);
        $this->authorize('destroy', [Video::class, $ids]);

        foreach ($ids as $id) {
            $video = $this->video->find($id);
            if (is_null($video)) {
                continue;
            }

            $video->delete();
        }

        return $this->success();
    }

    public function logPlay(Video $video)
    {
        $this->authorize('show', Video::class);

        app(LogVideoPlay::class)->execute($video);

        return $this->success();
    }
}
