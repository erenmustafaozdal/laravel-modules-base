<?php

namespace ErenMustafaOzdal\LaravelModulesBase;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'neighborhoods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['neighborhood'];
    public $timestamps = false;





    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the postal code of the district.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function postalCode()
    {
        return $this->hasOne('App\PostalCode');
    }





    /*
    |--------------------------------------------------------------------------
    | Model Set and Get Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * get the neighborhood uc first
     *
     * @return string
     */
    public function getNeighborhoodUcFirstAttribute()
    {
        return ucfirst_tr($this->neighborhood);
    }
}
