<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Laracasts\Flash\Flash;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use DB;

use ErenMustafaOzdal\LaravelModulesBase\Repositories\ImageRepository;
// exceptions
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\StoreException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\UpdateException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\DestroyException;
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\ActivateException;


abstract class AdminBaseController extends Controller
{
    /**
     * DataTables
     *
     * @var Datatables
     */
    protected $dataTables;

    /**
     * Model name
     *
     * @var string
     */
    protected $model = "";

    /**
     * if is use image repository, image repository object
     * @var ImageRepository
     */
    protected $imageRepo;

    /**
     * get Datatables
     *
     * @param query $query
     * @param array $addColumns
     * @param array $editColumns
     * @param array $removeColumns
     */
    public function getDatatables($query, $addColumns = [], $editColumns = [], $removeColumns = [])
    {
        $this->dataTables = Datatables::of($query);

        // add new urls
        $addUrls = array_has($addColumns, 'addUrls') ? array_pull($addColumns, 'addUrls') : [];
        $this->dataTables->addColumn('urls', function($model) use($addUrls)
        {
            $urls = [
                'details'   => route("api.{$this->getModel($model)}.detail", ['id' => $model->id]),
                'fast_edit' => route("api.{$this->getModel($model)}.fastEdit", ['id' => $model->id]),
                'show'      => route("admin.{$this->getModel($model)}.show", ['id' => $model->id]),
                'edit'      => route("api.{$this->getModel($model)}.update", ['id' => $model->id]),
                'destroy'   => route("api.{$this->getModel($model)}.destroy", ['id' => $model->id]),
            ];
            foreach($addUrls as $key => $value){
                if (isset($value['id']) && $value['id']) {
                    $urls[$key] = route($value['route'], ['id' => $model->id]);
                    continue;
                }
                $urls[$key] = route($value['route']);
            }
            return $urls;
        });

        // add columns
        $this->setColumns($addColumns,'add');
        // edit columns
        $this->setColumns($editColumns,'edit');
        // remove columns
        $this->setColumns($removeColumns,'remove');

        return $this->dataTables->addColumn('check_id', '{{ $id }}')->make(true);
    }

    /**
     * set data table columns
     *
     * @param array $columns
     * @param string $type => add|edit|remove
     */
    private function setColumns($columns, $type)
    {
        switch($type) {
            case 'add':
                foreach($columns as $key => $value) {
                    $this->dataTables->addColumn($key, $value);
                }
                break;
            case 'edit':
                foreach($columns as $key => $value) {
                    $this->dataTables->editColumn($key, $value);
                }
                break;
            case 'remove':
                foreach($columns as $value) {
                    $this->dataTables->removeColumn($value);
                }
                break;
        }
    }

    /**
     * store, flash success or error then redirect or return api result
     *
     * @param $class
     * @param $request
     * @param array $events
     * @param array $imageOptions
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeModel($class, $request, $events, $imageOptions = [], $path = null)
    {
        DB::beginTransaction();
        try {
            $datas = $imageOptions ? $request->except($imageOptions['column']) : $request->all();
            $this->model = $class::create($datas);

            if ( ! isset($this->model->id)) {
                throw new StoreException($request->all());
            }

            // eğer üye kaydı ise ve is_active true var ise
            if ($class === 'App\User' && $request->has('is_active')) {
                $this->activationComplete($this->model, $events);
            }

            if ($imageOptions && $request->has($imageOptions['column'])){
                $datas = $this->getData($request, $imageOptions);
                $this->model->fill([$imageOptions['column'] => $datas[$imageOptions['column']]]);

                if (! $this->model->save()) {
                    throw new StoreException($request->all());
                }
            }

            event(new $events['success']($this->model));
            DB::commit();

            if (is_null($path)) {
                return response()->json($this->returnData('success', $imageOptions));
            }
            Flash::success(trans('laravel-modules-base::admin.flash.store_success'));
            return $this->redirectRoute($path, $this->model);
        } catch (StoreException $e) {
            DB::rollback();
            event(new $events['fail']($e->getDatas()));

            if (is_null($path)) {
                return response()->json($this->returnData('error', $imageOptions));
            }
            Flash::error(trans('laravel-modules-base::admin.flash.store_error'));
            return $this->redirectRoute($path, $this->model);
        }
    }

    /**
     * update, flash success or error then redirect or return api result
     *
     * @param $model
     * @param $request
     * @param array $events
     * @param array $imageOptions
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateModel($model, $request, $events, $imageOptions = [], $path = null)
    {
        $this->model = $model;
        DB::beginTransaction();
        try {
            $this->model->fill($this->getData($request, $imageOptions));
            if ( ! $this->model->save()) {
                throw new UpdateException($this->model);
            }

            event(new $events['success']($this->model));
            DB::commit();

            if (is_null($path)) {
                return response()->json($this->returnData('success', $imageOptions));
            }
            Flash::success(trans('laravel-modules-base::admin.flash.update_success'));
            return $this->redirectRoute($path, $this->model);
        } catch (UpdateException $e) {
            DB::rollback();
            event(new $events['fail']($e->getDatas()));

            if (is_null($path)) {
                return response()->json($this->returnData('error', $imageOptions));
            }
            Flash::error(trans('laravel-modules-base::admin.flash.update_error'));
            return $this->redirectRoute($path, $this->model);
        }
    }

    /**
     * Delete and flash success or fail then redirect or return api result
     *
     * @param $model
     * @param array $events
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroyModel($model, $events, $path = null)
    {
        $this->model = $model;
        try {
            if ( ! $this->model->delete()) {
                throw new DestroyException($this->model);
            }

            event(new $events['success']($this->model));

            if (is_null($path)) {
                return response()->json($this->returnData('success'));
            }
            Flash::success(trans('laravel-modules-base::admin.flash.destroy_success'));
            return $this->redirectRoute($path, $this->model);
        } catch (DestroyException $e) {
            event(new $events['fail']($e->getDatas()));

            if (is_null($path)) {
                return response()->json($this->returnData('error'));
            }
            Flash::error(trans('laravel-modules-base::admin.flash.destroy_error'));
            return $this->redirectRoute($path, $this->model);
        }
    }

    /**
     * set activation complete
     *
     * @param $user
     * @param array $events
     * @return boolean
     */
    protected function activationComplete($user, $events)
    {
        try {
            $activation = Activation::create($user);
            if ( ! Activation::complete($user, $activation->code)) {
                throw new ActivateException($user->id, $activation->code, 'fail');
            }
            $user->is_active = true;
            $user->save();
            event(new $events['activationSuccess']($user));
            return true;
        } catch (ActivateException $e) {
            event(new $events['activationFail']($e->getId(),$e->getActivationCode(), $e->getType()));
            return false;
        }
    }

