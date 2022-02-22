<?php namespace App\Services;

use App\Services\Lists\LoadListContent;
use Common\Core\Bootstrap\BaseBootstrapData;

class AppBootstrapData extends BaseBootstrapData
{
    public function init()
    {
        parent::init();

        if (isset($this->data['user'])) {
            $this->getWatchlist();
            $this->getRatings();
        }

        return $this;
    }

    /**
     * @return void
     */
    private function getWatchlist()
    {
        $list = $this->data['user']->watchlist()->first();

        if (!$list) {
            return;
        }

        $items = app(LoadListContent::class)->execute($list, [
            'minimal' => true,
        ]);

        $this->data['watchlist'] = [
            'id' => $list->id,
            'items' => $items,
        ];
    }

    private function getRatings()
    {
        $this->data['ratings'] = $this->data['user']
            ->reviews()
            ->select(['id', 'reviewable_id', 'reviewable_type', 'score'])
            ->limit(500)
            ->get();
    }
}
