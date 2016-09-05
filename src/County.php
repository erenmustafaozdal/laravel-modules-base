<?php

namespace ErenMustafaOzdal\LaravelModulesBase;

use Illuminate\Database\Eloquent\Model;

class County extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'counties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['county'];
    public $timestamps = false;





    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the districts of the county.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function districts()
    {
        return $this->hasMany('App\District');
    }





    /*
    |--------------------------------------------------------------------------
    | Model Set and Get Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * get the county uc first
     *
     * @return string
     */
    public function getCountyUcFirstAttribute()
    {
        return ucfirst_tr($this->county);
    }
}
