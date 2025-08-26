<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreLocator;
use App\Models\Region;
use App\Models\StoreLocatorCity;
use App\Models\States;
use App\Models\CustomerSurvey;

class StoreLocatorController extends Controller
{
    public function getStoreLocators() {
        $stores = StoreLocator::with('featuredImageWeb:id,image')->where('status', 1)->select('id', 'name', 'name_arabic', 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status', 'clicks', 'image_media')->where('name', 'like', '%Jeddah%')->get();
        $regions = Region::where('status', 1)->get(['id as value', 'name as label']);
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

    public function getStoreLocatorsNew() {
        $stores = StoreLocator::with('featuredImageWeb:id,image', 'storeRegions:id,name,name_arabic')->where('status', 1)->select('id', 'name', 'name_arabic', 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status', 'clicks', 'image_media', 'region')
        // ->whereHas('storeRegions', function ($query) {
        //     $query->where('region', '!=', null);
        // })
        ->get();
        $regions = Region::
        whereHas('storeData', function ($query) {
            $query->where('region', '!=', null);
        })
        ->where('status', 1)->get(['id as value', 'name as label']);
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
    
    public function getStoreLocatorsNewUpdate(Request $request) {
        
        $lang = $request->lang ?? 'ar';
        $regionName = $lang == 'ar' ? 'name_arabic' : 'name';
        $stores = StoreLocator::with(['featuredImageWeb:id,image',
        'storeRegions' => function ($query) use ($regionName) {
                $query->select('id', $regionName);
        }
        ])->where('status', 1)->select('id',$regionName, 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status', 'clicks', 'image_media', 'region')
        // ->whereHas('storeRegions', function ($query) {
        //     $query->where('region', '!=', null);
        // })
        ->whereNotIn('id', [47, 48])
        ->get();
        
        $storeCities = StoreLocatorCity::pluck('city_id')->toArray();
        // dd($lang);
        $cityLabel = $lang == 'ar' ? 'name_arabic' : 'name';
            $cities = States::whereIn('id', $storeCities)
                ->get([
                    'id as value',
                    "$cityLabel as label"
                ]);
        
        // if($lang == 'ar'){
        //     $cities = States::
        //     whereIn('id', $storeCities)
        //     ->get(['id as value', 'name_arabic as label']);
        // }else{
        //     $cities = States::
        //     whereIn('id', $storeCities)
        //     ->get(['id as value', 'name as label']);
        // }
        
        $response = [
            'stores' => $stores,
            'regions' => $cities
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function FilterStores(Request $request) {
        $citiesData = $request->city;
        $stores = StoreLocator::with('featuredImageWeb:id,image')->where('status', 1)->select('id', 'name', 'name_arabic', 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status', 'clicks', 'image_media')
        // ->when($citiesData, function ($q) use ($citiesData) {
        //     whereHas('cities', function($query) use ($citiesData) {
        //         $query->where('city_id', $citiesData['value']);
        //     });
        // })
        ->whereHas('cities', function($query) use ($citiesData) {
            $query->where('city_id', $citiesData['value']);
        })
        // ->when($citiesData != '' && $citiesData != 'All', function ($q) use ($citiesData) {
        //     return $q->where('name', 'like', '%' . $citiesData . '%');
        // })
        ->whereNotIn('id', [47, 48])
        ->get();
        // $ids = [1,2,3];
        // whereIn('id',$ids)->
        $regions = Region::where('status', 1)->get(['id as value', 'name as label']);
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