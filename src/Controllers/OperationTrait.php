<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use Illuminate\Http\Request;
use DB;
use Laracasts\Flash\Flash;

use ErenMustafaOzdal\LaravelModulesBase\Repositories\FileRepository;
use ErenMustafaOzdal\LaravelModulesBase\Repositories\ImageRepository;
// exceptions
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\StoreException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\UpdateException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\DestroyException;

trait OperationTrait
{
    /**
     * model
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * model name
     *
     * @var string
     */
    private $modelName;

    /**
     * Related model id
     *
     * @var integer|null
     */
    private $relatedId = null;

    /**
     * model route regex
     *
     * @var string|null
     */
    private $routeRegex = null;

    /**
     * current request
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * files options
     *
     * @var array
     */
    private $fileOptions = [];

    /**
     * operation events
     *
     * @var array
     */
    private $events = [];

    /**
     * model relations for create or edit
     *
     * @var array
     */
    private $opsRelation = [];

    /**
     * if is use image or file repository, image or file repository object
     *
     * @var ImageRepository|FileRepository
     */
    private $repo;

    /**
     * operation has photo
     *
     * @var boolean
     */
    private $hasPhoto = false;

    /**
     * operation has file
     *
     * @var boolean
     */
    private $hasFile = false;

    /**
     * trait constructor method
     *
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * store data of the eloquent model
     *
     * @param $class
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function storeModel($class, $path = null)
    {
        DB::beginTransaction();
        try {
            $this->model = $class::create($this->getData());
            if ( ! isset($this->model->id) ) {
                throw new StoreException($this->request->all());
            }

            // eğer dosya varsa yükle
            if ($this->fileOptions) {
                $this->preUploadFile(StoreException::class);
            }

            // eğer başka ilişki varsa onları da ekle
            if ($this->opsRelation && ! $this->fillModel($this->opsRelation)) {
                throw new StoreException($this->request->all());
            }

            event(new $this->events['success']($this->model));
            DB::commit();

            if (is_null($path)) {
                return response()->json($this->returnData('success'));
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
     * update data of the eloquent model
     *
     * @param $model
     * @param string|null $path
     * @param boolean $updateRelation
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function updateModel($model, $path = null, $updateRelation = false)
    {
        $this->model = $model;
        DB::beginTransaction();
        try {
            $this->model->fill($this->getData());
            if ( ! $this->model->save()) {
                throw new UpdateException($this->model);
            }

            // eğer dosya varsa yükle
            if ($this->fileOptions) {
                $this->preUploadFile(UpdateException::class);
            }

            // eğer başka ilişki varsa onları da ekle
            if ($this->opsRelation && ! $this->fillModel($this->opsRelation)) {
                throw new UpdateException($this->request->all());
            }

            event(new $this->events['success']($this->model));
            DB::commit();

            if (is_null($path)) {
                return response()->json($this->returnData('success'));
            }

            Flash::success(trans('laravel-modules-base::admin.flash.update_success'));
            return $this->redirectRoute($path, $updateRelation); // yeni ilişkili kategoriye göre git
        } catch (UpdateException $e) {
            DB::rollback();
            event(new $this->events['fail']($e->getDatas()));

            if (is_null($path)) {
                return response()->json($this->returnData('error'));
            }
            Flash::error(trans('laravel-modules-base::admin.flash.update_error'));
            return $this->redirectRoute($path);
        }
    }

    /**
     * destroy data of the eloquent model or models
     *
     * @param \Illuminate\Database\Eloquent\Model|array $model [Model|ids]
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function destroyModel($model, $path = null)
    {
        $this->model = $model;
        try {
            if ( ! $this->model->delete()) {
                throw new DestroyException($this->model);
            }

            event(new $this->events['success']($this->model));

            if (is_null($path)) {
                return response()->json($this->returnData('success'));
            }
            Flash::success(trans('laravel-modules-base::admin.flash.destroy_success'));
            return $this->redirectRoute($path);
        } catch (DestroyException $e) {
            event(new $this->events['fail']($e->getDatas()));

            if (is_null($path)) {
                return response()->json($this->returnData('error'));
            }
            Flash::error(trans('laravel-modules-base::admin.flash.destroy_error'));
            return $this->redirectRoute($path);
        }
    }

    /**
     * publish group action
     *
     * @param $class
     * @return boolean
     */
    protected function publishGroupAction($class)
    {
        try {
            if ( ! $class::whereIn('id', $this->request->id)->update(['is_publish' => true])) {
                throw new UpdateException($this->request->id, 'group not published');
            }
            event(new $this->events['success']($this->request->id));
            return true;
        } catch (UpdateException $e) {
            event(new $this->events['fail']($e->getDatas()));
            return false;
        }
    }

