<?php

namespace App\Services\Titles\Store;

use App\Image;
use App\Person;
use App\Season;
use App\Services\Traits\StoresMediaImages;
use App\Title;
use App\Video;
use Carbon\Carbon;
use Common\Tags\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class StoreTitleData
{
    use StoresMediaImages;

    /**
     * @var Title
     */
    private $title;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Video
     */
    private $video;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Season
     */
    private $season;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        Video $video,
        Image $image,
        Person $person,
        Tag $tag,
        Season $season
    )
    {
        $this->video = $video;
        $this->image = $image;
        $this->person = $person;
        $this->tag = $tag;
        $this->season = $season;
    }

    public function execute(Title $title, array $data, array $config = []): Title
    {
        $this->title = $title;
        $this->data = $data;
        $this->config = $config;

        $this->persistData();
        $this->persistRelations();

        return $this->title;
    }

    private function persistData()
    {
        $titleData = array_filter($this->data, function ($value) {
            // make sure we don't overwrite existing values with null
            return !is_array($value) && ($this->config['overrideWithEmptyValues'] ?? !is_null($value));
        });

        $this->title->fill($titleData)->save();
    }

    private function persistRelations()
    {
        $relations = array_filter($this->data, function ($value) {
            return is_array($value);
        });

        foreach ($relations as $name => $values) {
            switch ($name) {
                case 'videos':
                    $this->persistVideos($values);
                    break;
                case 'images':
                    $this->storeImages($values, $this->title);
                    break;
                case 'genres':
                    $this->persistTags($values, 'genre');
                    break;
                case 'countries':
                    $this->persistTags($values, 'production_country');
                    break;
                case 'cast':
                    app(StoreCredits::class)->execute($this->title, $values);
                    break;
                case 'keywords':
                    $this->persistTags($values, 'keyword');
                    break;
                case 'seasons':
                   $this->persistSeasons($values);
            }
        }
    }

    /**
     * @param array $seasons
     */
    private function persistSeasons($seasons)
    {
        $newSeasons = collect($seasons)->map(function($season) {
            $season['title_id'] = $this->title->id;
            return $season;
        })->filter(function($season) {
            return !$this->title->seasons->contains('number', $season['number']);
        });

        if ($newSeasons->isNotEmpty()) {
            $this->season->insert($newSeasons->toArray());
        }
    }

    /**
     * @param array $tags
     * @param $type
     */
    private function persistTags($tags, $type)
    {
        $values = collect($tags)->map(function($tag) use($type) {
            return [
                'name' => slugify($tag['name']),
                'display_name' => Arr::get($tag, 'display_name', ucfirst($tag['name'])),
                'type' => $type
            ];
        });

        $tags = $this->tag->insertOrRetrieve($values, $type);

        $relation = $type === 'genre' ? $this->title->genres() : $this->title->keywords();
        $relation->syncWithoutDetaching($tags->pluck('id'));
    }

    /**
     * @param array $values
     */
    private function persistVideos($values)
    {
        $exists = [];
        $mediaItems = collect($values)->map(function($value, $i) use(&$exists) {
            $uniqueKey = strtolower($value['name']);
            $value['title_id'] = $this->title->id;
            $value['order'] = $i;
            $value['created_at'] = Carbon::now();
            $value['updated_at'] = Carbon::now();
            if (in_array($uniqueKey, $exists)) {
                return null;
            } else {
                $exists[] = $uniqueKey;
                return $value;
            }
        })->filter();

        $this->video->where('source', '!=', 'local')
            ->where('title_id', $this->title->id)
            ->whereNull('episode_num')
            ->delete();

        $this->video->insert($mediaItems->toArray());
    }
}
