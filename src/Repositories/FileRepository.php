<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Repositories;

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class FileRepository extends Filesystem
{

    /**
     * uploaded files path
     * @var array
     */
    public $files = [];

    /**
     * uploaded file name
     * @var string
     */
    public $fileName;

    /**
     * uploaded file size
     * @var integer
     */
    public $fileSize;

    /*
    |--------------------------------------------------------------------------
    | General Methods
    |--------------------------------------------------------------------------
    */

    /**
     * upload file
     *
     * @param $model
     * @param $request
     * @param array $configs
     * @return string|boolean
     */
    public function upload($model, $request, $configs)
    {
        if ($file = $request->file($configs['file_column'])) {
            $this->fileName = $this->createFileName($file);
            $this->fileSize = $file->getClientSize();
            $path = $this->getUploadPath($model, $configs);

            $file->move($path, $this->fileName);
            return $this->fileName;
        }
        return false;
    }

    /**
     * create file name
     *
     * @param $file
     * @return string
     */
    public function createFileName($file)
    {
        $filename = $file->getClientOriginalName();
        $mime = $file->getClientOriginalExtension();
        $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
        $filename = str_slug($filename, "-");
        $filename = $filename .  '.' . $mime;
        return $filename;
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
        return $configs['path'] . '/' . $model->id;
    }
}