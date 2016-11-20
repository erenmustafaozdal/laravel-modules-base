<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Middlewares;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class RelatedModel
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $model
     * @param string $relation
     * @return mixed
     */
    public function handle($request, Closure $next, $model, $relation)
    {
        $model = 'App\\' . $model;
        $params = array_values( $request->route()->parameters() );
        // eğer ikinci yoksa dön
        if ( ! isset($params[1])) {
            return $next($request);
        }

        $model = is_numeric($params[0]) ? $model::findOrFail($params[0]) : $params[0];
        $relation_id = is_numeric($params[1]) ? $params[1] : $params[1]->id;

        // baum mu değil mi ona göre query oluşturulur
        // pagecategory baum olduğunda buna gerek yok
        $parent = '\\'. get_parent_class($model);
        $query = get_parent_class(new $parent) === 'Baum\Node' ? $model->descendantsAndSelf() : $model->query();
        $relationModel = $query->whereHas($relation, function($query) use($relation_id)
        {
            return $query->whereId($relation_id);
        })->get();

        if ( $relationModel->isEmpty() ) {
            abort(404);
        }

        return $next($request);
    }
}
