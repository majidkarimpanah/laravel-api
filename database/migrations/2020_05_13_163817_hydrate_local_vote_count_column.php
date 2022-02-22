<?php

use App\Episode;
use App\Review;
use App\Title;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;

class HydrateLocalVoteCountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Review::where('reviewable_type', Title::class)
            ->where('type', Review::USER_REVIEW_TYPE)
            ->select('reviewable_id', 'reviewable_type', DB::raw('count(*) as review_count'))
            ->groupBy('reviewable_id')
            ->chunk(500, function(Collection $counts) {
                $counts->each(function($count) {
                    Title::where('id', $count['reviewable_id'])->update(['local_vote_count' => $count['review_count']]);
                });
            });

        Review::where('reviewable_type', Episode::class)
            ->where('type', Review::USER_REVIEW_TYPE)
            ->select('reviewable_id', 'reviewable_type', DB::raw('count(*) as review_count'))
            ->groupBy('reviewable_id')
            ->chunk(500, function(Collection $counts) {
                $counts->each(function($count) {
                    Episode::where('id', $count['reviewable_id'])->update(['local_vote_count' => $count['review_count']]);
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
