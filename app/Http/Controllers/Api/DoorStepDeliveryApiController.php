<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoorStepDelivery;
use App\Models\Productcategory;
use App\Models\Brand;
use App\Models\Product;
use App\Traits\CrudTrait;

class DoorStepDeliveryApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'door_step_delivery';
    protected $relationKey = 'door_step_delivery_id';


    public function model() {
        $data = ['limit' => -1, 'model' => DoorStepDelivery::class, 'sort' => ['id','desc']];
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
        return ['brand_id' => 'brands:id,name', 'productcategory_id' => 'productcategory:id,name', 'product_id' => 'products:id,name,sku'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         ];
    }
}
