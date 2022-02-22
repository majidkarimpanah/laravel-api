<?php

namespace App\Listeners;

use App\ListModel;
use Common\Auth\Events\UserCreated;

class CreateWatchlist
{
    /**
     * @var ListModel
     */
    private $list;

    /**
     * @param ListModel $list
     */
    public function __construct(ListModel $list)
    {
        $this->list = $list;
    }

    /**
     * @param UserCreated $event
     */
    public function handle(UserCreated $event)
    {
        if ($event->user->watchlist) return;

        $this->list->create([
            'name' => 'watchlist',
            'user_id' => $event->user->id,
            'system' => 1,
            'public' => 0,
        ]);
    }
}
