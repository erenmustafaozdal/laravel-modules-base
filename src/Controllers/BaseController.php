<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller implements DataTablesInterface, OperationInterface
{
    use DataTableTrait, OperationTrait;
}