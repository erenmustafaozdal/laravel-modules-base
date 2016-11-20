<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Middlewares;

use Closure;
use Sentinel;
use Route;
use Request;

class Permission
{
    /**
     * user routes
     *
     * @var array
     */
    private $userRoutes = [
        'admin.user.show',
        'admin.user.edit',
        'admin.user.update',
        'admin.user.changePassword',
        'admin.user.permission',
        'api.user.detail',
        'api.user.fastEdit',
        'api.user.update',
        'api.user.destroyAvatar',
        'api.user.avatarPhoto',
    ];

    /**
     * user destroy routes
     *
     * @var array
     */
    private $userDestroyRoutes = [
        'admin.user.destroy',
        'api.user.destroy'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = Route::currentRouteName();
        $method = $request->method();
        $parameters = Route::current()->parameters();
        $hackedRoute = routeHack($route,$parameters);

        // if user destroy route
        if (
            $method == 'GET'
            && in_array($route, $this->userDestroyRoutes)
            && !is_null(Request::route('users'))
            && Request::route('users')->id === Sentinel::getUser()->id
        ) {
            abort(403);
        }

//        dd($hackedRoute);
        if (
            $method == 'GET'
            && ! Sentinel::getUser()->is_super_admin
            && (
                (
                    ! in_array($route, $this->userRoutes)
                    || is_null(Request::route('users'))
                    || Request::route('users')->id !== Sentinel::getUser()->id
                )
                && ! hasPermission($hackedRoute)
            )
        ) {
            abort(403);
        }

        return $next($request);
    }
}
