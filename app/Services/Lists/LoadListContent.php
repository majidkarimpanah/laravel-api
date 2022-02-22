<?php

namespace App\Services\Lists;

use App\Episode;
use App\Listable;
use App\ListModel;
use App\Person;
use App\Title;
use Arr;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Str;

class LoadListContent
{
    public function execute(ListModel $list, array $options = []): Collection
    {
        $items = collect();

        $pivot = app(Listable::class)
            ->where('list_id', $list->id)
            ->limit(Arr::get($options, 'limit', 500))
            ->orderBy('order', 'asc')
            ->get();

        if ($pivot->isNotEmpty()) {
            $items = $this->loadModels($pivot);
        }

        if (Arr::get($options, 'minimal')) {
            $items = $items->map(function ($item) {
                return ['id' => $item->id, 'type' => $item->model_type];
            });
        }

        return $items;
    }

    private function loadModels(Collection $pivot): Collection
    {
        $items = $pivot
            ->groupBy('listable_type')
            ->map(function (Collection $modelGroup, $model) {
                $builder = app($model)->whereIn(
                    'id',
                    $modelGroup->pluck('listable_id'),
                );

                if (
                    $model === Title::class &&
                    app(Settings::class)->get('streaming.show_label')
                ) {
                    $builder->withCount('stream_videos');
                }

                $items = $builder->get($this->getSelectFields($model));

                if ($model === Title::class) {
                    $preferFullVideos = app(Settings::class)->get(
                        'streaming.prefer_full',
                    );
                    $items->load([
                        'genres',
                        'videos' => function (HasMany $query) use (
                            $items,
                            $preferFullVideos
                        ) {
                            $query
                                ->where('type', '!=', 'external')
                                ->where(
                                    'category',
                                    $preferFullVideos ? '=' : '!=',
                                    'full',
                                )
                                ->groupBy('title_id');

                            if (!$preferFullVideos) {
                                $query->limit($items->count());
                            }
                        },
                    ]);
                }

                // TODO: loads all person credits, not just one, same on person index page
                // TODO: show known_for widget in landscape and portrait item as well
                // tODO: try https://github.com/staudenmeir/eloquent-eager-limit#readme
                if ($model === Person::class) {
                    $items->load('popularCredits');
                }

                return $items->map(function ($item) use ($modelGroup) {
                    $pivot = $modelGroup
                        ->first(function ($record) use ($item) {
                            return $record->listable_id === $item->id &&
                                $record->listable_type === get_class($item);
                        })
                        ->toArray();

                    $item['pivot'] = [
                        'id' => $pivot['id'],
                        'order' => $pivot['order'],
                        'created_at' => $pivot['created_at'],
                    ];
                    $item['description'] = Str::limit(
                        $item['description'],
                        600,
                    );
                    return $item;
                });
            })
            ->flatten();

        return $items->sortBy('pivot.order')->values();
    }

    private function getSelectFields(string $model): array
    {
        $fields = ['id', 'name', 'poster', 'description'];

        if ($model === Title::class) {
            $fields = array_merge($fields, [
                'is_series',
                'year',
                'tmdb_vote_average',
                'backdrop',
                'runtime',
                'release_date',
                'popularity',
                'local_vote_average',
                'tmdb_vote_average',
            ]);
        } elseif ($model === Person::class) {
            $fields = array_merge($fields, ['known_for']);
        } elseif ($model === Episode::class) {
            $fields = array_merge($fields, [
                'title_id',
                'season_number',
                'episode_number',
            ]);
        }

        return $fields;
    }
}
