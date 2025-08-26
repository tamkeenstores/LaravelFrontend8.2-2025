<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\AffiliationHelper;
use App\Models\Affiliation;
use DB;

class AffiliationApiController extends Controller
{
    public function getAffiliation($id) {
        $success = false;
        $data = [];
        
        $AffiliationData = AffiliationHelper::AffiliationData($id);
        if($AffiliationData){
            $success = true;
        }
        $response = [
            'success' => $success,
            'data' => $AffiliationData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getCheckAffiliation($slug){
        $success = false;
        $AffiliationData = Affiliation::select(['id', 'slug_code', 'custom_link', 'click'])
        ->where([
            ['status', '=', 1],
            ['disable_rules', '=', 1],
            ['slug_code', '=', $slug],
        ])
        ->where(function ($query) {
            $query->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', now()->toDateString());
        })
        ->where(function ($query) {
            $query->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', now()->toDateString());
        })
        ->first();

        
        if ($AffiliationData) {
            $AffiliationData->click += 1; // Increment the click count.
            $AffiliationData->update(); // Save the changes.
            $success = true;
        }
        
        $response = [
            'success' => $success,
            'data' => $AffiliationData,
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
