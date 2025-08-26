<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;
use App\Models\CacheStores;

class SliderController extends Controller
{
    public function getSliders() {
        $leftslider = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
        ->orderBy('sorting', 'asc') 
        ->where('position', 0)
        ->where('status', 1)
        ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'sorting'
        ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
        
        $rightslider = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
        ->orderBy('sorting', 'asc') 
        ->where('position', 1)
        ->where('status', 1)
        ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'sorting'
        ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
        
        // $middleslider = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
        // ->orderBy('sorting', 'asc') 
        // ->where('position', 2)
        // ->where('status', 1)
        // ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'sorting'
        // ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->take(2)->get();
        
        $response = [
            'data'=>$leftslider,
            'leftslider' => $leftslider,
            'rightslider' => $rightslider,
            // 'middleslider' => $middleslider,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }


    public function getSlidersType($type) {
        //return $type;
        $seconds = 86400;
        Cache::forget('homesliders_'.$type);
        if(Cache::has('homesliders_'.$type))
            $sliders = Cache::get('homesliders_'.$type);
        else{
            // CacheStores::create([
            //     'key' => 'homesliders_'.$type,
            //     'type' => 'slider'
            // ]);
            $sliders = Cache::remember('homesliders_'.$type, $seconds, function () use ($type) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'sorting'
                    ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer', 'position')->get();
            });
        }
        $response = [
            'data'=>$sliders,
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