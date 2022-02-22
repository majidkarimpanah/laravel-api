<?php

namespace App\Actions\Plays;

use App\Video;
use Common\Plays\LogModelPlay;
use Illuminate\Database\Eloquent\Model;

class LogVideoPlay extends LogModelPlay
{
    public function execute(Model $model)
    {
        if (request()->get('timeWatched')) {
            $this->updateTimeWatched($model);
        } else {
            $this->logVideoPlay($model);
        }
    }

    private function logVideoPlay(Video $video)
    {
        parent::execute($video);
    }

    private function updateTimeWatched(Video $video)
    {
        $lastPlay = $video
            ->plays()
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastPlay) {
            $lastPlay
                ->fill(['time_watched' => request()->get('timeWatched')])
                ->save();
        }
    }
}
