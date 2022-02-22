<?php

namespace App\Http\Controllers;

use App\Jobs\IncrementModelViews;
use App\Person;
use App\Services\People\Retrieve\GetPersonCredits;
use App\Services\People\Store\StorePersonData;
use App\Services\Titles\Retrieve\FindOrCreateMediaItem;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Common\Settings\Settings;
use DB;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PersonController extends BaseController
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Person $person, Request $request)
    {
        $this->person = $person;
        $this->request = $request;
    }

    public function index()
    {
        $this->authorize('index', Person::class);

        $builder = $this->person->with(['popularCredits']);

        if (!app(Settings::class)->get('tmdb.includeAdult')) {
            $builder->where('adult', false);
        }

        if (
            $this->request->get('mostPopular') &&
            ($min = app(Settings::class)->get(
                'content.people_index_min_popularity',
            ))
        ) {
            $builder->where('popularity', '>', $min);
        }

        $datasource = new MysqlDataSource($builder, $this->request->all());

        if (!$this->request->get('order')) {
            $datasource->order = 'popularity:desc';
        }

        $pagination = $datasource->paginate();

        $pagination->map(function (Person $person) {
            $person->description = Str::limit($person->description, 500);
            $person->setRelation(
                'popular_credits',
                $person->popularCredits->slice(0, 1),
            );
            return $person;
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function show($id, $name = null)
    {
        $this->authorize('show', Person::class);

        $person = app(FindOrCreateMediaItem::class)->execute(
            $id,
            Person::MODEL_TYPE,
        );

        if ($person->needsUpdating()) {
            try {
                $data = Person::dataProvider()->getPerson($person);
                $person = app(StorePersonData::class)->execute($person, $data);
            } catch (ClientException $e) {
                // person might not exist on tmdb anymore
            }
        }

        $response = array_merge(
            ['person' => $person],
            app(GetPersonCredits::class)->execute($person),
        );

        $this->dispatch(
            new IncrementModelViews(Person::MODEL_TYPE, $person->id),
        );

        return $this->success($response);
    }

    public function store()
    {
        $this->authorize('store', Person::class);

        $data = $this->request->all();
        $data['popularity'] = Arr::get($data, 'popularity') ?: 50;
        $person = $this->person->create($data);

        return $this->success(['person' => $person]);
    }

    public function update($id)
    {
        $this->authorize('update', Person::class);

        $person = $this->person->findOrFail($id);

        $data = $this->request->all();
        $data['popularity'] = Arr::get($data, 'popularity') ?: 50;
        $person->fill($data)->save();

        return $this->success(['person' => $person]);
    }

    public function destroy()
    {
        $this->authorize('destroy', Person::class);

        $ids = $this->request->get('ids');

        $this->person->whereIn('id', $ids)->delete();
        DB::table('creditables')
            ->whereIn('person_id', $ids)
            ->delete();

        return $this->success();
    }
}
