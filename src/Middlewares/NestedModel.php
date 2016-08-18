<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Middlewares;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class NestedModel
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $model
     * @return mixed
     */
    public function handle($request, Closure $next, $model)
    {
        $model = 'App\\' . $model;
        $params = array_values( $request->route()->parameters() );
        // eğer ikinci yoksa dön
        if ( ! isset($params[1])) {
            return $next($request);
        }

        $parent_model = is_numeric($params[0]) ? $model::findOrFail($params[0]) : $params[0];
        $nested_model = is_numeric($params[1]) ? $model::findOrFail($params[1]) : $params[1];

        if ( ! $nested_model->isDescendantOf($parent_model)) {
            abort(404);
        }

        return $next($request);
    }
}
