<?php

namespace App\Services\Admin;

use App\Person;
use App\Title;
use App\User;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;

class GetAnalyticsHeaderData implements GetAnalyticsHeaderDataAction
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var Person
     */
    private $person;

    /**
     * @param Title $title
     * @param User $user
     * @param Person $person
     */
    public function __construct(Title $title, User $user, Person $person)
    {
        $this->user = $user;
        $this->title = $title;
        $this->person = $person;
    }

    public function execute($channel)
    {
        return [
            [
                'icon' => 'movie',
                'name' => 'Total Movies',
                'type' => 'number',
                'value' => $this->title->where('is_series', false)->count(),
            ],
            [
                'icon' => 'live-tv',
                'name' => 'Total Series',
                'type' => 'number',
                'value' => $this->title->where('is_series', true)->count(),
            ],
            [
                'icon' => 'recent-actors',
                'name' => 'Total People',
                'type' => 'number',
                'value' => $this->person->count(),
            ],
            [
                'icon' => 'people',
                'name' => 'Total Users',
                'type' => 'number',
                'value' => $this->user->count(),
            ],
        ];
    }
}