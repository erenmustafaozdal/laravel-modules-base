<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use Yajra\Datatables\Datatables;

trait DataTableTrait
{
    /**
     * Datatables object
     *
     * @var \Yajra\Datatables\Datatables
     */
    protected $dataTables;

    /**
     * get Datatables
     *
     * @param $query
     * @param array $addColumns
     * @param array $editColumns
     * @param array $removeColumns
     * @return \Yajra\Datatables\Datatables
     */
    public function getDatatables($query,array $addColumns = [],array $editColumns = [],array $removeColumns = [])
    {
        $this->dataTables = Datatables::of($query);

        // add new urls
        $addUrls = array_has($addColumns, 'addUrls') ? array_pull($addColumns, 'addUrls') : [];
        $this->dataTables->addColumn('urls', function($model) use($addUrls)
        {
            // if addUrls variable is empty return
            if ( ! $addUrls) { return false; }

            $urls = $this->getDefaultUrls($model);
            foreach($addUrls as $key => $value) {
                $urls[$key] = $this->getUrl($value, $model);
            }
            return $urls;
        });

        // add, edit, remove columns
        $this->setColumns([ 'add' => $addColumns, 'edit' => $editColumns, 'remove' => $removeColumns ]);

        return $this->dataTables->addColumn('check_id', '{{ $id }}')->make(true);
    }

    /**
     * set data table columns
     *
     * @param array $columns [array('add' => ..., 'edit' => ...,'remove' => ...)]
     * @return void
     */
    public function setColumns(array $columns)
    {
        // add columns
        foreach($columns['add'] as $key => $value) {
            $this->dataTables->addColumn($key, $value);
        }

        // edit columns
        foreach($columns['edit'] as $key => $value) {
            $this->dataTables->editColumn($key, $value);
        }

        // remove columns
        foreach($columns['remove'] as $value) {
            $this->dataTables->removeColumn($value);
        }
    }

    /**
     * get url
     *
     * @param array $value
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    private function getUrl($value, $model)
    {
        if(isset($value['id']) && $value['id']) {
            $routeParam = isset($value['model'])
                ? [ 'id' => $value['id'], $value['model'] => $model->id]
                : ['id' => $model->id];
            return route($value['route'], $routeParam);
        }

        return route($value['route']);
    }

    /**
     * get default urls for Datatables
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    private function getDefaultUrls($model)
    {
        $slug = getModelSlug($model);
        return [
            'details'   => route("api.{$slug}.detail", ['id' => $model->id]),
            'fast_edit' => route("api.{$slug}.fastEdit", ['id' => $model->id]),
            'edit'      => route("api.{$slug}.update", ['id' => $model->id]),
            'destroy'   => route("api.{$slug}.destroy", ['id' => $model->id]),
            'show'      => route("admin.{$slug}.show", ['id' => $model->id])
        ];
    }
}