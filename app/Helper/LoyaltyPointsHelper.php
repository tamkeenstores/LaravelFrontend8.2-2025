<?php

namespace App\Helper;
use Request;
use App\Models\Rules;
use App\Models\User;
use App\Models\States;
use App\Models\Order;
use App\Models\shippingAddress;
use App\Models\LoyaltyHistory;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyRestrictions;
use App\Models\LoyaltySetting;
use App\Models\LoyaltySettingDesktop;
use App\Models\LoyaltySettingMobile;
use App\Models\OrderLoyaltyPoints;
use App\Models\WalletHistory;
use App\Jobs\LoyaltyPointsViewJob;
use Carbon\Carbon;
use DateTimeZone;
use DB;

class LoyaltyPointsHelper
{
    static function LoyaltyPointsData($data, $device = 'desktop'){
        $dataa = [];
        $filters = [];
        $LoyalPointsdataArray = [];
        $LoyalPointsdataids = [];
        $order = Order::with('details.productData','ordersummary')->where('id',$data)->first();
        if($order){
            $filters['productids'] = $order->details()->where('unit_price','!=',0)->pluck('product_id')->toArray();
            $filters['productqty'] = $order->details()->where('unit_price','!=',0)->pluck('quantity')->toArray();
            $filters['productprice'] = $order->details()->where('unit_price','!=',0)->pluck('unit_price')->toArray();
            
            
            $shippingAddress = shippingAddress::where('id',$order->shipping_id)->first();
            $filters['userid'] = $order->customer_id;
            $filters['paymentmethod'] = $order->paymentmethod;
            $filters['city'] = isset($shippingAddress->stateData) ? $shippingAddress->stateData->name : false;
            $total = $order->ordersummary->where('type','total')->pluck('price')->toArray();
            $total = $total[0];
            // $subtotal = $order->ordersummary->where('type','subtotal')->pluck('price')->toArray();
            $subtotal = $order->ordersummary->where('type','total')->pluck('price')->toArray();
            $subtotal = $subtotal[0];
            $filters['subtotal'] = $subtotal;
            $LoyaltySetting = LoyaltySetting::with('settingdesktop')
                ->whereHas('settingdesktop', function ($a) use ($total) {
                    return $a->where('min_order_value','<=',$total)->where('max_order_value','>=',$total);
                })
                ->when($device == 'desktop', function ($q) {
                    return $q->whereRaw("FIND_IN_SET('0', discount_devices)");
                })
                ->when($device != 'desktop', function ($q) {
                    return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
                })
                ->where('status',1)
                ->first();
                
                if($order->customer_id == '181775'){
            if($LoyaltySetting){
                $reward_points = $LoyaltySetting->settingdesktop()->where('min_order_value','<=',$total)->where('max_order_value','>=',$total)->pluck('reward_points')->toArray();
                if($reward_points){
                            $LoyaltyHistory = LoyaltyHistory::create([
                                'user_id' => $order->customer_id,
                                'title' => 'order placed',
                                'title_arabic' => 'تم الطلب',
                                'calculate_type' => 1,
                                'points' => $total*$reward_points[0],
                                'order_id' => $order->id,
                            ]);
                            
                            
                            $userPlus = User::where('id', $order->customer_id)->first();
                            $currentAmountPlus = $userPlus->amount + str_replace(",","",$total);
                            $userPlus->amount = $currentAmountPlus;
                            $userPlus->save();
                            
                            if($userPlus){
                                $walletHistory = WalletHistory::create([
                                    'user_id' => $order->customer_id,
                                    'order_id' => $order->id,
                                    'type' => 1,
                                    'amount' => $total,
                                    'description' => 'Order Placed',
                                    'description_arabic' => 'تم الطلب',
                                    'wallet_type' => 'loyalty',
                                    'title' => 'Order Placed',
                                    'title_arabic' => 'تم الطلب',
                                    'current_amount' => $currentAmountPlus,
                                    'status' => 0,
                                ]);
                            }
                            
                            $userMinus = User::where('id', $order->customer_id)->first();
                            $currentAmountMinus = $userMinus->amount - str_replace(",","",$total);
                            $userMinus->amount = $currentAmountMinus;
                            $userMinus->save();
                            
                            if($userMinus){
                                $walletHistory = WalletHistory::create([
                                    'user_id' => $order->customer_id,
                                    'order_id' => $order->id,
                                    'type' => 0,
                                    'amount' => $total,
                                    'description' => 'Order Placed',
                                    'description_arabic' => 'تم الطلب',
                                    'wallet_type' => 'loyalty',
                                    'title' => 'Order Placed',
                                    'title_arabic' => 'تم الطلب',
                                    'current_amount' => $currentAmountMinus,
                                    'status' => 0,
                                ]);
                            }
                        }
            }
            
            
            $productids = $filters['productids'];
            $LoyaltyProgramsData  = LoyaltyProgram::
                with('restrictions.brandsData.productname:id,brands','restrictions.categoriesData.productname:id','restrictions.subtagsData.tagProducts:id','restrictions.productData:id', 'conditions.brandsData.productname:id,brands','conditions.categoriesData.productname:id','conditions.subtagsData.tagProducts:id','conditions.productData:id')
                ->where('status',1)
                ->when($device == 'desktop', function ($q) {
                    return $q->whereRaw("FIND_IN_SET('0', discount_devices)");
                })
                ->when($device != 'desktop', function ($q) {
                    return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
                })
                ->where(function($a) use ($productids){
                    return $a->whereHas('restrictions', function ($b) use ($productids) {
                        return $b->where('restriction_type',1)->where(function($c) use ($productids){
                            return $c->whereHas('brandsData.productname',function($e) use ($productids){
                                return $e->whereIn('products.id',$productids);
                            });
                        });
                    })->orWhereHas('restrictions', function ($b) use ($productids) {
                        return $b->where('restriction_type',2)->where(function($c) use ($productids){
                            return $c->whereHas('productData',function($e) use ($productids){
                                return $e->whereIn('products.id',$productids);
                            });
                        });
                    })->orWhereHas('restrictions', function ($b) use ($productids) {
                        return $b->where('restriction_type',3)->where(function($c) use ($productids){
                            return $c->whereHas('subtagsData.tagProducts',function($e) use ($productids){
                                return $e->whereIn('products.id',$productids);
                            });
                        });
                    })->orWhereHas('restrictions', function ($b) use ($productids) {
                        return $b->where('restriction_type',4)->where(function($c) use ($productids){
                            return $c->whereHas('categoriesData.productname',function($e) use ($productids){
                                return $e->whereIn('products.id',$productids);
                            });
                        });
                    });
                })
                // ->leftJoin(DB::raw("(select count(id) as total,amount_id from order_summary where type = 'loyalty_discount' group by amount_id) totalused"), function($join) {
                //     $join->on('loyalty_program.id', '=', 'totalused.amount_id');
                // })
                // ->where(function($a){
                //     return $a->whereNull('totalused.total')->orWhereRaw('loyalty_program.usage_limit > totalused.total');
                // })
                ->where(function($a){
                    return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
                })
                ->where(function($a){
                    return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
                })->get();
            
            
            foreach ($LoyaltyProgramsData as $key => $value) {
                $filterids = $filters['productids'];
                $filterqty = $filters['productqty'];
                $filterprice = $filters['productprice'];
            	$conditionapplied = LoyaltyPointsHelper::checkConditions($filters,$value->conditions,$value->condition_match_status);
                
                
                if($conditionapplied){
                	$matchpro = [];
        	        foreach($value->restrictions as $k => $restriction){
        	            if($restriction->restriction_type == 1){
        	                    $matchpro = $restriction->brandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
        	                    $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	                    $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	                    $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	                    if($qty > 0){
            	                    $LoyalPointsdataArray[] = [
            	                           'user_id' => $order->customer_id,
                                            'title' => $value->name,
                                            'title_arabic' => $value->name_arabic,
                                            'calculate_type' => 1,
                                            'points' => $restriction->extra_reward_points*$qty,
                                            'order_id' => $order->id, 
            	                        ];
            	                        $LoyalPointsdataids[] = $value->id;
        	                    }
        	            }
        	            if($restriction->restriction_type == 2){
        	                $matchpro = $restriction->productData->whereIn('id', $filterids)->pluck('id')->toArray();
        	                $good_keys =array_keys(array_intersect_key($filterids, $matchpro));
        	                $price = array_sum(array_filter($filterprice, function($k) use ($good_keys) {return in_array($k, $good_keys);}, ARRAY_FILTER_USE_KEY));
        	                $qty = array_sum(array_filter($filterqty, function($k) use ($good_keys) {return in_array($k, $good_keys);}, ARRAY_FILTER_USE_KEY));
        	                $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	                if($qty > 0){
                                $LoyalPointsdataArray[] = [
                                      'user_id' => $order->customer_id,
                                        'title' => $value->name,
                                        'title_arabic' => $value->name_arabic,
                                        'calculate_type' => 1,
                                        'points' => $restriction->extra_reward_points*$qty,
                                        'order_id' => $order->id, 
                                    ];
                                    $LoyalPointsdataids[] = $value->id;
        	                }
        	            }
        	            if($restriction->restriction_type == 3){
    	                    $matchpro = $restriction->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
        	                $good_keys = array_keys(array_intersect_key($filterids, $matchpro));
        	                $price = array_sum(array_filter($filterprice, function($k) use ($good_keys) {return in_array($k, $good_keys);}, ARRAY_FILTER_USE_KEY));
        	                $qty = array_sum(array_filter($filterqty, function($k) use ($good_keys) {return in_array($k, $good_keys);}, ARRAY_FILTER_USE_KEY));
        	                $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	                if($qty > 0){
                                $LoyalPointsdataArray[] = [
                                      'user_id' => $order->customer_id,
                                        'title' => $value->name,
                                        'title_arabic' => $value->name_arabic,
                                        'calculate_type' => 1,
                                        'points' => $restriction->extra_reward_points*$qty,
                                        'order_id' => $order->id, 
                                    ];
                                    $LoyalPointsdataids[] = $value->id;
        	                }
    	                    
        	           }
        	           if($restriction->restriction_type == 4){
             	            $matchpro = $restriction->categoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
        	                $good_keys =array_keys(array_intersect_key($filterids, $matchpro));
        	                $price = array_sum(array_filter($filterprice, function($k) use ($good_keys) {return in_array($k, $good_keys);}, ARRAY_FILTER_USE_KEY));
        	                $qty = array_sum(array_filter($filterqty, function($k) use ($good_keys) {return in_array($k, $good_keys);}, ARRAY_FILTER_USE_KEY));
        	                $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	                if($qty > 0){
                                $LoyalPointsdataArray[] = [
                                      'user_id' => $order->customer_id,
                                        'title' => $value->name,
                                        'title_arabic' => $value->name_arabic,
                                        'calculate_type' => 1,
                                        'points' => $restriction->extra_reward_points*$qty,
                                        'order_id' => $order->id, 
                                    ];
                                    $LoyalPointsdataids[] = $value->id;
        	                }
        	           }
        	        }
                }
    
            
            }
            
            if($LoyalPointsdataArray){
                foreach($LoyalPointsdataArray as $k => $LoyaltyHistory){
                    LoyaltyHistory::create($LoyaltyHistory);
                    
                    $reward_points = $LoyaltySetting->settingdesktop()->where('min_order_value','<=',$total)->where('max_order_value','>=',$total)->pluck('reward_points')->toArray();
                    if($reward_points){
                                $userPlus = User::where('id', $order->customer_id)->first();
                                $currentAmountPlus = $userPlus->amount + ($LoyaltyHistory['points']*$reward_points[0]);
                                $userPlus->amount = $currentAmountPlus;
                                $userPlus->save();
                                
                                if($userPlus){
                                    $walletHistory = WalletHistory::create([
                                        'user_id' => $order->customer_id,
                                        'order_id' => $order->id,
                                        'type' => 1,
                                        'amount' => $LoyaltyHistory['points']*$reward_points[0],
                                        'description' => 'Order Placed',
                                        'description_arabic' => 'تم الطلب',
                                        'wallet_type' => 'loyalty',
                                        'title' => 'Order Placed',
                                        'title_arabic' => 'تم الطلب',
                                        'current_amount' => $currentAmountPlus,
                                        'status' => 0,
                                    ]);
                                }
                                
                                $userMinus = User::where('id', $order->customer_id)->first();
                                $currentAmountMinus = $userMinus->amount - ($LoyaltyHistory['points']*$reward_points[0]);
                                $userMinus->amount = $currentAmountMinus;
                                $userMinus->save();
                                
                                if($userMinus){
                                    $walletHistory = WalletHistory::create([
                                        'user_id' => $order->customer_id,
                                        'order_id' => $order->id,
                                        'type' => 0,
                                        'amount' => $LoyaltyHistory['points']*$reward_points[0],
                                        'description' => 'Order Placed',
                                        'description_arabic' => 'تم الطلب',
                                        'wallet_type' => 'loyalty',
                                        'title' => 'Order Placed',
                                        'title_arabic' => 'تم الطلب',
                                        'current_amount' => $currentAmountMinus,
                                        'status' => 0,
                                    ]);
                                }
                    }
                }
                if($LoyalPointsdataids){
                    foreach(array_unique($LoyalPointsdataids) as $k => $LoyalPointsdataid){
                        OrderLoyaltyPoints::create([
                            'order_id' => $order->id,
                            'loyaltypoints_id' => $LoyalPointsdataid,
                        ]);
                    }
                }
            }
            }
        }             
        // return $LoyalPointsdataArray;
    }
    
