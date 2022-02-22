<?php

namespace App\Services\Lists;

use App\Listable;
use App\ListModel;
use Common\Settings\Settings;
use Illuminate\Support\Collection;

class DeleteLists
{
    /**
     * @var ListModel
     */
    private $list;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param ListModel $list
     * @param Settings $settings
     */
    public function __construct(ListModel $list, Settings $settings)
    {
        $this->list = $list;
        $this->settings = $settings;
    }

    /**
     * @param Collection $listIds
     */
    public function execute(Collection $listIds)
    {
        app(Listable::class)->whereIn('list_id', $listIds)->delete();
        $this->list->whereIn('id', $listIds)->delete();

        // remove deleted lists from homepage.lists setting
        $homepageLists = $this->settings->getJson('homepage.lists');
        if ($homepageLists) {
            $filtered = array_filter($homepageLists, function($listId) use($listIds) {
                return !$listIds->contains($listId);
            });
            $this->settings->save(['homepage.lists' => json_encode($filtered)]);
        }
    }
}