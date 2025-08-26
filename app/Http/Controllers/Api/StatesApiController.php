<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\States;
use App\Traits\CrudTrait;

class StatesApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'shipping_location';
    protected $relationKey = 'shipping_location_id';


    public function model() {
        $data = ['limit' => -1, 'model' => States::class, 'sort' => ['id','desc']];
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
        return ['region_id' => 'region:id,name'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['region' => Region::get(['id as value', 'name as label'])];
    }
}
