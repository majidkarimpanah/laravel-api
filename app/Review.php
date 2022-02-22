<?php

namespace App;

use Common\Search\Searchable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $user_id
 * @property string body
 * @property integer reviewable_id
 * @property string reviewable_type
 * @method static Review findOrFail($id, $columns = ['*'])
 */
class Review extends Model
{
    use Searchable;

    const USER_REVIEW_TYPE = 'user';
    const MODEL_TYPE = 'review';
    protected $guarded = ['id'];
    protected $appends = ['media_type', 'model_type'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'reviewable_id' => 'integer',
        'rating' => 'integer',
    ];

    public function getMediaTypeAttribute()
    {
        if ($this->attributes['reviewable_type'] === Episode::class) {
            return Episode::MODEL_TYPE;
        } else {
            return Title::MODEL_TYPE;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select(
            'id',
            'first_name',
            'last_name',
            'email',
            'avatar',
        );
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
