<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\State;
use App\Helper\CouponHelper;
use DB;

class CouponController extends Controller
{
    public function getCoupon(Request $request, $device = 'desktop') {
        $success = false;
        $data = $request->all();
        
        $couponData = CouponHelper::couponData($data, $device);
        if($couponData)
        $success = true;
        $response = [
            'success' => $success,
            'data' => $couponData,
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
