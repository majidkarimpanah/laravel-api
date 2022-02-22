<?php

namespace App;

use App\Services\Data\Contracts\DataProvider;
use App\Services\Data\Local\LocalDataProvider;
use App\Services\Data\Tmdb\TmdbApi;
use Carbon\Carbon;
use Common\Search\Searchable;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @property boolean $allow_update;
 * @property boolean $fully_synced;
 * @property integer $tmdb_id;
 * @property Carbon $updated_at;
 * @property-read Collection|Title[] $credits;
 * @property string known_for
 * @property string description
 * @method static Person findOrFail($id, $columns = ['*'])
 */
class Person extends Model
{
    use Searchable;

    const MODEL_TYPE = 'person';

    protected $guarded = ['id', 'relation_data', 'model_type'];
    protected $appends = ['model_type'];

    protected $casts = [
        'id' => 'integer',
        'tmdb_id' => 'integer',
        'allow_update' => 'boolean',
        'fully_synced' => 'boolean',
        'adult' => 'boolean',
    ];

    /**
     * @param Collection $people
     * @param string $uniqueKey
     * @return Collection
     */
    public function insertOrRetrieve(Collection $people, $uniqueKey)
    {
        $people = $people->map(function ($value) {
            unset($value['relation_data']);
            unset($value['model_type']);
            unset($value['id']);
            return $value;
        });

        $existing = $this->whereIn($uniqueKey, $people->pluck($uniqueKey))
            ->get()
            ->mapWithKeys(function ($person) use ($uniqueKey) {
                return [$person[$uniqueKey] => $person];
            });

        $new = $people->filter(function ($person) use ($existing, $uniqueKey) {
            return !isset($existing[$person[$uniqueKey]]);
        });

        if ($new->isNotEmpty()) {
            $new->transform(function ($person) {
                $person['created_at'] = Arr::get(
                    $person,
                    'created_at',
                    Carbon::now(),
                );
                return $person;
            });
            $this->insert($new->toArray());
            return $this->whereIn(
                $uniqueKey,
                $people->pluck($uniqueKey),
            )->get();
        } else {
            return $existing;
        }
    }

    public function needsUpdating($forceAutomation = false)
    {
        // auto update disabled in settings
        if (
            !$forceAutomation &&
            app(Settings::class)->get('content.people_provider') ===
                Title::LOCAL_PROVIDER
        ) {
            return false;
        }

        // person was never synced from external site
        if (!$this->exists || ($this->allow_update && !$this->fully_synced)) {
            return true;
        }

        // sync every week
        return $this->allow_update &&
            $this->updated_at->lessThan(Carbon::now()->subWeek());
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }

    /**
     * @return BelongsToMany
     */
    public function credits()
    {
        return $this->morphedByMany(Title::class, 'creditable')
            ->select(
                'titles.id',
                'is_series',
                'poster',
                'backdrop',
                'popularity',
                'name',
                'year',
            )
            ->withPivot(['id', 'job', 'department', 'order', 'character'])
            ->orderBy('titles.year', 'desc')
            ->where('titles.adult', 0);
    }

    public function popularCredits(): BelongsToMany
    {
        return $this->morphedByMany(Title::class, 'creditable')
            ->select('titles.id', 'is_series', 'name', 'year')
            ->orderBy('titles.popularity', 'desc')
            ->where('titles.adult', 0);
    }

    /**
     * @param int|null $tileId
     * @return BelongsToMany
     */
    public function episodeCredits($tileId = null)
    {
        $query = $this->morphedByMany(Episode::class, 'creditable');
        if ($tileId) {
            $query->where('episodes.title_id', $tileId);
        }
        $query
            ->select(
                'episodes.id',
                'episodes.title_id',
                'name',
                'year',
                'season_number',
                'episode_number',
            )
            ->withPivot(['job', 'department', 'order', 'character'])
            ->orderBy('episodes.season_number', 'desc')
            ->orderBy('episodes.episode_number', 'desc');
        return $query;
    }

    /**
     * @param int|null $tileId
     * @return BelongsToMany
     */
    public function seasonCredits($tileId = null)
    {
        $query = $this->morphedByMany(Season::class, 'creditable');
        if ($tileId) {
            $query->where('seasons.title_id', $tileId);
        }
        $query
            ->select('seasons.id', 'seasons.title_id')
            ->withPivot(['job', 'department', 'order', 'character'])
            ->orderBy('seasons.number', 'desc');
        return $query;
    }

    /**
     * @return DataProvider
     */
    public static function dataProvider()
    {
        if (
            app(Settings::class)->get('content.people_provider') !==
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
            'image' => $this->poster,
            'model_type' => self::MODEL_TYPE,
        ];
    }
}
