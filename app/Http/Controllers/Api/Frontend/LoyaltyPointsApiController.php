<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoyaltyPoints;
use App\Models\LoyaltyTransactions;
use App\Models\State;
use App\Helper\LoyaltyPointsHelper;
use DB;

class LoyaltyPointsApiController extends Controller
{
    public function getloyaltyPoints($id, $device = 'desktop') {
        $success = false;
        $data = [];
        $loyaltyPointsDataa = false;
        
        $loyaltyPointsDataa = LoyaltyPointsHelper::LoyaltyPointsData($id, $device);
        if($loyaltyPointsDataa){
            $success = true;
        }
        $response = [
            'success' => $success,
            'data' => $loyaltyPointsDataa,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUserLoyaltyData($id) {
        $user = User::where('id', $id)->first(['id', 'phone_number']);
        $loyaltyData = '';
        if($user) {
            $loyaltyData = LoyaltyPoints::where('mobile_number', $user->phone_number)->first();
        }
        
        $response = [
            'data' => $loyaltyData
        ];

        $responseJson = json_encode($response);
        $data = gzencode($responseJson, 9);

        return response($data)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    //erp loyality history
    public function getERPUserLoyaltyDataHisoty($id) {
        $user = User::where('id', $id)->first(['id', 'phone_number']);
        $getUserLoyality = '';
        if($user) {
            $mobile = $user->phone_number ?? "";
            $mobile = str_replace([' ', '-', '_'], '', $mobile);
            if (substr($mobile, 0, 4) === '+966') {
                $mobile = substr($mobile, 4);
            } elseif (substr($mobile, 0, 3) === '966') {
                $mobile = substr($mobile, 3);
            } elseif (substr($mobile, 0, 5) === '00966') {
                $mobile = substr($mobile, 5);
            }
            $mobile = '0' . ltrim($mobile, '0');
            $getUserLoyality = LoyaltyTransactions::where(function ($query) use ($mobile) {
                    $query->where('mobile_number', $mobile)             // try with 0
                          ->orWhere('mobile_number', ltrim($mobile, '0')); // try without 0
                })->select(['id', 'mobile_number', 'type', 'usage', 'earning_against', 'loyalty_points', 't_loyaltypoints', 'total_amount', 'date', 'order_number', 'update_from'])->get();
        }
        
        $response = [
            'data' => $getUserLoyality
        ];

        $responseJson = json_encode($response);
        $data = gzencode($responseJson, 9);

        return response($data)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
}
