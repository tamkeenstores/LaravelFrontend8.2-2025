<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tax;
use App\Models\Country;
use App\Models\States;
use App\Traits\CrudTrait;

class TaxApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'tax';
    protected $relationKey = 'tax_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Tax::class, 'sort' => ['id','asc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        return [];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return [
                'countries' => Country::get(),
                'states' => States::where('country_id','191')->orderby('id', 'DESC')->get(['id', 'name', 'name_arabic', 'city_code']),
         ];
    }
}
