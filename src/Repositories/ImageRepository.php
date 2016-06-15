<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Repositories;

use Intervention\Image\Facades\Image;

use ErenMustafaOzdal\LaravelModulesBase\Repositories\FileRepository;

class ImageRepository extends FileRepository
{
    /**
     * upload photo
     *
     * @param $model
     * @param $request
     * @param \Illuminate\Support\Collection $configs
     * @param boolean|false $isTemplate
     * @return string|boolean
     */
    public function uploadPhoto($model, $request, $configs, $isTemplate = false)
    {
        if ($photo = $request->file($configs['column'])) {
            $photoName = $this->createFileName($photo);
            $path = $this->getUploadPath($model, $configs, $isTemplate);

            if ($isTemplate) {
                return $this->template($photo, $photoName, $path);
            }
            dd($path);
        }
        return false; // request is not file
    }

    /**
     * get upload path
     *
     * @param $model
     * @param \Illuminate\Support\Collection $configs
     * @param boolean $isTemplate
     * @return string|\Illuminate\Support\Collection
     */
    public function getUploadPath($model, $configs, $isTemplate)
    {
        $path = $this->uploadPath . '/' . $configs['path'] . '/' . $model->id;

        if ($isTemplate) {
            return $path . '/template';
        }

        $paths = [];
        $paths['original'] = $path . '/original';
        $paths['thumbnails'] = [];
        foreach ($configs['thumbnails'] as $name => $thumb) {
            $paths['thumbnails'][$name] = $path . '/' . $name;
        }
        return $paths;
    }

    /**
     * make template photo
     *
     * @param $photo
     * @param $photoName
     * @param $path
     * @return string
     */
    public function template($photo, $photoName, $path)
    {
        $this->cleanDirectory($path);
        $this->makeDirectory($path, 0775, true);
        Image::make( $photo )->encode('jpg')->save($path . '/' . $photoName );
        return '/' . $path . '/' . $photoName;
    }
}