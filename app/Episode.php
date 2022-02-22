<?php

namespace App;

use App\Services\Traits\HasCreditableRelation;
use Carbon\Carbon;
use Common\Comments\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;

/**
 * Class Episode
 * @property string $type;
 * @property int $episode_number;
 * @property int season_number
 * @property int title_id
 * @property Carbon $updated_at;
 * @method static Episode findOrFail($id, $columns = ['*'])
 */
class Episode extends Model
{
    use HasCreditableRelation;

    const MODEL_TYPE = 'episode';

    protected $guarded = ['id'];
    protected $appends = ['model_type', 'rating', 'vote_count'];
    protected $dates = ['release_date'];

    protected $casts = [
        'id' => 'integer',
        'episode_number' => 'integer',
        'season_number' => 'integer',
        'year' => 'integer',
        'title_id' => 'integer',
        'season_id' => 'integer',
        'allow_update' => 'boolean',
        'tmdb_vote_count' => 'integer',
        'popularity' => 'integer',
        'rating' => 'float',
        'vote_count' => 'integer',
    ];

    public $hidden = [
        'imdb_rating',
        'imdb_votes_num',
        'tmdb_vote_average',
        'local_vote_average',
        'local_vote_count',
        'tmdb_vote_count',
        'mc_user_score',
        'mc_critic_score',
    ];

    /**
     * @return float
     */
    public function getRatingAttribute()
    {
        return Arr::get($this->attributes, config('common.site.rating_column'));
    }

    public function getVoteCountAttribute()
    {
        $column = str_replace(
            '_average',
            '_count',
            config('common.site.rating_column'),
        );
        return Arr::get($this->attributes, $column) ?: 0;
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy(
            'created_at',
            'desc',
        );
    }

    public function stream_videos(): HasMany
    {
        return $this->hasMany(Video::class)
            ->where('approved', true)
            ->where('category', 'full')
            ->orderBy('order', 'asc');
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->poster,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
