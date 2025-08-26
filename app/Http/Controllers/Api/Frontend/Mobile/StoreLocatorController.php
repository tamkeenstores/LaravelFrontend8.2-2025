<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreLocator;
use App\Models\Region;

class StoreLocatorController extends Controller
{
    public function getMobStoreLocators($lang = 'en') {
        $stores = StoreLocator::with('featuredImageWeb:id,image')->where('status', 1)->select('id', 'name', 'name_arabic', 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status', 'clicks', 'image_media')->get();
        $regions = Region::
        when($lang == 'en', function ($q) {
            return $q->with('citydata:id,name')->select('id', 'name');
        })
        ->when($lang != 'en', function ($q) {
            return $q->with('citydata:id,name_arabic')->select('id', 'name_arabic');
        })->get();
        $response = [
            'stores' => $stores,
            'regions' => $regions,
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
