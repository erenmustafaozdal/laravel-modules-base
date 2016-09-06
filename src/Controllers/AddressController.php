<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Province;
use App\County;
use App\District;
use App\Neighborhood;
use App\PostalCode;

class AddressController extends Controller
{
    /**
     * get the all provinces
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function provinces(Request $request)
    {
        return Province::where('province', 'like', "{$request->input('query')}%")->get();
    }

    /**
     * get the specific province counties
     *
     * @param Province $province
     * @return \Illuminate\Support\Collection
     */
    public function counties(Province $province)
    {
        return $province->counties;
    }

    /**
     * get the specific county districts
     *
     * @param County $county
     * @return \Illuminate\Support\Collection
     */
    public function districts(County $county)
    {
        return $county->districts;
    }

    /**
     * get the specific district neighborhoods
     *
     * @param District $district
     * @return \Illuminate\Support\Collection
     */
    public function neighborhoods(District $district)
    {
        return $district->neighborhoods;
    }

    /**
     * get the specific neighborhood postal code
     *
     * @param Neighborhood $neighborhood
     * @return PostalCode
     */
    public function postalCode(Neighborhood $neighborhood)
    {
        return $neighborhood->postalCode;
    }
}