<?php

namespace App\Services\News;

use App\NewsArticle;
use App\Services\Data\Contracts\NewsProviderInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportNewsFromRemoteProvider
{
    /**
     * @var NewsProviderInterface
     */
    private $newsProvider;

    /**
     * @var NewsArticle
     */
    private $newsArticle;

    /**
     * @param NewsProviderInterface $newsProvider
     * @param NewsArticle $newsArticle
     */
    public function __construct(
        NewsProviderInterface $newsProvider,
        NewsArticle $newsArticle
    ) {
        $this->newsProvider = $newsProvider;
        $this->newsArticle = $newsArticle;
    }

    public function execute()
    {
        $newArticles = $this->newsProvider->getArticles()->map(function($article) {
            $article['slug'] = slugify(Str::limit($article['title'], 50));
            $article['type'] = NewsArticle::PAGE_TYPE;
            $article['meta'] = json_encode($article['meta']);
            $article['created_at'] = Carbon::now();
            $article['updated_at'] = Carbon::now();
            return $article;
        });

        $existing = $this->newsArticle->whereIn('slug', $newArticles->pluck('slug'))->get();

        // filter out already existing articles
        $newArticles = $newArticles->filter(function($newArticle) use($existing) {
            return ! $existing->first(function($existingArticle) use($newArticle) {
                return $existingArticle['title'] === $newArticle['title'] || $existingArticle['slug'] === $newArticle['slug'];
            });
        })->unique('slug');

        $this->newsArticle->insert($newArticles->toArray());
    }
}
