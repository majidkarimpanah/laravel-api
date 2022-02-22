<?php

namespace App\Http\Controllers;

use App\Person;
use App\Services\People\Retrieve\GetPersonCredits;
use App\Services\Titles\Retrieve\FindOrCreateMediaItem;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class PersonCreditsController extends BaseController
{
    public function fullTitleCredits(int $personId, int $titleId, string $department)
    {
        $this->authorize('show', Person::class);

        $person = app(FindOrCreateMediaItem::class)->execute($personId, Person::MODEL_TYPE);

        $credits = app(GetPersonCredits::class)->execute($person, ['titleId' => $titleId]);

        $title = Arr::first($credits['credits'][$department], function($title) use($titleId) {
            return $title['id'] === (int) $titleId;
        });

        return $this->success(['credits' => $title['episodes']]);
    }
}
