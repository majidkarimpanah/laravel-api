<?php

namespace App\Http\Controllers;

use App\ListModel;
use App\Title;
use Common\Core\BaseController;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoOrderController extends BaseController
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
     * @param Title $title
     * @param Request $request
     */
    public function __construct(Title $title, Request $request)
    {
        $this->title = $title;
        $this->request = $request;
    }

    /**
     * @param int $titleId
     * @return JsonResponse
     */
    public function changeOrder($titleId) {

        $title = $this->title->findOrFail($titleId);

        $this->authorize('update', $title);

        $this->validate($this->request, [
            'ids'   => 'array|min:1',
            'ids.*' => 'integer'
        ]);

        $queryPart = '';
        foreach($this->request->get('ids') as $order => $id) {
            $queryPart .= " when id=$id then $order";
        }

        DB::table('videos')
            ->whereIn('id', $this->request->get('ids'))
            ->update(['order' => DB::raw("(case $queryPart end)")]);

        return $this->success();
    }
}
