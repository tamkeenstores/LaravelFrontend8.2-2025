<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PriceAlert;

class PriceAlertController extends Controller
{
    public function checkPriceAlertProduct(Request $request) {
        $data = $request->all();
        $success = false;
        $userId = $data['user_id'] ?? null;
        $productId = $data['product_id'] ?? null;
        
        if ($userId && $productId) {
            $pricealert = PriceAlert::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
            if($pricealert){
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
    
    public function addPriceAlert(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $wislistsData = PriceAlert::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($wislistsData){
            $msg = 'Price Alert Already setup For this PRoduct !';
        }else{
            $priceAlert = PriceAlert::create([
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
                'product_sale_price' => $data['product_sale_price'],
            ]);
            $success = true;
            $msg = 'This product has been added in the Price Alert!';
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
    
    public function removePriceAlert(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $pricealertData = PriceAlert::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($pricealertData){
            $pricealertData->delete();
            $success = true;
            $msg = 'This product has been deleted in the Price Alert!';
        }else{
            $msg = 'This product has not in the Price Alert!';
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
