<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\State;
use App\Helper\GiftVoucherHelper;
use DB;

class GiftVoucherApiController extends Controller
{
    public function getGiftVoucher($id, $device = 'desktop') {
        $success = false;
        $data = [];
        
        $giftVoucherData = GiftVoucherHelper::giftVoucherData($id, $device);
        if($giftVoucherData){
            $success = true;
        }
        $response = [
            'success' => $success,
            'data' => $giftVoucherData,
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
