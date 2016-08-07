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

        // if user destroy route
        if (
            $method == 'GET'
            && in_array($route, $this->userDestroyRoutes)
            && Request::route('users')->id === Sentinel::getUser()->id
        ) {
            abort(403);
        }

        if (
            $method == 'GET'
            && ! Sentinel::getUser()->is_super_admin
            && (
                (
                    ! in_array($route, $this->userRoutes)
                    || Request::route('users')->id !== Sentinel::getUser()->id
                )
                && ! Sentinel::hasAccess( $route )
            )
        ) {
            abort(403);
        }

        return $next($request);
    }
}
