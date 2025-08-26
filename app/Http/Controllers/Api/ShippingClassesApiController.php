<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingClasses;
use App\Traits\CrudTrait;

class ShippingClassesApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'shipping_classes';
    protected $relationKey = 'shipping_classes';


    public function model() {
        $data = ['limit' => -1, 'model' => ShippingClasses::class, 'sort' => ['id','desc']];
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
        // return ['id' => 'region', 'id' => 'productsCount'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return []; 
    }
    public function multidelete(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $deletetags = ShippingClasses::whereIn('id',$ids)->get();
            $deletetags->each->delete();
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Selected Shipping Classes Has been deleted!']);
            
    }
}
