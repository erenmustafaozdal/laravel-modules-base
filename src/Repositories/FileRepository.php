<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Repositories;

use Illuminate\Support\Facades\File;

class FileRepository
{
    /**
     * create file name
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function createFileName($file)
    {
        $filename = $file->getClientOriginalName();
        $mime = $file->getClientOriginalExtension();
        $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
        $filename = str_slug($filename, "-");
        $filename = $filename . '_' . time() .  '.' . $mime;
        return $filename;
    }

    /**
     * make directory
     *
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return boolean
     */
    protected function makeDirectory($path, $mode = 0775, $recursive = false)
    {
        if ( ! File::exists($path)) {
            File::makeDirectory($path,  $mode, $recursive);
        }
        return true;
    }

    /**
     * delete directory
     *
     * @param string $path
     * @return bool
     */
    protected function deleteDirectory($path)
    {
        if ( ! $this->isDirectory($path)) {
            return true;
        }
        return File::deleteDirectory($path);
    }

    /**
     * is directory
     *
     * @param string $path
     * @return bool
     */
    protected function isDirectory($path)
    {
        return File::isDirectory($path);
    }

    /**
     * empty the directory of all files and folders
     *
     * @param string $path
     * @return bool
     */
    protected function cleanDirectory($path)
    {
        return File::cleanDirectory($path);
    }
}