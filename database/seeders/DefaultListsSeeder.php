<?php

namespace Database\Seeders;

use App\ListModel;
use App\User;
use Common\Settings\Settings;
use Illuminate\Database\Seeder;

class DefaultListsSeeder extends Seeder
{
    /**
     * @var ListModel
     */
    private $list;

    /**
     * @var User
     */
    private $user;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param ListModel $list
     * @param User $user
     * @param Settings $settings
     */
    public function __construct(ListModel $list, User $user, Settings $settings)
    {
        $this->list = $list;
        $this->user = $user;

        $admin = $this->user->where('email', 'Ic0OdCIodqz8q1r@demo.com')->first();
        $this->userId = $admin ? $admin->id : 1;
        $this->settings = $settings;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // top movies
        $this->createList([
            'name' => 'Highest Rated Movies',
            'description' => 'Movies with highest user rating.',
        ]);

        // top series
        $this->createList([
            'name' => 'Highest Rated TV Shows',
            'description' => 'TV Shows with highest user rating.',
        ]);

        // coming soon
        $this->createList([
            'name' => 'Coming Soon',
            'description' => 'Movies that will soon release in theaters.',
        ]);

        // now playing
        $this->createList([
            'name' => 'Now Playing',
            'description' => 'Movies that are currently playing in theaters.',
        ]);

        if ( ! $this->settings->get('homepage.lists')) {
            $this->settings->save([
                'homepage.lists' => json_encode([1, 2, 3, 4]),
            ]);
        }
    }

    private function createList($params)
    {
        $this->list->firstOrCreate([
            'name' => $params['name']
        ], array_merge([
            'user_id' => $this->userId,
            'public' => 1,
            'auto_update' => 'movie:topRated'
        ], $params));
    }
}
