<?php
/*
|--------------------------------------------------------------------------
| laravel trans helper hacked
|--------------------------------------------------------------------------
*/
if (! function_exists('humanFileSize')) {
    /**
     * translate given message with laravel trans function
     *
     * @param integer $byte
     * @param integer $decimals
     * @return number
     */
    function humanFileSize($byte, $decimals = 2)
    {
        static $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($byte / $step) > 0.9) {
            $byte = $byte / $step;
            $i++;
        }
        return round($byte, $decimals).$units[$i];
    }
}



/*
|--------------------------------------------------------------------------
| get model slug name
|--------------------------------------------------------------------------
*/
if (! function_exists('getModelSlug')) {
    /**
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @return string
     */
    function getModelSlug($model)
    {
        if ( ! is_string($model)) {
            return snake_case(substr(strrchr(get_class($model), '\\'), 1));
        }

        $path_args = explode('\\', $model);
        $path_args = explode('@', $path_args[count($path_args)-1]);
        return snake_case(str_replace(['Controller', 'Api'], '',$path_args[0]));
    }
}



/*
|--------------------------------------------------------------------------
| get module snake case
|--------------------------------------------------------------------------
*/
if (! function_exists('getModule')) {
    /**
     * @param string $action
     * @return string
     */
    function getModule($action)
    {
        $path_args = explode('\\', $action);
        if ($path_args[0] !== 'ErenMustafaOzdal') {
            $path_args = explode('@', $action);
            $parent_action = get_parent_class( new $path_args[0]() );
            $path_args = explode('\\', $parent_action);
        }

        $module = snake_case($path_args[1]);
        return str_replace('_','-',$module);
    }
}



/*
|--------------------------------------------------------------------------
| get module specific base name
|--------------------------------------------------------------------------
*/
if (! function_exists('getBaseName')) {
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $subBase
     * @return string
     */
    function getBaseName($model, $subBase = '')
    {
        $class = is_string($model) ? $model : get_class($model);
        $baseName = class_basename($class);
        $moduleName = studly_case( getModule($class) );
        $namespace  = "\\ErenMustafaOzdal\\{$moduleName}";
        $namespace .= $subBase ? "\\{$subBase}" : "";
        return $namespace . "\\{$baseName}";
    }
}



/*
|--------------------------------------------------------------------------
| Debug Backtrace (deBack) Methods
|--------------------------------------------------------------------------
*/
if (! function_exists('deBackFunction')) {
    /**
     * get the caller function in the debug backtrace
     *
     * @param array $trace
     * @return string
     */
    function deBackFunction($trace)
    {
        return $trace[1]['function'];
    }
}



/*
|--------------------------------------------------------------------------
| ucfirst with turkish support
|--------------------------------------------------------------------------
*/
if( ! function_exists('ucfirst_tr'))
{
    /**
     * @param string $value
     * @param bool|true $lower_str_end
     * @param string $encoding
     * @return string
     */
    function ucfirst_tr($value, $lower_str_end = false, $encoding = 'UTF-8') {
        $values = explode(' ', $value);
        $values_len = count($values);

        $result = [];
        for ($i=0; $i<$values_len; $i++)
        {
            $first_letter = mb_strtoupper(mb_substr(str_replace(array('İ','i'),array('İ','İ'),$values[$i]), 0, 1, $encoding), $encoding);

            if ($lower_str_end) {
                $value_end = mb_strtolower(mb_substr($values[$i], 1, mb_strlen($values[$i], $encoding), $encoding), $encoding);
            } else {
                $value_end = mb_substr($values[$i], 1, mb_strlen($values[$i], $encoding), $encoding);
            }

            array_push($result, $first_letter . $value_end);
        }

        return implode(' ', $result);
    }
}



/*
|--------------------------------------------------------------------------
| str to upper with turkish support
|--------------------------------------------------------------------------
*/
if( ! function_exists('strtoupper_tr'))
{
    /**
     * @param string $value
     * @param bool|false $lower_str_end
     * @param string $encoding
     * @return mixed|string
     */
    function strtoupper_tr($value, $lower_str_end = false, $encoding = 'UTF-8') {
        return mb_strtoupper(str_replace(array('İ','i'),array('İ','İ'),$value), $encoding);
    }
}



/*
|--------------------------------------------------------------------------
| unset element and return
|--------------------------------------------------------------------------
*/
if( ! function_exists('unsetReturn'))
{
    /**
     * @param $variable
     * @param string|null $key
     * @return mixed
     */
    function unsetReturn(&$variable, $key = null) {
        if (is_array($variable) && array_key_exists($key, $variable)) {
            $val = $variable[$key];
            unset($variable[$key]);
            return $val;
        }
        $val = $variable;
        unset($variable);
        return $val;
    }
}



/*
|--------------------------------------------------------------------------
| laravel route helper hacked
|--------------------------------------------------------------------------
*/
if (! function_exists('lmbRoute')) {
    /**
     * Generate a URL to a named route.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @return string
     */
    function lmbRoute($name, $parameters = [], $absolute = true)
    {
        $anchor = '#not-permission';
        // route yoksa dön
        if ( ! Route::has($name) ) {
            return $anchor;
        }

        $prefixes = LMBPermission::getRoutePrefix();
        $authUser = Sentinel::check();
        $namePrefix = explode('.',$name)[0];
        // route prefix içinde ise ve oturum açıksa ve süper yönetici değilse ve yetkisi yoksa
        if ( in_array( $namePrefix, $prefixes ) && $authUser && ! $authUser->is_super_admin && ! Sentinel::hasAccess($name) ) {
            return $anchor;
        }

        return route($name, $parameters, $absolute);
    }
}



/*
|--------------------------------------------------------------------------
| get percent
|--------------------------------------------------------------------------
*/
if (! function_exists('getPercent')) {
    /**
     * @param integer $max
     * @param integer $value
     * @return float
     */
    function getPercent($max, $value)
    {
        return $value * 100 / $max;
    }
}