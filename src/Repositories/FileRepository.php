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
        $columns = explode('.',$this->options['column']);
        $file = count($columns) > 1 ? $request->file($columns[1]) : $request->file($columns[0]);
        if ($file) {
            $this->fileName = $this->createFileName($file);
            $this->fileSize = $file->getClientSize();
            return $this->moveFile($file,$model, $request);
        }
        return false;
    }

    /**
     * move upload file
     *
     * @param $file
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function moveFile($file, $model, $request)
    {
        $path = $this->getUploadPath($model, $this->options);
        $this->makeDirectoryBeforeUpload($path, true);
        $file->move($path, $this->fileName);
        return $this->fileName;
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
            $this->deleteDirectory($path, $cleanDirectory);
            $this->makeDirectory($path, 0775, true);
            return true;
        }

        if (! $this->exists($path)) {
            $this->makeDirectory($path, 0775, true);
            return true;
        }
        $this->cleanDirectory($path);
    }
}