<?php

namespace App\Http\Controllers;

use App\Video;
use App\VideoRating;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use App\Services\Videos\RateVideo;

class VideoApproveController extends BaseController
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
     * @var VideoRating
     */
    private $videoRating;

    /**
     * @param Video $video
     * @param VideoRating $videoRating
     * @param Request $request
     */
    public function __construct(
        Video $video,
        VideoRating $videoRating,
        Request $request
    )
    {
        $this->video = $video;
        $this->request = $request;
        $this->videoRating = $videoRating;
    }

    public function approve(Video $video)
    {
        $this->authorize('update', $video);

        $video->update(['approved' => true]);

        return $this->success(['video' => $video]);
    }

    public function disapprove(Video $video)
    {
        $this->authorize('update', $video);

        $video->update(['approved' => false]);

        return $this->success(['video' => $video]);
    }
}
