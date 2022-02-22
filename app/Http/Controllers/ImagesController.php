<?php

namespace App\Http\Controllers;

use App\Image;
use App\Services\Images\StoreMediaImageOnDisk;
use App\Title;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Image as ImageManager;
use Storage;

class ImagesController extends BaseController
{
    /**
     * @var Image
     */
    private $image;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Image $image
     * @param Request $request
     */
    public function __construct(Image $image, Request $request)
    {
        $this->image = $image;
        $this->request = $request;
    }

    public function store()
    {
        $modelId = $this->request->get('modelId');
        $model = app(Title::class)->findOrFail($modelId);

        $this->authorize('store', $model);

        $this->validate($this->request, [
            'file' => 'required|image',
            'modelId' => 'required|integer'
        ]);

        $url = app(StoreMediaImageOnDisk::class)
            ->execute($this->request->file('file'));

        $image = $this->image->create([
            'url' => $url,
            'type' => 'backdrop',
            'source' => 'local',
            'model_type' => Title::class,
            'model_id' => $modelId
        ]);

        return $this->success(['image' => $image]);
    }

    public function destroy()
    {
        $img = $this->image->findOrFail($this->request->get('id'));
        $model = app($img->model_type)->findOrFail($img->model_id);

        $this->authorize('destroy', $model);

        $this->validate($this->request, [
            'id' => 'required|integer'
        ]);

        if ($img->source === 'local') {
            // storage/media-images/backdrops/kw4q4eg5g8q4eq6/original.jpg
            $dir = str_replace('storage/', '', dirname($img->url));
            if (Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->deleteDirectory($dir);
            }
        }

        $img->delete();

        return $this->success();
    }
}
