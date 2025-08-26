<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\State;
use App\Models\WalletSetting;
use App\Helper\WalletHelper;
use DB;

class WalletDataApiController extends Controller
{
    public function getWalletData($id, $device = 'desktop') {
        $success = false;
        $data = [];
        
        $walletData = WalletHelper::walletData($id, $device);
        if($walletData){
            $success = true;
        }
        $response = [
            'success' => $success,
            'data' => $walletData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function getWalletDataCheckout(Request $request) {
        $success = false;
        $data = [];
        
        $walletsData  = WalletSetting::where('status',1)->first();
        if($walletsData){
            $success = true;
            $data = User::where('id', $request->userid)->first(['id','amount']);
        }
        $response = [
            'success' => $success,
            'data' => $data,
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
