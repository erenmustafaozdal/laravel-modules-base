<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;


interface DataTablesInterface
{
    /**
     * get Datatables
     *
     * @param $query
     * @param array $addColumns
     * @param array $editColumns
     * @param array $removeColumns
     * @return \Yajra\Datatables\Datatables
     */
    public function getDatatables($query,array $addColumns = [],array $editColumns = [],array $removeColumns = []);

    /**
     * set data table columns
     *
     * @param array $columns [array('add' => ..., 'edit' => ...,'remove' => ...)]
     * @return void
     */
    public function setColumns(array $columns);
}