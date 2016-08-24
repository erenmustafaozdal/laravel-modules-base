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
     * @var string
     */
    protected $key;

    /**
     * collection relation
     *
     * @var string
     */
    protected $relation;

    /**
     * collection results
     *
     * @var array
     */
    protected $results = [];

    /**
     * get rendered collection with relation
     *
     * @param Collection $items
     * @param string $relation
     * @param string $glue
     * @param string $key
     * @return Collection
     */
    public function relationRender(Collection $items, $relation, $glue = '/', $key = 'name')
    {
        $this->glue = $glue;
        $this->key = $key;
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
            $item->parents = $glue . $item->name;
            $this->results[] = $model;
            if ($item->$relation->count() > 0) {
                $this->getOptions($item->$relation,$item->parents . '/');
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
        $key = $this->key;
        return [
            'id'        => $item->id,
            $key        => $item->$key,
            'parents'   => $glue . $item->parents
        ];
    }
}