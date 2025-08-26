<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReturnRefund;
use App\Models\ReturnProducts;
use App\Models\ReturnQuestions;
use App\Models\ReturnReasons;
use App\Models\Order;

use DB;

class ReturnRefundController extends Controller
{
    public function getReturnQuestions() {
        $questions = ReturnQuestions::where('status', 1)->select('id', 'question', 'question_arabic', 'status')
        ->with('answers')
        ->get();
        $response = [
            'data' => $questions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUserReturnOrders($id) {
        $orderStatus = 8;
        $orders = Order::where('customer_id', $id)->where('status', $orderStatus)
        ->whereHas('statustimeline', function ($query) use ($orderStatus) {
                return $query->where('status', $orderStatus)->whereRaw('DATEDIFF(NOW(), created_at)<=7');
        })
        ->leftJoin('order_summary as totaldata', function($join) {
            $join->on('totaldata.order_id', '=', 'order.id');
            $join->on('totaldata.type', '=', DB::raw("'total'"));
        })
        ->select('order.id', 'order_no', 'order.created_at', 'totaldata.price')
        ->withCount('details')
        ->get();
        $response = [
            'orders' => $orders,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUserReturns($id) {
        $returndata = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"))
        ->with('returnData.products', 'returnData.products.OrderDetailData', 'returnData.products.ProductData:id,name,name_arabic,sku')
        ->first();
        $response = [
            'returndata' => $returndata,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function CreateReturn(Request $request) {
        $randomNumber = mt_rand(100000, 999999);
        $returnno = 'RR-' . $randomNumber;
        
        $return = ReturnRefund::create([
            'return_no' => $returnno,
            'order_id' => isset($request->order_id) ? $request->order_id : null,
            'user_id' => isset($request->user_id) ? $request->user_id : null,
            // 'reason_return' => isset($request->reason_return) ? $request->reason_return : null,
            // 'issues' => isset($request->issues) ? $request->issues : null,
        ]);
        
        $returnproducts = ReturnProducts::create([
            'return_id' => $return->id,
            'order_detail_id' => isset($request->order_detail_id) ? $request->order_detail_id : null,
            'product_id' => isset($request->product_id) ? $request->product_id : null,
            'quantity' => isset($request->quantity) ? $request->quantity : null,
            'unit_price' => isset($request->unit_price) ? $request->unit_price : null,
            'comment' => isset($request->comment) ? $request->comment : null,
        ]);
        
        foreach($request->reasons as $key => $value){
            ReturnReasons::create([
                'return_id' => $return->id,
                'question_id' => $key,
                'answer_id' => $value,
                
            ]);
        }
        
        $response = [
            'success' => 'true',
            'message' => 'Return Request Created Successfully!',
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
