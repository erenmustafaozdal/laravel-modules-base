<?php

namespace ErenMustafaOzdal\LaravelModulesBase;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'provinces';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['province'];
    public $timestamps = false;





    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the counties of the province.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function counties()
    {
        return $this->hasMany('App\County');
    }





    /*
    |--------------------------------------------------------------------------
    | Model Set and Get Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * get the province uc first
     *
     * @return string
     */
    public function getProvinceUcFirstAttribute()
    {
        return ucfirst_tr($this->province);
    }
}
