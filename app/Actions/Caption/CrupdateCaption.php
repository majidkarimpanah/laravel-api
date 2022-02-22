<?php

namespace App\Actions\Caption;

use App\VideoCaption;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Storage;

class CrupdateCaption
{
    /**
     * @var VideoCaption
     */
    private $caption;

    /**
     * @param VideoCaption $caption
     */
    public function __construct(VideoCaption $caption)
    {
        $this->caption = $caption;
    }

    /**
     * @param array $data
     * @param VideoCaption $caption
     * @return VideoCaption
     */
    public function execute($data, $caption = null)
    {
        if ( ! $caption) {
            $caption = $this->caption->newInstance([
                'user_id' => Auth::id(),
                'hash' => Str::random(36),
            ]);
        }

        $attributes = [
            'name' => $data['name'],
            'language' => $data['language'],
            'video_id' => $data['video_id'],
        ];

        if ($captionFile = Arr::get($data, 'caption_file')) {
            $disk = Storage::disk(config('common.site.uploads_disk'));
            $disk->putFileAs('captions', $captionFile, $caption->hash);
        }

        $caption->fill($attributes)->save();

        return $caption;
    }
}
