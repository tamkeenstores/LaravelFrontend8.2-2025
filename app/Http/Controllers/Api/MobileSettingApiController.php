<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileSetting;

class MobileSettingApiController extends Controller
{
    public function index(Request $request) {
        
        $data = MobileSetting::with('ImageEn:id,image', 'ImageAr:id,image')->first();
        
        return response()->json(['data' => $data]);
    }
    
    public function store(Request $request) 
    {
        $data = MobileSetting::with('ImageEn:id,image', 'ImageAr:id,image')->first();
        if($data) {
             
             $general = MobileSetting::whereId($data->id)->update([
                'project_sale_image' => isset($request->project_sale_image) ? $request->project_sale_image : null,
                'project_sale_image_arabic' => isset($request->project_sale_image_arabic) ? $request->project_sale_image_arabic : null,
                'project_sale_heading' => isset($request->project_sale_heading) ? $request->project_sale_heading : null,
                'project_sale_heading_arabic' => isset($request->project_sale_heading_arabic) ? $request->project_sale_heading_arabic : null,
                'project_sale_status' => isset($request->project_sale_status) ? $request->project_sale_status : 0,
            ]);
        }
        else {
            $general = MobileSetting::create([
                'project_sale_image' => isset($request->project_sale_image) ? $request->project_sale_image : null,
                'project_sale_image_arabic' => isset($request->project_sale_image_arabic) ? $request->project_sale_image_arabic : null,
                'project_sale_heading' => isset($request->project_sale_heading) ? $request->project_sale_heading : null,
                'project_sale_heading_arabic' => isset($request->project_sale_heading_arabic) ? $request->project_sale_heading_arabic : null,
                'project_sale_status' => isset($request->project_sale_status) ? $request->project_sale_status : 0,
            ]);
        }
        return response()->json(['success' => true, 'message' => 'Mobile Setting Has been updated!']);
    }
}
