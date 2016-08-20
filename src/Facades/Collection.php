<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Facades;

use Illuminate\Support\Facades\Facade;

class Collection extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'laravelmodulesbase.collection';
    }
}