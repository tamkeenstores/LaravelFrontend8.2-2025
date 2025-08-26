<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceCenter;
use App\Models\Region;
use App\Models\MaintenanceCenterCity;
use App\Models\States;

class MaintenanceController extends Controller
{
    public function getMaintenanceLocater(Request $request) {
        
        $lang = isset($request->lang) ? $request->lang : null;
        $maintenance = MaintenanceCenter::with('featuredImageWeb:id,image', 'maintenanceCenterRegions:id,name,name_arabic')->where('status', 1)->select('id', 'name', 'name_arabic', 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status', 'region')
        ->get();
        
        $maintenanceCities = MaintenanceCenterCity::pluck('city_id')->toArray();
        
        if($lang == 'ar'){
            $cities = States::
            whereIn('id', $maintenanceCities)
            ->get(['id as value', 'name_arabic as label']);
        }else{
            $cities = States::
            whereIn('id', $maintenanceCities)
            ->get(['id as value', 'name as label']);
        }
        
        $response = [
            'maintenance' => $maintenance,
            'regions' => $cities,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    public function FilterMaitenance(Request $request) {
        $citiesData = $request->city;
        $lang = isset($request->lang) ? $request->lang : null;
        $maintenance = MaintenanceCenter::with('featuredImageWeb:id,image')->where('status', 1)->select('id', 'name', 'name_arabic', 'address', 'lat', 'lng', 'phone_number', 'time', 'direction_button', 'sort',
        'status')
        ->whereHas('cityData', function($query) use ($citiesData) {
            $query->where('city', $citiesData['value']);
        })
        ->get();
        $maintenanceCities = MaintenanceCenterCity::pluck('city_id')->toArray();
        if($lang == 'ar'){
            $cities = States::
            whereIn('id', $maintenanceCities)
            ->get(['id as value', 'name_arabic as label']);
        }else{
            $cities = States::
            whereIn('id', $maintenanceCities)
            ->get(['id as value', 'name as label']);
        }
        $response = [
            'maintenance' => $maintenance,
            'regions' => $cities,
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