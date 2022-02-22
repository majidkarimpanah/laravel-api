<?php

namespace App;

use App\Services\Data\Contracts\DataProvider;
use App\Services\Data\Local\LocalDataProvider;
use App\Services\Data\Tmdb\TmdbApi;
use App\Services\Traits\HasCreditableRelation;
use Carbon\Carbon;
use Common\Comments\Comment;
use Common\Search\Searchable;
use Common\Settings\Settings;
use Common\Tags\Tag;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Str;

/**
 * Class Title
 * @property integer $id;
 * @property boolean $allow_update;
 * @property boolean $fully_synced;
 * @property string $model_type;
 * @property boolean $series_ended;
 * @property Carbon $updated_at;
 * @property Carbon $release_date;
 * @property-read Collection $keywords;
 * @property-read Collection $genres;
 * @property-read Collection $videos;
 * @property-read Collection $images;
 * @property-read Collection|Season[] $seasons;
 * @property-read int $seasons_count;
 * @property Season $season;
 * @property integer $season_count;
 * @property string|null $tmdb_id;
 * @method whereGenre(string|array $genres);
 */
class Title extends Model
{
    use HasCreditableRelation, Searchable;

    const MOVIE_TYPE = 'movie';
    const SERIES_TYPE = 'series';
    const MODEL_TYPE = 'title';
    const LOCAL_PROVIDER = 'local';

    protected $guarded = ['id', 'type'];
    protected $dates = ['release_date'];
    protected $appends = ['rating', 'model_type', 'vote_count'];

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

    protected $casts = [
        'id' => 'integer',
        'allow_update' => 'boolean',
        'series_ended' => 'boolean',
        'is_series' => 'boolean',
        'tmdb_vote_count' => 'integer',
        'runtime' => 'integer',
        'views' => 'integer',
        'season_count' => 'integer',
        'episode_count' => 'integer',
        'year' => 'integer',
        'popularity' => 'integer',
        'tmdb_vote_average' => 'float',
        'local_vote_average' => 'float',
        'fully_synced' => 'boolean',
        'adult' => 'boolean',
        'rating' => 'float',
        'vote_count' => 'integer',
        'show_videos' => 'boolean',
        'stream_videos_count' => 'integer',
    ];

    public function needsUpdating($forceAutomation = false)
    {
        // auto update disabled in settings
        if (
            !$forceAutomation &&
            app(Settings::class)->get('content.title_provider') ===
                Title::LOCAL_PROVIDER
        ) {
            return false;
        }

        // title was never synced from external site
        if (!$this->exists || ($this->allow_update && !$this->fully_synced)) {
            return true;
        }

        // series ended and fully synced
        //if ($this->series_ended) return false;

        if (!$this->release_date) {
            return true;
        }

        // title was released over a month ago, no need to sync anymore
        //if ($this->release_date->lessThan(Carbon::now()->subMonth())) return false;

        // only partial data was fetched
        if (
            !$this->runtime &&
            !$this->revenue &&
            !$this->country &&
            !$this->budget &&
            !$this->imdb_id
        ) {
            return true;
        }

        // sync every week
        return $this->allow_update &&
            $this->updated_at->lessThan(Carbon::now()->subWeek());
    }

    /**
     * @param Collection $titles
     * @param string $uniqueKey
     * @return Collection
     */
    public function insertOrRetrieve(Collection $titles, $uniqueKey)
    {
        // TODO: refactor this into a trait so can use in both title and person models
        // (and possible elsewhere (genres, tags) etc)

        // model "guarded" property will not work here, need to
        // manually unset props that have no corresponding db columns
        $titles = $titles->map(function ($value) {
            unset($value['relation_data']);
            unset($value['id']);
            unset($value['model_type']);
            return $value;
        });

        $existing = $this->whereIn($uniqueKey, $titles->pluck($uniqueKey))
            ->get()
            ->mapWithKeys(function ($title) use ($uniqueKey) {
                return [$title[$uniqueKey] => $title];
            });

        $new = $titles->filter(function ($title) use ($existing, $uniqueKey) {
            return !isset($existing[$title[$uniqueKey]]);
        });

        if ($new->isNotEmpty()) {
            $new->transform(function ($title) {
                $title['created_at'] = Arr::get(
                    $title,
                    'created_at',
                    Carbon::now(),
                );
                return $title;
            });
            $this->insert($new->toArray());
            return $this->whereIn(
                $uniqueKey,
                $titles->pluck($uniqueKey),
            )->get();
        } else {
            return $existing;
        }
    }

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

