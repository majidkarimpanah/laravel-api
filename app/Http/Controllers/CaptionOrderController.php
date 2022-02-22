<?php

namespace App\Http\Controllers;

use DB;
use App\Video;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;

class CaptionOrderController extends BaseController
{
    /**
     * @var Video
     */
    private $video;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Video $video
     * @param Request $request
     */
    public function __construct(Video $video, Request $request)
    {
        $this->video = $video;
        $this->request = $request;
    }

    /**
     * @param int $videoId
     * @return JsonResponse
     */
    public function changeOrder($videoId) {

        $video = $this->video->findOrFail($videoId);

        $this->authorize('update', $video);

        $this->validate($this->request, [
            'ids'   => 'array|min:1',
            'ids.*' => 'integer'
        ]);

        $queryPart = '';
        foreach($this->request->get('ids') as $order => $id) {
            $queryPart .= " when id=$id then $order";
        }

        DB::table('video_captions')
            ->whereIn('id', $this->request->get('ids'))
            ->where('video_id', $videoId)
            ->update(['order' => DB::raw("(case $queryPart end)")]);

        return $this->success();
    }
}
