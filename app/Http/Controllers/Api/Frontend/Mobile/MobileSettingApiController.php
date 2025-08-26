<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileSetting;

class MobileSettingApiController extends Controller
{
    public function SettingData(Request $request) {
        $settingdata = MobileSetting::first();
        if($settingdata->project_sale_status == 1) {
            $projectsaledata = MobileSetting::select('project_sale_heading','project_sale_heading_arabic', 'project_sale_image', 'project_sale_image_arabic')
            ->with('ImageEn:id,image', 'ImageAr:id,image')->get();
        }
        else {
            $projectsaledata = [];
        }
        
        $response = [
            'projectsaledata' => $projectsaledata,
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
