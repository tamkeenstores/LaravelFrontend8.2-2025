<?php

namespace App\Helper;
use Request;
use App\Models\Affiliation;
use App\Models\User;
use App\Models\States;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderSummary;
use App\Models\shippingAddress;
use App\Models\AffiliationRestriction;
use App\Models\UserComissions;
use Carbon\Carbon;
use DateTimeZone;
use DB;

class AffiliationHelper
{
    static function AffiliationData($data){
        $AffiliationDataCreate = [];
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
            $filters['city'] = isset($shippingAddress->stateData) ? $shippingAddress->stateData->name : null;
            $subtotal = $order->ordersummary->where('type','subtotal')->pluck('price');
            $subtotal = $subtotal[0];
            $filters['subtotal'] = $subtotal;
            
            $affiliationcodeFalse = false;
            $AppliedCoupon = $order->ordersummary->where('type','discount')->pluck('amount_id')->toArray();
            $AppliedDiscountrules = $order->ordersummary->where('type','discount_rule')->pluck('amount_id')->toArray();
            $affiliationcode = isset($order->affiliationcode) ? $order->affiliationcode : false;
            if($affiliationcode == false){
                $affiliationcodeFalse = true;
            }
            // print_r($AppliedCoupon);
            // echo "Test";
            // print_r($affiliationcodeFalse);die();
        $productids = $filters['productids'];
        $disable_rules = true;
// restrictions
        $AffiliationData  = Affiliation::with('UsersData','restrictions.brandsData.productname:id,brands','restrictions.categoriesData.productname:id','restrictions.subtagsData.tagProducts:id','restrictions.productData:id','rulesData:id,name','couponData:id,coupon_code','conditions')
        ->where('status',1)
        // ->where('disable_rules',0)
        ->when($affiliationcode,function($q) use ($affiliationcode){
            return $q->where('disable_rules',1)->where('slug_code',$affiliationcode);
        })
        ->when($affiliationcodeFalse,function($a) use ($AppliedDiscountrules,$AppliedCoupon){
            return $a->whereHas('rulesData',function($b) use ($AppliedDiscountrules){
                return $b->where('disable_rules',0)->where('rules_type',1)->whereIn('discount_rules.id',$AppliedDiscountrules);
            })->orWhereHas('couponData',function($b) use ($AppliedCoupon){
                return $b->where('disable_rules',0)->where('rules_type',2)->whereIn('coupon.id',$AppliedCoupon);
            });
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })->get();
        
        // print_r(sizeof($AffiliationData));die();
        if(sizeof($AffiliationData)){
            foreach ($AffiliationData as $key => $value){
                $MatchedDiscountrules = null;
                if($value->disable_rules == 0 && $value->rules_type == 1){
                    if($AppliedDiscountrules && $value->rules_id){
                        $MatchedDiscountrules = implode(',',array_intersect(explode(',',$value->rules_id),$AppliedDiscountrules));
                    }
                }
                
                $MatchedCoupon = null;
                if($value->disable_rules == 0 && $value->rules_type == 2){
                    if($AppliedCoupon && $value->coupon_id){
                        $MatchedCoupon = implode(',',array_intersect(explode(',',$value->coupon_id),$AppliedCoupon));
                        // print_r($MatchedCoupon);die();
                    }
                }
                
                
                    $filterids = $filters['productids'];
                    $filterqty = $filters['productqty'];
                    $filterprice = $filters['productprice'];
                    if($value->disable_conditions == 0){
                        $conditionapplied = AffiliationHelper::checkConditions($filters,$value->conditions,1);
                        
                        if(!$conditionapplied){
                            continue;
                        }
                    }
                    
                $matchpro = [];
    	        foreach($value->restrictions as $k => $restriction){
    	            
    	            if($restriction->restriction_type == 1){
    	                $amount = 0;
    	                $matchpro = $restriction->brandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
    	                if($matchpro){
        	               $bad_keys=array_keys(array_diff($filterids,$matchpro));
            	           $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           
            	           if($restriction->restriction_discount_type == 0){
                	            $amount = number_format(($restriction->discount_amount * $price) / 100,2);
                	            if($restriction->max_cap_amount && $restriction->max_cap_amount < $amount){
                	                $amount = number_format($restriction->max_cap_amount,2);
                	            }
            	           }
            	           
            	           if($restriction->restriction_discount_type == 1){
                	            $amount = number_format($restriction->discount_amount,2);
                	       }
            	            
            	           if($amount > 0){
            	               $AffiliationDataCreate[] = $amount;
            	           }
    	                }
    	            }
    	            if($restriction->restriction_type == 2){
    	                $amount = 0;
    	                $matchpro = $restriction->productData->whereIn('id', $filterids)->pluck('id')->toArray();
    	                if($matchpro){
        	               $bad_keys=array_keys(array_diff($filterids,$matchpro));
            	           $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           
            	           if($restriction->restriction_discount_type == 0){
                	            $amount = number_format(($restriction->discount_amount * $price) / 100,2);
                	            if($restriction->max_cap_amount && $restriction->max_cap_amount < $amount){
                	                $amount = number_format($restriction->max_cap_amount,2);
                	            }
            	           }
            	           
            	           if($restriction->restriction_discount_type == 1){
                	            $amount = number_format($restriction->discount_amount,2);
                	       }
            	            
            	           if($amount > 0){
            	               $AffiliationDataCreate[] = $amount;
            	           }
    	                }
    	            }
    	            if($restriction->restriction_type == 3){
    	                $amount = 0;
    	                $matchpro = $restriction->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
    	                if($matchpro){
        	               $bad_keys=array_keys(array_diff($filterids,$matchpro));
            	           $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           
            	           if($restriction->restriction_discount_type == 0){
                	            $amount = number_format(($restriction->discount_amount * $price) / 100,2);
                	            if($restriction->max_cap_amount && $restriction->max_cap_amount < $amount){
                	                $amount = number_format($restriction->max_cap_amount,2);
                	            }
            	           }
            	           
            	           if($restriction->restriction_discount_type == 1){
                	            $amount = number_format($restriction->discount_amount,2);
                	       }
            	            
            	           if($amount > 0){
            	               $AffiliationDataCreate[] = $amount;
            	           }
    	                }
    	            }
    	            if($restriction->restriction_type == 4){
    	                $amount = 0;
    	                $matchpro = $restriction->categoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
    	                if($matchpro){
        	               $bad_keys=array_keys(array_diff($filterids,$matchpro));
            	           $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	           
            	           if($restriction->restriction_discount_type == 0){
                	            $amount = number_format(($restriction->discount_amount * $price) / 100,2);
                	            if($restriction->max_cap_amount && $restriction->max_cap_amount < $amount){
                	                $amount = number_format($restriction->max_cap_amount,2);
                	            }
            	           }
            	           
            	           if($restriction->restriction_discount_type == 1){
                	            $amount = number_format($restriction->discount_amount,2);
                	       }
            	            
            	           if($amount > 0){
            	               $AffiliationDataCreate[] = $amount;
            	           }
    	                }
    	            }
    	        }
    	        
            }
            
            $usersData = User::whereIn('phone_number',explode(',',$value->specific_users_id))->get(['id','phone_number']);
            if($usersData){
                // array_sum($AffiliationDataCreate);
                foreach($usersData as $k => $userData){
                    $UserComissions = UserComissions::create([
                        'user_id'=> $userData->id,
                        'affiliation_id'=> $value->id,
                        'title'=> $value->name,
                        'title_arabic'=> $value->name_arabic,
                        'disable_type'=> $value->disable_rules,
                        'rules_type'=> $value->disable_rules == 0 ? $value->rules_type : null,
                        'rules_id'=> $value->rules_type == 1 ? $MatchedDiscountrules : null,
                        'coupon_id'=> $value->rules_type == 2 ? $MatchedCoupon : null,
                        'slug_code'=> $value->disable_rules == 1 ? $value->slug_code : null,
                        'value'=> array_sum($AffiliationDataCreate),
                        'order_no'=> $order->order_no,
                        'notes'=> $value->notes,
                        'status'=> 0,
                        ]);
                }
            }
            }
        }
        
        
        // return $AffiliationDataCreate;
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
        // print_r($conditions);
        $conditionmatch = 0;
        foreach ($conditions as $key => $condition) {
            // if($condition->condition_type == 1){
            //     // brands
            //     $brandsin = $condition->brandsData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
            //     if($condition->select_include_exclude == 1){
            //         $bad_keys=array_keys(array_diff_key($filters['productids'],$brandsin->toArray()));
            //         $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $add = true;
                    
            //         if($condition->select_quantity == 1 && $qty >= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 2 && $qty > $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 3 && $qty != $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 4 && $qty <= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 5 && $qty < $condition->quantity)
            //             $add = false;
                        
            //         if($condition->min_amount && $condition->min_amount > $price)
            //             $add = false;
                        
            //         if($condition->max_amount && $condition->max_amount < $price)
            //             $add = false;
                    
                    
            //         if($add)
            //             $conditionmatch += 1;
            //     }
            //     else{
            //         if(sizeof($brandsin->toArray()) < 1)
            //             $conditionmatch += 1;
            //     }
    
            // }
            // if($condition->condition_type == 2){
            //     // products
            //     $productsin = $condition->productData->whereIn('id', $filters['productids'])->pluck('id');
            //     if($condition->select_include_exclude == 1){
            //         $bad_keys=array_keys(array_diff_key($filters['productids'],$productsin->toArray()));
            //         $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $add = true;
                    
            //         if($condition->select_quantity == 1 && $qty >= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 2 && $qty > $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 3 && $qty != $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 4 && $qty <= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 5 && $qty < $condition->quantity)
            //             $add = false;
                        
            //         if($condition->min_amount && $condition->min_amount > $price)
            //             $add = false;
                        
            //         if($condition->max_amount && $condition->max_amount < $price)
            //             $add = false;
                    
                    
            //         if($add)
            //             $conditionmatch += 1;
            //     }
            //     else{
            //         if(sizeof($productsin->toArray()) < 1)
            //             $conditionmatch += 1;
            //     }
            // }
            // if($condition->condition_type == 3){
            //     // Sub Tags
            //     $subtagsin = $condition->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
            //     if($condition->select_include_exclude == 1){
            //         $bad_keys=array_keys(array_diff_key($filters['productids'],$subtagsin->toArray()));
            //         $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $add = true;
                    
            //         if($condition->select_quantity == 1 && $qty >= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 2 && $qty > $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 3 && $qty != $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 4 && $qty <= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 5 && $qty < $condition->quantity)
            //             $add = false;
                        
            //         if($condition->min_amount && $condition->min_amount > $price)
            //             $add = false;
                        
            //         if($condition->max_amount && $condition->max_amount < $price)
            //             $add = false;
                    
                    
            //         if($add)
            //             $conditionmatch += 1;
            //     }
            //     else{
            //         if(sizeof($subtagsin->toArray()) < 1)
            //             $conditionmatch += 1;
            //     }
            // }
            // if($condition->condition_type == 4){
            //     // Categories
            //     $categoiesin = $condition->categoriesData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
            //     if($condition->select_include_exclude == 1){
            //         $bad_keys=array_keys(array_diff_key($filters['productids'],$categoiesin->toArray()));
            //         $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            //         $add = true;
                    
            //         if($condition->select_quantity == 1 && $qty >= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 2 && $qty > $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 3 && $qty != $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 4 && $qty <= $condition->quantity)
            //             $add = false;
            //         if($condition->select_quantity == 5 && $qty < $condition->quantity)
            //             $add = false;
                        
            //         if($condition->min_amount && $condition->min_amount > $price)
            //             $add = false;
                        
            //         if($condition->max_amount && $condition->max_amount < $price)
            //             $add = false;
                    
                    
            //         if($add)
            //             $conditionmatch += 1;
            //     }
            //     else{
            //         if(sizeof($categoiesin->toArray()) < 1)
            //             $conditionmatch += 1;
            //     }
            // }
            
            if($condition->condition_type == 5){
                if($filters['paymentmethod']){
                    $result = array_search($filters['paymentmethod'],explode(',',$condition->payment_method_id));
                    
                    if($result > 0){
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
            // if($condition->condition_type == 9){
            //     $now = Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d');
            //     // print_r($now);
            //     if($now == $condition->date){
            //         $conditionmatch += 1;
            //     }
            // }
            
            // Time
            if($condition->condition_type == 10){
                $now = Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('H:i');
                if($now >= $condition->start_time && $now <= $condition->end_time){
                    $conditionmatch += 1;
                }
            }
            
            
            if($userData){
                
                // Email
                // if($condition->condition_type == 11){
                    
                //     $result = array_search($userData->email,explode(',',$condition->email));
                    
                //     if($condition->select_include_exclude == 1 && $result > -1){
                //         $conditionmatch += 1;
                //     }
                //     elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0 )){
                //         $conditionmatch += 1;
                //     }
                // }
                
                // First Orders
                if($condition->condition_type == 12){
                    if(isset($userData->OrdersData) && $userData->OrdersDataDiscountRule->count() == '0'){
                        $conditionmatch += 1;
                    }
                }
                
                // Phone Number 
                // if($condition->condition_type == 13){
                //     $result = array_search($userData->phone_number,explode(',',$condition->phone_number));
                //     if($condition->select_include_exclude == 1 && $result > -1){
                //         $conditionmatch += 1;
                //     }
                //     elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0 )){
                //         $conditionmatch += 1;
                //     }
                // }
                
                // DOB
                if($condition->condition_type == 14){
                    if(isset($userData->date_of_birth) && $userData->date_of_birth == $condition->dob){
                        $conditionmatch += 1;
                    }
                }
                
                // No Of Orders
                // if($condition->condition_type == 15){
                //     if(isset($userData->OrdersData) && $userData->OrdersDataDiscountRule->where('status','>=',0)->where('status','=<',4)->count() <= $condition->no_of_orders){
                //         $conditionmatch += 1;
                //     }
                // }
            }
            
            // city 
            if($cityData){
                if($condition->condition_type == 16){
                    $result = array_search($cityData->id,explode(',',$condition->city_id));
                    if($result > 0){
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