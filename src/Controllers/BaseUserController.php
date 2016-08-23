<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;
use Cartalyst\Sentinel\Laravel\Facades\Activation;

// exceptions
use ErenMustafaOzdal\LaravelModulesBase\Exceptions\ActivateException;

class BaseUserController extends Controller implements DataTablesInterface, OperationInterface
{
    use DataTableTrait;
    use OperationTrait {
        storeModel as traitStoreModel;
        updateModel as traitUpdateModel;
    }

    /**
     * store data of the eloquent model
     *
     * @param $class
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeModel($class, $path = null)
    {
        $result = $this->traitStoreModel($class, $path);

        if ($this->request->is_active) {
            $this->activationComplete();
            return $result;
        }

        $this->activationRemove();
        return $result;
    }

    /**
     * update data of the eloquent model
     *
     * @param $class
     * @param string|null $path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateModel($class, $path = null)
    {
        $result = $this->traitUpdateModel($class, $path);

        if ($this->request->has('is_active') && $this->request->is_active) {
            $this->activationComplete();
            return $result;
        } else if ($this->request->has('is_active')) {
            $this->activationRemove();
        }
        return $result;
    }

    /**
     * set activation complete
     *
     * @return boolean
     */
    protected function activationComplete()
    {
        try {
            $activation = Activation::create($this->model);
            if ( ! Activation::complete($this->model, $activation->code)) {
                throw new ActivateException($this->model->id, $activation->code, 'fail');
            }

            if ($this->callerActivationMethod(debug_backtrace())) {
                $this->model->is_active = true;
                $this->model->save();
            }

            event(new $this->events['activationSuccess']($this->model));
            return true;
        } catch (ActivateException $e) {
            $this->model->is_active = false;
            $this->model->save();
            event(new $this->events['activationFail']($e->getId(),$e->getActivationCode(), $e->getType()));
            return false;
        }
    }

    /**
     * activation remove
     *
     * @return boolean
     */
    protected function activationRemove()
    {
        try {
            if ( ! $activation = Activation::completed($this->model)) {
                throw new ActivateException($this->model, '', 'not_completed');
            }

            if ( ! Activation::remove($this->model)) {
                throw new ActivateException($this->model, $activation->code, 'not_remove');
            }

            if ($this->callerActivationMethod(debug_backtrace())) {
                $this->model->is_active = false;
                $this->model->save();
            }

            event(new $this->events['activationRemove']($this->model));
            return true;
        } catch (ActivateException $e) {
            event(new $this->events['activationFail']($e->getId(),$e->getActivationCode(), $e->getType()));
            return false;
        }
    }

    /**
     * activate group action
     *
     * @param $class
     * @return boolean
     */
    protected function activateGroupAction($class)
    {
        $users = $class::whereIn('id', $this->request->id)->get();
        foreach($users as $user) {
            $this->setModel($user);
            $this->activationComplete();
        }
        return true;
    }

    /**
     * not activate group action
     *
     * @param $class
     * @return boolean
     */
    protected function notActivateGroupAction($class)
    {
        $users = $class::whereIn('id', $this->request->id)->get();
        foreach($users as $user) {
            $this->setModel($user);
            $this->activationRemove();
        }
        return true;
    }

    /**
     * get the caller activation method or not
     *
     * @param array $trace
     * @return boolean
     */
    private function callerActivationMethod($trace)
    {
        return in_array( deBackFunction($trace), [
            'activate',
            'notActivate',
            'activateGroupAction',
            'notActivateGroupAction'
        ]);
    }
}