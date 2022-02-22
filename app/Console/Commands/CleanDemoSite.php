<?php

namespace App\Console\Commands;

use App\ListModel;
use App\User;
use Artisan;
use Common\Auth\Permissions\Permission;
use Common\Auth\Permissions\Traits\SyncsPermissions;
use Common\Localizations\Localization;
use Common\Settings\Settings;
use Hash;
use Illuminate\Console\Command;

class CleanDemoSite extends Command
{
    use SyncsPermissions;

    /**
     * @var string
     */
    protected $signature = 'demo:clean';

    /**
     * @var string
     */
    protected $description = 'Reset demo site.';

    /**
     * @var User
     */
    private $user;

    /**
     * @var ListModel
     */
    private $list;

    /**
     * @param User $user
     * @param ListModel $list
     */
    public function __construct(User $user, ListModel $list)
    {
        parent::__construct();
        $this->user = $user;
        $this->list = $list;
    }

    /**
     * @return void
     */
    public function handle()
    {
        // reset admin user
        $this->cleanAdminUser('admin@admin.com');

        // delete localizations
        app(Localization::class)->get()->each(function(Localization $localization) {
            if (strtolower($localization->name) !== 'english') {
                $localization->delete();
            }
        });

        if (env('RESET_HOMEPAGE_LISTS')) {
            $this->resetHomepageLists();
        }

        Artisan::call('lists:update');
    }

    private function resetHomepageLists()
    {
        // delete homepage lists
        $listUser = $this->user->find(496);
        $listUser->lists()->delete();

        // set auto-update of all lists to false
        app(ListModel::class)->whereNotNull('auto_update')->update(['auto_update' => null]);

        // create new homepage lists
        $lists = $listUser->lists()->createMany([
            [
                'name' => 'Trending Movies',
                'description' => 'Currently trending movies.',
                'auto_update' => 'movie:popular',
                'public' => true,
            ],
            [
                'name' => 'Now Playing',
                'description' => 'Movies that are currently playing in theaters.',
                'auto_update' => 'movie:nowPlaying',
                'public' => true,
            ],
            [
                'name' => 'Releasing Soon',
                'description' => 'Movies that will soon be playing in theaters.',
                'auto_update' => 'movie:upcoming',
                'public' => true,
            ],
            [
                'name' => 'Trending TV Shows',
                'description' => 'Currently trending TV shows.',
                'auto_update' => 'tv:popular',
                'public' => true,
            ],
            [
                'name' => 'Airing Today',
                'description' => 'TV Shows Airing Today.',
                'auto_update' => 'tv:airingToday',
                'public' => true,
            ],
        ]);

        // set IDs of new homepage lists
        app(Settings::class)->save([
            'homepage.lists' => $lists->pluck('id'),
        ]);
    }

    private function cleanAdminUser($email)
    {
        $admin = $this->user
            ->where('email', $email)
            ->first();

        if ( ! $admin) return;

        $admin->avatar = null;
        $admin->username = 'admin';
        $admin->first_name = 'Demo';
        $admin->last_name = 'Admin';
        $admin->password = Hash::make('admin');
        $admin->save();

        $adminPermission = app(Permission::class)->where('name', 'admin')->first();
        $this->syncPermissions($admin, [$adminPermission]);
    }
}
