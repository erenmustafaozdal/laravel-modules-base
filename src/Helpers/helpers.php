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