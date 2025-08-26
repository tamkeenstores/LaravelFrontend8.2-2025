<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockAlert;

class StockAlertController extends Controller
{
    public function checkStockAlertProduct(Request $request) {
        $data = $request->all();
        $success = false;
        $userId = $data['user_id'] ?? null;
        $productId = $data['product_id'] ?? null;
        
        if ($userId && $productId) {
            $stockalert = StockAlert::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
            if($stockalert){
                $success = true;
            }
        }
        $response = [
            'success' => $success
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function addStockAlert(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $wislistsData = StockAlert::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($wislistsData){
            $msg = 'Stock Alert Already setup For this Product!';
        }else{
            $priceAlert = StockAlert::create([
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
                'product_qty' => $data['product_sale_price'],
            ]);
            $success = true;
            $msg = 'This product has been added in the Stock Alert!';
        }
            
        $response = [
            'success' => $success,
            'msg' => $msg
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function removeStockAlert(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $pricealertData = StockAlert::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($pricealertData){
            $pricealertData->delete();
            $success = true;
            $msg = 'This product has been deleted in the Stock Alert!';
        }else{
            $msg = 'This product has not in the Stock Alert!';
        }
            
        $response = [
            'success' => $success,
            'msg' => $msg
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
