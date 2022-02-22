<?php

use App\ListModel;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;

class CreateWatchlistForExistingUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::with('watchlist')
            ->chunkById(50, function (Collection $users) {
                $records = $users->filter(function(User $user) {
                    return !$user->watchlist;
                })->map(function(User $user) {
                    return [
                        'name' => 'watchlist',
                        'user_id' => $user->id,
                        'system' => 1,
                        'public' => 0,
                    ];
                });

                if ($records->isNotEmpty()) {
                    ListModel::insert($records->toArray());
                }
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
