<?php

namespace App\Listeners;

use App\ListModel;
use App\Services\Lists\DeleteLists;
use Common\Auth\Events\UserCreated;
use Common\Auth\Events\UsersDeleted;

class DeleteUserLists
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
     * @param UsersDeleted $event
     */
    public function handle(UsersDeleted $event)
    {
        $listIds = $this->list->whereIn('user_id', $event->users->pluck('id'))->pluck('id');
        app(DeleteLists::class)->execute($listIds);
    }
}