    /**
     * destroy group action
     *
     * @param $class
     * @return boolean
     */
    protected function destroyGroupAction($class)
    {
        $result = $class::destroy($this->request->id);
        if ( is_integer($result) && $result > 0) {
            return true;
        }
        return false;
    }

    /**
     * not publish group action
     *
     * @param $class
     * @return boolean
     */
    protected function notPublishGroupAction($class)
    {
        try {
            if ( ! $class::whereIn('id', $this->request->id)->update(['is_publish' => false])) {
                throw new UpdateException($this->request->id, 'group not not published');
            }
            event(new $this->events['success']($this->request->id));
            return true;
        } catch (UpdateException $e) {
            event(new $this->events['fail']($e->getDatas()));
            return false;
        }
    }

    /**
     * fill model datas to database
     *
     * @param array $datas
     * @return boolean
     */
    protected function fillModel($datas)
    {
        $grouped = collect($datas)->groupBy('relation_type');
        foreach($grouped as $key => $groups) {

            // no relation
            if ($key === 'not') {
                foreach($groups as $group) {
                    $this->model->fill($group['datas'])->save();
                }
                continue;
            }

            // hasOne relation
            if ($key === 'hasOne') {
                foreach($groups as $group) {
                    $relation = $group['relation'];
                    if (is_null($this->model->$relation)) {
                        $this->model->$relation()->save(new $group['relation_model']($group['datas']));
                        continue;
                    }
                    $this->model->$relation->fill($group['datas'])->save();
                }
                continue;
            }

            // hasMany relation
            if ($key === 'hasMany') {
                foreach($groups as $group) {
                    $relation = $group['relation'];
                    $relation_models = [];
                    foreach ($group['datas'] as $data) {
                        $relation_models[] = new $group['relation_model']($data);
                    }
                    $this->model->$relation()->saveMany($relation_models);
                }
                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * get data, if image column passed, except it
     */
    protected function getData()
    {
        if ( ! $this->fileOptions) {
            return $this->request->all();
        }

        $excepts = collect($this->fileOptions)->keyBy(function ($item) {
            $columns = explode('.', $item['column']);
            return count($columns) === 1 ? $columns[0] : $columns[1];
        })->keys()->all();
        return $this->request->except($excepts);
    }

    /**
     * pre upload file control function
     *
     * @param $exception
     */
    private function preUploadFile($exception)
    {
        $datas = [];
        foreach($this->fileOptions as $options) {
            $result = $this->uploadFile($options);
            if ($result !== false) {
                $datas[] = $result;
            }
        }

        if ( ! empty($datas) && ! $this->fillModel($datas)) {
            throw new $exception($this->request->all());
        }
    }

    /**
     * upload file or files
     *
     * @param array $options
     * @return array|boolean
     */
    protected function uploadFile($options)
    {
        if ( $options['type'] === 'file' ) {
            $this->repo = new FileRepository($options);
            $this->hasFile = true;
        } else {
            $this->repo = new ImageRepository($options);
            $this->hasPhoto = true;
        }

        if ( ! $this->repo->upload($this->model, $this->request) ) {
            return false;
        }
        return $this->repo->getDatas();
    }

    /**
     * return data for api
     *
     * @param string $type
     * @return array
     */
    protected function returnData($type)
    {
        $data = ['result' => $type];
        if ( $this->hasPhoto ){
            $data['photos'] = $this->repo->files;
        }
        if ( $this->hasFile ) {
            $data['files'] = $this->repo->files;
        }
        return $data;
    }

    /**
     * return redirect url path
     *
     * @param string $path
     * @param boolean $isUpdate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function redirectRoute($path, $isUpdate = false)
    {
        $indexPos = strpos($path,'index');
        $dotPos = strpos($path,'.');
        $slug = getModelSlug($this->model);

        // İlişkisiz yalın sayfalardan index hariç
        if ( $indexPos === false && $dotPos === false ) {
            return redirect( lmbRoute("admin.{$slug}.{$path}", ['id' => $this->model->id]) );
        }

        // İlişkili sayfalardan index hariç
        if( $indexPos === false ) {
            $id = $isUpdate && ! is_null($this->model->category_id) ? $this->model->category_id : ( $isUpdate ? $this->model->categories->first()->id : $this->relatedId);
            return redirect( lmbRoute("admin.{$path}", [
                'id'                => $id,
                $this->routeRegex   => $this->model->id
            ]) );
        }

        // İlişkisiz sayfalardan index
        if ($dotPos === false) {
            return redirect( lmbRoute("admin.{$slug}.{$path}") );
        }

        // İlişkili sayfalardan index
        return redirect( lmbRoute("admin.{$path}", ['id' => $this->relatedId]) );
    }

    /**
     * set the file options
     *
     * @param array $options
     */
    protected function setFileOptions(array $options)
    {
        $this->fileOptions = $options;
    }

    /**
     * set to file options is file from elfinder
     *
     * @param string $column
     */
    protected function setElfinderToOptions($column)
    {
        $this->fileOptions = collect($this->fileOptions)->map(function($item, $key) use($column)
        {
            if ($item['column'] === $column) {
                $item['isElfinder'] = true;
            }
            return $item;
        })->all();
    }

    /**
     * set the events
     *
     * @param array $events
     */
    protected function setEvents(array $events)
    {
        $this->events = $events;
    }

    /**
     * set the operation relation data
     *
     * @param array $relationData
     */
    protected function setOperationRelation(array $relationData)
    {
        $this->opsRelation = $relationData;
    }

    /**
     * set the relation route data
     *
     * @param integer $id
     * @param string $routeRegex
     */
    protected function setRelationRouteParam($id, $routeRegex)
    {
        $this->relatedId = $id;
        $this->routeRegex = $routeRegex;
    }

    /**
     * set the model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * get the model
     *
     * @return \Illuminate\Database\Eloquent\Model $model
     */
    protected function getModel()
    {
        return $this->model;
    }

    /**
     * update alias method
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $events
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function updateAlias($model, $events = [], $path = null)
    {
        $namespace = getBaseName($model, 'Events');

        $events = $events ? $events : [
            'success'   => "{$namespace}\\UpdateSuccess",
            'fail'      => "{$namespace}\\UpdateFail",
        ];
        $this->setEvents($events);
        return $this->updateModel($model, $path);
    }

    /**
     * group operation alias method
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return boolean
     */
    protected function groupAlias($model)
    {
        $namespace = getBaseName($model, 'Events');
        $events = [];
        switch($this->request->action) {
            case 'activate':
                $events['activationSuccess'] = \ErenMustafaOzdal\LaravelUserModule\Events\Auth\ActivateSuccess::class;
                $events['activationFail'] = \ErenMustafaOzdal\LaravelUserModule\Events\Auth\ActivateFail::class;
                break;
            case 'not_activate':
                $events['activationRemove'] = \ErenMustafaOzdal\LaravelUserModule\Events\Auth\ActivateRemove::class;
                $events['activationFail'] = \ErenMustafaOzdal\LaravelUserModule\Events\Auth\ActivateFail::class;
                break;
            case 'publish':
                $events['success'] = "{$namespace}\\PublishSuccess";
                $events['fail'] = "{$namespace}\\PublishFail";
                break;
            case 'not_publish':
                $events['success'] = "{$namespace}\\NotPublishSuccess";
                $events['fail'] = "{$namespace}\\NotPublishFail";
                break;
            case 'destroy':
                break;
        }
        $this->setEvents($events);
        $action = camel_case($this->request->action) . 'GroupAction';
        return $this->$action($model);
    }
}