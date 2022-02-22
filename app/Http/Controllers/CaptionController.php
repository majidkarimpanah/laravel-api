<?php

namespace App\Http\Controllers;

use App\Actions\Caption\CrupdateCaption;
use App\Http\Requests\CrupdateCaptionRequest;
use App\Video;
use App\VideoCaption;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaptionController extends BaseController
{
    /**
     * @var VideoCaption
     */
    private $caption;

    /**
     * @var Request
     */
    private $request;

    public function __construct(VideoCaption $caption, Request $request)
    {
        $this->caption = $caption;
        $this->request = $request;
    }

    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [Video::class, $userId]);

        $paginator = new Paginator($this->caption, $this->request->all());

        if ($userId = $paginator->param('userId')) {
            $paginator->where('user_id', $userId);
        }

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @param VideoCaption $caption
     * @return StreamedResponse
     */
    public function show(VideoCaption $caption)
    {
        $this->authorize('show', $caption->video);

        $fs = Storage::drive(config('common.site.uploads_disk'));
        $path = "captions/{$caption->hash}";
        $stream = $fs->readStream($path);

        return \Response::stream(function() use($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => "text/vtt",
            "Content-Length" => $fs->getSize($path),
            "Content-disposition" => "inline; filename=\"" . $caption->hash . "\"",
        ]);
    }

    /**
     * @param CrupdateCaptionRequest $request
     * @return Response
     */
    public function store(CrupdateCaptionRequest $request)
    {
        $this->authorize('store', Video::class);

        $caption = app(CrupdateCaption::class)->execute($request->all());

        return $this->success(['caption' => $caption]);
    }

    /**
     * @param VideoCaption $caption
     * @param CrupdateCaptionRequest $request
     * @return Response
     */
    public function update(VideoCaption $caption, CrupdateCaptionRequest $request)
    {
        $this->authorize('store', $caption->video);

        $caption = app(CrupdateCaption::class)->execute($request->all(), $caption);

        return $this->success(['caption' => $caption]);
    }

    /**
     * @param string $ids
     * @return Response
     */
    public function destroy($ids)
    {
        $captionIds = explode(',', $ids);

        $captions = $this->caption
            ->whereIn('id', $captionIds)
            ->get();

        $this->authorize('store', [Video::class, $captions->pluck('video_id')]);

        // delete caption files
        $paths = $captions
            ->pluck('hash')
            ->map(function($hash) {
                return "captions/$hash";
            });
        $disk = Storage::disk(config('common.site.uploads_disk'));
        $disk->delete($paths->toArray());

        // delete caption models
        $this->caption->whereIn('id', $captionIds)->delete();

        return $this->success();
    }
}
