<?php

namespace App;

use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Caption
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Video video
 * @mixin Eloquent
 */
class VideoCaption extends Model
{
    protected $guarded = ['id'];

     protected $casts = [
         'id' => 'integer',
         'user_id' => 'integer',
         'video_id' => 'integer',
     ];

     public function video()
     {
         return $this->belongsTo(Video::class);
     }
}
