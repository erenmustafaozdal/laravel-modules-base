<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Services;

use Illuminate\Support\Collection;

class CollectionService
{
    /*
    |--------------------------------------------------------------------------
    | Nested Baum Collection get with ancestors and self
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
     * ancestors and self render and get
     *
     * @param Collection $items
     * @param string $glue
     * @param array $keys
     * @return array
     */
    public function renderAncestorsAndSelf($items, $glue = '/', $keys = ['name'])
    {
        $items = $items->map(function($item,$key) use($keys, $glue)
        {
            $ancSelf = $item->ancestorsAndSelf()->get();
            $result = [ 'id' => $item->id];
            foreach ($keys as $k) {
                $plucks = $ancSelf->pluck($k);
                $plucks->pop();
                $result['parent_' . $k] = $plucks->implode($glue);
                $result[$k] = $item->$k;
            }
            return $result;
        });
        $sortedGroups = $items->groupBy('parent_' . $keys[0])->map(function($item) use($keys)
        {
            return $item->sortBy(function ($item, $key) use($keys) {
                return str_slug($item[$keys[0]]);
            });
        });
        $result = [];
        foreach($sortedGroups as $group) {
            $result = array_merge_recursive($result,$group->all());
        }
        return $result;
    }
}