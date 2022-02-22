<?php

namespace App;

use App\Services\Data\Contracts\DataProvider;
use App\Services\Data\Local\LocalDataProvider;
use App\Services\Data\Tmdb\TmdbApi;
use Arr;
use Carbon\Carbon;
use Common\Search\Searchable;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property int $id
 * @property boolean $system
 * @property string $auto_update
 * @property boolean public
 * @method static ListModel findOrFail($id, $columns = ['*'])
 */
class ListModel extends Model
{
    use Searchable;

    const MODEL_TYPE = 'list';

    protected $table = 'lists';
    protected $guarded = ['id'];
    protected $hidden = ['pivot'];
    protected $appends = ['model_type'];
    protected $casts = [
        'id' => 'integer',
        'system' => 'boolean',
        'public' => 'boolean',
        'user_id' => 'integer',
    ];

    public function updateImage()
    {
        if ($this->items->isNotEmpty()) {
            $this->image = $this->items->first()->poster;
            $this->save();
        }
    }

    /**
     * @param array $items
     */
    public function attachItems($items)
    {
        if (empty($items)) {
            return;
        }

        $listables = collect($items)->map(function ($item, $key) {
            return [
                'list_id' => $this->id,
                'listable_id' => $item['id'],
                'listable_type' => $this->getListableType($item['type']),
                'created_at' => Carbon::now(),
                'order' => $key,
            ];
        });

        app(Listable::class)->insert($listables->toArray());
        $this->updateImage();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->morphedByMany(Title::class, 'listable', null, 'list_id');
    }

    /**
     * @param string $type
     * @return string
     */
    public function getListableType($type)
    {
        switch ($type) {
            case Title::MOVIE_TYPE:
            case Title::SERIES_TYPE:
            case Title::MODEL_TYPE:
                return Title::class;
            case Person::MODEL_TYPE:
                return Person::class;
            case Episode::MODEL_TYPE:
                return Episode::class;
        }
    }

    /**
     * @param array $params
     * @return DataProvider
     */
    public static function dataProvider($params = [])
    {
        $localCategories = ['latestVideos', 'lastAdded', 'lastAdded'];

        if (in_array(Arr::get($params, 'category'), $localCategories)) {
            return app(LocalDataProvider::class);
        }

        if (
            app(Settings::class)->get('content.list_provider') ===
            Title::LOCAL_PROVIDER
        ) {
            return app(LocalDataProvider::class);
        } else {
            return app(TmdbApi::class);
        }
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
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
            'description' => $this->description,
            'image' => $this->image,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
