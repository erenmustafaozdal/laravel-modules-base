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