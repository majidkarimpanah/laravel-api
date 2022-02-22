<?php

namespace App\Http\Controllers;

use App\Services\Titles\Retrieve\GetRelatedTitles;
use App\Title;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelatedTitlesController extends BaseController
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     * @param Title $title
     */
    public function __construct(Request $request, Title $title)
    {
        $this->title = $title;
        $this->request = $request;
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function index($id)
    {
        $this->authorize('index', Title::class);

        $title = $this->title
            ->with('keywords', 'genres')
            ->findOrFail($id);

        $related = app(GetRelatedTitles::class)
            ->execute($title, $this->request->all());

        return $this->success(['titles' => $related]);
    }
}
