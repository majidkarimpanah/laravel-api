<?php

namespace App\Services\Traits;


trait ShowsDemoImages
{
    public function getPosterAttribute($value)
    {
        if (config('common.site.demo')) {
            $num = rand(1, 30);
            return url("demo-images/posters/{$num}.jpeg");
        } else {
            return $value;
        }
    }

    public function getUrlAttribute($value)
    {
        if (config('common.site.demo')) {
            $num = rand(1, 10);
            return url("demo-images/backdrops/{$num}.jpeg");
        } else {
            return $value;
        }
    }
}