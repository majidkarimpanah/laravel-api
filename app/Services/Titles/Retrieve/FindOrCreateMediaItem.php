<?php

namespace App\Services\Titles\Retrieve;

use App\Person;
use App\Title;
use App\Services\Traits\HandlesTitleId;
use Illuminate\Support\Str;

class FindOrCreateMediaItem
{
    use HandlesTitleId;

    /**
     * @var Title|Person
     */
    private $model;

    /**
     * @param string $id external service or local db id
     * @param $type
     * @return Title|Person|null
     */
    public function execute($id, $type)
    {
        $this->initModel($type);

        // simple model ID
        if (is_numeric($id) || ctype_digit($id)) {
            return $this->model->findOrFail($id);

        // legacy ID (83-aquaman)
        } else if (Str::contains($id, '-')) {
            list($id, $name) = explode('-', $id, 2);
            $model = $this->model->findOrFail($id);
            // make sure slug in url matches model name
            if ($name !== slugify($model->name)) {
                return abort(404);
            }
            return $model;
        // external site id (tmdb|movie|55)
        } else {
            return $this->getByExternalId($id);
        }
    }

    /**
     * @param $encodedId
     * @return Title|Person
     */
    private function getByExternalId($encodedId)
    {
        list($provider, $type, $id) = array_values($this->decodeId($encodedId));
        if (!$provider || !$type || !$id) abort(404);

        if ($provider === 'tmdb') {
            $params = ['tmdb_id' => (int) $id];
            if ($this->model->model_type === Title::MODEL_TYPE) {
                $params['is_series'] = $type === Title::SERIES_TYPE;
            }
            $mediaItem = $this->model->firstOrCreate($params);
        }

        return $mediaItem;
    }

    private function initModel($type) {
        if ($type === Person::MODEL_TYPE) {
            $this->model = app(Person::class);
        } else {
            $this->model = app(Title::class);
        }
    }
}
