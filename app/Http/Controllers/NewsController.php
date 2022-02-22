<?php

namespace App\Http\Controllers;

use App\NewsArticle;
use App\Services\News\ImportNewsFromRemoteProvider;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var NewsArticle
     */
    private $article;

    public function __construct(Request $request, NewsArticle $article)
    {
        $this->request = $request;
        $this->article = $article;
    }

    public function index()
    {
        $this->authorize('show', NewsArticle::class);

        $paginator = new MysqlDataSource($this->article, $this->request->all());

        $pagination = $paginator->paginate();

        if ($this->request->get('stripHtml')) {
            $pagination
                ->map(function (NewsArticle $article) {
                    // remove html tags
                    $article->body = strip_tags($article->body);

                    // remove last "...see full article"
                    $parts = explode('...', $article->body);
                    if (
                        count($parts) > 1 &&
                        Str::contains(last($parts), 'See full article')
                    ) {
                        array_pop($parts);
                    }
                    $article->body = implode('', $parts);
                    return $article;
                })
                ->values();
        }

        return $this->success(['pagination' => $pagination]);
    }

    public function show($id)
    {
        $article = $this->article->findOrFail($id);

        $this->authorize('show', $article);

        return $this->success(['article' => $article]);
    }

    public function update($id)
    {
        $article = $this->article->findOrFail($id);

        $this->authorize('update', $article);

        $this->validate($this->request, [
            'title' => 'min:5|max:250',
            'body' => 'min:5',
            'image' => 'url',
        ]);

        $meta = $article->meta;

        if ($image = $this->request->get('image')) {
            $meta['image'] = $image;
        }

        $article
            ->fill([
                'title' => $this->request->get('title'),
                'body' => $this->request->get('body'),
                'meta' => $meta,
            ])
            ->save();

        return $this->success(['article' => $article]);
    }

    public function store()
    {
        $this->authorize('store', NewsArticle::class);

        $this->validate($this->request, [
            'title' => 'required|min:5|max:250',
            'body' => 'required|min:5',
            'image' => 'required|url',
        ]);

        $article = $this->article->create([
            'title' => $this->request->get('title'),
            'slug' => Str::limit($this->request->get('title'), 30),
            'body' => $this->request->get('body'),
            'meta' => ['image' => $this->request->get('image')],
            'type' => NewsArticle::PAGE_TYPE,
        ]);

        return $this->success(['article' => $article]);
    }

    public function destroy()
    {
        $this->authorize('destroy', NewsArticle::class);

        $this->validate($this->request, [
            'ids' => 'required|array',
        ]);

        $this->article->whereIn('id', $this->request->get('ids'))->delete();

        return $this->success();
    }

    /**
     * @return JsonResponse
     */
    public function importFromRemoteProvider()
    {
        $this->authorize('store', NewsArticle::class);

        app(ImportNewsFromRemoteProvider::class)->execute();

        return $this->success();
    }
}
