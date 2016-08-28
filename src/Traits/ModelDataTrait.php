<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Traits;

use Carbon\Carbon;

trait ModelDataTrait
{
    /**
     * module name
     *
     * @var string
     */
    protected $module;

    /**
     * model slug
     *
     * @var string
     */
    protected $modelSlug;

    /**
     * model options
     *
     * @var array
     */
    protected $option = [];

    /**
     * column name
     *
     * @var string
     */
    protected $column;

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
        $this->setAttributes($modelSlug,'photo');
        $photo = $this->getFile();

        if( ! is_null($photo)) {
            $src = $this->getFileSrc($photo, $relation, $type);
        } else {
            $type = $type === 'original' ? 'biggest' : $type;
            $src = config("{$this->module}.{$this->modelSlug}.default_img_path") . "/{$type}.jpg";
        }

        $attr = $this->getHTMLAttributes($attributes);
        return $onlyUrl ? asset($src) : '<img src="'.asset($src).'" '.$attr.'>';
    }

    /**
     * get the html document element
     *
     * @param array $attributes
     * @param boolean $onlyUrl
     * @param string|null $modelSlug
     * @param string|null $relation
     * @return string
     */
    public function getDocument($attributes = [], $onlyUrl = false, $modelSlug = null, $relation = null)
    {
        $this->setAttributes($modelSlug,'file');
        $file = $this->getFile();

        if (is_null($file)) {
            return '';
        }
        $src = $this->getFileSrc($file, $relation);
        $attr = $this->getHTMLAttributes($attributes);
        return $onlyUrl ? asset($src) : '<a href="'.asset($src).'" '.$attr.'> ' . $file . '</a>';
    }

    /**
     * set the model specific attribute
     *
     * @param string $modelSlug
     * @param string $type
     */
    private function setAttributes($modelSlug, $type)
    {
        $this->module = getModule(get_class($this));
        $this->modelSlug = is_null($modelSlug) ? getModelSlug($this) : $modelSlug;
        $this->options = config("{$this->module}.{$this->modelSlug}.uploads.{$type}");
        $this->column = $this->options['column'];
    }

    /**
     * get file
     *
     * @return string|null
     */
    private function getFile()
    {
        $columnParams = explode('.',$this->column);
        return count($columnParams) == 1 ? $this->$columnParams[0] : $this->$columnParams[1];
    }

    /**
     * get the file src
     *
     * @param string $file
     * @param string|null $relation
     * @param string|null $type
     * @return string
     */
    private function getFileSrc($file, $relation, $type = null)
    {
        $id = is_null($relation) ? $this->id : $this->$relation->id;
        $src  = $this->options['path']."/{$id}/";
        if (is_null($type)) {
            return $src . $file;
        }
        $src .= $type === 'original' ? "original/{$file}" : "thumbnails/{$type}_{$file}";
        return $src;
    }

    /**
     * get html attribute for file
     *
     * @param array $attributes
     * @return string
     */
    private function getHTMLAttributes($attributes)
    {
        $attr = '';
        foreach($attributes as $key => $value) {
            $attr .= $key.'="'.$value.'" ';
        }
        return $attr;
    }






    /*
    |--------------------------------------------------------------------------
    | Model get and set attribute
    |--------------------------------------------------------------------------
    */

    /**
     * Get the name uc first attribute.
     *
     * @return string
     */
    public function getNameUcFirstAttribute()
    {
        return ucfirst_tr($this->name);
    }

    /**
     * Set slug encrypted
     *
     * @param $slug
     */
    public function setSlugAttribute($slug)
    {
        if ( ! $slug) {
            $slug = str_slug($this->name, '-');
        }

        $this->attributes['slug'] =  $slug;
    }

    /**
     * Get the title uc first attribute.
     *
     * @param  string $title
     * @return string
     */
    public function getTitleUcFirstAttribute($title)
    {
        return ucfirst_tr($title);
    }

    /**
     * Set the is_publish attribute.
     *
     * @param boolean $is_publish
     * @return string
     */
    public function setIsPublishAttribute($is_publish)
    {
        $this->attributes['is_publish'] = $is_publish == 1 || $is_publish === 'true' || $is_publish === true ? true : false;
    }

    /**
     * Get the is_publish attribute.
     *
     * @param boolean $is_publish
     * @return string
     */
    public function getIsPublishAttribute($is_publish)
    {
        return $is_publish == 1 ? true : false;
    }

    /**
     * Get the created_at attribute.
     *
     * @param  $date
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format(config('laravel-user-module.date_format'));
    }

    /**
     * Get the created_at attribute for humans.
     *
     * @return string
     */
    public function getCreatedAtForHumansAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    /**
     * Get the created_at attribute for datatable.
     *
     * @return array
     */
    public function getCreatedAtTableAttribute()
    {
        return [
            'display'       => $this->created_at_for_humans,
            'timestamp'     => Carbon::parse($this->created_at)->timestamp,
        ];
    }

    /**
     * Get the updated_at attribute.
     *
     * @param  $date
     * @return string
     */
    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format(config('laravel-user-module.date_format'));
    }

    /**
     * Get the updated_at attribute for humans.
     *
     * @return string
     */
    public function getUpdatedAtForHumansAttribute()
    {
        return Carbon::parse($this->updated_at)->diffForHumans();
    }

    /**
     * Get the updated_at attribute for datatable.
     *
     * @return array
     */
    public function getUpdatedAtTableAttribute()
    {
        return [
            'display'       => $this->updated_at_for_humans,
            'timestamp'     => Carbon::parse($this->updated_at)->timestamp,
        ];
    }

    /**
     * Get the link attribute.
     *
     * @return string
     */
    public function getHtmlLinkAttribute()
    {
        if (is_null($this->link) || is_null($this->link->link)) {
            return '';
        }
        return "<a href='{$this->link->link}' target='_blank'> {$this->link->link} </a>";
    }
}