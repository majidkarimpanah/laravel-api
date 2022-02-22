<?php

namespace App\Http\Controllers;

use App\Episode;
use App\Person;
use App\Season;
use DB;
use App\Title;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitleCreditController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Person
     */
    private $person;

    /**
     * @param Request $request
     * @param Person $person
     */
    public function __construct(Request $request, Person $person)
    {
        $this->request = $request;
        $this->person = $person;
    }

    /**
     * Update title or episode "creditable" pivot record.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function update($id)
    {
        $this->authorize('update', Title::class);

        $this->validate($this->request, [
            'credit' => 'required|array',
        ]);

        DB::table('creditables')
            ->where('id', $id)
            ->update($this->lowercasePayload($this->request->get('credit')));

        $credit = DB::table('creditables')->find($id);

        return $this->success(['credit' => (array) $credit]);
    }

    /**
     * @param array $payload
     * @return array
     */
    private function lowercasePayload($payload)
    {
        return collect($payload)->mapWithKeys(function($value, $key) {
            if ($key === 'department' || $key === 'job') {
                $value = strtolower($value);
            }
            return [$key => $value];
        })->toArray();
    }

    /**
     * Attach credit to title or episode.
     *
     * @return JsonResponse
     */
    public function store()
    {
        $this->authorize('store', Title::class);

        $this->validate($this->request, [
            'personId' => 'required|integer|exists:people,id',
            'creditable.id' => 'required|integer',
            'creditable.type' => 'required|string',
            'pivot' => 'required|array',
        ]);

        $creditableId = $this->request->get('creditable')['id'];
        $creditableType = $this->getCreditableType($this->request->get('creditable')['type']);
        $order = null;

        if ($this->request->get('pivot')['department'] === 'cast') {
            $order = DB::table('creditables')->where([
                'creditable_id' => $creditableId,
                'creditable_type' => $creditableType,
                'department' => 'cast',
            ])->count() + 1;
        }

        $creditId = DB::table('creditables')->insertGetId(array_merge([
            'person_id' => $this->request->get('personId'),
            'creditable_id' => $creditableId,
            'creditable_type' => $creditableType,
            'order' => $order ?: 0,
        ], $this->lowercasePayload($this->request->get('pivot'))));

        $person = $this->person->find($this->request->get('personId'))->toArray();
        $person['pivot'] = $this->request->get('pivot');
        $person['pivot']['id'] = $creditId;

        return $this->success(['credit' => $person]);
    }

    /**
     * Remove title or episode "creditable" pivot record.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('destroy', Title::class);

        DB::table('creditables')
            ->where('id', $id)
            ->delete();

        return $this->success();
    }

    /**
     * @return JsonResponse
     */
    public function changeOrder() {

        $this->authorize('update', Title::class);

        $this->validate($this->request, [
            'ids'   => 'array|min:1',
            'ids.*' => 'integer'
        ]);

        $queryPart = '';
        foreach($this->request->get('ids') as $order => $id) {
            $queryPart .= " when id=$id then $order";
        }

        DB::table('creditables')
            ->whereIn('id', $this->request->get('ids'))
            ->update(['order' => DB::raw("(case $queryPart end)")]);

        return $this->success();
    }

    private function getCreditableType($type)
    {
        if ($type === Title::MODEL_TYPE) {
            return Title::class;
        } else if ($type === Season::MODEL_TYPE) {
            return Season::class;
        } else {
            return Episode::class;
        }
    }
}
