<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Storage;
use Image as ImageManager;

class StoreMediaImageOnDisk
{
    // sizes should be ordered by size (desc), to avoid blurry images
    private $sizes = [
        'original' => null,
        'large' => 500,
        'medium' => 300,
        'small' => 92,
    ];

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function execute(UploadedFile $file)
    {
        $hash = Str::random(30);
        $img = ImageManager::make($file);
        $extension = $file->extension() ?? 'jpeg';

        foreach ($this->sizes as $key => $size) {
            $this->storeFile($img, $key, $hash, $extension, $size);
        }

        return "storage/media-images/backdrops/$hash/original.$extension";
    }

    private function storeFile(Image $img, string $name, string $hash, string $extension, ?int $size)
    {
        if ($size) {
            $img->resize($size, null, function(Constraint $constraint) {
                $constraint->aspectRatio();
            });
        }

        Storage::disk('public')->put("media-images/backdrops/$hash/$name.$extension", $img->encode($extension));
    }
}
