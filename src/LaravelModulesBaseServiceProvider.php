<?php

namespace ErenMustafaOzdal\LaravelModulesBase;

use ErenMustafaOzdal\LaravelModulesBase\Services\CollectionService;
use ErenMustafaOzdal\LaravelModulesBase\Services\PermissionService;
use Illuminate\Support\ServiceProvider;
use ErenMustafaOzdal\LaravelModulesBase\Validators\ElfinderValidator;
use ErenMustafaOzdal\LaravelModulesBase\Validators\ColorValidator;
use ErenMustafaOzdal\LaravelModulesBase\Validators\MediaValidator;

class LaravelModulesBaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/Routes/routes.php';
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-modules-base');
        $this->publishes([
            __DIR__.'/../resources/lang' => base_path('resources/lang/vendor/laravel-modules-base'),
        ]);

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../config/laravel-modules-base.php'   => config_path('laravel-modules-base.php')
        ], 'config');
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

        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-modules-base.php', 'laravel-modules-base'
        );

        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Sentinel', 'Cartalyst\Sentinel\Laravel\Facades\Sentinel');
        });

        $router = $this->app['router'];
        $router->middleware('permission',\ErenMustafaOzdal\LaravelModulesBase\Middlewares\Permission::class);
        $router->middleware('nested_model',\ErenMustafaOzdal\LaravelModulesBase\Middlewares\NestedModel::class);
        $router->middleware('related_model',\ErenMustafaOzdal\LaravelModulesBase\Middlewares\RelatedModel::class);

        // register services
        $this->registerCollectionService();
        $this->registerPermissionService();

        $this->app->booting(function() {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('LMBCollection', 'ErenMustafaOzdal\LaravelModulesBase\Facades\Collection');
            $loader->alias('LMBPermission', 'ErenMustafaOzdal\LaravelModulesBase\Facades\Permission');

            // register validation
            $this->registerValidationRules($this->app['validator']);
        });

        // model binding
        $router->model(config('laravel-modules-base.url.province'),  'App\Province');
        $router->model(config('laravel-modules-base.url.county'),  'App\County');
        $router->model(config('laravel-modules-base.url.district'),  'App\District');
        $router->model(config('laravel-modules-base.url.neighborhood'),  'App\Neighborhood');
        $router->model(config('laravel-modules-base.url.postal_code'),  'App\PostalCode');
    }

    /**
     * Registers the collection service
     *
     * @return void
     */
    protected function registerCollectionService()
    {
        $this->app->singleton('laravelmodulesbase.collection', function ($app) {
            return new CollectionService();
        });
    }

    /**
     * Registers the permission service
     *
     * @return void
     */
    protected function registerPermissionService()
    {
        $this->app->singleton('laravelmodulesbase.permission', function ($app) {
            return new PermissionService();
        });
    }

    /**
     * Registers validation rules
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function registerValidationRules($validator)
    {
        /**
         * elfinder validator
         */
        $validator->resolver(function($translator, $data, $rules, $messages)
        {
            // youtube validator
            if(array_key_exists('video',$data)) {
                return new MediaValidator($translator, $data, $rules, $messages);
            }

            // hex validator
            if(array_key_exists('site_first_color',$data) || array_key_exists('first_footer_color',$data)) {
                return new ColorValidator($translator, $data, $rules, $messages);
            }

            // elfinder validator
            return new ElfinderValidator($translator, $data, $rules, $messages);
        });
        $validator->replacer('elfinder_max', function($message, $attribute, $rule, $parameters) {
            return str_replace(':size',$parameters[0],$message);
        });
        $validator->replacer('elfinder', function($message, $attribute, $rule, $parameters) {
            return str_replace(':values',implode(', ', $parameters),$message);
        });

    }
}