    static function checkConditions($filters,$conditions,$conditiontype){
        $userData = false;
        $cityData = false;
        if($filters['userid']){
            $userData = User::with('OrdersData')->where('id',$filters['userid'])->first();
        }
        
        if($filters['city']){
            $cityData = States::where('name',$filters['city'])->orWhere('name_arabic',$filters['city'])->first();
        }
        
        $conditionmatch = 0;
        foreach ($conditions as $key => $condition) {
            if($condition->condition_type == 1){
                // brands
                $brandsin = $condition->brandsData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    $bad_keys=array_keys(array_diff_key($filters['productids'],$brandsin->toArray()));
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $add = true;
                    
                    if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 2 && $qty > $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 3 && $qty != $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 5 && $qty < $condition->quantity)
                        $add = false;
                        
                    if($condition->min_amount && $condition->min_amount > $price)
                        $add = false;
                        
                    if($condition->max_amount && $condition->max_amount < $price)
                        $add = false;
                    
                    
                    if($add)
                        $conditionmatch += 1;
                }
                else{
                    if(sizeof($brandsin->toArray()) < 1)
                        $conditionmatch += 1;
                }
    
            }
            if($condition->condition_type == 2){
                // products
                $productsin = $condition->productData->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    $bad_keys=array_keys(array_diff_key($filters['productids'],$productsin->toArray()));
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $add = true;
                    
                    if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 2 && $qty > $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 3 && $qty != $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 5 && $qty < $condition->quantity)
                        $add = false;
                        
