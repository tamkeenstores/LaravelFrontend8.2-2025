<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreLocator;
use App\Models\Region;
use App\Models\StoreLocatorCity;
use App\Models\States;
use App\Models\CustomerSurvey;
use App\Models\Order;
use App\Models\ErpSecondDbShowroomOrder;

class CustomerSurveyController extends Controller
{
    
    public function getStoreLocaterDataByLnCode(Request $request) {
        
        $customerSurv = null;
        $lnCode = $request->ln_code;
        $orderNo = $request->order_no;
        $lang = $request->lang ?? 'ar';
        $regionName = $lang == 'ar' ? 'name_arabic' : 'name';
        $address = $lang == 'ar' ? 'address_arabic' : 'address';
        $stores = StoreLocator::select('id',$regionName, $address, 'lat','ln_code', 'lng', 'phone_number', 'time', 'direction_button')
        ->where('ln_code',$lnCode)
        ->first();
        
        $order = ErpSecondDbShowroomOrder::where('order_no', $orderNo)->first(['id', 'order_no', 'created_at']);
        if($orderNo){
            $customerSurv = CustomerSurvey::where('order_no', $orderNo)->first();
        }
        
        $response = [
            'data' => $order,
            'stores' => $stores,
            'success' => $customerSurv ? false : true
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function submitCustomerSurveyForm(Request $request) {
        
        $success = false;
        $customerSurv = null;
        $orderNo = $request->order_no;
        $lang = $request->lang ?? 'ar';
        $order = ErpSecondDbShowroomOrder::where('order_no', $request->order_no)->first();
        if($order){
            $customerSurv = CustomerSurvey::create([
                            // 'order_id' => $order->id,
                            // 'customer_id' => $request->customer_id ?? null,
                            'order_no' => $request->order_no,
                            'customer_name' => $order->first_name ?? null,
                            'customer_phone' => $order->phone_number ?? null,
                            'customer_city' => $order->city ?? null,
                            'showroom_id' => $request->showroom_id ?? null,
                            'answer' => $request->answer ?? null,
                        ]);
        }
        
        $response = [
            'data' => $customerSurv,
            'success' => $customerSurv ? true : false
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}