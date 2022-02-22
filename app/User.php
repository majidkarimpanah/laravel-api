<?php

namespace App;

use Common\Auth\BaseUser;
use Common\Comments\Comment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read Collection|ListModel[] $watchlist
 */
class User extends BaseUser
{
    use HasApiTokens;

    public function watchlist(): HasOne
    {
        return $this->hasOne(ListModel::class)
            ->where('system', 1)
            ->where('name', 'watchlist');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(ListModel::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
