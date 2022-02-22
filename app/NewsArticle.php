<?php

namespace App;

use Common\Pages\CustomPage;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $body
 * @property array $meta
 */
class NewsArticle extends CustomPage
{
    const PAGE_TYPE = 'news_article';
    const MODEL_TYPE = 'newsArticle';

    protected $table = 'custom_pages';
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('pageType', function (Builder $builder) {
            $builder->where('type', self::PAGE_TYPE);
        });
    }

    public function getMetaAttribute()
    {
        return json_decode($this->attributes['meta'], true);
    }

    public function setMetaAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['meta'] = json_encode($value);
        }
    }
}
