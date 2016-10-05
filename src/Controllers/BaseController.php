<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;
use Config;

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
            if ( ( (is_array($request->file($inputName)) && $request->file($inputName)[0]) || (!is_array($request->file($inputName)) && $request->file($inputName)) ) || $request->has($inputName)) {
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

    /**
     * change options with model category
     *
     * @param $category
     * @return void
     */
    protected function changeOptions($category)
    {
        $thumbnails = $category->ancestorsAndSelf()->with('thumbnails')->get()->map(function($item)
        {
            return $item->thumbnails->keyBy('slug')->map(function($item)
            {
                return [ 'width' => $item->photo_width, 'height' => $item->photo_height ];
            });
        })->reduce(function($carry,$item)
        {
            return $carry->merge($item);
        },collect())->toArray();
        Config::set('laravel-document-module.document.uploads.photo.aspect_ratio', $category->aspect_ratio);
        Config::set('laravel-document-module.document.uploads.photo.thumbnails', $thumbnails);
    }
}