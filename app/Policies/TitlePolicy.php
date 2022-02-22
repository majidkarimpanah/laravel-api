<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TitlePolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('titles.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('titles.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('titles.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('titles.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('titles.delete');
    }
}
