<?php

namespace App\Http\Controllers;

use App\ListModel;
use App\Services\Lists\LoadListContent;
use Common\Core\BaseController;
use Common\Settings\Settings;

class HomepageContentController extends BaseController
{
    /**
     * @var ListModel
     */
    private $list;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(ListModel $list, Settings $settings)
    {
        $this->list = $list;
        $this->settings = $settings;
    }

    public function show()
    {

        $homepageLists = $this->settings->getJson('homepage.lists');
        if (!$homepageLists) {
            return ['lists' => []];
        }

        $lists = $this->list
            ->whereIn('id', $homepageLists)
            ->where('system', false)
            ->get();
        $itemCount = $this->settings->get('homepage.list_items_count', 10);
        $sliderItemCount = $this->settings->get(
            'homepage.slider_items_count',
            5,
        );
        // sort lists by order specified in settings
        $lists = $lists
            ->sortBy(function ($model) use ($homepageLists) {
                return array_search($model->id, $homepageLists);
            })
            ->values();

        $lists = $lists->map(function (ListModel $list, $index) use (
            $itemCount,
            $sliderItemCount,
            $homepageLists
        ) {
            $list->items = app(LoadListContent::class)->execute($list, [
                'limit' =>
                    $index === 0 ? $sliderItemCount : min($itemCount, 30),
            ]);
            return $list;
        });

        $options = [
            'prerender' => [
                'view' => 'home.show',
                'config' => 'home.show',
            ],
        ];

        return $this->success(['lists' => $lists], 200, $options);
    }
}
