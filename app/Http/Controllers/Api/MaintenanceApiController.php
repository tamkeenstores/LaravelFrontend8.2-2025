<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenance;
use App\Models\Product;
use App\Models\MaintainenceDisProducts;
use App\Traits\CrudTrait;

class MaintenanceApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'maintenance';
    protected $relationKey = 'maintenance_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Maintenance::class, 'sort' => ['id','desc']];
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
        return ['order_no'=> 'OrderData.Address.stateData', 'user_data' => 'UserData', 'pro_data' => 'ProData.featuredImage'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
    
    public function getDisabledProductData()
    {
        $data = MaintainenceDisProducts::get();
        return response()->json(['data' => $data]);
    }
    
    public function AddDisabledPro(Request $request)
    {
        if (isset($request->disabled_product_id)) {
                MaintainenceDisProducts::truncate();
             foreach ($request->disabled_product_id as $k => $value) {
                $data = [
                    'disabled_product_id' => isset($value['product_id']) ? $value['product_id'] : null,
                    'disabled_product_sku' => isset($value['product_sku']) ? $value['product_sku'] : null,
                ];
        
                MaintainenceDisProducts::create($data);
             }
         }
         return response()->json(['success' => true, 'message' => 'Disabled Products Has been saved!']);
    }
    
    public function destroy($id) {
        $success = false;
        $data = Maintenance::where('id', $id)->first();
        if($data) {
            $data->delete();
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
    
    public function multidelete(Request $request)
    {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $data = Maintenance::whereIn('id', $ids)->get();
            if(count($data)) {
                foreach($data as $dat) {
                    $dat->delete();
                }   
            }
            $success = true;
        }
        $response = ['success' => $success, 'message' => 'Maintenance Has been deleted!'];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
