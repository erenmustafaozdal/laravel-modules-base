<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Services;

use Illuminate\Support\Collection;

class CollectionService
{

    /**
     * glue between items
     *
     * @var string
     */
    protected $glue;

    /**
     * get this key values of collection
     *
     * @var array
     */
    protected $keys;

    /**
     * collection relation
     *
     * @var array
     */
    protected $relation;

    /**
     * collection results
     *
     * @var array
     */
    protected $results = [];



    /*
    |--------------------------------------------------------------------------
    | Collection render with relation
    | its render the nested category
    |
    | Input:
    | - Category
    |    - Sub Category
    |
    | Result: Category{Glue: maybe '/'}Sub Category
    |--------------------------------------------------------------------------
    */

    /**
     * get rendered collection with relation
     *
     * @param Collection $items
     * @param string $relation
     * @param string $glue
     * @param array $keys
     * @return Collection
     */
    public function relationRender(Collection $items, $relation, $glue = '/', $keys = ['name'])
    {
        $this->glue = $glue;
        $this->keys = $keys;
        $this->relation = $relation;
        return collect( $this->getOptions($items) );
    }

    /**
     * get options of collection
     *
     * @param Collection $items
     * @param string $glue
     * @return array
     */
    private function getOptions(Collection $items, $glue = '')
    {
        $relation = $this->relation;
        foreach($items as $item) {
            $model = $this->getValues($item,$glue);
            $item->parents = $glue . $item->name_uc_first;
            $this->results[] = $model;
            if ($item->$relation->count() > 0) {
                $this->getOptions($item->$relation,$item->parents . $this->glue);
            }
        }
        return $this->results;
    }

    /**
     * get key values
     *
     * @param $item
     * @param string $glue
     * @return array
     */
    private function getValues($item, $glue)
    {
        $datas = [
            'id'        => $item->id,
            'parents'   => $glue . $item->parents
        ];
        foreach($this->keys as $key) {
            $datas[$key] = $item->$key;
        }
        return $datas;
    }
}