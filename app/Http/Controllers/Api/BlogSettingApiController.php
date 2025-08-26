<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogSetting;

class BlogSettingApiController extends Controller
{
    public function index(Request $request) {
        $data = BlogSetting::with('SliderImage:id,image')->first();
        return response()->json(['data' => $data]);
    }
    
    public function store(Request $request) 
    {
        $data = BlogSetting::with('SliderImage:id,image')->first();
        if($data) {
             $blogsetting = BlogSetting::whereId($data->id)->update([
                'meta_title_en' => isset($request->meta_title_en) ? $request->meta_title_en : null,
                'meta_title_ar' => isset($request->meta_title_ar) ? $request->meta_title_ar : null,
                'meta_tag_en' => isset($request->meta_tag_en) ? $request->meta_tag_en : null,
                'meta_tag_ar' => isset($request->meta_tag_ar) ? $request->meta_tag_ar : null,
                'meta_canonical_en' => isset($request->meta_canonical_en) ? $request->meta_canonical_en : null,
                'meta_canonical_ar' => isset($request->meta_canonical_ar) ? $request->meta_canonical_ar : null,
                'meta_description_en' => isset($request->meta_description_en) ? $request->meta_description_en : null,
                'meta_description_ar' => isset($request->meta_description_ar) ? $request->meta_description_ar : null,
                'slider_image' => isset($request->image_media) ? $request->image_media : null,
            ]);
        }
        else {
            $blogsetting = BlogSetting::create([
                'meta_title_en' => isset($request->meta_title_en) ? $request->meta_title_en : null,
                'meta_title_ar' => isset($request->meta_title_ar) ? $request->meta_title_ar : null,
                'meta_tag_en' => isset($request->meta_tag_en) ? $request->meta_tag_en : null,
                'meta_tag_ar' => isset($request->meta_tag_ar) ? $request->meta_tag_ar : null,
                'meta_canonical_en' => isset($request->meta_canonical_en) ? $request->meta_canonical_en : null,
                'meta_canonical_ar' => isset($request->meta_canonical_ar) ? $request->meta_canonical_ar : null,
                'meta_description_en' => isset($request->meta_description_en) ? $request->meta_description_en : null,
                'meta_description_ar' => isset($request->meta_description_ar) ? $request->meta_description_ar : null,
                'slider_image' => isset($request->image_media) ? $request->image_media : null,
            ]);
            
        }
        return response()->json(['success' => true, 'message' => 'General Setting Has been updated!']);
    }
}
