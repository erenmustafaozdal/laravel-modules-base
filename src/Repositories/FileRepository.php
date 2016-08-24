<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Repositories;

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class FileRepository extends Filesystem
{

    /**
     * file options
     * @var array
     */
    public $options = [];

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

    /**
     * elfinder file path
     * @var string|null
     */
    public $elfinderFilePath = null;

    /**
     * class constructor method
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /*
    |--------------------------------------------------------------------------
    | General Methods
    |--------------------------------------------------------------------------
    */

    /**
     * upload file
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $request
     * @return string|boolean
     */
    public function upload($model, $request)
    {
        $file = $this->getFile($request);

        if ($file) {
            $this->setFileName($file);
            $this->setFileSize($file);
            return $this->moveFile($file, $model);
        }
        return false;
    }

    /**
     * move upload file
     *
     * @param $file
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    public function moveFile($file, $model)
    {
        $path = $this->getUploadPath($model, $this->options);
        $this->makeDirectoryBeforeUpload($path, true);

        if ( ! is_null($this->elfinderFilePath) ) {
            $this->copy($this->elfinderFilePath, $path . '/' . $this->fileName);
        } else {
            $this->move($file, $path . '/' . $this->fileName);
        }

        return $this->fileName;
    }

    /**
     * set file name
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|string $file
     * @return void
     */
    protected function setFileName($file)
    {
        $this->fileName = $this->createFileName($file);
    }

    /**
     * set file size
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|string $file
     * @return void
     */
    protected function setFileSize($file)
    {
        $this->fileSize = ! is_null($this->elfinderFilePath) ? $this->getFileSize() : $file->getClientSize();
    }

    /**
     * create file name
     *
     * @param $file
     * @return string
     */
    public function createFileName($file)
    {
        if ( ! is_null($this->elfinderFilePath) ) {
            return $this->getFileName();
        }

        $filename = $file->getClientOriginalName();
        $mime = $file->getClientOriginalExtension();
        $parts = explode('.',$filename);
        return str_slug($parts[0], "-") .  '.' . $mime;
    }

    /**
     * get file or string path
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|string
     */
    protected function getFile($request)
    {
        $columns = explode('.',$this->options['column']);
        if ( isset($this->options['isElfinder']) && $this->options['isElfinder'] ) {
            $file = count($columns) > 1 ? $request->input($columns[1]) : $request->input($columns[0]);
            return $this->elfinderFilePath = public_path($file);
        }
        return count($columns) > 1 ? $request->file($columns[1]) : $request->file($columns[0]);
    }

    /**
     * get file size of the elfinder file
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->size($this->elfinderFilePath);
    }

    /**
     * get file name of the elfinder file
     *
     * @return string
     */
    public function getFileName()
    {
        $filename = substr( strrchr( $this->elfinderFilePath, '/' ), 1 );
        $parts = explode('.',$filename);
        return str_slug($parts[0]) . '.' . $parts[1];
    }

    /**
     * get upload path
     *
     * @param $model
     * @return string|\Illuminate\Support\Collection
     */
    protected function getUploadPath($model)
    {
        return $this->options['path'] . '/' . $model->id;
    }

    /**
     * get datas for save the database
     */
    public function getDatas()
    {
        $relation = $this->options['relation'];
        $columnParams = explode('.',$this->options['column']);
        if ( ! $relation ) {
            return [
                'relation_type'     => 'not',
                'datas' => [
                    $columnParams[0]    => $this->fileName,
                    'size'              => $this->fileSize
                ]
            ];
        }
        return  [
            'relation_type'         => $relation,
            'relation'              => $columnParams[0],
            'relation_model'        => $this->options['relation_model'],
            'datas' => [
                $columnParams[1]    => $this->fileName,
                'size'              => $this->fileSize
            ]
        ];
    }





    /*
    |--------------------------------------------------------------------------
    | Directory Methods
    |--------------------------------------------------------------------------
    */

    /**
     * delete multiple directories with model path
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleteDirectories($model)
    {
        foreach($this->options as $option){
            $this->deleteDirectory($option['path'] . "/{$model->id}");
        }
    }

    /**
     * if not exists make directory
     * or clean directory
     *
     * @param string $path
     * @param boolean $cleanDirectory
     * @return boolean
     */
    public function makeDirectoryBeforeUpload($path, $cleanDirectory = false)
    {
        // tamamen silip oluşturmak istendi ise
        if ($this->exists($path) && $cleanDirectory) {
            $this->deleteDirectory($path, true);
            $this->makeDirectory($path, 0775, true, true);
            return true;
        }

        if (! $this->exists($path)) {
            $this->makeDirectory($path, 0775, true);
            return true;
        }
        $this->cleanDirectory($path);
    }
}