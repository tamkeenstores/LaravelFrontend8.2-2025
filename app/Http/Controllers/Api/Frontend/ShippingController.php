<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ShippingZone;
use App\Models\ShippingZoneRegion;
use App\Models\ShippingLocation;
use App\Models\ShippingClasses;
use App\Models\States;
use DB;
use Illuminate\Support\Facades\Cache;

class ShippingController extends Controller
{
    
    public function getShippingUpdate(Request $request) {
        $success = false;
        $seconds = 86400; // 24 hours cache
    
        $data = $request->all();
        $userid = isset($data['userid']) ? $data['userid'] : false;
        $city = isset($data['city']) ? $data['city'] : false;
        $productArray = $data['productids'] ?? [];
        $CouponCode = isset($data['extraData']['discounts']['coupon']) ?$data['extraData']['discounts']['coupon'] : false;
        $subtotal = isset($data['subtotal']) ?$data['subtotal'] : false;
        
        // print_r($CouponCode);die();
        // Create a unique cache key
        $cacheKey = 'shipping_zone_' . md5(json_encode([
            'userid' => $userid,
            'city' => $city,
            'productids' => $productArray,
            'subtotal' => $subtotal,
            'couponcode' => $CouponCode,
            'update' => "update"
        ]));
    
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            
            $ShippingZone = ShippingZone::
                select(['shipping_zone.id','shipping_zone.name','shipping_zone.name_arabic'])
                ->where('status', '1')
                ->when($city, function ($q) use ($city) {
                    return $q->whereHas('shippingZoneRegion.city', function ($query) use ($city) {
                        return $query->where('states.name', $city)
                                     ->orWhere('states.name_arabic', $city);
                    });
                })
                ->with([
                    'methods' => function ($q) {
                        return $q->limit(1)->orderBy('type', 'ASC');
                    },
                    'methods.flat_classes' => function ($q) use ($productArray) {
                        return $q->whereHas('shipClass.products', function ($query) use ($productArray) {
                                return $query->whereIn('products.id', $productArray);
                            })
                            ->orderBy('cost', 'DESC');
                    }
                ])
                ->first();
    
            $shippingData = null;
            
            
            if ($ShippingZone && count($ShippingZone->methods)) {
                $success = true;
                $amount = 0;
                if($ShippingZone->methods[0]->type == 0){
                    $amount = $ShippingZone->methods[0]->cost;
                    if (isset($ShippingZone->methods[0]->flat_classes[0])) {
                        $amount = $ShippingZone->methods[0]->flat_classes[0]->cost;
                }
            }
            elseif($ShippingZone->methods[0]->type == 1 && $CouponCode){
                
                
                $methods = $ShippingZone->methods[0]->where('coupon_code',$CouponCode['title'])->first();
                if($methods){
                    if($methods->coupon_code_cost >= $subtotal){
                        // $flat_classes = $methods->load('flat_classes');
                        // $amount = $flat_classes->flat_classes[0];
                        $amount = $ShippingZone->methods[0]->flat_classes[0]->cost;
                    }
                }
                
                // if($userid == "181775"){
                    
                //     return $amount;
                // }
            }
                
    
                $shippingData = [
                    'id' => $ShippingZone->id,
                    'name' => $ShippingZone->name,
                    'name_arabic' => $ShippingZone->name_arabic,
                    'amount' => $amount
                ];
            }
    
            $response = [
                'success' => $success,
                'data' => $shippingData
            ];
    
            // Cache the response (uncompressed)
            Cache::put($cacheKey, $response, $seconds);
        }
    
        // Return compressed JSON response
        $responseJson = json_encode($response);
        $compressed = gzencode($responseJson, 9);
    
