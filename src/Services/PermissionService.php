<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Services;

class PermissionService
{
    /**
     * ErenMustafaOzdal providers
     *
     * @var array
     */
    private $myModules;

    /**
     * routes
     *
     * @var array
     */
    public $routes = [];

    /**
     * route names
     *
     * @var \Illuminate\Support\Collection
     */
    public $routeNames;

    /**
     * counts
     *
     * @var array
     */
    public $counts = [ 'module' => 0, 'route' => 0 ];

    /**
     * construct method
     */
    public function __construct()
    {
        $this->myModules = $this->getMyModules();
        $this->setRoutes();
        $this->setCounts();
    }

    /**
     * set routes
     *
     * @return void
     */
    private function setRoutes()
    {
        foreach($this->myModules as $module) {
            $scModule = snake_case( $module, '-' );
            $subModules = config("{$scModule}.permissions");
            if ( ! is_null($subModules)) {
                foreach($subModules as $sub => $routes) {
                    $hasRoutes = array_filter(array_keys($routes['routes']),function($item)
                    {
                        return hasPermission($item);
                    });
                    if(count($hasRoutes) > 0) {
                        $this->routes[$scModule . '_' . $sub] = $routes;
                    }
                }
            }
        }
    }

    /**
     * set module, part, route counts
     *
     * @return void
     */
    private function setCounts()
    {
        foreach($this->routes as $module) {
            $this->counts['module']++;
            foreach($module['routes'] as $route) {
                $this->counts['route']++;
            }
        }
    }

    /**
     * get ErenMustafaOzdal namespace provider
     *
     * @return array
     */
    protected function getMyModules()
    {
        $all = array_keys( app()->getLoadedProviders() );
        $modules = [];
        foreach($all as $provider) {
            $parts = explode('\\', $provider);
            if ($parts[0] === 'ErenMustafaOzdal' && array_search($parts[1],$modules) === false) {
                array_push($modules, snake_case($parts[1], '-'));
            }
        }
        return $modules;
    }
}