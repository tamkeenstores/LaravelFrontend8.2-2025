<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\States;
use App\Traits\CrudTrait;

class RegionApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'region';
    protected $relationKey = 'region_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Region::class, 'sort' => ['id','desc']];
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
        return ['citydata_id' => 'citydata:id,name,region', 'cityname_id' => 'cityname:id,name,region'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['states' => States::where('country_id','191')->get(['id as value', 'name as label'])];
    }
}
