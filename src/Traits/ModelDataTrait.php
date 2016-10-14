<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;

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





    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * set nodes
     *
     * @param $class
     * @param $request
     * @param string $type => move|store
     */
    public function setNode($class, Request $request, $type = 'store')
    {
        if ( ! $request->has('position')) {
            $model = $class::find($request->input('parent'));
            $this->makeChildOf($model);
            return;
        }

        $input = $type === 'store' ? 'parent' : 'related';
        switch($request->input('position')) {
            case 'firstChild':
                $model = $class::find($request->input($input));
                $this->makeFirstChildOf($model);
                break;
            case 'lastChild':
                $model = $class::find($request->input($input));
                $this->makeChildOf($model);
                break;
            case 'before':
                $model = $class::find($request->input('related'));
                $this->moveToLeftOf($model);
                break;
            case 'after':
                $model = $class::find($request->input('related'));
                $this->moveToRightOf($model);
                break;
        }
    }

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
        $id = is_null($relation) ? $this->id : $this->$relation;
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
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * get extra column datas with model values
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExtrasWithValues($query, $model)
    {
        $modelSlug = $model ? getModelSlug($model) : false;
        return $query->with([
            'extras' => function($query) use($model,$modelSlug)
            {
                if ( ! $model ) return $query;

                return $query->with([
                    "{$modelSlug}s" => function($query) use($model,$modelSlug)
                    {
                        return $query->wherePivot("{$modelSlug}_id",$model->id);
                    }
                ]);
            }
        ]);
    }

    /**
     * get published model data
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('is_publish',true);
    }

    /**
     * get has a minimum one published child element of category
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $relation
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasPublishedElement($query, $relation)
    {
        return $query->has($relation, '>=', 1, 'and', function($query)
        {
            return $query->published();
        });
    }





    /*
    |--------------------------------------------------------------------------
    | Model get and set attribute
    |--------------------------------------------------------------------------
    */

    /**
     * Get the url link attribute.
     *
     * @return string
     */
    public function getUrlLinkAttribute()
    {
        return "<a href='{$this->url}'>{$this->url}</a>";
    }

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
     * Get the title uc first attribute.
     *
     * @return string
     */
    public function getTitleUcFirstAttribute()
    {
        return ucfirst_tr($this->title);
    }

    /**
     * Set slug encrypted
     *
     * @param $slug
     */
    public function setSlugAttribute($slug)
    {
        if ( ! $slug) {
            $title = is_null($this->name) ? $this->title : $this->name;
            $slug = str_slug($title, '-');
        }

        $this->attributes['slug'] =  $slug;
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


    /*
    |--------------------------------------------------------------------------
    | Data Table Configs get and set attribute methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the datatable_filter string attribute.
     *
     * @return string
     */
    public function getDatatableFilterStringAttribute()
    {
        return $this->datatable_filter == 1 ? 'true' : 'false';
    }

    /**
     * Get the datatable_filter attribute.
     *
     * @param boolean $datatable_filter
     * @return string
     */
    public function getDatatableFilterAttribute($datatable_filter)
    {
        return $datatable_filter == 1 ? true : false;
    }

    /**
     * Get the datatable_tools string attribute.
     *
     * @return string
     */
    public function getDatatableToolsStringAttribute()
    {
        return $this->datatable_tools == 1 ? 'true' : 'false';
    }

    /**
     * Get the datatable_tools attribute.
     *
     * @param boolean $datatable_tools
     * @return string
     */
    public function getDatatableToolsAttribute($datatable_tools)
    {
        return $datatable_tools == 1 ? true : false;
    }

    /**
     * Get the datatable_fast_add string attribute.
     *
     * @return string
     */
    public function getDatatableFastAddStringAttribute()
    {
        return $this->datatable_fast_add == 1 ? 'true' : 'false';
    }

    /**
     * Get the datatable_fast_add attribute.
     *
     * @param boolean $datatable_fast_add
     * @return string
     */
    public function getDatatableFastAddAttribute($datatable_fast_add)
    {
        return $datatable_fast_add == 1 ? true : false;
    }

    /**
     * Get the datatable_group_action string attribute.
     *
     * @return string
     */
    public function getDatatableGroupActionStringAttribute()
    {
        return $this->datatable_group_action == 1 ? 'true' : 'false';
    }

    /**
     * Get the datatable_group_action attribute.
     *
     * @param boolean $datatable_group_action
     * @return string
     */
    public function getDatatableGroupActionAttribute($datatable_group_action)
    {
        return $datatable_group_action == 1 ? true : false;
    }

    /**
     * Get the datatable_detail string attribute.
     *
     * @return string
     */
    public function getDatatableDetailStringAttribute()
    {
        return $this->datatable_detail == 1 ? 'true' : 'false';
    }

    /**
     * Get the datatable_detail attribute.
     *
     * @param boolean $datatable_detail
     * @return string
     */
    public function getDatatableDetailAttribute($datatable_detail)
    {
        return $datatable_detail == 1 ? true : false;
    }


    /*
    |--------------------------------------------------------------------------
    | Other Configs get and set attribute methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the description_is_editor attribute.
     *
     * @param boolean $description_is_editor
     * @return string
     */
    public function getDescriptionIsEditorAttribute($description_is_editor)
    {
        return $description_is_editor == 1 ? true : false;
    }

    /**
     * Get the config_propagation attribute.
     *
     * @param boolean $config_propagation
     * @return string
     */
    public function getConfigPropagationAttribute($config_propagation)
    {
        return $config_propagation == 1 ? true : false;
    }


    /*
    |--------------------------------------------------------------------------
    | Thumbnail Configs get and set attribute methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the photo_width attribute with pixel.
     *
     * @return string|null
     */
    public function getPhotoWidthPxAttribute()
    {
        return is_null($this->photo_width) ? null : $this->photo_width . ' ' . lmcTrans('admin.fields.pixel');
    }

    /**
     * Get the photo_height attribute with pixel.
     *
     * @return string|null
     */
    public function getPhotoHeightPxAttribute()
    {
        return is_null($this->photo_height) ? null : $this->photo_height . ' ' . lmcTrans('admin.fields.pixel');
    }

    /**
     * get the aspect ration with photo width and photo height
     *
     * @return float|null
     */
    public function getAspectRatioAttribute()
    {
        if ($this->photo_width == 0 || $this->photo_height == 0) {
            return null;
        }
        return $this->photo_width/$this->photo_height;
    }

    /**
     * Get the first_name attribute.
     *
     * @return string
     */
    public function getFirstNameUcFirstAttribute()
    {
        return ucfirst_tr($this->first_name);
    }

    /**
     * Get the last_name attribute.
     *
     * @return string
     */
    public function getLastNameUpperAttribute()
    {
        return strtoupper_tr($this->last_name);
    }

    /**
     * Get the fullname attribute.
     *
     * @return string
     */
    public function getFullnameAttribute()
    {
        return $this->first_name_uc_first.' '.$this->last_name_upper;
    }
}