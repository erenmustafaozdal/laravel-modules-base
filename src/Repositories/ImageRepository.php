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
        $columns = explode('.',$this->options['column']);
        // elfinder veya fileinput durumuna göre file belirlenir
        $isElfinder = isset($this->options['isElfinder']) && $this->options['isElfinder'];
        if ($isElfinder) {
            $file = count($columns) > 1 ? $request->input($columns[1]) : $request->input($columns[0]);
            $file = $this->elfinderFilePath = public_path($file);
        } else {
            $file = count($columns) > 1 ? $request->file($columns[1]) : $request->file($columns[0]);
        }

        if ($file) {
            $this->fileName = $this->createFileName($file, $isElfinder);
            $this->fileSize = $isElfinder ? $this->getFileSize() : $file->getClientSize();
            return $this->moveImage($file, $model, $request, $isElfinder);
        }
        return false;
    }

    /**
     * move upload image
     *
     * @param $photo
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Illuminate\Http\Request $request
     * @param boolean $isElfinder
     * @return array
     */
    public function moveImage($photo, $model, $request, $isElfinder)
    {
        $path = $this->getUploadPath($model, $this->options);

        $this->photos['original'] = $this->original($photo, $path['original']);
        $this->photos['thumbnails'] = $this->thumbnails($photo, $path['thumbnails'], $request, $this->options);
        return $this->photos;
    }

    /**
     * get upload path
     *
     * @param $model
     * @return string|\Illuminate\Support\Collection
     */
    protected function getUploadPath($model)
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
     * @param array $path
     * @param $request
     * @return string
     */
    protected function thumbnails($photo, $path, $request)
    {
        $this->makeDirectoryBeforeUpload($path, false);
        $photos = [];
        foreach ($this->options['thumbnails'] as $name => $thumb) {
            $thumb_path = $path . '/' . $name . '_' . $this->fileName;
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