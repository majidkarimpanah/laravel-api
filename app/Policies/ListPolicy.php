<?php

namespace App\Policies;

use App\ListModel;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Collection;

class ListPolicy
{
    use HandlesAuthorization;

    public function index(User $user, $userId = null)
    {
        return ($userId && $user->id === $userId) || $user->hasPermission('lists.view');
    }

    public function show(User $user, ListModel $list)
    {
        return $user->hasPermission('lists.view') ||
            $user->id === $list->user_id ||
            $list->public;
    }

    public function store(User $user)
    {
        return $user->hasPermission('lists.create');
    }

    public function update(User $user, ListModel $list)
    {
        if ($list->user_id === $user->id) {
            return true;
        }
        if (config('common.site.demo')) {
            abort(403, "You can't do that on demo site.");
        }
        return $user->hasPermission('lists.update');
    }

    public function destroy(User $user, Collection $lists)
    {
        if ($user->hasPermission('lists.delete')) return true;

        return $lists->every(function(ListModel $list) use($user) {
            return $list->user_id === $user->id;
        });
    }
}
