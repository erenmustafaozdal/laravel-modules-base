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
     * @param string|null $modelSlug
     * @param string|null $relation
     * @return string
     */
    public function getPhoto($attributes = [], $type='original', $onlyUrl = false, $modelSlug = null, $relation = null)
    {
        $module = getModule(get_class($this));
        $modelSlug = is_null($modelSlug) ? getModelSlug($this) : $modelSlug;
        $options = config("{$module}.{$modelSlug}.uploads.photo");
        $column = $options['column'];

        if( ! is_null($this->photo)) {
            $columnParams = explode('.',$column);
            $photo = count($columnParams) == 1 ? $this->$column : $this->$columnParams[1];

            $id = is_null($relation) ? $this->id : $this->$relation->id;
            $src  = $options['path']."/{$id}/";
            $src .= $type === 'original' ? "original/{$photo}" : "thumbnails/{$type}_{$photo}";
        } else {
            $type = $type === 'original' ? 'biggest' : $type;
            $src = config("{$module}.{$modelSlug}.default_img_path") . "/{$type}.jpg";
        }

        $attr = '';
        foreach($attributes as $key => $value) {
            $attr .= $key.'="'.$value.'" ';
        }
        return $onlyUrl ? asset($src) : '<img src="'.asset($src).'" '.$attr.'>';
    }
}