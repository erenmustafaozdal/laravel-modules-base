<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Validators;

use Illuminate\Validation\Validator;
use File;

class ElfinderValidator extends Validator
{

    /**
     * validator elfinder file
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validateElfinder($attribute, $value, $parameters, $validator)
    {
        if( ! File::exists($value) ) {
            return false;
        }

        if(count($parameters)>0) {
            return in_array( File::extension($value),$parameters );
        }

        return true;
    }

    /**
     * validator elfinder max size file
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validateElfinderMax($attribute, $value, $parameters, $validator)
    {
        $size = $parameters[0] * 1024; // parameters kB to B
        if ( File::size($value) > $size ) {
            return false;
        }
        return true;
    }

}