        return response($compressed)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($compressed),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getShipping(Request $request) {
        $success = false;
        $seconds = 86400; // 24 hours cache
    
        $data = $request->all();
        $userid = isset($data['userid']) ? $data['userid'] : false;
        $city = isset($data['city']) ? $data['city'] : false;
        $productArray = $data['productids'] ?? [];
    
        // Create a unique cache key
        $cacheKey = 'shipping_zone_' . md5(json_encode([
            'userid' => $userid,
            'city' => $city,
            'productids' => $productArray
        ]));
    
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            $ShippingZone = ShippingZone::
                select(['shipping_zone.id','shipping_zone.name','shipping_zone.name_arabic'])
                ->where('status', '1')
                ->when($city, function ($q) use ($city) {
                    return $q->whereHas('shippingZoneRegion.city', function ($query) use ($city) {
                        return $query->where('states.name', $city)
                                     ->orWhere('states.name_arabic', $city);
                    });
                })
                ->with([
                    'methods' => function ($q) {
                        return $q->limit(1)->orderBy('type', 'ASC');
                    },
                    'methods.flat_classes' => function ($q) use ($productArray) {
                        return $q->whereHas('shipClass.products', function ($query) use ($productArray) {
                                return $query->whereIn('products.id', $productArray);
                            })
                            ->orderBy('cost', 'DESC');
                    }
                ])
                ->first();
    
            $shippingData = null;
    
            if ($ShippingZone && count($ShippingZone->methods)) {
                $success = true;
                $amount = $ShippingZone->methods[0]->cost;
                
                if ($ShippingZone->methods[0]->type == 0 && isset($ShippingZone->methods[0]->flat_classes[0])) {
                    $amount = $ShippingZone->methods[0]->flat_classes[0]->cost;
                }
    
                $shippingData = [
                    'id' => $ShippingZone->id,
                    'name' => $ShippingZone->name,
                    'name_arabic' => $ShippingZone->name_arabic,
                    'amount' => $amount
                ];
            }
    
            $response = [
                'success' => $success,
                'data' => $shippingData
            ];
    
            // Cache the response (uncompressed)
            Cache::put($cacheKey, $response, $seconds);
        }
    
        // Return compressed JSON response
        $responseJson = json_encode($response);
        $compressed = gzencode($responseJson, 9);
    
        return response($compressed)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($compressed),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getShippingold(Request $request) {
        $success = false;
        $data = $request->all();
        $userid = isset($data['userid']) ? $data['userid'] : false;
        $city = isset($data['city']) ? $data['city'] : false;
        $productArray = $data['productids'];
        
        $ShippingZone = ShippingZone::
        select(['shipping_zone.id','shipping_zone.name','shipping_zone.name_arabic'])
        ->where('status','1')
        ->when($city, function ($q) use ($city) {
            return $q->whereHas('shippingZoneRegion.city', function ($query) use ($city) {
                return $query->where('states.name', $city)->orWhere('states.name_arabic', $city);
            });
        })
        ->with([
            'methods' => function ($q) use($productArray) {
                return $q->limit(1)->orderBy('type','ASC');   
            },
            'methods.flat_classes' => function ($q) use($productArray) {
                return $q->whereHas('shipClass.products', function ($query) use ($productArray) {
                    return $query->whereIn('products.id', $productArray);
                })
                ->orderBy('cost','DESC')->first();
            }
        ])
        ->first();
        if($ShippingZone){
            $success = true;
            $amount = $ShippingZone->methods[0]->cost;
            if(sizeof($ShippingZone->methods[0]->flat_classes)){
                $amount = $ShippingZone->methods[0]->flat_classes[0]->cost;
            }
            $data = ['id' => $ShippingZone->id,'name' => $ShippingZone->name,'name_arabic' => $ShippingZone->name_arabic,'amount'=>$amount];
        }
        $response = [
            'success' => $success,
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
    
    // public function getRegister(Request $request) {
    //     $data = $request->all();
    //     $user = false;
    //     $success = false;
    //     if($data['phone_number']){
    //         $user = User::where('phone_number',$request->phone_number)->first(['id','phone_number']);
    //         if($user){
    //             $success = false;
    //         }else{
    //             $success = true;
    //             $user = User::where('email',$request->email)->first(['id','phone_number','email']);
    //             if($user){
    //                 $success = false;
    //             }else{
    //                 $userData = User::create([
    //                     'first_name' => $data['first_name'],
    //                     'last_name' => $data['last_name'],
    //                     'phone_number' => $data['phone_number'],
    //                     'role_id' => $data['role_id'],
    //                     'email' => $data['email'],
    //                     'date_of_birth' => $data['date_of_birth'],
    //                     'notes' => $data['notes'],
    //                     'status' => $data['status'],
    //                     'password' => $data['password'],
    //                     'user_device' => $data['user_device'],
    //                     'lang' => $data['lang'],
    //                     'gender' => $data['gender']
    //                 ]);
    //                 $user = $userData->id;
    //                 $success = true;
    //             }
    //         }
    //     }
        
    //     $response = [
    //         'success' => $success,
    //         'user' => $user
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
}
