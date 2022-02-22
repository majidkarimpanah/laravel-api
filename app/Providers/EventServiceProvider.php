<?php

namespace App\Providers;

use App\Listeners\CreateWatchlist;
use App\Listeners\DeleteUserLists;
use Common\Auth\Events\UserCreated;
use Common\Auth\Events\UsersDeleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserCreated::class => [
            CreateWatchlist::class,
        ],
        UsersDeleted::class => [
            DeleteUserLists::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
