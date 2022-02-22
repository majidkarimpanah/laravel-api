<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $source
 * @property string $url
 */
class Image extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['model_id', 'model_type'];
}
