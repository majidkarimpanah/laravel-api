<?php

namespace App\Http\Controllers;

use App\Video;
use App\VideoRating;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use App\Services\Videos\RateVideo;

class VideoRatingController extends BaseController
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

    public function rate($videoId)
    {
        $this->authorize('rate', Video::class);

        $this->validate($this->request, [
            'rating' => 'required|in:positive,negative'
        ]);

        $video = app(RateVideo::class)->execute(
            $videoId,
            $this->request->get('rating'),
            $this->request->ip()
        );

        return $this->success(['video' => $video]);
    }
}
