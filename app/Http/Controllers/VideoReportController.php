<?php

namespace App\Http\Controllers;

use App\Video;
use App\VideoReport;
use Auth;
use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoReportController extends BaseController
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
     * @var VideoReport
     */
    private $videoReport;

    /**
     * @param Video $video
     * @param VideoReport $videoReport
     * @param Request $request
     */
    public function __construct(
        Video $video,
        VideoReport $videoReport,
        Request $request
    )
    {
        $this->video = $video;
        $this->request = $request;
        $this->videoReport = $videoReport;
    }

    /**
     * @param Video $video
     * @return JsonResponse
     */
    public function report(Video $video)
    {
        $userId = Auth::id();
        $userIp = $this->request->ip();

        // if we can't match current user, bail
        if ( ! $userId && ! $userIp) return null;

        $alreadyReported = $video->reports()
            ->where(function(Builder $query) use($userId, $userIp) {
                $query->where('user_id', $userId)->orWhere('user_ip', $userIp);
            })->first();

        if ($alreadyReported) {
            return $this->error(__('You have already reported this video.'));
        } else {
            $report = $video->reports()->create([
                'user_id' => $userId,
                'user_ip' => $userIp
            ]);
            return $this->success(['report' => $report]);
        }
    }
}
