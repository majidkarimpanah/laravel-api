<?php

namespace App\Services\Videos;

use App\Video;
use App\VideoRating;
use Auth;
use Illuminate\Database\Eloquent\Builder;

class RateVideo
{
    /**
     * @var Video
     */
    private $video;

    /**
     * @param Video $video
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * @param int $videoId
     * @param string $newRating
     * @param string $userIp
     * @return Video
     */
    public function execute($videoId, $newRating, $userIp)
    {
        $userId = Auth::id();

        // if we can't match current user, bail
        if ( ! $userId && ! $userIp) return null;

        $video = $this->video->findOrFail($videoId);

        $videoRating = $video->ratings()
            ->where(function(Builder $query) use($userId, $userIp) {
                $query->where('user_id', $userId)->orWhere('user_ip', $userIp);
            })->first();

        // remove old rating from this user
        if ($videoRating) {
            $this->removeRating($video, $videoRating);
        }

        // create a new rating
        if ( ! $videoRating || $videoRating->rating !== $newRating) {
            $this->createRating($video, $newRating, $userId, $userIp);
        }

        return $video;
    }

    /**
     * Delete VideoRating and decrement matching votes column on video.
     *
     * @param Video $video
     * @param VideoRating $videoRating
     */
    private function removeRating(Video $video, VideoRating $videoRating)
    {
        $column = $videoRating->rating . '_votes';
        if ($video->$column > 0) {
            $video->decrement($column);
        }
        $videoRating->delete();
    }

    /**
     * Create VideoRating and increment matching votes column on video.
     *
     * @param Video $video
     * @param string $rating
     * @param int $userId
     * @param string $userIp
     */
    private function createRating(Video $video, $rating, $userId, $userIp)
    {
        $video->ratings()->create([
            'rating' => $rating,
            'user_id' => $userId,
            'user_ip' => $userIp
        ]);

        $video->increment($rating . '_votes', 1);
    }
}