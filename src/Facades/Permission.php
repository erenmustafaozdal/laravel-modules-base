<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Facades;

use Illuminate\Support\Facades\Facade;

class Permission extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'laravelmodulesbase.permission';
    }
}