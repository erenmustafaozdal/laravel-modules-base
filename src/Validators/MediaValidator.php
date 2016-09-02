<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Validators;

use Illuminate\Validation\Validator;

class MediaValidator extends Validator
{
    /**
     * youtube url regex
     *
     * @var string
     */
    private $urlRegex = '/^https:\/\/www\.youtube\.com\/watch\?v=([^\&\?\/]+)/';

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
    public function validateYoutubeLink($attribute, $value, $parameters, $validator)
    {
        return preg_match($this->urlRegex, $value);
    }

}