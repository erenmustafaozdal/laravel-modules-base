<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use DB;
use Laracasts\Flash\Flash;

// exceptions
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\StoreException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\UpdateException;

trait OperationNodeTrait
{
    use OperationTrait;

    /**
     * nested define values
     * this value set the parent value
     *
     * @var array $defineValues
     */
    private $defineValues = [];

    /**
     * model name attribute
     *
     * @var string
     */
    protected $name = 'name';

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
     * @param string|null $path
     * @param integer|null $id
     * @throws StoreException
     * @return array
     */
    protected function storeNode($class, $path = null, $id = null)
    {
        DB::beginTransaction();
        try {
            $datas = $this->request->parent != 0 || $this->request->related != 0 ? $this->getDefineDatas($class) : $this->request->all();
            $this->model = $class::create($datas);
            $this->model->setNode($class, $this->request);

            // eğer başka ilişki varsa onları da ekle
            if ($this->opsRelation && ! $this->fillModel($this->opsRelation)) {
                throw new StoreException($this->request->all());
            }

            event(new $this->events['success']($this->model));
            DB::commit();

            if (is_null($path)) {
                $attribute = "{$this->name}_uc_first";
                return response()->json([
                    'id' => $this->model->id,
                    'name' => $this->model->$attribute
                ]);
            }

            Flash::success(trans('laravel-modules-base::admin.flash.store_success'));
            return $this->redirectRoute($path);
        } catch (StoreException $e) {
            DB::rollback();
            event(new $this->events['fail']($e->getDatas()));

            if (is_null($path)) {
                return response()->json($this->returnData('error'));
            }
            Flash::error(trans('laravel-modules-base::admin.flash.store_error'));
            return $this->redirectRoute($path);
        }
    }

    /**
     * move nestable node
     *
     * @param $model
     * @throws StoreException
     * @return array
     */
    protected function moveModel($model)
    {
        $this->model = $model;
        DB::beginTransaction();
        try {
            if ($this->request->position === 'firstChild' || $this->request->position === 'lastChild') {
                $this->model->fill($this->getDefineDatas($this->model))->save();
            }
            $this->model->setNode(get_class($this->model), $this->request, 'move');

            // eğer başka ilişki varsa onları da ekle
            if ($this->opsRelation && ! $this->fillModel($this->opsRelation)) {
                throw new StoreException($this->request->all());
            }

            event(new $this->events['success']($this->model));
            DB::commit();
            $attribute = "{$this->name}_uc_first";
            return response()->json([
                'id'        => $this->model->id,
                'name'      => $this->model->$attribute
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
        $attribute = "{$this->name}_uc_first";
        return [
            'id'        => $model->id,
            'parent'    => $model->parent_id,
            'name'      => $model->$attribute,
            'level'     => $model->depth,
            'type'      => $model->isLeaf() ? 'file' : 'folder'
        ];
    }

    /**
     * set the define values
     *
     * @param array $columns
     * @return void
     */
    protected function setDefineValues($columns)
    {
        $this->defineValues = $columns;
    }

    /**
     * get the define datas
     *
     * @param $class
     * @return array
     */
    protected function getDefineDatas($class)
    {
        $class = is_string($class) ? $class : get_class($class);
        $id = $this->request->has('parent') && $this->request->parent != 0 ? $this->request->parent : $this->request->related;
        $parent = $class::findOrFail($id);
        $datas = $this->request->all();
        foreach($this->defineValues as $value) {
            $datas[$value] = $parent->$value;
        }
        return $datas;
    }
}