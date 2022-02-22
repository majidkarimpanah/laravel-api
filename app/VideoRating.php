<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoRating extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer'
    ];
    public $timestamps = false;
}
