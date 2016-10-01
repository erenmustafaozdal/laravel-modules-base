<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;

class BaseNodeController extends Controller implements OperationInterface
{
    use OperationNodeTrait;

    /**
     * default relation datas
     *
     * @var array
     */
    private $relations = [
        'thumbnails' => [
            'relation_type'     => 'hasMany',
            'relation'          => 'thumbnails',
            'relation_model'    => '\App\DocumentThumbnail',
            'datas'             => null
        ],
        'extras' => [
            'relation_type'     => 'hasMany',
            'relation'          => 'extras',
            'relation_model'    => '\App\DocumentExtra',
            'datas'             => null
        ]
    ];

    /**
     * set the relations
     *
     * @param $request
     * @return void
     */
    protected function setRelation($request)
    {
        $this->changeRelationModel();
        $relation = [];
        if ($request->has('group-thumbnail')) {
            $this->relations['thumbnails']['datas'] = collect($request->get('group-thumbnail'))->reject(function($item)
            {
                return ! $item['thumbnail_slug'] || ! $item['thumbnail_width'] || ! $item['thumbnail_height'];
            })->map(function($item,$key)
            {
                $item['slug'] = $item['thumbnail_slug'];
                unsetReturn($item,'thumbnail_slug');
                $item['photo_width'] = $item['thumbnail_width'];
                unsetReturn($item,'thumbnail_width');
                $item['photo_height'] = $item['thumbnail_height'];
                unsetReturn($item,'thumbnail_height');
                return $item;
            });
            if ($this->relations['thumbnails']['datas']->count() > 0) $relation[] = $this->relations['thumbnails'];
        }
        if ($request->has('group-extra')) {
            $this->relations['extras']['datas'] = collect($request->get('group-extra'))->reject(function($item)
            {
                return ! $item['extra_name'] || ! $item['extra_type'];
            })->map(function($item,$key)
            {
                $item['name'] = $item['extra_name'];
                unsetReturn($item,'extra_name');
                $item['type'] = $item['extra_type'];
                unsetReturn($item,'extra_type');
                return $item;
            });
            if ($this->relations['extras']['datas']->count() > 0) $relation[] = $this->relations['extras'];
        }
        $this->setOperationRelation($relation);
    }

    /**
     * set relation define datas
     *
     * @param $parent
     * @return void
     */
    protected function setRelationDefine($parent)
    {
        $this->changeRelationModel();
        $this->setDefineValues(['has_description','has_photo','show_title','show_description','show_photo','datatable_filter','datatable_tools','datatable_fast_add','datatable_group_action','datatable_detail','description_is_editor','config_propagation','photo_width','photo_height']);
        $this->relations['thumbnails']['datas'] = $parent->thumbnails()->get(['slug','photo_width','photo_height'])->toArray();
        $this->relations['extras']['datas'] = $parent->extras()->get(['name','type'])->toArray();
        $this->setOperationRelation($this->relations);
    }

    /**
     * change relation model
     */
    protected function changeRelationModel()
    {
        $module = getModule(get_called_class());
        $module = explode('-',$module);
        $module = ucfirst_tr($module[1]);
        $this->relations['thumbnails']['relation_model'] = "\\App\\{$module}Thumbnail";
        $this->relations['extras']['relation_model'] = "\\App\\{$module}Extra";
    }
}