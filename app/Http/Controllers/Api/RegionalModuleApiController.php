<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegionalModule;
use App\Models\States;
use App\Traits\CrudTrait;

class RegionalModuleApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'regional_modules';
    protected $relationKey = 'regional_modules_id';


    public function model() {
        $data = ['limit' => -1, 'model' => RegionalModule::class, 'sort' => ['id','desc']];
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
        return ['citydata_id' => 'citydata:id,name'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['states' => States::where('country_id','191')->get(['id as value', 'name as label'])];
        // return ['city' => States::get(['id', 'name'])];
    }
}