                    if($condition->min_amount && $condition->min_amount > $price)
                        $add = false;
                        
                    if($condition->max_amount && $condition->max_amount < $price)
                        $add = false;
                    
                    
                    if($add)
                        $conditionmatch += 1;
                }
                else{
                    if(sizeof($productsin->toArray()) < 1)
                        $conditionmatch += 1;
                }
            }
            if($condition->condition_type == 3){
                // Sub Tags
                $subtagsin = $condition->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    $bad_keys=array_keys(array_diff_key($filters['productids'],$subtagsin->toArray()));
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $add = true;
                    
                    if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 2 && $qty > $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 3 && $qty != $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 5 && $qty < $condition->quantity)
                        $add = false;
                        
                    if($condition->min_amount && $condition->min_amount > $price)
                        $add = false;
                        
                    if($condition->max_amount && $condition->max_amount < $price)
                        $add = false;
                    
                    
                    if($add)
                        $conditionmatch += 1;
                }
                else{
                    if(sizeof($subtagsin->toArray()) < 1)
                        $conditionmatch += 1;
                }
            }
            if($condition->condition_type == 4){
                // Categories
                $categoiesin = $condition->categoriesData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    $bad_keys=array_keys(array_diff_key($filters['productids'],$categoiesin->toArray()));
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $add = true;
                    
                    if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 2 && $qty > $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 3 && $qty != $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                        $add = false;
                    if($condition->select_quantity == 5 && $qty < $condition->quantity)
                        $add = false;
                        
                    if($condition->min_amount && $condition->min_amount > $price)
                        $add = false;
                        
                    if($condition->max_amount && $condition->max_amount < $price)
                        $add = false;
                    
                    
                    if($add)
                        $conditionmatch += 1;
                }
                else{
                    if(sizeof($categoiesin->toArray()) < 1)
                        $conditionmatch += 1;
                }
            }
            
            if($condition->condition_type == 5){
                if($filters['paymentmethod']){
                    $result = array_search($filters['paymentmethod'],explode(',',$condition->payment_method_id));
                    
                    if($condition->select_include_exclude == 1 && $result > -1){
                        $conditionmatch += 1;
                    }elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0 )){
                        $conditionmatch += 1;
                    }
                }
            }
            
            // min max amount
            if($condition->condition_type == 7){
                $add = true;
                $totalprice = $filters['subtotal'];
                if($condition->min_amount && $condition->min_amount > $totalprice)
                    $add = false;
                    
                if($condition->max_amount && $condition->max_amount < $totalprice)
                    $add = false;
                    
                if($add)
                    $conditionmatch += 1;
            }
            
            // Date
            if($condition->condition_type == 9){
                $now = Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d');
                // print_r($now);
                if($now == $condition->date){
                    $conditionmatch += 1;
                }
            }
            
            // Time
            if($condition->condition_type == 10){
                $now = Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('H:i');
                if($now >= $condition->start_time && $now <= $condition->end_time){
                    $conditionmatch += 1;
                }
            }
            
            
            if($userData){
                
                // Email
                if($condition->condition_type == 11){
                    
                    $result = array_search($userData->email,explode(',',$condition->email));
                    
                    if($condition->select_include_exclude == 1 && $result > -1){
                        $conditionmatch += 1;
                    }
                    elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0 )){
                        $conditionmatch += 1;
                    }
                }
                
                // First Orders
                if($condition->condition_type == 12){
                    if(isset($userData->OrdersData) && $userData->OrdersDataDiscountRule->count() == '0'){
                        $conditionmatch += 1;
                    }
                }
                
                // Phone Number 
                if($condition->condition_type == 13){
                    $result = array_search($userData->phone_number,explode(',',$condition->phone_number));
                    
                    if($condition->select_include_exclude == 1 && $result > -1){
                        $conditionmatch += 1;
                    }
                    elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0 )){
                        $conditionmatch += 1;
                    }
                    
                }
                
                // DOB
                if($condition->condition_type == 14){
                    if(isset($userData->date_of_birth) && $userData->date_of_birth == $condition->dob){
                        $conditionmatch += 1;
                    }
                }
                
                // No Of Orders
                if($condition->condition_type == 15){
                    if(isset($userData->OrdersData) && $userData->OrdersDataDiscountRule->where('status','>=',0)->where('status','=<',4)->count() <= $condition->no_of_orders){
                        $conditionmatch += 1;
                    }
                }
            }
            
            if($cityData){
            // city 
                if($condition->condition_type == 16){
                    $result = array_search($cityData->id,explode(',',$condition->city_id));
                    if($condition->select_include_exclude == 1 && $result > -1){
                        $conditionmatch += 1;
                    }
                    elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0 )){
                        $conditionmatch += 1;
                    }
                }
            }
            
        }
        
        
        
            
        if($conditiontype == 1){
            return $conditionmatch == sizeof($conditions->toArray());
        }
        elseif($conditiontype = 2){
            return $conditionmatch >= 1;
        }
        
        else{
            // return false;
        }
        
        // print_r($conditionmatch);die;
    }
}