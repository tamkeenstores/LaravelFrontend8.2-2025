<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingZone;
use App\Models\States;
use App\Models\ShippingClasses;
use App\Models\ZoneShippingMethod;
use App\Models\ShippingZoneRegion;
use App\Models\ZoneFreeShippingDetails;
use App\Models\FlatRateClass;

class ShippingZoneController extends Controller
{
    
    public function index() {
        $shippingzone = ShippingZone::select('id','name','name_arabic','status')->with('ShippingZoneregion.city', 'methods.flat_classes')
        ->orderBy('id', 'desc')->get();
        $response = [
            'shippingzone' => $shippingzone
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        // echo json_encode(array('shippingzone' => $shippingzone));
    }

    public function create() {
        $shippinglocations = States::with('region')->get(['id', 'region', 'name', 'name_arabic', 'country_id']);
        $shipping_classes = ShippingClasses::get(['id','name', 'name_arabic']);
        // echo json_encode(array('shipping_locations'=> $shippinglocations,'shipping_classes' => $shipping_classes));
        $response = [
            'shipping_locations'=> $shippinglocations,'shipping_classes' => $shipping_classes
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function store(Request $request) {
        // print_r($request->all());die();
        $shipping_zone = ShippingZone::Create([
            'name' => $request['name'],
            'name_arabic' => $request['name_arabic'],
            'notes' => $request['notes'],
            'status' => $request['status'],
        ]);

        if (isset($request['locations'])) {
            foreach ($request['locations'] as $k => $val) {
               $shipping_zone_region = ShippingZoneRegion::Create([
                    'shipping_zone_id' => $shipping_zone->id,
                    'zone_region' => $val['value']
                ]); 
            }
        }
        if(isset($request['methods']) && count($request['methods']) >= 1) {
            foreach ($request['methods'] as $i => $data) {
                $zoneshippingmethod = ZoneShippingMethod::create([
                    'zone_id' => $shipping_zone->id,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'title_arabic' => $data['title_arabic'],
                    'status' => isset($data['status']) ? $data['status'] : 0,
                    'tax_status' => isset($data['tax_status']) ? $data['tax_status'] : 0,
                    'cost' => isset($data['cost']) ? $data['cost'] : null,
                    'coupon_code' => isset($data['coupon_code']) ? $data['coupon_code'] : null,
                    'coupon_code_cost' => isset($data['coupon_code_cost']) ? $data['coupon_code_cost'] : 0,
                ]);

                if(isset($data['classes']) && count($data['classes']) >= 1) {
                    foreach ($data['classes'] as $a => $class) {
                        FlatRateClass::Create([
                            'zone_shipping_id' => $zoneshippingmethod->id,
                            'class_id' => $class['class'],
                            'cost' => $class['cost'],
                        ]);
                    }
                }
            }
        }
        if($shipping_zone->id) {
            return response()->json(['success' => true, 'message' => 'Shipping Zone has been created!']);
        }   else {
            return response()->json(['success' => false, 'message' => 'something went wrong!']);
        }
    }

    public function edit($id) {
        $shippingzone = ShippingZone::with('ShippingZoneregion.city', 'methods.flat_classes')->findOrFail($id);
        $shippinglocations = States::with('region')->get(['id', 'region', 'name', 'name_arabic', 'country_id']);
        $shipping_classes = ShippingClasses::get(['id','name', 'name_arabic']);
        // echo json_encode(array('shippingzone' => $shippingzone,'shipping_locations'=> $shippinglocations,'shipping_classes' => $shipping_classes));
        $response = [
            'shippingzone' => $shippingzone,'shipping_locations'=> $shippinglocations,'shipping_classes' => $shipping_classes
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function update(Request $request, $id) {
        
        if (isset($request['locations'])) {
            $zoneregion = ShippingZoneRegion::where('shipping_zone_id', '=',$id)->get();
            $zoneregion->each->delete();
                
            foreach ($request['locations'] as $k => $val) {
               $shipping_zone_region = ShippingZoneRegion::Create([
                    'shipping_zone_id' => $id,
                    'zone_region' => $val['value']
                ]); 
            }
        }
        
        if(isset($request['methods'])) {
                $zoneshipping = ZoneShippingMethod::where('zone_id', '=',$id)->pluck('id')->toArray();
                $flatrate = FlatRateClass::whereIn('zone_shipping_id', $zoneshipping)->get();
                foreach($flatrate as $ke => $new) {
                    $new->delete();
                }
                // $flatrate->each->delete();
                $dateee = ZoneShippingMethod::where('zone_id', '=',$id)->get();
                $dateee->each->delete();
                
                
                foreach ($request['methods'] as $i => $data) {
                    $zoneshippingmethod = ZoneShippingMethod::create([
                        'zone_id' => $id,
                        'type' => $data['type'],
                        'title' => $data['title'],
                        'title_arabic' => $data['title_arabic'],
                        'status' => isset($data['status']) ? $data['status'] : 0,
                        'tax_status' => isset($data['tax_status']) ? $data['tax_status'] : 0,
                        'cost' => isset($data['cost']) ? $data['cost'] : null,
                        'coupon_code' => isset($data['coupon_code']) ? $data['coupon_code'] : null,
                        'coupon_code_cost' => isset($data['coupon_code_cost']) ? $data['coupon_code_cost'] : 0,
                    ]);
    
                    if(isset($data['classes'])) {
                        foreach ($data['classes'] as $a => $class) {
                            FlatRateClass::Create([
                                'zone_shipping_id' => $zoneshippingmethod->id,
                                'class_id' => $class['class'],
                                'cost' => $class['cost'],
                            ]);
                        }
                    }
                }
        }
        
        $shipping_zone = array(
            'name' => $request['name'],
            'name_arabic' => $request['name_arabic'],
            'notes' => $request['notes'],
            'status' => $request['status'],    
        );
        
        $row = ShippingZone::whereId($id)->first();
        $row->update($shipping_zone);  
        
        if($id) {
            return response()->json(['success' => true, 'message' => 'Shipping Zone has been updated!']);
        }   else {
            return response()->json(['success' => false, 'message' => 'something went wrong!']);
        }
    }
    
    public function destroy($id) {
        $zoneregion = ShippingZoneRegion::where('shipping_zone_id', '=',$id)->get();
        if($zoneregion){
            $zoneregion->each->delete();
        }
        $zoneshipping = ZoneShippingMethod::where('zone_id', '=',$id)->pluck('id')->toArray();
        
        if($zoneshipping) {
            $flatrate = FlatRateClass::where('zone_shipping_id', $zoneshipping)->get();
            if($flatrate){
                foreach($flatrate as $ke => $new) {
                    $new->delete();
                }
            }
        }
        
        $dateee = ZoneShippingMethod::where('zone_id', '=',$id)->get();
        if($dateee) {
            $dateee->each->delete();
        }
        $shippingzone = ShippingZone::find($id);
        if($shippingzone) {
            $shippingzone->delete();
        }
        
        return response()->json(['success' => true, 'message' =>'Shipping Zone Has been deleted!']);
    }
    
    public function multidelete(Request $request) {
        $zoneids = $request->get('id');
        $success = false;
        foreach ($zoneids as $zoneid) {
            ShippingZoneRegion::where('shipping_zone_id', '=',$zoneid)->delete();
            $zonemethod = ZoneShippingMethod::where('zone_id', '=',$zoneid)->pluck('id');
            FlatRateClass::whereIn('zone_shipping_id',$zonemethod)->delete();
            $dateee = ZoneShippingMethod::where('zone_id', '=',$zoneid)->get();
            $dateee->each->delete();
            ShippingZone::where('id', $zoneid)->delete();
            $success = true;
        }
        
        return response()->json(['success' => $success, 'message' =>'Shipping Zones Has been deleted!']);
    }
}