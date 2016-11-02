<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Validators;

use Illuminate\Validation\Validator;

class ColorValidator extends Validator
{
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

}