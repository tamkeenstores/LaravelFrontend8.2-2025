<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSliders;
use App\Models\Productcategory;
use App\Models\Product;
use App\Models\Brand;
use App\Models\SubTags;

class AppSlidersController extends Controller
{
    public function appSliders() {
        $data = AppSliders::with('ImageEn:id,image', 'ImageAr:id,image', 'ProductData:id,name,name_arabic,sku,slug', 'BrandData:id,name,name_arabic,slug', 'CategoryData:id,name,name_arabic,slug', 'TagData:id,name,name_arabic')
        ->where('status', 1)
        ->orderBy('sorting', 'ASC')
        ->get();
        
        $response = [
            'data' => $data
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
