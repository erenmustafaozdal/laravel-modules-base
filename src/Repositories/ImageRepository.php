<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Repositories;

use Intervention\Image\Facades\Image;

use ErenMustafaOzdal\LaravelModulesBase\Repositories\FileRepository;

class ImageRepository extends FileRepository
{
    /**
     * uploaded photos path
     * @var array
     */
    public $photos = [];

    /**
     * uploaded photo name
     * @var string
     */
    public $photoName;

    /**
     * intervention image object
     * @var Image
     */
    private $image;

    /**
     * upload photo
     *
     * @param $model
     * @param $request
     * @param array $configs
     * @return string|boolean
     */
    public function uploadPhoto($model, $request, $configs)
    {
        if ($photo = $request->file($configs['column'])) {
            $this->photoName = $this->createFileName($photo);
            $path = $this->getUploadPath($model, $configs);

            $this->photos['original'] = $this->original($photo, $path['original']);
            $this->photos['thumbnails'] = $this->thumbnails($photo, $path['thumbnails'], $request, $configs);
            return $this->photos;
        }
        return false; // request is not file
    }

    /**
     * get upload path
     *
     * @param $model
     * @param array $configs
     * @return string|\Illuminate\Support\Collection
     */
    protected function getUploadPath($model, $configs)
    {
        $path = $configs['path'] . '/' . $model->id;

        $paths = [];
        $paths['original'] = $path . '/original';
        $paths['thumbnails'] = $path . '/thumbnails';
        return $paths;
    }

    /**
     * make original photo
     *
     * @param $photo
     * @param $path
     * @return string
     */
    protected function original($photo, $path)
    {
        $this->cleanDirectory($path);
        $this->makeDirectory($path, 0775, true);
        Image::make( $photo )->encode('jpg')->save($path . '/' . $this->photoName );
        return '/' . $path . '/' . $this->photoName;
    }

    /**
     * make original photo
     *
     * @param $photo
     * @param array $path
     * @param $request
     * @param array $configs
     * @return string
     */
    protected function thumbnails($photo, $path, $request, $configs)
    {
        $this->cleanDirectory($path);
        $this->makeDirectory($path, 0775, true);
        $photos = [];
        foreach ($configs['thumbnails'] as $name => $thumb) {
            $thumb_path = $path . '/' . $name . '_' . $this->photoName;
            $this->image = Image::make( $photo )
                ->encode('jpg');

            $this->resizeImage($request, $thumb);
            $this->image->save($thumb_path);
            $photos[$name] = '/' . $thumb_path;
        }
        return $photos;
    }

    /**
     * resize image
     *
     * @param $request
     * @param array $thumbnail
     * @return void
     */
    private function resizeImage($request, $thumbnail)
    {
        if ( ! $request->has('width') && ! $request->has('height')) {
            $this->image->fit($thumbnail['width'], $thumbnail['height'], function($constraint)
            {
                $constraint->upsize();
            });
            return;
        }

        $this->image->crop($request->input('width'), $request->input('height'), $request->input('x'), $request->input('y'))
            ->resize($thumbnail['width'], null, function($constraint)
            {
                $constraint->aspectRatio();
            });
    }
}