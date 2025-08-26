<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\State;
use App\Helper\DiscountRulesHelper;
use DB;

class DiscountRuleController extends Controller
{
    public function getDiscountRule(Request $request, $device = 'desktop') {
        $success = false;
        $data = $request->all();
        
        $discountData = DiscountRulesHelper::ruleData($data, $device);
        if($discountData)
        $success = true;
        $response = [
            'success' => $success,
            'data' => $discountData,
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
