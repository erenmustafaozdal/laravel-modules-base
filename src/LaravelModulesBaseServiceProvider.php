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
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-modules-base');
        $this->publishes([
            __DIR__.'/../resources/lang' => base_path('resources/lang/vendor/laravel-modules-base'),
        ]);
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

        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Sentinel', 'Cartalyst\Sentinel\Laravel\Facades\Sentinel');
        });

        $router = $this->app['router'];
        $router->middleware('permission',\ErenMustafaOzdal\LaravelModulesBase\Middlewares\Permission::class);
        $router->middleware('nested_model',\ErenMustafaOzdal\LaravelModulesBase\Middlewares\NestedModel::class);
        $router->middleware('related_model',\ErenMustafaOzdal\LaravelModulesBase\Middlewares\RelatedModel::class);
    }
}
