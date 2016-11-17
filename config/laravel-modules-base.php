<?php

return [

    /*
    |--------------------------------------------------------------------------
    | General config
    |--------------------------------------------------------------------------
    */
    'from_mail'         => 'eren.060737@gmail.com',
    'developer_mail'    => 'eren.060737@gmail.com',
    'developer_name'    => 'Eren Mustafa ÖZDAL',

    /*
    |--------------------------------------------------------------------------
    | URL config
    |--------------------------------------------------------------------------
    */
    'url' => [
        'province'          => 'provinces',             // province url
        'county'            => 'counties',              // county url
        'district'          => 'districts',             // district url
        'neighborhood'      => 'neighborhoods',         // neighborhood url
        'postal_code'       => 'postal-codes',          // postal_code url
        'middleware'        => ['auth', 'permission']   // module middleware
    ],

    /*
    |--------------------------------------------------------------------------
    | Extra Columns Type for design
    |--------------------------------------------------------------------------
    */
    'column_types' => [ 'text_input', 'date_input' ],

    /*
    |--------------------------------------------------------------------------
    | Controller config
    | if you make some changes on controller, you create your controller
    | and then extend the Laravel Dealer Module Controller. If you don't need
    | change controller, don't touch this config
    |--------------------------------------------------------------------------
    */
    'controller' => [
        'address_namespace'     => 'ErenMustafaOzdal\LaravelModulesBase\Controllers',
        'address'               => 'AddressController'
    ]
];
