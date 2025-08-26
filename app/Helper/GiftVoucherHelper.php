<?php

namespace App\Helper;
use Request;
use App\Models\GiftVoucher;
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
use App\Helper\NotificationHelper;
use Carbon\Carbon;
use DateTimeZone;
use DB;
use Illuminate\Support\Facades\Log;


class GiftVoucherHelper
{
    static function giftVoucherData($data, $device = 'desktop'){
        $createCoupondata = [];
        $createCouponids = [];
        $dataa = [];
        $filters = [];
        $checkRule = true;
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
            $subtotal = $order->ordersummary->where('type','subtotal')->pluck('price');
            $subtotal = $subtotal[0];
            $filters['subtotal'] = $subtotal;
        
        $productids = $filters['productids'];
        // \DB::enableQueryLog();
        // $queries = DB::getQueryLog();
        $GiftVouchersData  = GiftVoucher::
            with(
            'appliedbrandsData:id,name',
            'appliedcategoriesData:id,name',
            'appliedsubtagsData:id,name',
            'appliedproductData:id,name',
            'brandsData.productname:id,brands',
            'categoriesData.productname:id',
            'subtagsData.tagProducts:id',
            'productData:id',
            'restrictions',
            'restrictions.rulesData:id',
            'restrictions.freegiftsData:id',
            'restrictions.specialoffersData:id',
            'restrictions.couponData:id',
            'restrictions.fbtData:id', 
            'conditions.brandsData.productname:id,brands',
            'conditions.categoriesData.productname:id',
            'conditions.subtagsData.tagProducts:id',
            'conditions.productData:id'
            )
        ->where('status',1)
        ->when($order->mobileapp == 0, function ($q) {
            return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
        })
        ->when($order->mobileapp == 1, function ($q) {
            return $q->whereRaw("FIND_IN_SET('2', discount_devices)");
        })
        // ->where(function($a) use ($productids){
        //     return $a->where('voucher_restriction_type',1)->where(function($b) use ($productids){
        //         return $a->whereHas('brandsData.productname',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     })->orWhere('voucher_restriction_type',2)->where(function($b) use ($productids){
        //         return $b->whereHas('subtagsData.tagProducts',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     })->orWhere('voucher_restriction_type',3)->where(function($b) use ($productids){
        //         return $b->whereHas('productData',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     })->orWhere('voucher_restriction_type',4)->where(function($b) use ($productids){
        //         return $b->whereHas('categoriesData.productname',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     });
        // })
        // ->where(function($a) use ($productids){
        //     return $a->whereHas('brandsData.productname',function($b) use ($productids){
        //         return $b->whereIn('products.id',$productids);
        //     })->orWhereHas('subtagsData.tagProducts',function($b) use ($productids){
        //         return $b->whereIn('products.id',$productids);
        //     })->orWhereHas('productData',function($b) use ($productids){
        //         return $b->whereIn('products.id',$productids);
        //     })->orWhereHas('categoriesData.productname',function($b) use ($productids){
        //         return $b->whereIn('products.id',$productids);
        //     });
        // })
        // ->where(function($a) use ($productids){
        //      return $a->whereHas('brandsData.productname',function($b) use ($productids){
        //             return $b->whereIn('products.id',$productids);
        //     })
        //     ->orWhereHas('subtagsData.tagProducts',function($b) use ($productids){
        //             return $b->whereIn('products.id',$productids);
        //     })
        //     ->orWhereHas('productData',function($b) use ($productids){
        //             return $b->whereIn('products.id',$productids);
        //     })
        //     ->orWhereHas('categoriesData.productname',function($b) use ($productids){
        //             return $b->whereIn('products.id',$productids);
        //     });
        // })
        // ->where(function($a) use ($productids){
        //      return $a->where('voucher_restriction_type',1,function($b) use ($productids){
        //         return $b->whereHas('brandsData.productname',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     })
        //     ->orWhere('voucher_restriction_type',2,function($b) use ($productids){
        //         return $b->whereHas('subtagsData.tagProducts',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     })
        //     ->orWhere('voucher_restriction_type',3,function($b) use ($productids){
        //         return $b->whereHas('productData',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     })
        //     ->orWhere('voucher_restriction_type',4,function($b) use ($productids){
        //         return $b->whereHas('categoriesData.productname',function($c) use ($productids){
        //             return $c->whereIn('products.id',$productids);
        //         });
        //     });
        // })
        ->leftJoin(DB::raw("(select count(id) as total,giftvouchers_id from order_giftvouchers group by giftvouchers_id) totalused"), function($join) {
            $join->on('gift_voucher.id', '=', 'totalused.giftvouchers_id');
        })
        ->where(function($a){
            return $a->whereNull('totalused.total')->orWhereRaw('gift_voucher.usage_limit_voucher > totalused.total');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })->get();
        // $queries = DB::getQueryLog();
        // $last_query = end($queries);
        // print_r($queries);die();
        // print_r(json_encode($GiftVouchersData->toArray()));die();
        // print_r(sizeof($GiftVouchersData));die();
        
        foreach ($GiftVouchersData as $key => $value) {
            $filterids = $filters['productids'];
            $filterqty = $filters['productqty'];
            $filterprice = $filters['productprice'];
        	$conditionapplied = GiftVoucherHelper::checkConditions($filters,$value->conditions,$value->condition_match_status);
        	if($conditionapplied){
        	
            // checkRule
            if($value->voucher_disable_rules != 1) {
                if(count($value->restrictions) >= 1) {
                    foreach($value->restrictions as $key => $val) {
                        if($val->disabled_type == 1) {
                            $checkOrd = $order->ordersummary->where('type','discount_rule')->pluck('amount_id');
                            if(count($checkOrd)) {
                                $commaSeparatedIds = $val->rules_id;
                                $idArray = explode(',', $commaSeparatedIds);
                                if (in_array($checkOrd[0], $idArray)) {
                                    $checkRule = false;
                                }  
                            }
                        }
                    }
                }
            }
        	
        	$matchpro = [];
	        if($value->voucher_restriction_type == 1){
	                $matchpro = array_merge($value->brandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            if($value->voucher_restriction_type == 2){
                $matchpro = array_merge($value->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            if($value->voucher_restriction_type == 3){
                $matchpro = array_merge($value->productData->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
            if($value->voucher_restriction_type == 4){
                $matchpro = array_merge($value->categoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
            }
	        $amount = 0;
        	if($value->discount_type == 3){
        	    if($matchpro){
        	        $amount = number_format(($value->discount_amount * $subtotal) / 100,2);
        	        if($value->max_cap_amount && $value->max_cap_amount < $amount){
    	                $amount = $value->max_cap_amount;
    	            }
        	    }
        	    if($amount > 0){
            	    $createCoupondata['giftVoucher'][$key] = [
            	        'id' => $value->id,
            	        'title' => "Percentage(%) Based on Order Value",
            	        'title_arabic' => $value->name_arabic,
            	        'amount' => $amount,
            	        'coupon_restriction_type' => $value->voucher_applied_type,
            	        'disable_rule_coupon' => $value->voucher_disable_rules,
            	        'condition_match_status' => $value->condition_match_status,
            	        'start_date' => $value->applied_start_date,
            	        'end_date' => $value->applied_end_date,
            	        'auto_apply' => isset($value->auto_apply) ? $value->auto_apply : 0
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
            	    $createCoupondata['giftVoucher'][$key] = [
            	        'id' => $value->id,
            	       // 'title' => $value->name,
            	        'title' => "Percentage(%) Based on Product Value",
            	        'title_arabic' => $value->name_arabic,
            	        'amount' => $amount,
            	        'coupon_restriction_type' => $value->voucher_applied_type,
            	        'disable_rule_coupon' => $value->voucher_disable_rules,
            	        'condition_match_status' => $value->condition_match_status,
            	        'start_date' => $value->applied_start_date,
            	        'end_date' => $value->applied_end_date,
            	        'auto_apply' => isset($value->auto_apply) ? $value->auto_apply : 0
            	    ];
            	    $createCouponids[] = $value->id;
        	    }
        	}
        	
        	if($value->discount_type == 5){
        	    if($matchpro){
        	        $amount = number_format($value->discount_amount,2);
    	        }
        	   
        	    if($amount > 0){
            	    $createCoupondata['giftVoucher'][$key] = [
            	        'id' => $value->id,
            	       // 'title' => $value->name,
            	        'title' => "Fixed Amount(SAR) Based on Order Value",
            	        'title_arabic' => $value->name_arabic,
            	        'amount' => $amount,
            	        'coupon_restriction_type' => $value->voucher_applied_type,
            	        'disable_rule_coupon' => $value->voucher_disable_rules,
            	        'condition_match_status' => $value->condition_match_status,
            	        'start_date' => $value->applied_start_date,
            	        'end_date' => $value->applied_end_date,
            	        'auto_apply' => isset($value->auto_apply) ? $value->auto_apply : 0
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
            	    $createCoupondata['giftVoucher'][$key] = [
            	        'id' => $value->id,
            	       // 'title' => $value->name,
            	        'title' => "Fixed Amount(SAR) Based on Product Value",
            	        'title_arabic' => $value->name_arabic,
            	        'amount' => $amount,
            	        'coupon_restriction_type' => $value->voucher_applied_type,
            	        'disable_rule_coupon' => $value->voucher_disable_rules,
            	        'condition_match_status' => $value->condition_match_status,
            	        'start_date' => $value->applied_start_date,
            	        'end_date' => $value->applied_end_date,
            	        'auto_apply' => isset($value->auto_apply) ? $value->auto_apply : 0
            	    ];
            	    $createCouponids[] = $value->id;
        	    }
        	}
        	
        	if($value->voucher_applied_type == 1){
        	    if($value->appliedbrandsData){
            	    $createCoupondata['giftVoucher'][$key]['brands'] = $value->appliedbrandsData->pluck('id')->toArray();
        	    }
        	}
        	if($value->voucher_applied_type == 2){
        	    if($value->appliedsubtagsData){
            	    $createCoupondata['giftVoucher'][$key]['sub_tags'] = $value->appliedsubtagsData->pluck('id')->toArray();
        	    }
        	}
        	if($value->voucher_applied_type == 3){
        	    if($value->appliedproductData){
            	    $createCoupondata['giftVoucher'][$key]['products'] = $value->appliedproductData->pluck('id')->toArray();
        	    }
        	}
        	if($value->voucher_applied_type == 4){
        	    if($value->appliedcategoriesData){
            	    $createCoupondata['giftVoucher'][$key]['categories'] = $value->appliedcategoriesData->pluck('id')->toArray();
        	    }
        	}
        	
            // $coupon_restriction['createCoupon'] = [];
        	if($value->voucher_disable_rules != 1){
            	foreach($value->restrictions as $r => $restriction){
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['disabled_type'] = $restriction->disabled_type;
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['select_include_exclude'] = $restriction->select_include_exclude;
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['rules_id'] = $restriction->rules_id;
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['free_gifts_id'] = $restriction->free_gifts_id;
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['special_offers_id'] = $restriction->special_offers_id;
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['coupon_id'] = $restriction->coupon_id;
            	    $createCoupondata['giftVoucher'][$key]['coupon_restriction'][$r]['fbt_id'] = $restriction->fbt_id;
            	}
        	}
        	}
        
        }
        $chars = "0123456789";
        $code = "GV";
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars)-1)];
        }
        if($createCoupondata){
        	foreach($createCoupondata['giftVoucher'] as $c => $data){
        	if($checkRule == true) {
        	    $params = [
                [
                  "type" => "text", 
                  "text" => $userData->first_name
                ],
                [
                  "type" => "text", 
                  "text" => $userData->last_name
                ],
                [
                  "type" => "text", 
                  "text" => $code
                ],
                [
                  "type" => "text", 
                  "text" => isset($data['end_date']) ? $data['end_date'] : null
                ],
            ];
            $WhatsAppresponse = NotificationHelper::whatsappmessage("+966".$userData->phone_number,'giftcoupon',$order->lang,$params);
            $phone = str_replace("-","","+966".$userData->phone_number);
            $phone = str_replace("_","",$phone);
            if ($order->lang == 'en') {
                 $SMSresponse = NotificationHelper::sms($phone,'Dear '.$userData->first_name.' '.$userData->last_name.',

Thank you for shopping with Tamkeen Stores.
Congratulations! You are eligible for a gift voucher worth.
The coupon code is: '.$code.'

Coupon Expiry: '.$data['end_date'].'
To benefit from the coupon,, visit this link: https://tamkeenstores.com.sa/en');
            }
            else{
                 $SMSresponse = NotificationHelper::sms($phone, '
                 '.$userData->first_name.' '.$userData->last_name.' العزيز،
شكرا لك لاختيارنا.
تهانينا! أنت مؤهل لقسيمة شرائية لعرض اليوم الوطني بقيمة .
رمز القسيمة هو: '.$code.'

انتهاء القسيمة: '.$data['end_date'].'

للإستفادة من القسيمة ، قم بزيارة هذا الرابط: https://tamkeenstores.com.sa/\ar');
            }
            
            	$Coupon = Coupon::create([
                    'coupon_code' => $code,
                    'description' => isset($data['title']) ? $data['title'] : null,
                    'discount_devices' => '0,1',
                    'start_date' => isset($data['start_date']) ? $data['start_date'] : null,
                    'end_date' => isset($data['end_date']) ? $data['end_date'] : null,
                    'status' => 1,
                    'discount_type' => 2,
                    'discount_amount' => isset($data['amount']) ? $data['amount'] : 0,
                    'max_cap_amount' => null,
                    'usage_limit_coupon' => 1,
                    'usage_limit_user' => 1,
                    'coupon_restriction_type' => isset($data['coupon_restriction_type']) ? $data['coupon_restriction_type'] : 1,
                    'restriction_auto_apply' => isset($data['auto_apply']) ? $data['auto_apply'] : 0,
                    'disable_rule_coupon' => isset($data['disable_rule_coupon']) ? $data['disable_rule_coupon'] : null,
                    'condition_match_status' => isset($data['condition_match_status']) ? $data['condition_match_status'] : null,
                    'connect_with_arabyads' => 0,
                    'show_on_commission' => 0,
                    'status' => 1,
                    'voucher_order_number' => $order->order_no,
                    'coupon_creation' => isset($data['title']) ? $data['title'] : null,
                    
                    
                ]);
                if($Coupon){
                    
                    if($data['coupon_restriction_type'] == 1 && $data['brands']){
                        foreach($data['brands'] as $k => $value){
                            $Couponbrand = CouponBrand::create([
                                'coupon_id' => $Coupon->id,
                                'brand_id' => isset($value) ? $value : null,
                            ]);
                        };
                    }
                    if($data['coupon_restriction_type'] == 2 && $data['sub_tags']){
                        foreach ($data['sub_tags'] as $k => $value)  {
                            $CouponTag = CouponSubTag::create([
                                'coupon_id' => $Coupon->id,
                                'sub_tag_id' => isset($value) ? $value : null,
                            ]);
                        }
                    }
                
                    if($data['coupon_restriction_type'] == 3 && $data['products']){
                        foreach ($data['products'] as $k => $value)  {
                            $CouponProduct = CouponProduct::create([
                                'coupon_id' => $Coupon->id,
                                'product_id' => isset($value) ? $value : null,
                            ]);
                        }
                    }
                
                    if($data['coupon_restriction_type'] == 4 && $data['categories']){
                        foreach ($data['categories'] as $k => $value)  {
                            $CouponCategory = CouponCategory::create([
                                'coupon_id' => $Coupon->id,
                                'category_id' => isset($value) ? $value : null,
                            ]);
                        }
                    }
                    
                    if($data['disable_rule_coupon'] !== 1 && $data['coupon_restriction']){
                        foreach ($data['coupon_restriction'] as $k => $value)  {
                            $value['coupon_id' ] = $Coupon->id;
                            CouponRestriction::create($value);
                        }
                        
                    }
                    $conditiondata = [
                        'rule_id' => $Coupon->id,
                        'module_type' => 1,
                        'condition_type' => 13,
                        'select_include_exclude' => 1,
                        'phone_number' => isset($userData) ? $userData->phone_number : null,
                    ];
                    RulesConditions::create($conditiondata);
                }
                
                OrderGiftVouchers::create([
                    'order_id' => $order->id,
                    'giftvouchers_id' => $data['id'],
                ]);
        	}
        	}
        }
        
        
        
        }
        // return $createCoupondata;
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