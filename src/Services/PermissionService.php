<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Services;

use Illuminate\Routing\Router;

class PermissionService
{
    /**
     * which i will take route name
     * @var array
     */
    private $routePrefix = ['admin', 'api'];

    /**
     * Router class object
     * @var Router $route
     */
    protected $router;

    /**
     * route collection
     * @var $routeCollection
     */
    protected $routeCollection;

    /**
     * all route names
     * @var \Illuminate\Support\Collection
     */
    protected $allRouteNames;

    /**
     * construct method
     *
     * @param Router $router
     */
    public function __construct(Router $router = null)
    {
        if (is_null($router)) {
            return;
        }
        $this->router = $router;
        $this->routeCollection = $this->router->getRoutes();
        $this->allRouteNames = collect([ 'all' => collect() ]);
        foreach($this->routePrefix as $routePrefix) {
            $this->allRouteNames->put($routePrefix, collect());
        }
        $this->takeNames();
    }

    /**
     * take route names
     *
     * @return void
     */
    protected function takeNames()
    {
        foreach($this->routeCollection as $route) {
            $this->detect($route);
        }
    }

    /**
     * detect route name with its prefix
     *
     * @param $route
     * @return boolean
     */
    protected function detect($route)
    {
        $action = $route->getAction();
        if ( ! isset($action['as'])) {
            return false;
        }

        $routeName = $action['as'];
        $prefix = strchr($routeName, '.', true);
        if ( $prefix === false ||  ! in_array($prefix, $this->routePrefix) ) {
            return false;
        }

        $subRoute = substr( strchr($routeName, '.'), 1);
        $transaction = substr( strchr($subRoute, '.'), 1);
        // eğer api ise create, show ve edit iptal et
        if ($prefix === 'api' && in_array($transaction, ['create','show','edit'])) {
            return false;
        }

        $parts = $this->getHyphenNameSpace($action['controller']);
        $namespace = $parts['namespace'];
        $controller = $parts['controller'];

        $this->allRouteNames['all']->put($routeName, [
            'namespace'     => $namespace,
            'controller'    => $controller,
            'route'         => $routeName,
            'sub_route'     => $subRoute
        ]);
        $this->allRouteNames[$prefix]->put($routeName, [
            'namespace'     => $namespace,
            'controller'    => $controller,
            'route'         => $routeName,
            'sub_route'     => $subRoute
        ]);
    }

    /**
     * get hyphen namespace
     *
     * @param string $action
     * @return array
     */
    protected function getHyphenNameSpace($action)
    {
        $parts_of_namespace = explode('\\', $action);
        $parts = explode('@', $action);
        if ($parts_of_namespace[0] !== 'ErenMustafaOzdal') {
            $parent_action = get_parent_class( new $parts[0]() );
            $parts_of_namespace = explode('\\', $parent_action);
        }
        $controller = explode('\\',$parts[0]);
        $controller = end( $controller );
        $namespace = preg_replace( '/([a-zA-Z])(?=[A-Z])/', '$1-', $parts_of_namespace[1] );
        return [
            'controller'    => $controller,
            'namespace'     => strtolower($namespace)
        ];
    }

    /**
     * get route collection
     *
     * @return \Illuminate\Routing\RouteCollection
     */
    public function getCollection()
    {
        return $this->routeCollection;
    }

    /**
     * get all route names
     *
     * @return \Illuminate\Support\Collection
     */
    public function getNames()
    {
        return $this->allRouteNames;
    }

    /**
     * get route names with specific prefix
     *
     * @param string $prefix
     * @return \Illuminate\Support\Collection | boolean
     */
    public function getSpecificNames($prefix)
    {
        if ( ! $names = $this->allRouteNames->get($prefix) ) {
            return false;
        }
         return collect($names);
    }

    /**
     * get route names with parts without all key
     *
     * @return \Illuminate\Support\Collection
     */
    public function getNameParts()
    {
        return $this->allRouteNames->except('all');
    }

    /**
     * get route names with all key
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllNames()
    {
        return $this->allRouteNames->get('all');
    }

    /**
     * get route names with group by controller name
     *
     * @return \Illuminate\Support\Collection
     */
    public function groupByController()
    {
        return $this->getAllNames()->groupBy(function ($item, $key)
        {
            if ( $item['namespace'] === 'laravel-modules-core' ) {
                return "{$item['namespace']}::admin.permission.{$item['controller']}";
            }
            return "laravel-modules-core::{$item['namespace']}/admin.permission.{$item['controller']}";
        })->sortBy(function ($item, $key) {
            return $key;
        });
    }

    /**
     * get route names for the blade
     *
     * @return \Illuminate\Support\Collection
     */
    public function namesForBlade()
    {
        return $this->groupByController()->each(function ($item, $key)
        {
            $item["{$item[0]['namespace']}::admin.permission.{$key}"] = $item;
            return $item;
        });
    }

    /**
     * get all permission route names count
     *
     * @return integer
     */
    public function permissionCount()
    {
        return $this->getAllNames()->count();
    }

    /**
     * get permission rate
     *
     * @param integer $count
     * @return integer
     */
    public function permissionRate($count)
    {
        return intval( $count * 100 / $this->getAllNames()->count() );
    }

    /**
     * get route prefixes
     *
     * @return array
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }
}