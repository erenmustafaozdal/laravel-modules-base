<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Traits;

trait ModelDataTrait
{
    /**
     * get the html photo element
     *
     * @param array $attributes
     * @param string $type  original or thumbnails key
     * @param boolean $onlyUrl
     * @return string
     */
    public function getPhoto($attributes = [], $type='original', $onlyUrl = false)
    {
        dd('bu trait sınıfını düzenle');
        if( ! is_null($this->photo)) {
            $src  = config('laravel-user-module.user.uploads.path')."/{$this->id}/";
            $src .= $type === 'original' ? "original/{$this->photo}" : "thumbnails/{$type}_{$this->photo}";
        } else {
            $type = $type === 'original' ? 'biggest' : $type;
            $src = config('laravel-user-module.user.avatar_path') . "/{$type}.jpg";
        }

        $attr = '';
        foreach($attributes as $key => $value) {
            $attr .= $key.'="'.$value.'" ';
        }
        return $onlyUrl ? asset($src) : '<img src="'.asset($src).'" '.$attr.'>';
    }
}