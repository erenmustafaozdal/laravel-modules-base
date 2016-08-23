<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;

class BaseNodeController extends Controller implements OperationInterface
{
    use OperationNodeTrait;
}