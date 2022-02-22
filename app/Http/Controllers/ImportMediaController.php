<?php

namespace App\Http\Controllers;

use App\Person;
use App\Season;
use App\Services\Data\Tmdb\TmdbApi;
use App\Services\People\Store\StorePersonData;
use App\Services\Titles\Retrieve\FindOrCreateMediaItem;
use App\Services\Titles\Store\StoreSeasonData;
use App\Services\Titles\Store\StoreTitleData;
use App\Services\Traits\HandlesTitleId;
use App\Title;
use Common\Core\BaseController;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ImportMediaController extends BaseController
{
    use HandlesTitleId;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function importMediaItem()
    {
        $this->authorize('store', Title::class);

        $this->validate($this->request, [
            'mediaType' => 'required|string',
            'tmdbId' => 'required|integer',
        ]);

        $mediaType = $this->request->get('mediaType');
        $tmdbId = $this->request->get('tmdbId');

        $encodedId = $this->encodeId('tmdb', $mediaType, $tmdbId);
        $mediaItem = app(FindOrCreateMediaItem::class)->execute($encodedId, $mediaType);

        // always update item when importing via tmdb ID directly
        $mediaItem = $mediaItem->model_type === Person::MODEL_TYPE ?
            $this->updatePerson($mediaItem) :
            $this->updateTitle($mediaItem);

        return ['mediaItem' => $mediaItem];
    }

    public function importViaBrowse()
    {
        $this->authorize('store', Title::class);

        if ( ! config('services.tmdb.key')) {
            return 'Enter your Themoviedb API key in settings page before importing titles.';
        }

        @set_time_limit(0);
        @ini_set('memory_limit','200M');

        $type = $this->request->get('type', 'movie');
        $limit = $this->request->get('limit', 500);
        $page = $this->request->get('page', 1);

        $tmdbParams = $this->request->except(['type', 'limit', 'page']);

        // if page is more then 1, need to increase limit as well
        $limit = min(500, $limit + $page);

        for ($i = $page; $i <= $limit; $i++) {
            $response = app(TmdbApi::class)->browse($i, $type, $tmdbParams);

            if ($response['total_pages'] < $limit) {
                $limit = $response['total_pages'];
            }

            $response['results']->each(function($result, $index) use($i) {
                $title = app(FindOrCreateMediaItem::class)->execute($result['id'], Title::MODEL_TYPE);
                if ($title->needsUpdating(true)) {
                    $this->updateTitle($title);
                }
                echo "Imported page: $i | title: $index | {$title->name} <br>";
                $this->flushOutput();
            });
        }

        return 'Done Importing.';
    }

    private function updateTitle(Title $title)
    {
        try {
            $data = app(TmdbApi::class)->getTitle($title);
        } catch (ClientException $e) {
            return null;
        }

        $newTitle = app(StoreTitleData::class)->execute($title, $data);

        // update 3 last seasons
        if ($newTitle->is_series) {
            $title->seasons->reverse()->take(3)->each(function(Season $season) use($title) {
                $data = Title::dataProvider(['forSeason' => true])
                    ->getSeason($title, $season->number);
                app(StoreSeasonData::class)->execute($title, $data);
            });
        }

        return $newTitle;
    }

    private function updatePerson(Person $person)
    {
        $data = app(TmdbApi::class)->getPerson($person);
        return app(StorePersonData::class)->execute($person, $data);
    }

    private function flushOutput()
    {
        flush();
        $levels = ob_get_level();
        for ($i=0; $i<$levels; $i++) {
            ob_end_flush();
        }
    }
}
