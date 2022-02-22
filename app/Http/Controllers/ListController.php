<?php

namespace App\Http\Controllers;

use App\ListModel;
use App\Services\Lists\DeleteLists;
use App\Services\Lists\LoadListContent;
use App\Services\Lists\UpdateListsContent;
use Arr;
use Auth;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ListController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ListModel
     */
    private $list;

    public function __construct(Request $request, ListModel $list)
    {
        $this->request = $request;
        $this->list = $list;
    }

    public function index()
    {
        $this->authorize('index', [ListModel::class, Auth::id()]);

        $builder = $this->list->newQuery();

        if ($userId = $this->request->get('userId')) {
            $builder->where('user_id', $userId);
        }

        if ($listIds = $this->request->get('listIds')) {
            $builder->whereIn('id', explode(',', $listIds));
        }

        if ($excludeSystem = $this->request->get('excludeSystem')) {
            $builder->where('system', false);
        }

        $paginator = new MysqlDataSource($builder, $this->request->all());

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function show(int $id)
    {
        $list = $this->list->with('user')->findOrFail($id);

        $this->authorize('show', $list);

        $items = app(LoadListContent::class)->execute($list);
        $items = $items
            ->sortBy(
                $this->request->get('sortBy', 'pivot.order'),
                SORT_REGULAR,
                $this->request->get('sortDir') === 'desc',
            )
            ->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $items->count(),
            $items->count() ?: 1,
        );

        return $this->success([
            'list' => $list,
            'items' => $paginator,
        ]);
    }

    public function store()
    {
        $this->authorize('store', ListModel::class);

        $this->validate($this->request, [
            'details.name' => 'required|string|max:100',
            'details.description' => 'nullable|string|max:500',
            'details.public' => 'boolean',
            'details.auto_update' => 'nullable|string',
            'items' => 'array',
        ]);

        $details = $this->request->get('details');
        $autoUpdate = Arr::get($details, 'auto_update');

        $list = $this->list->create([
            'name' => $details['name'],
            'description' => $details['description'],
            'auto_update' => $autoUpdate,
            'public' => $details['public'],
            'user_id' => Auth::id(),
        ]);

        if ($items = $this->request->get('items')) {
            $list->attachItems($items);
        }

        if ($autoUpdate) {
            app(UpdateListsContent::class)->execute([$list]);
        }

        return $this->success(['list' => $list]);
    }

    public function update(int $id)
    {
        $list = $this->list->findOrFail($id);

        $this->authorize('update', $list);

        $this->validate($this->request, [
            'details.name' => 'required|string|max:100',
            'details.description' => 'nullable|string|max:500',
        ]);

        $originalAutoUpdate = $list->auto_update;
        $list->fill($this->request->get('details'))->save();

        if ($originalAutoUpdate !== $list->auto_update) {
            app(UpdateListsContent::class)->execute([$list]);
        }

        return $this->success(['list' => $list]);
    }

    public function destroy(string $ids)
    {
        $listIds = explode(',', $ids);

        // make sure system lists can't be deleted
        $lists = $this->list
            ->whereIn('id', $listIds)
            ->where('system', false)
            ->get();

        $this->authorize('destroy', [ListModel::class, $lists]);

        app(DeleteLists::class)->execute($lists->pluck('id'));

        return $this->success();
    }

    /**
     * @return JsonResponse
     */
    public function autoUpdateContent()
    {
        $this->authorize('store', ListModel::class);

        $lists = $this->list
            ->whereNotNull('auto_update')
            ->limit(10)
            ->get();

        app(UpdateListsContent::class)->execute($lists);

        return $this->success();
    }
}