    /**
     * activation remove
     *
     * @param $user
     * @param array $events
     * @return boolean
     */
    protected function activationRemove($user, $events)
    {
        try {
            if ( ! $activation = Activation::completed($user)) {
                throw new ActivateException($user, '', 'not_completed');
            }

            if ( ! Activation::remove($user)) {
                throw new ActivateException($user, $activation->code, 'not_remove');
            }
            $user->is_active = false;
            $user->save();
            event(new $events['activationRemove']($user));
            return true;
        } catch (ActivateException $e) {
            event(new $events['activationFail']($e->getId(),$e->getActivationCode(), $e->getType()));
            return false;
        }
    }

    /**
     * activate group action
     *
     * @param $class
     * @param array $ids
     * @param array $events
     * @return boolean
     */
    protected function activateGroupAction($class, $ids, $events)
    {
        $users = $class::whereIn('id', $ids)->get();
        foreach($users as $user) {
            $this->activationComplete($user,$events);
        }
        return true;
    }

    /**
     * not activate group action
     *
     * @param $class
     * @param array $ids
     * @param array $events
     * @return boolean
     */
    protected function notActivateGroupAction($class, $ids, $events)
    {
        $users = $class::whereIn('id', $ids)->get(['id']);
        foreach($users as $user) {
            $this->activationRemove($user, $events);
        }
        return true;
    }

    /**
     * destroy group action
     *
     * @param $class
     * @param array $ids
     * @param array $events
     * @return boolean
     */
    protected function destroyGroupAction($class, $ids, $events)
    {
        $result = $class::destroy($ids);
        if ( is_integer($result) && $result > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get data, if image column is passed, upload it
     *
     * @param $request
     * @param array $imageOptions
     * @return mixed
     */
    protected function getData($request, $imageOptions)
    {
        if ( ! $imageOptions || ! $request->file($imageOptions['column'])){
            return $request->all();
        }
        $this->imageRepo = new ImageRepository();
        $datas = $request->except($imageOptions['column']);
        $this->imageRepo->uploadPhoto($this->model, $request, $imageOptions);
        $datas[$imageOptions['column']] = $this->imageRepo->photoName;
        return $datas;
    }

    /**
     * return data for api
     *
     * @param string $type
     * @param array $imageOptions
     * @return array
     */
    protected function returnData($type, $imageOptions = [])
    {
        $data = ['result' => $type];
        if ($imageOptions){
            $data['photos'] = $this->imageRepo->photos;
        }
        return $data;
    }

    /**
     * return redirect url path
     *
     * @param string $path
     * @param boolean|false $model
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function redirectRoute($path, $model = false)
    {
        if ($path !== 'index') {
            return redirect( route($this->routePath($path), ['id' => $model->id]) );
        }
        return redirect( route($this->routePath($path)) );
    }

    /**
     * Returns route path as string
     *
     * @param string $path
     * @return string
     */
    public function routePath($path = "index")
    {
        return 'admin.' . $this->getModel($this->model) . '.' . $path;
    }

    /**
     * get model slug
     *
     * @param $model
     * @return string
     */
    protected function getModel($model)
    {
        return snake_case( substr( strrchr( get_class($model), '\\' ), 1 ) );
    }
}
