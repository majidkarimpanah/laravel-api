<?php

namespace App\Services\Reviews;

use DB;
use App\Review;

class UpdateReviewableAverageScore
{
    /**
     * @param int $id
     * @param string $type
     */
    public function execute($id, $type)
    {
        $votes = app(Review::class)
            ->where('type', Review::USER_REVIEW_TYPE)
            ->where('reviewable_type', $type)
            ->where('reviewable_id', $id)
            ->select(DB::raw('avg(`score`) as average'), DB::raw('count(*) as count'))
            ->first();

        $average = number_format((float) $votes['average'], 1);

        // title or episode
        $model = app($type)->find($id);
        $model->local_vote_average = $average;
        $model->local_vote_count = $votes['count'];
        $model->save();
    }
}
