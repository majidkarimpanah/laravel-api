<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonPolicy
{
    use HandlesAuthorization;
    
    public function index(User $user)
    {
        return $user->hasPermission('people.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('people.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('people.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('people.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('people.delete');
    }
}
