<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use Illuminate\Http\Request;
use DB;
use Laracasts\Flash\Flash;

// exceptions
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\StoreException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\UpdateException;

trait OperationNodeTrait
{
    use OperationTrait;

    /**
     * get nestable nodes
     *
     * @param $class
     * @param integer|null $id
     * @return array
     */
    protected function getNodes($class, $id)
    {
        $records = [];
        $records['nodes'] = [];
        $models = $this->getNodeModels($class, $id);
        foreach ($models as $model) {
            $records['nodes'][] = $this->getNodeValues($model);
        }
        return $records;
    }

    /**
     * store nestable node
     *
     * @param $class
     * @return array
     */
    protected function storeNode($class)
    {
        DB::beginTransaction();
        try {
            $this->model = $class::create($this->request->all());
            $this->model->setNode($this->request);

            event(new $this->events['success']($this->model));
            DB::commit();

            return response()->json([
                'id' => $this->model->id,
                'name' => $this->model->name
            ]);
        } catch (StoreException $e) {
            DB::rollback();
            event(new $this->events['fail']($e->getDatas()));

            return response()->json($this->returnData('error'));
        }
    }

    /**
     * move nestable node
     *
     * @param $model
     * @return array
     */
    protected function moveModel($model)
    {
        $this->model = $model;
        DB::beginTransaction();
        try {
            $this->model->setNode($this->request, 'move');

            event(new $this->events['success']($this->model));
            DB::commit();
            return response()->json([
                'id'        => $this->model->id,
                'name'      => $this->model->name
            ]);
        } catch (UpdateException $e) {
            DB::rollback();
            event(new $this->events['fail']($e->getDatas()));
            return response()->json($this->returnData('error'));
        }
    }

    /**
     * get node models
     *
     * @param $class
     * @param integer $relation_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getNodeModels($class, $relation_id)
    {
        if ($this->request->id === '0') {
            return is_null($relation_id)
                ? $class::all()->toHierarchy()
                : $class::where('parent_id',$relation_id)->get()->toHierarchy();
        }

        return $class::find($this->request->id)->descendants()->limitDepth(1)->get();
    }

    /**
     * get node values for return data
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    private function getNodeValues($model)
    {
        return [
            'id'        => $model->id,
            'parent'    => $model->parent_id,
            'name'      => $model->name,
            'level'     => $model->depth,
            'type'      => $model->isLeaf() ? 'file' : 'folder'
        ];
    }
}