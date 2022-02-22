<?php

namespace App\Services\Lists;

use App\Listable;
use App\ListModel;
use App\Services\Data\Contracts\DataProvider;
use App\Services\Titles\Retrieve\FindOrCreateMediaItem;
use App\Services\Titles\Store\StoreTitleData;
use App\Title;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Str;

class UpdateListsContent
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param Collection|ListModel[] $lists
     */
    public function execute($lists)
    {
        foreach ($lists as $list) {
            if (
                !$list->auto_update ||
                !Str::contains($list->auto_update, ':')
            ) {
                continue;
            }

            // movie:upcoming
            [$type, $category] = explode(':', $list->auto_update, 2);

            $dataProvider = ListModel::dataProvider(['category' => $category]);
            $titles = $dataProvider->getTitles($type, $category);

            // bail if we could not fetch any titles from remote site
            if (!$titles || $titles->isEmpty()) {
                continue;
            }

            // detach all list items from the list
            app(Listable::class)
                ->where([
                    'list_id' => $list->id,
                ])
                ->delete();

            // store fetched titles locally
            $titles->each(function ($titleData) use ($list, $dataProvider) {
                $title = app(FindOrCreateMediaItem::class)->execute(
                    $titleData['id'],
                    Title::MODEL_TYPE,
                );
                if ($title->needsUpdating()) {
                    $titleData = $this->getFullTitleData($title, $dataProvider);
                    app(StoreTitleData::class)->execute($title, $titleData);
                }
                app(AttachListItem::class)->execute($list, [
                    'itemId' => $title->id,
                    'itemType' => Title::MODEL_TYPE,
                ]);
            });

            $list->updateImage();
        }
    }

    private function getFullTitleData(Title $title, DataProvider $dataProvider)
    {
        if (isset($this->cache[$title->id])) {
            return $this->cache[$title->id];
        } else {
            try {
                $data = $dataProvider->getTitle($title);
            } catch (ClientException $e) {
                return [];
            }
            $this->cache[$title->id] = $data;
            return $data;
        }
    }
}
