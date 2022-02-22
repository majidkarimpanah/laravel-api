<?php

namespace App\Services\Traits;

use App\Person;
use App\Title;
use Illuminate\Support\Collection;

trait StoresMediaImages
{
    /**
     * @param array $values
     * @param Title|Person $model
     */
    public function storeImages($values, $model)
    {
        $values = array_map(function($value) use($model) {
            $value['model_id'] = $model->id;
            $value['model_type'] = get_class($model);
            return $value;
        }, $values);

        $model->images()->where('source', '!=', 'local')->delete();
        $model->images()->insert($values);
    }
}