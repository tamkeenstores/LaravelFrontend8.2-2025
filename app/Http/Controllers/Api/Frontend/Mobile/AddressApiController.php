<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\shippingAddress;
use App\Models\States;
use DB;
use App\Models\CacheStores;
use Illuminate\Support\Facades\Cache;

class AddressApiController extends Controller
{
    public function getMobRegData($lang = 'en') {
        $mainregions = Region::where('region.status', 1)->
        select('region.id', 'region.name', 'region.name_arabic', DB::raw('states.name as city_name'), DB::raw('states.name_arabic as city_name_arabic'), DB::raw('states.id as city_id'))
        ->Join('states', function($join) {
            $join->on('states.region', '=', 'region.id');
        })
        ->groupBy('states.id')
        ->get();
        
        
        $regiondata = [];
        foreach($mainregions as $key => $val) {
                $regiondata[$val->id]['region_id'] = $val->id;
                // $regiondata[$val->id]['city_id'][] = $val->city_id;
            if($lang == 'ar') {
                $regiondata[$val->id]['region_name_arbic'] = $val->name_arabic;
                $regiondata[$val->id]['city_name'][$val->city_id] = $val->city_name_arabic; 
            }
            else {
                $regiondata[$val->id]['region_name'] = $val->name;
                $regiondata[$val->id]['city_name'][$val->city_id] = $val->city_name;   
            } 
        }
        
        $regions = Region::where('status', 1)->get();
        
        $response = [
            'regions_city' => $regiondata,
            'regions'=> $regions
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getMobAddressData($id,$lang = 'en') {
        $address = shippingAddress::where('id', $id)->select('id', 'address', 'address_option', 'state_id', 'make_default', 'shippinginstractions', 'address_label')
        ->With('stateData:id,name,name_arabic,region', 'stateData.region:id,name,name_arabic')->first();
        // $regions = Region::where('status', 1)->
        //     when($lang == 'en', function ($q) {
        //         return $q->with('citydata:id,name')->select('id', 'name');
        //     })
        //     ->when($lang != 'en', function ($q) {
        //         return $q->with('citydata:id,name_arabic')->select('id', 'name_arabic');
        //     })->get();
        // $arabicregions = Region::with('citydata:id,name_arabic')->get(['id', 'name_arabic']);
        
        $mainregions = Region::where('region.status', 1)->
        select('region.id', 'region.name', 'region.name_arabic', DB::raw('states.name as city_name'), DB::raw('states.name_arabic as city_name_arabic'), DB::raw('states.id as city_id'))
        ->Join('states', function($join) {
            $join->on('states.region', '=', 'region.id');
        })
        ->groupBy('states.id')
        ->get();
        
        
        $regiondata = [];
        foreach($mainregions as $key => $val) {
                $regiondata[$val->id]['region_id'] = $val->id;
                // $regiondata[$val->id]['city_id'][] = $val->city_id;
            if($lang == 'ar') {
                $regiondata[$val->id]['region_name_arbic'] = $val->name_arabic;
                $regiondata[$val->id]['city_name'][$val->city_id] = $val->city_name_arabic; 
            }
            else {
                $regiondata[$val->id]['region_name'] = $val->name;
                $regiondata[$val->id]['city_name'][$val->city_id] = $val->city_name;   
            } 
        }
        
        $regions = Region::where('status', 1)->get();
        
        $selectedcity = States::where('id', $address->stateData->id)->
        when($lang == 'en', function ($q) {
            return $q->select('id', 'name');
        })
        ->when($lang != 'en', function ($q) {
            return $q->select('id', 'name_arabic');
        })->first();
        
        $response = [
            'addressdata' => $address,
            'regions_city' => $regiondata,
            'regions'=> $regions,
            'selectedcity' => $selectedcity
            // 'arabicregions' =>$arabicregions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getShippingData($id) {
       $data = shippingAddress::where('id', $id)->With('stateData:id,name,name_arabic,region', 'stateData.region:id,name,name_arabic', 'userData:id,first_name,last_name,email,phone_number')
        ->select('id', 'customer_id','address','shippinginstractions','state_id')->first();
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getCityDropdown() {
        $dataen = States::where('country_id', 191)->select('id as value', 'name as label')->where('status',1)->get();
        $dataar = States::where('country_id', 191)->select('id as value', 'name_arabic as label')->where('status',1)->get();
        
        $response = [
            'data_en' => $dataen,
            'data_ar' => $dataar,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getCityList() {
       $data = States::where('country_id', 191)->select('id', 'name','name_arabic')->where('status',1)->get();
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getCityListLang($lang) {
        $seconds = 86400; // 24 hours cache
        $lang = $lang ?? 'ar';
        $cacheKey = "get_city_list_by_{$lang}"; // Added version
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                if($lang == 'ar') {
                    $data = States::where('country_id', 191)->select('id as value', 'name_arabic as label')->where('status',1)->get();
                }
                else {
                    $data = States::where('country_id', 191)->select('id as value', 'name as label')->where('status',1)->get();
                }
                
                $response = [
                    'data' => $data,
                ];
            } catch (\Exception $e) {
                Log::error("Get city list lang: " . $e->getMessage());
                $response = [
                    'error' => 'Failed to load Get city list lang data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
            }
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
