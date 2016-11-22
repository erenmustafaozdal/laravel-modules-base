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
            $isGroup = is_integer($column) && is_array($optionName) && isset($optionName['group']);
            $configName = $isGroup || isset($optionName['column']) ? $optionName['config'] : $optionName;
            $columnParts = explode('.',($isGroup || isset($optionName['column']) ? $optionName['column'] : $column));
            $inputName = count($columnParts) > 1 ? $columnParts[1] : $columnParts[0];
            $inputName = isset($optionName['inputPrefix']) ? $optionName['inputPrefix'] . $inputName : $inputName;
            $fullColumn = implode('.', $columnParts);

            // options set edilir
            if (
                ($isGroup && (
                    is_array($request->file($optionName['group'])) && $request->file("{$optionName['group']}.{$column}.{$inputName}")
                    || $request->has("{$optionName['group']}.{$column}.{$inputName}")
                    )
                )
                || ( (is_array($request->file($inputName)) && $request->file($inputName)[0])
                    || (!is_array($request->file($inputName)) && $request->file($inputName))
                )
                || $request->has($inputName)
            ) {
                $moduleOptions = config("{$module}.{$model}.uploads.{$configName}");
                // if column is array
                if (is_array($moduleOptions['column'])) {
                    $moduleOptions['column'] = $moduleOptions['column'][$inputName];
                }
                // is group
                if ($isGroup) {
                    $moduleOptions['group'] = $optionName['group'];
                }
                // add some data
                if ($isGroup || isset($optionName['column'])) {
                    $moduleOptions['index'] = $column;
                    if (isset($optionName['changeThumb'])) $moduleOptions['changeThumb'] = $optionName['changeThumb'];
                    if (isset($optionName['is_reset'])) $moduleOptions['is_reset'] = $optionName['is_reset'];
                    $moduleOptions['add_column'] = isset($optionName['add_column']) ? $optionName['add_column'] : [];
                    $moduleOptions['inputPrefix'] = isset($optionName['inputPrefix']) ? $optionName['inputPrefix'] : [];
                }
                array_push($options, $moduleOptions);
            }
            // elfinder mi belirtilir
            if (
                ($isGroup && $request->has("{$optionName['group']}.{$column}.{$inputName}"))
                || ($request->has($inputName) && ! $request->file($inputName)[0])
            ) {
                $elfinderOption = $isGroup || isset($optionName['column'])  ? ['index' => count($options)-1, 'column' => $optionName['column']] : $fullColumn;
                array_push($elfinders, $elfinderOption);
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