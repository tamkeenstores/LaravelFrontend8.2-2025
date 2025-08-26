<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FooterPages;

class FooterPagesController extends Controller
{
    public function FooterPage($slug) {
        $footerpages = FooterPages::where('page_link',$slug)->where('status', 1)->select('id', 'page_name', 'page_link', 'meta_title_en', 'meta_title_ar', 
        'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'page_content_en', 'page_content_ar', 'status')->first();
        // print_r($footerpages);die;
        $response = [
            'data' => $footerpages
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
