<?php

namespace App;

use Common\Search\Searchable;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int positive_votes
 * @property int negative_votes
 * @property string name
 * @property string description
 * @property string thumbnail
 * @property int season_num
 * @property int episode_num
 * @property-read Episode episode
 */
class Video extends Model
{
    use Searchable;

    const VIDEO_TYPE_EMBED = 'embed';
    const VIDEO_TYPE_DIRECT = 'direct';
    const VIDEO_TYPE_EXTERNAL = 'external';
    const MODEL_TYPE = 'video';

    protected $guarded = ['id'];
    protected $appends = ['score', 'model_type'];
    protected $casts = [
        'negative_votes' => 'integer',
        'positive_votes' => 'integer',
        'order' => 'integer',
        'approved' => 'boolean',
        'reports' => 'integer',
        'title_id' => 'integer',
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function title()
    {
        return $this->belongsTo(Title::class);
    }

    public function ratings()
    {
        return $this->hasMany(VideoRating::class);
    }

    public function reports()
    {
        return $this->hasMany(VideoReport::class);
    }

    public function captions()
    {
        return $this->hasMany(VideoCaption::class)->orderBy('order', 'asc');
    }

    public function plays()
    {
        return $this->hasMany(VideoPlay::class);
    }

    public function latestPlay()
    {
        return $this->hasOne(VideoPlay::class)->orderBy('created_at', 'desc');
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public function getScoreAttribute()
    {
        $total = $this->positive_votes + $this->negative_votes;
        if (!$total) {
            return null;
        }
        return round(($this->positive_votes / $total) * 100);
    }

    public function scopeSelectScore(Builder $query)
    {
        return $query->select([
            '*',
            DB::raw('((positive_votes + 1.9208) / (positive_votes + negative_votes) -.96 * SQRT((positive_votes * negative_votes) / (positive_votes + negative_votes) + 0.9604) /
         (positive_votes + negative_votes)) / (1 + 3.8416 / (positive_votes + negative_votes))
         AS score'),
        ]);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
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
            'image' => $this->thumbnail,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
