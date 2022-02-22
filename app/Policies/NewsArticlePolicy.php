<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsArticlePolicy
{
    use HandlesAuthorization;

    public function index(User $user)
    {
        return $user->hasPermission('news.view');
    }

    public function show(User $user)
    {
        return $user->hasPermission('news.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('news.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('news.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('news.delete');
    }
}
