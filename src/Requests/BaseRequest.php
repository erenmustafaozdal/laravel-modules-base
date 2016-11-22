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
        if ( ( $this->has('photo') || $this->file('photo') ) && is_array($this->photo)) {
            foreach ($this->photo as $key => $val) {
                $item = $key + 1;
                $messages['photo.' . $key . '.required'] = "{$item}. Fotoğraf alanı gereklidir.";
                $messages['photo.' . $key . '.elfinder_max'] = "{$item}. Fotoğraf alanı en fazla :size bayt boyutunda olmalıdır.";
                $messages['photo.' . $key . '.elfinder'] = "{$item}. Fotoğraf dosya biçimi :values olmalıdır.";
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
     * @param boolean $isRequired
     * @return void
     */
    protected function addFileRule($attribute, $size, $mimes, $count = 1, $isRequired = false)
    {
        if ($this->has($attribute) && is_string($this->$attribute)) {
            $this->rules[$attribute] = "elfinder_max:{$size}|elfinder:{$mimes}";
        } else  if ($this->file($attribute) && is_array($this->$attribute)){
            $this->rules[$attribute] = "array|max:{$count}";
            foreach($this->file($attribute) as $key => $file) {
                if(array_search($key, ['x','y','width','height']) !== false) {
                    continue;
                }

                $this->rules[$attribute . '.' . $key] = "max:{$size}|image|mimes:{$mimes}";
                if ($isRequired) {
                    $this->rules[$attribute . '.' . $key] = "required|{$this->rules[$attribute . '.' . $key]}";
                }
            }
        } else if ($this->has($attribute) && is_array($this->$attribute)) {
            $this->rules[$attribute] = "array|max:{$count}";
            foreach($this->get($attribute) as $key => $file) {
                if(array_search($key, ['x','y','width','height']) !== false) {
                    continue;
                }

                $this->rules[$attribute . '.' . $key] = "elfinder_max:{$size}|elfinder:{$mimes}";
                if ($isRequired) {
                    $this->rules[$attribute . '.' . $key] = "required|{$this->rules[$attribute . '.' . $key]}";
                }
            }
        } else if ($this->file($attribute)) {
            $this->rules[$attribute] = "max:{$size}|image|mimes:{$mimes}";
        }

        if ($isRequired) {
            $this->rules[$attribute] = "required|{$this->rules[$attribute]}";
        }
    }
}