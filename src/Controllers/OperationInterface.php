<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

interface OperationInterface
{
    /**
     * store data of the eloquent model
     *
     * @param $class
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeModel($class, $path = null);

    /**
     * update data of the eloquent model
     *
     * @param $model
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateModel($model, $path = null);

    /**
     * destroy data of the eloquent model or models
     *
     * @param \Illuminate\Database\Eloquent\Model|array $model [Model|ids]
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroyModel($model, $path = null);
}