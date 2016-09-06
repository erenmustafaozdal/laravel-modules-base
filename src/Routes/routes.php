<?php
//max level nested function 100 hatasını düzeltiyor
ini_set('xdebug.max_nesting_level', 300);

/*
|--------------------------------------------------------------------------
| Address Routes
|--------------------------------------------------------------------------
*/
Route::group([
    'prefix'        => 'address',
    'middleware'    => config('laravel-modules-base.url.middleware'),
    'namespace'     => config('laravel-modules-base.controller.address_namespace')
], function()
{
    // get provinces
    Route::get(config('laravel-modules-base.url.province'), [
        'as'                => 'address.provinces',
        'uses'              => config('laravel-modules-base.controller.address').'@provinces'
    ]);

    // get specific province counties
    Route::get(
        config('laravel-modules-base.url.province') .
        '/{' . config('laravel-modules-base.url.province') . '}/' .
        config('laravel-modules-base.url.county')
    , [
        'as'                => 'address.counties',
        'uses'              => config('laravel-modules-base.controller.address').'@counties'
    ]);

    // get specific county districts
    Route::get(
        config('laravel-modules-base.url.county') .
        '/{' . config('laravel-modules-base.url.county') . '}/' .
        config('laravel-modules-base.url.district')
    , [
        'as'                => 'address.districts',
        'uses'              => config('laravel-modules-base.controller.address').'@districts'
    ]);

    // get specific district neighborhoods
    Route::get(
        config('laravel-modules-base.url.district') .
        '/{' . config('laravel-modules-base.url.district') . '}/' .
        config('laravel-modules-base.url.neighborhood')
    , [
        'as'                => 'address.neighborhoods',
        'uses'              => config('laravel-modules-base.controller.address').'@neighborhoods'
    ]);

    // get specific neighborhood postal codes
    Route::get(
        config('laravel-modules-base.url.neighborhood') .
        '/{' . config('laravel-modules-base.url.neighborhood') . '}/' .
        config('laravel-modules-base.url.postal_code')
    , [
        'as'                => 'address.postalCode',
        'uses'              => config('laravel-modules-base.controller.address').'@postalCode'
    ]);
});
