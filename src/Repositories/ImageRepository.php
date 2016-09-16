<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Repositories;

use Intervention\Image\Facades\Image;

use ErenMustafaOzdal\LaravelModulesBase\Repositories\FileRepository;

class ImageRepository extends FileRepository
{
    /**
     * intervention image object
     * @var Image
     */
    private $image;

    /**
     * class constructor method
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * upload file
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $request
     * @return string|boolean
     */
    public function upload($model, $request)
    {
        $files = $this->getFile($request);

        if (array_search(null,$files) === false) {
            foreach($files as $key => $file) {
                $this->setFileName($file);
                $this->setFileSize($file);
                $this->files[] = $this->moveImage($file, $key, $model, $request);
            }
            return count($this->files) === 1 ? $this->files[0] : $this->files;
        }
        return false;
    }

    /**
     * move upload image
     *
     * @param $photo
     * @param integer $photoKey
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function moveImage($photo, $photoKey, $model, $request)
    {
        $path = $this->getUploadPath($model);

        $photos['fileName'] = $this->fileName;
        $photos['fileSize'] = $this->fileSize;
        $photos['original'] = $this->original($photo, $path['original']);
        $photos['thumbnails'] = $this->thumbnails($photo, $photoKey, $path['thumbnails'], $request);
        return $photos;
    }

    /**
     * get upload path
     *
     * @param $model
     * @return string|\Illuminate\Support\Collection
     */
    public function getUploadPath($model)
    {
        $path = $this->options['path'] . '/' . $model->id;

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
        $this->makeDirectoryBeforeUpload($path, false);
        Image::make( $photo )->encode('jpg')->save($path . '/' . $this->fileName );
        return '/' . $path . '/' . $this->fileName;
    }

    /**
     * make original photo
     *
     * @param $photo
     * @param integer $photoKey
     * @param array $path
     * @param $request
     * @return string
     */
    protected function thumbnails($photo, $photoKey, $path, $request)
    {
        $this->makeDirectoryBeforeUpload($path, false);
        $photos = [];
        foreach ($this->options['thumbnails'] as $name => $thumb) {
            $thumb_path = $path . '/' . $name . '_' . $this->fileName;
            $this->image = Image::make( $photo )
                ->encode('jpg');

            $this->resizeImage($request, $photoKey, $thumb);
            $this->image->save($thumb_path);
            $photos[$name] = '/' . $thumb_path;
        }
        return $photos;
    }

    /**
     * resize image
     *
     * @param $request
     * @param integer $photoKey
     * @param array $thumbnail
     * @return void
     */
    private function resizeImage($request, $photoKey, $thumbnail)
    {
        if ( ! $request->width[$photoKey] && ! $request->height[$photoKey] ) {
            $this->image->resize($thumbnail['width'], $thumbnail['height'], function($constraint)
            {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            return;
        }

        $this->image->crop($request->width[$photoKey], $request->height[$photoKey], $request->x[$photoKey], $request->y[$photoKey])
            ->resize($thumbnail['width'], null, function($constraint)
            {
                $constraint->aspectRatio();
            });
    }
}