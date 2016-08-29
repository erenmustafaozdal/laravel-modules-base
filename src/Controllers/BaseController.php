<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller implements DataTablesInterface, OperationInterface
{
    use DataTableTrait, OperationTrait;

    /**
     * set file options
     *
     * @param \Illuminate\Http\Request $request
     * @param array $params [ ['column_name' => 'option_name'] ]
     * @return void
     */
    protected function setToFileOptions($request, $params)
    {
        $module = getModule(get_called_class());
        $model = getModelSlug(get_called_class());
        $options = [];
        $elfinders = [];
        foreach($params as $column => $optionName) {
            $columnParts = explode('.',$column);
            $inputName = count($columnParts) > 1 ? $columnParts[1] : $columnParts[0];
            $fullColumn = implode('.', $columnParts);

            // options set edilir
            if ($request->file($inputName)[0] || $request->has($inputName)) {
                array_push($options, config("{$module}.{$model}.uploads.{$optionName}"));
            }
            // elfinder mi belirtilir
            if ($request->has($inputName) && ! $request->file($inputName)[0]) {
                array_push($elfinders, $fullColumn);
            }
        }
        $this->setFileOptions($options);
        foreach($elfinders as $elfinder) {
            $this->setElfinderToOptions($elfinder);
        }
    }
}