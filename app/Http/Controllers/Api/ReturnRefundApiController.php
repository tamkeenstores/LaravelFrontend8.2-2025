<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnRefund;
use App\Models\ReturnReasons;
use App\Models\ReturnProducts;
use App\Models\Brand;
use App\Traits\CrudTrait;

class ReturnRefundApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'return_refund';
    protected $relationKey = 'return_refund_id';


    public function model() {
        $data = ['limit' => -1, 'model' => ReturnRefund::class, 'sort' => ['id','desc']];
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
        return ['Userdata_id' => 'UserData:id,first_name,last_name,phone_number,email', 'Orderdata_id' => 'OrderData:id,order_no,status,created_at,customer_id,erp_fetch_date,shipping_id,madac_id',
        'products_id' => 'products', 'address' => 'OrderData.Address.stateData', 'orderDetail' => 'products.OrderDetailData.productData:id,sku', 'reasons_data' => 'reasons.answer', 'reasons_data2' => 'reasons.question'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return [];
    }
    
    public function destroy($id) {
        $success = false;
        $data = ReturnRefund::where('id', $id)->first();
        $return = ReturnReasons::where('return_id', $id)->get();
        $rpro = ReturnProducts::where('return_id', $id)->get();
        if($data) {
            $data->delete();
            if(count($return) >= 1) {
                $return->each->delete();
            }
            if(count($rpro) >= 1) {
                $rpro->each->delete();
            }
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
    
    public function multidelete(Request $request)
    {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $data = ReturnRefund::whereIn('id', $ids)->get();
            $return = ReturnReasons::whereIn('return_id', $ids)->get();
            $rpro = ReturnProducts::whereIn('return_id', $ids)->get();
            if(count($data)) {
                foreach($data as $dat) {
                    $dat->delete();
                }  
                if(count($return) >= 1) {
                    $return->each->delete();
                }
                if(count($rpro) >= 1) {
                    $rpro->each->delete();
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
