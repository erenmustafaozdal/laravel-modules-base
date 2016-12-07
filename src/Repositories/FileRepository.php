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
        $files = $this->getFile($request);

        if (array_search(null,$files) === false) {
            foreach($files as $file) {
                $this->setFileName($file);
                $this->setFileSize($file);
                $this->files[] = $this->moveFile($file, $model);
            }
            return $this->files;
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

        return [
            'fileName'  => $this->fileName,
            'fileSize'  => $this->fileSize
        ];
    }

    /**
     * set file name
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|string $file
     * @return void
     */
    protected function setFileName($file)
    {
        if (isset($this->options['thumbnails'])) {
            $thumbs = isset($this->options['changeThumb'])
                ? $this->options['thumbnails'][$this->options['changeThumb']]
                : $this->options['thumbnails'];
            $thumbs = array_values($thumbs);
            $firstThumb = array_shift($thumbs);
            if (isset($firstThumb['name'])) {
                $this->fileName = $firstThumb['name'];
                return;
            }
        }
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
        $this->fileSize = ! is_null($this->elfinderFilePath) ? $this->getFileSize() : (is_string($file) ? $this->size($file) : $file->getClientSize());
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
        if ( is_string($file) ) {
            return substr( strrchr( $file, '/' ), 1 );
        }

        $filename = $file->getClientOriginalName();
        $mime = $file->getClientOriginalExtension();
        $parts = explode('.',$filename);
        array_pop($parts);
        return str_slug( implode(' ', $parts), '-' ) . '_' . time() .  '.' . $mime;
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
        $column = count($columns) > 1 ? $columns[1] : $columns[0];
        $inputName = isset($this->options['group'])
            ? "{$this->options['group']}.{$this->options['index']}.{$column}"
            : $column;
        $inputName = isset($this->options['inputPrefix']) && $this->options['inputPrefix'] ? $this->options['inputPrefix'] . $inputName : $inputName;
        if ( isset($this->options['isElfinder']) && $this->options['isElfinder'] ) {
            $file = $request->input($inputName);
            $this->elfinderFilePath = public_path($file);
        } else {
            $file = $request->file($inputName);
            // eğer dosya yoksa init var mı bakılır
            if (! $file || (is_array($file) && array_search(null,$file) !== false) ) {
                $column  = 'init_';
                $column .= count($columns) > 1 ? $columns[1] : $columns[0];
                $file = $request->get($column);
            }
        }
        return is_array($file) ? $file : [$file];
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
        $last = array_pop($parts);
        $filename = str_slug(implode('_',$parts)) . '_' . time();
        return $filename  . '.' . $last;
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
     *
     * @param $request
     * @return array
     */
    public function getDatas($request)
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
        $datas = [];
        foreach ($this->files as $file) {
            $data = [
                $columnParams[1]    => $file['fileName'],
                'size'              => $file['fileSize']
            ];
            if ((isset($this->options['group']) && isset($this->options['add_column'])) || isset($this->options['add_column'])) {
                foreach($this->options['add_column'] as $column) {
                    $columnData = isset($this->options['group'])
                        ? "{$this->options['group']}.{$this->options['index']}.{$column}"
                        : (isset($this->options['inputPrefix']) ? $this->options['inputPrefix'] . $column : $column);
                    $columnData = $request->input($columnData);
                    if ($columnData) {
                        $data[$column] = $columnData;
                    }
                }
            }
            $datas[] = $data;
        }

        return  [
            'relation_type'     => $relation,
            'relation'          => $columnParams[0],
            'relation_model'    => $this->options['relation_model'],
            'is_reset'          => isset($this->options['is_reset']) && $this->options['is_reset'] ? true : false,
            'changeToHasOne'    => isset($this->options['changeToHasOne']) ? $this->options['changeToHasOne'] : false,
            'datas'             => $relation === 'hasOne' ? $datas[0] : $datas
        ];
    }





    /*
    |--------------------------------------------------------------------------
    | File Methods
    |--------------------------------------------------------------------------
    */

    /**
     * delete file with model path
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null $parentRelation
     */
    public function deletePhoto($model, $parentRelation = null)
    {
        $thumbs = $this->options['photo']['thumbnails'];
        $id = is_null($parentRelation) ? $model->id : $model->$parentRelation->id;
        $path = $this->options['photo']['path'] . "/{$id}";
        // delete original
        $this->delete($path . "/original/{$model->photo}");
        // thumbnails delete
        foreach($thumbs as $thumb => $size){
            $this->delete($path . "/thumbnails/{$thumb}_{$model->photo}");
        }
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
     * @param string|null $parentRelation
     */
    public function deleteDirectories($model, $parentRelation = null)
    {
        $id = is_null($parentRelation) ? $model->id : $model->$parentRelation->id;
        foreach($this->options as $option){
            $this->deleteDirectory($option['path'] . "/{$id}");
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
        if (! $this->exists($path)) {
            $this->makeDirectory($path, 0775, true);
            return true;
        }

        // tamamen silip oluşturmak istendi ise
        if (($this->exists($path) && $cleanDirectory) || (isset($this->options['is_reset']) && $this->options['is_reset'])) {
            $this->deleteDirectory($path, true);
            $this->makeDirectory($path, 0775, true, true);
            return true;
        }

        if ($this->options['relation'] !== 'hasMany' && ( ! isset($this->options['is_reset']) ||  $this->options['is_reset']) ) {
            $this->cleanDirectory($path);
        }
        return true;
    }
}