    public function setReleaseDateAttribute($value)
    {
        $this->attributes['release_date'] = $value;

        if ($this->release_date) {
            $this->attributes['year'] = $this->release_date->year;
        }
    }

    /**
     * @return BelongsToMany
     */
    public function genres()
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->where('tags.type', 'genre')
            ->select('tags.id', 'tags.type', 'name', 'display_name');
    }

    /**
     * @return BelongsToMany
     */
    public function keywords()
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->where('tags.type', 'keyword')
            ->select('tags.id', 'tags.type', 'name', 'display_name');
    }

    /**
     * @return BelongsToMany
     */
    public function countries()
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->where('tags.type', 'production_country')
            ->select('tags.id', 'tags.type', 'name', 'display_name');
    }

    /**
     * @return HasMany
     */
    public function videos()
    {
        [$col, $dir] = explode(
            ':',
            app(Settings::class)->get('streaming.default_sort', 'order:asc'),
        );

        $query = $this->hasMany(Video::class);
        if ($col === 'score') {
            $query->selectScore();
        } elseif ($col === 'order') {
            return $query->orderBy(DB::raw('`order` = 0, `order`'), $dir);
        } else {
            return $query->orderBy($col, $dir);
        }
    }

    public function stream_videos()
    {
        [$col, $dir] = explode(
            ':',
            app(Settings::class)->get('streaming.default_sort', 'order:asc'),
        );
        return $this->hasMany(Video::class)
            ->where('approved', true)
            ->where('category', 'full')
            ->orderBy($col, $dir);
    }

    /**
     * @return MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'model')
            ->select(['id', 'model_id', 'model_type', 'url', 'type', 'source'])
            ->orderBy('order', 'asc');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy(
            'created_at',
            'desc',
        );
    }

    /**
     * @return HasMany
     */
    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    /**
     * @return HasOne
     */
    public function season()
    {
        return $this->hasOne(Season::class);
    }

    /**
     * @return HasMany
     */
    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Get last and next episode to air for this series.
     *
     * @return Collection|Episode[]
     */
    public function getLastAndNextEpisodes()
    {
        $episodes = app(Episode::class)
            ->where('title_id', $this->id)
            ->where('season_number', $this->season_count)
            ->whereBetween('release_date', [
                Carbon::now()->subWeek(2),
                Carbon::now()->addWeek(2),
            ])
            ->whereNotNull('description')
            ->orderBy('release_date', 'desc')
            ->limit(6)
            ->get()
            ->sortBy('release_date')
            ->values();

        if (
            $episodes->count() < 2 ||
            (!$episodes[0]['poster'] && !$episodes[1]['poster'])
        ) {
            return null;
        }

        return collect([
            'next_episode' => $episodes[1],
            'current_episode' => $episodes[0],
        ]);
    }

    /**
     * @param array $params
     * @return DataProvider
     */
    public static function dataProvider($params = [])
    {
        $settings = app(Settings::class);

        // Fetch title seasons, even if automation is disabled because they can't be
        // fetched when importing multiple titles without hitting tmdb api rate limits
        if (
            Arr::get($params, 'forSeason') &&
            $settings->get('content.force_season_update')
        ) {
            return app(TmdbApi::class);
        }

        if (
            app(Settings::class)->get('content.title_provider') !==
            Title::LOCAL_PROVIDER
        ) {
            return app(TmdbApi::class);
        } else {
            return app(LocalDataProvider::class);
        }
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_title' => $this->original_title,
            'release_date' => $this->release_date,
            'popularity' => $this->popularity,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return ['id', 'created_at', 'updated_at', 'release_date', 'popularity'];
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => "$this->name ($this->year)",
            'description' => Str::limit($this->description, 100),
            'image' => $this->poster,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
