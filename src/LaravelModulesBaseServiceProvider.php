<?php

namespace ErenMustafaOzdal\LaravelModulesBase;

use Illuminate\Support\ServiceProvider;

class LaravelModulesBaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('Laracasts\Flash\FlashServiceProvider');
        $this->app->register('Yajra\Datatables\DatatablesServiceProvider');
        $this->app->register('Cartalyst\Sentinel\Laravel\SentinelServiceProvider');
        $this->app->register('Intervention\Image\ImageServiceProvider');
    }
}
