<?php

namespace Database\Seeders;

use App\ListModel;
use App\User;
use Illuminate\Database\Seeder;

class WatchlistSeeder extends Seeder
{
    /**
     * Create watchlist for users that don't already have one.
     *
     * @return void
     */
    public function run()
    {
        $userIds = app(User::class)
            ->whereDoesntHave('watchlist')
            ->pluck('id');

        $userIds->each(function($userId) {
            app(ListModel::class)->create([
                'name' => 'watchlist',
                'user_id' => $userId,
                'system' => 1,
                'public' => 0,
            ]);
        });
    }
}
