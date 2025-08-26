<?php

namespace App\Helper;
use Request;
use App\Models\Coupon;
use App\Models\User;
use App\Models\States;
use Carbon\Carbon;
use DateTimeZone;
use DB;

class CouponHelper
{
    static function couponData($data, $device = 'desktop'){
        
        $productids = $data['productids'];
        $deviceType = isset($data['device']) ? $data['device'] : false;
        $couponCode = isset($data['coupon_code']) && $data['coupon_code'] ? $data['coupon_code'] : false;
        $CouponsData = Coupon::when($couponCode, function ($q) use ($couponCode) {
            return $q->where('coupon_code',$couponCode);
        })->when($couponCode == false, function ($q) {
            return $q->where('restriction_auto_apply',1);
        })
        ->when($deviceType == 'desktop', function ($q) {
            return $q->whereRaw("FIND_IN_SET('0', discount_devices)");
        })
        ->when($deviceType == 'app', function ($q) {
            return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
        })
        ->with('brands.productname:id,brands','category.productname:id','subtags.tagProducts:id','products:id','conditions', 'conditions.brandsData.productname:id,brands','conditions.categoriesData.productname:id','conditions.subtagsData.tagProducts:id','conditions.productData:id','restrictions.rulesData:id','restrictions.freegiftData','restrictions.fbtData')
        ->where(function($a) use ($productids){
            return $a->whereHas('brands.productname',function($b) use ($productids){
                return $b->where('coupon_restriction_type',1)->whereIn('products.id',$productids);
            })
            ->orWhereHas('subtags.tagProducts',function($b) use ($productids){
                return $b->where('coupon_restriction_type',2)->whereIn('products.id',$productids);
            })
            ->orWhereHas('products',function($b) use ($productids){
                return $b->where('coupon_restriction_type',3)->whereIn('products.id',$productids);
            })
            ->orWhereHas('category.productname',function($b) use ($productids){
                return $b->where('coupon_restriction_type',4)->whereIn('products.id',$productids);
            });
        })
        ->where('status',1)
        ->leftJoin(DB::raw("(select count(id) as total,amount_id from order_summary where type = 'discount' group by amount_id) totalused"), function($join) {
            $join->on('coupon.id', '=', 'totalused.amount_id');
        })
        ->where(function($a){
            return $a->whereNull('totalused.total')->orWhereRaw('coupon.usage_limit_coupon > totalused.total');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->get();
        
        // if($data['userid'] == "171"){
            // return $CouponsData;
        // }
        
        
        $discountdata = [];
        $discountedAmount = 0;
        $discountedCouponData = [];
        $extraDataNew = [];
        
        if($CouponsData){
            foreach ($CouponsData as $key => $CouponData) {
            $conditionapplied = CouponHelper::checkConditions($data,$CouponData->conditions,$CouponData->condition_match_status);
            
            //return $conditionapplied;
            $amount = 0;
            if($conditionapplied){
                $filterids = $data['productids'];
                $filterqty = $data['productqty'];
                $filterprice = $data['productprice'];
                $matchpro = [];
                if($CouponData->coupon_restriction_type == 1){
                    $matchpro = array_merge($CouponData->brands->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
                    
                }
                if($CouponData->coupon_restriction_type == 2){
                    $matchpro = array_merge($CouponData->subtags->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
                    
                }
                if($CouponData->coupon_restriction_type == 3){
                    $matchpro = array_merge($CouponData->products->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
                    
                }
                if($CouponData->coupon_restriction_type == 4){
                    $matchpro = array_merge($CouponData->category->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
                    
                }
                // return $matchpro;
                
                $bad_keys=array_keys(array_diff($filterids,$matchpro));
	            $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
	            $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
	            $amount = 0;

                $pricearray = array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	            $qtyarray = array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);

                $finalprice = 0;
                foreach($pricearray as $plkey => $plvalue){
                    // if($lowprice != $plvalue)
                        $finalprice += ($plvalue * $qtyarray[$plkey]);
                }

	            //return $price;
                if($CouponData->discount_type == 1){
    	            $amount = number_format(($CouponData->discount_amount * $finalprice) / 100,2);
    	            if($CouponData->max_cap_amount && $CouponData->max_cap_amount < $amount){
    	                $amount = $CouponData->max_cap_amount;
    	            }
    	        }
    	        
    	        if($CouponData->discount_type == 2){
    	            $amount = $CouponData->discount_amount;
    	        }
    	        
    	        if($CouponData->discount_type == 3){
    	            $amount = $CouponData->discount_amount * $qty;
    	        }
    	        $amount = str_replace(',', '', $amount);
    	        //return $amount;
            }
            
            $extraData = [];
            $filterrulesDataIds = [];
	        if($data['cartdata']['discounts']['discuountRules']){
	            $filterrulesDataIds = array_column($data['cartdata']['discounts']['discuountRules'], 'id');
	        }
    	       
	        if($CouponData->disable_rule_coupon == 1){
                //$amount = 0;
            }else{
                
                if($CouponData->restrictions){
                    $conditionDataStatus = false;
                   
                    foreach($CouponData->restrictions as $k => $restrictionData){
                        
                        // Rules Condition
                        if($restrictionData->rulesData->isNotEmpty() && $restrictionData->disabled_type == 1){
                            $RulesMatchedids = false;
                            if($restrictionData->select_include_exclude == 1){
                                $RulesMatchedids = $restrictionData->rulesData()->whereIn('id', $filterrulesDataIds)->pluck('id');
                                $extraData['discount_rules'] = $RulesMatchedids;
                                //if($RulesMatchedids){
                                  	//$amount = 0;
                                    //$conditionDataStatus = true;
                                //}
                            }else{
                                $RulesMatchedids = $restrictionData->rulesData()->whereIn('id', $filterrulesDataIds)->pluck('id');
                                $extraData['discount_rules'] = $RulesMatchedids;
                            }
                        }
                        
                        $gifts = [];
                        $fbt = [];
                        if($data['cartdata']['products']){
                            
                            foreach($data['cartdata']['products'] as $key => $pro){
                                if(isset($pro['gift']) && sizeof($pro['gift'])){
                                    foreach($pro['gift'] as $gkey => $gift){
                                        $gifts[] = $gift['gift_id'];
                                    }
                                }
                                
                                if(isset($pro['fbt']) && sizeof($pro['fbt'])){
                                    foreach($pro['fbt'] as $gkey => $fbt){
                                        $fbt[] = $fbt['fbt_id'];
                                    }
                                }
                            }
                            if($gifts){
                                $gifts =array_unique($gifts);
                            }
                            if($fbt){
                                $fbt = array_unique($fbt);
                            }
                        }
                        if($restrictionData->freegiftData->isNotEmpty() && $restrictionData->disabled_type == 2){
                            $RulesMatchedids = false;
                            if($restrictionData->select_include_exclude == 1){
                                $RulesMatchedids = $restrictionData->freegiftData()->whereIn('id', $gifts)->pluck('id');
                                 
                                if($RulesMatchedids){
                                    $amount = 0;
                                    $conditionDataStatus = true;
                                }
                            }else{
                                $RulesMatchedids = $restrictionData->freegiftData()->whereIn('id', $gifts)->pluck('id');
                                $extraData['free_gifts'] = $RulesMatchedids;
                            }
                            
                            
                        }
                        
                        if($restrictionData->fbtData->isNotEmpty() && $restrictionData->disabled_type == 5){
                            $RulesMatchedids = false;
                            if($restrictionData->select_include_exclude == 1){
                                $RulesMatchedids = $restrictionData->fbtData()->whereIn('id', $fbt)->pluck('id');
                                 
                                if($RulesMatchedids){
                                    $amount = 0;
                                    $conditionDataStatus = true;
                                }
                            }else{
                                $RulesMatchedids = $restrictionData->fbtData()->whereIn('id', $fbt)->pluck('id');
                                $extraData['fbt'] = $RulesMatchedids;
                            }
                            
                            
                        }
                        
                    }
                    
                    if($conditionDataStatus){
                        $amount = 0;
                    }
                }
            }
                
                
            
            
	        if($amount > 0){
	            $discountedAmount += $amount;
	            $discountedCouponData = $CouponData;
	            $extraDataNew = $extraData;
	           // $discountdata = [
            //         'id' => $CouponData->id,
        	   //     'title' => $CouponData->coupon_code,
        	   //     'title_arabic' => $CouponData->coupon_code,
        	   //     'amount' => $amount,
        	   //     'extradata' => $extraData,
    	       // ];
    	        
            //     break;
	        }
        }
        }
        
        if($discountedAmount > 0){
            $discountdata = [
                    'id' => $discountedCouponData->id,
        	        'title' => $discountedCouponData->coupon_code,
        	        'title_arabic' => $discountedCouponData->coupon_code,
        	        'amount' => $discountedAmount,
        	        'extradata' => $extraDataNew,
    	        ];
        }
        
        return $discountdata;
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
        $matchpro = [];
        foreach ($conditions as $key => $condition) {
            if($condition->condition_type == 1){
                // brands
                $brandsin = $condition->brandsData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    
                    
                    $bad_keys=array_keys(array_diff_key($filters['productids'],$brandsin->toArray()));
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    
                    $add = true;
                    
                    if($condition->rule_id == '272'){
                        
                        $priceArray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                        $qtyArray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                        
                        $priceArrayAmount = 0;
                        if(sizeof($priceArray) > 0){
                            foreach($priceArray as $k => $priceData){
                                $priceArrayAmount += $priceData*$qtyArray[$k];
                            }
                            
                        }
                        $price = $priceArrayAmount;     
                    }
                    
                    
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
                    if(isset($userData->OrdersData) && $userData->OrdersData->where('status','>=',0)->where('status','<=',4)->count() == '0'){
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
                    if(isset($userData->OrdersData) && $userData->OrdersDataDiscountRule->where('status','>=',0)->where('status','<=',4)->count() <= $condition->no_of_orders){
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
            return false;
        }
    }
}