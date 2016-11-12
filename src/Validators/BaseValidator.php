<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Validators;

use Illuminate\Validation\Validator;
use File;

class BaseValidator extends Validator
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





    /**
     * hex regex
     *
     * @var string
     */
    private $hexRegex = '/^#([a-f0-9]{6}|[a-f0-9]{3})$/';

    /**
     * validator youtube link
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @param $validator
     * @return bool
     */
    public function validateHex($attribute, $value, $parameters, $validator)
    {
        return preg_match($this->hexRegex, $value);
    }





    /**
     * youtube url regex
     *
     * @var string
     */
    private $youtubeRegex = '/^http(s)?:\/\/(www\.)?youtube\.com\/watch\?v=([^\&\?\/]+)/';

    /**
     * vimedo url regex
     *
     * @var string
     */
    private $vimeoRegex = '/^http(s)?:\/\/(www\.)?vimeo\.com\/([^\&\?\/]+)/';

    /**
     * validator youtube link
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @param $validator
     * @return bool
     */
    public function validateVideoLink($attribute, $value, $parameters, $validator)
    {
        return preg_match($this->youtubeRegex, $value) || preg_match($this->vimeoRegex, $value);
    }
}