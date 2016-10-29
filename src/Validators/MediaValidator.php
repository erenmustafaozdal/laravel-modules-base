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