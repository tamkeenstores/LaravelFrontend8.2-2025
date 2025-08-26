<?php

namespace App\Helper;
use Request;
use App\Models\Wallet;
use App\Models\User;
use App\Models\States;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderSummary;
use App\Models\OrderStatusTimeLine;
use App\Models\OrderBogo;
use App\Models\OrderFBT;
use App\Models\OrderFreeGift;
use App\Models\shippingAddress;
use App\Models\Coupon;
use App\Models\CouponBrand;
use App\Models\CouponSubTag;
use App\Models\CouponProduct;
use App\Models\CouponCategory;
use App\Models\RulesConditions;
use App\Models\CouponRestriction;
use App\Models\OrderGiftVouchers;
use App\Models\WalletHistory;
use App\Helper\NotificationHelper;
use Carbon\Carbon;
use DateTimeZone;
use DB;

class WalletHelper
{
    static function walletData($data, $device = 'desktop'){
        $createWalletdata = [];
        $createCouponids = [];
        $dataa = [];
        $filters = [];
        $order = Order::with('details.productData','ordersummary')->where('id',$data)->first();
        if($order){
            
            $filters['productids'] = $order->details()->where('unit_price','!=',0)->pluck('product_id')->toArray();
            $filters['productqty'] = $order->details()->where('unit_price','!=',0)->pluck('quantity')->toArray();
            $filters['productprice'] = $order->details()->where('unit_price','!=',0)->pluck('unit_price')->toArray();
            
            $shippingAddress = shippingAddress::where('id',$order->shipping_id)->first();
            $filters['userid'] = $order->customer_id;
            $userData = User::where('id',$order->customer_id)->first();
            $filters['userid'] = $order->customer_id;
            $filters['paymentmethod'] = $order->paymentmethod;
            $filters['city'] = isset($shippingAddress->stateData) ? $shippingAddress->stateData->name : false;
            $subtotal = $order->ordersummary->where('type','total')->pluck('price');
            $subtotal = $subtotal[0];
            $filters['subtotal'] = $subtotal;
        
        $productids = $filters['productids'];
        // 'appliedbrandsData:id,name','appliedcategoriesData:id,name','appliedsubtagsData:id,name','appliedproductData:id,name',
        $walletsData  = Wallet::
            with('brandsData.productname:id,brands','categoriesData.productname:id','subtagsData.tagProducts:id','productData:id','restrictions.rulesData:id','restrictions.freegiftsData:id','restrictions.specialoffersData:id','restrictions.couponData:id','restrictions.fbtData:id', 'conditions.brandsData.productname:id,brands','conditions.categoriesData.productname:id','conditions.subtagsData.tagProducts:id','conditions.productData:id')
        ->where('status',1)
        ->when($order->mobileapp == 0, function ($q) {
            return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
        })
        ->when($order->mobileapp == 1, function ($q) {
            return $q->whereRaw("FIND_IN_SET('2', discount_devices)");
        })
        ->where(function($a) use ($productids){
            return $a->whereHas('brandsData.productname',function($b) use ($productids){
                return $b->whereIn('products.id',$productids);
            })->orWhereHas('subtagsData.tagProducts',function($b) use ($productids){
                return $b->whereIn('products.id',$productids);
            })->orWhereHas('productData',function($b) use ($productids){
                return $b->whereIn('products.id',$productids);
            })->orWhereHas('categoriesData.productname',function($b) use ($productids){
                return $b->whereIn('products.id',$productids);
            });
        })
        ->leftJoin(DB::raw("(select count(id) as total,order_id from wallet_histories group by order_id) totalused"), function($join) {
            $join->on('wallet.id', '=', 'totalused.order_id');
        })
        ->where(function($a){
            return $a->whereNull('totalused.total')->orWhereRaw('wallet.usage_limit_wallet > totalused.total');
        })
        ->leftJoin(DB::raw("(select count(id) as total,order_id from wallet_histories where user_id = ".$order->customer_id." group by order_id) totalusedusers"), function($join) {
            $join->on('wallet.id', '=', 'totalusedusers.order_id');
        })
        ->where(function($a){
            return $a->whereNull('totalusedusers.total')->orWhereRaw('wallet.usage_limit_user > totalusedusers.total');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })->get();
        
        // print_r($walletsData);die();


        // $queries = DB::getQueryLog();
        // $last_query = end($queries);
        // print_r($queries);die();
        // print_r(json_encode($walletsData->toArray()));die();
        // print_r(sizeof($walletsData));die();
        
        $conditionappliedData = 0;
        foreach ($walletsData as $key => $value) {
            $filterids = $filters['productids'];
            $filterqty = $filters['productqty'];
            $filterprice = $filters['productprice'];
        	$conditionapplied = WalletHelper::checkConditions($filters,$value->conditions,$value->condition_match_status);
        	$conditionappliedData += $conditionapplied;
        	
        	if($conditionapplied){
        	$matchpro = [];
	        if($value->wallet_restriction_type == 1){
	                $matchpro = array_merge($value->brandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            if($value->wallet_restriction_type == 2){
                $matchpro = array_merge($value->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            if($value->wallet_restriction_type == 3){
                $matchpro = array_merge($value->productData->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            if($value->wallet_restriction_type == 4){
                $matchpro = array_merge($value->categoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            // print_r($matchpro);die();
            
            
	        $amount = 0;
                            
        	if($value->discount_type == 3){
        	    if($matchpro){
        	        $amount = number_format(($value->discount_amount * $subtotal) / 100,2);
        	        if($value->max_cap_amount && $value->max_cap_amount < $amount){
    	                $amount = $value->max_cap_amount;
    	            }
        	    }
        	    if($amount > 0){
            	    $createWalletdata[$key] = [
            	        'user_id' => $filters['userid'],
                        'order_id' => $value->id,
                        'type' => 1,
                        'amount' => $amount,
                        'description' => $value->description,
                        'description_arabic' => $value->description,
                        'wallet_type' => 'wallet',
                        'title' => $value->name,
                        'title_arabic' => $value->name_arabic,
                        'current_amount' => $amount,
                        'status' => 0,
            	    ];
            	    $createCouponids[] = $value->id;
        	    }
        	}
        	
        	if($value->discount_type == 4){
        	    if($matchpro){
    	            $bad_keys=array_keys(array_diff($filterids,$matchpro));
    	            $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
    	            $amount = number_format(($value->discount_amount * $price) / 100,2);
        	        if($value->max_cap_amount && $value->max_cap_amount < $amount){
    	                $amount = $value->max_cap_amount;
    	            }
    	        }
        	    if($amount > 0){
            	    $createWalletdata[$key] = [
            	        'user_id' => $filters['userid'],
                        'order_id' => $value->id,
                        'type' => 1,
                        'amount' => $amount,
                        'description' => $value->description,
                        'description_arabic' => $value->description,
                        'wallet_type' => 'wallet',
                        'title' => $value->name,
                        'title_arabic' => $value->name_arabic,
                        'current_amount' => $amount,
                        'status' => 0,
            	    ];
            	    $createCouponids[] = $value->id;
        	    }
        	}
        	
        	if($value->discount_type == 5){
        	    if($matchpro){
        	        $amount = number_format($value->discount_amount,2);
    	        }
        	   
        	    if($amount > 0){
            	    $createWalletdata[$key] = [
            	        'user_id' => $filters['userid'],
                        'order_id' => $value->id,
                        'type' => 1,
                        'amount' => $amount,
                        'description' => $value->description,
                        'description_arabic' => $value->description,
                        'wallet_type' => 'wallet',
                        'title' => $value->name,
                        'title_arabic' => $value->name_arabic,
                        'current_amount' => $amount,
                        'status' => 0,
            	    ];
            	    $createCouponids[] = $value->id;
        	    }
        	}
        	
        	if($value->discount_type == 6){
        	    if($matchpro){
    	            $bad_keys=array_keys(array_diff($filterids,$matchpro));
    	            $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
    	            $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
    	            $amount = number_format($value->discount_amount * $qty,2);
        	        }
        	    if($amount > 0){
            	    $createWalletdata[$key] = [
            	        'user_id' => $filters['userid'],
                        'order_id' => $value->id,
                        'type' => 1,
                        'amount' => $amount,
                        'description' => $value->description,
                        'description_arabic' => $value->description,
                        'wallet_type' => 'wallet',
                        'title' => $value->name,
                        'title_arabic' => $value->name_arabic,
                        'current_amount' => $amount,
                        'status' => 0,
            	    ];
            	    $createCouponids[] = $value->id;
        	    }
        	}
        	}
        
        }
        
        // print_r($createWalletdata);die();
        
        if($createWalletdata){
                foreach($createWalletdata as $w => $data){
                    
                        $userPlus = User::where('id', $data['user_id'])->first();
                        $currentAmountPlus = $userPlus->amount + str_replace(",","",$data['amount']);
                        $userPlus->amount = $currentAmountPlus;
                        $userPlus->save();
                        
                        if($userPlus){
                            $walletHistory = WalletHistory::create([
                                'user_id' => $userPlus->id,
                                'order_id' => isset($data['order_id']) ? $data['order_id'] : null,
                                'type' => 1,
                                'amount' => $data['amount'],
                                'description' => isset($data['description']) ? $data['description'] : null,
                                'description_arabic' => isset($data['description_arabic']) ? $data['description_arabic'] : null,
                                'wallet_type' => 'wallet',
                                'title' => isset($data['title']) ? $data['title'] : null,
                                'title_arabic' => isset($data['title_arabic']) ? $data['title_arabic'] : null,
                                'current_amount' => $currentAmountPlus,
                                'status' => 0,
                            ]);
                        }
                        
                }
        }
        
        return $createWalletdata;
        
        
    }
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
    }
}