<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Region;
use App\Traits\CrudTrait;

class WarehouseController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'warehouse';
    protected $relationKey = 'warehouse_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Warehouse::class, 'sort' => ['id','desc']];
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
        return ['media' => 'featuredImageWeb', 'city_id' => 'cityData'];
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
