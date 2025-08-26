<?php

namespace App\Helper;
use Request;
use Session;
use Cookie;
use App\Models\Storetoken;
use Auth;

use DeDmytro\CloudflareImages\Facades\CloudflareApi;
use DeDmytro\CloudflareImages\Http\Responses\DetailsResponse;
use DeDmytro\CloudflareImages\Http\Entities\Image;

use App\Models\Media;

class Media_helper
{
    static function mediadelete($id){
        $mediaCdn = Media::where('id', $id)->first();
        $response = CloudflareApi::images()->delete($mediaCdn->cdn_id);
        $mediaCdn->delete();
    }
    
    static function mediaGet($id){
        $mediaCdn = Media::where('id', $id)->first();
        return $mediaCdn;
    }
    
    static function mediaupload($file){
        $response = CloudflareApi::images()->upload($file);

        $image = $response->result;
        $mediaCdn = Media::create([
            'cdn_id' => $image->id,
            // 'uploaded_by' => Auth()->id,
            'small' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', $image->variants->small),
            'extrasmall' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', $image->variants->extrasmall),
            'medium' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', $image->variants->medium),
            'large' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', $image->variants->large),
            'thumbnail' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', $image->variants->thumbnail),
            'productimagegallery' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', $image->variants->productimagegallery),
            'productimages' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/',$image->variants->productimages),
            'file_url' => str_replace('https://imagedelivery.net/', 'https://cdn-media.tamkeenstores.com.sa/cdn-cgi/imagedelivery/', str_replace('small','',$image->variants->small))
        ]);
        return $mediaCdn->id;
    }
}