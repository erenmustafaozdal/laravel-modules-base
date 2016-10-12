<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Requests;

use App\Http\Requests\Request;

class BaseRequest extends Request
{
    /**
     * rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * get message of the rules
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];

        // photo message eklenir
        if ($this->file('photo') && is_array($this->photo)) {
            foreach ($this->photo as $key => $val) {
                $item = $key + 1;
                $messages['photo.' . $key . '.max'] = "{$item}. Fotoğraf değeri :max kilobayt değerinden küçük olmalıdır.";
                $messages['photo.' . $key . '.image'] = "{$item}. Fotoğraf alanı resim dosyası olmalıdır.";
                $messages['photo.' . $key . '.mimes'] = "{$item}. Fotoğraf dosya biçimi :values olmalıdır.";
            }
        }

        return $messages;
    }

    /**
     * add file rule to rules
     *
     * @param string $attribute
     * @param string $size
     * @param string $mimes
     * @param integer $count
     * @return void
     */
    protected function addFileRule($attribute, $size, $mimes, $count = 1)
    {
        if ($this->has($attribute) && is_string($this->$attribute)) {
            $this->rules[$attribute] = "elfinder_max:{$size}|elfinder:{$mimes}";
        } else  if ($this->file($attribute) && is_array($this->$attribute)){
            $this->rules[$attribute] = "array|max:{$count}";
            for($i = 0; $i < count($this->file($attribute)); $i++) {
                $this->rules[$attribute . '.' . $i] = "max:{$size}|image|mimes:{$mimes}";
            }
        } else if ($this->has($attribute)) {
            $this->rules[$attribute] = "max:{$size}|image|mimes:{$mimes}";
        }
    }
}