<?php

namespace App\Helper;
use Request;
use App\Models\Rules;
use App\Models\User;
use App\Models\States;
use Carbon\Carbon;
use DateTimeZone;
use DB;

class DiscountRulesHelper
{
    protected static $maxdiscount = 0;
    static function ruleData($data, $device = 'desktop'){
        
        $skipids = isset($data['extradata'])  && $data['extradata'] ? $data['extradata']['discount_rules'] : false;
        $productids = $data['productids'];
        $deviceType = isset($data['device']) ? $data['device'] : false;
        $discountTypeCon = isset($data['discountType']) ? $data['discountType'] : false;
        $RulesData = Rules::select('id','name','name_arabic','discount_type','bogo_discount_type','bogo_status','cart_discount_depend','cart_fixed_amount','condition_match_status','cart_maxcap_amount')
        ->with('restrictions.brandsData.productname:id,brands','restrictions.categoriesData.productname:id','restrictions.subtagsData.tagProducts:id','restrictions.productData:id', 'conditions.brandsData.productname:id,brands','conditions.categoriesData.productname:id','conditions.subtagsData.tagProducts:id','conditions.productData:id','bulkdiscount','bogodiscount.productData:id,sku,status,name,name_arabic,price,sale_price,quantity,brands,feature_image','bogodiscount.productData.featuredImage','bogodiscount.productData.brand:id,name,name_arabic,brand_image_media','bogodiscount.productData.brand.BrandMediaImage:id,image')
        ->where('status',1)
        ->when($discountTypeCon === 1, function ($q) {
            return $q->where('discount_type', '1');
        })
        ->when($discountTypeCon === 0, function ($q) {
            return $q->where('discount_type', '!=', '1');
        })
        // ->when($device == 'desktop', function ($q) {
        //     return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
        // })
        // ->when($device != 'desktop', function ($q) {
        //     return $q->whereRaw("FIND_IN_SET('2', discount_devices)");
        // })
        ->when($deviceType == 'desktop', function ($q) {
            return $q->whereRaw("FIND_IN_SET('1', discount_devices)");
        })
        ->when($deviceType == 'app', function ($q) {
            return $q->whereRaw("FIND_IN_SET('2', discount_devices)");
        })
        ->when($skipids, function ($q) use ($skipids) {
            return $q->whereNotIn('id',$skipids);
        })
        // ->where(function($a) use ($productids){
        //     return $a->whereHas('restrictions', function ($b) use ($productids) {
        //         return $b->where('restriction_type',1)->where(function($c) use ($productids){
        //             return $c->where(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',1)->whereHas('brandsData.productname',function($e) use ($productids){
        //                     return $e->whereIn('products.id',$productids);
        //                 });
        //             })->orWhere(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',2)->whereHas('brandsData.productname',function($e) use ($productids){
        //                     return $e->whereNotIn('products.id',$productids);
        //                 });
        //             });
        //         });
        //     })->orWhereHas('restrictions', function ($b) use ($productids) {
        //         return $b->where('restriction_type',2)->where(function($c) use ($productids){
        //             return $c->where(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',1)->whereHas('subtagsData.tagProducts',function($e) use ($productids){
        //                     return $e->whereIn('products.id',$productids);
        //                 });
        //             })->orWhere(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',2)->whereHas('subtagsData.tagProducts',function($e) use ($productids){
        //                     return $e->whereNotIn('products.id',$productids);
        //                 });
        //             });
        //         });
        //     })->orWhereHas('restrictions', function ($b) use ($productids) {
        //         return $b->where('restriction_type',3)->where(function($c) use ($productids){
        //             return $c->where(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',1)->whereHas('productData',function($e) use ($productids){
        //                     return $e->whereIn('products.id',$productids);
        //                 });
        //             })->orWhere(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',2)->whereHas('productData',function($e) use ($productids){
        //                     return $e->whereNotIn('products.id',$productids);
        //                 });
        //             });
        //         });
        //     })->orWhereHas('restrictions', function ($b) use ($productids) {
        //         return $b->where('restriction_type',4)->where(function($c) use ($productids){
        //             return $c->where(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',1)->whereHas('categoriesData.productname',function($e) use ($productids){
        //                     return $e->whereIn('products.id',$productids);
        //                 });
        //             })->orWhere(function($d) use ($productids){
        //                 return $d->where('select_include_exclude',2)->whereHas('categoriesData.productname',function($e) use ($productids){
        //                     return $e->whereNotIn('products.id',$productids);
        //                 });
        //             });
        //         });
        //     });
        // })
        ->leftJoin(DB::raw("(select count(id) as total,amount_id from order_summary where type = 'discount_rule' group by amount_id) totalused"), function($join) {
            $join->on('discount_rules.id', '=', 'totalused.amount_id');
        })
        ->where(function($a){
            return $a->whereNull('totalused.total')->orWhereRaw('discount_rules.usage_limit > totalused.total');
        })
        // ->whereRaw('discount_rules.usage_limit > totalused.total')
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })->get();
        // return $RulesData;
        $discountdata = [
            'cart' => [],
            'bulk' => [],
            'bogo' => []
        ];
        
        $userData = false;
        $cityData = false;
        if($data['userid']){
            $userData = User::with('OrdersData')->where('id',$data['userid'])->first();
        }
        
        $city = 'Jeddah'; // default

        if (isset($data['city']) && trim($data['city']) !== '' && strtolower(trim($data['city'])) !== 'undefined') {
            $city = trim($data['city']);
        }

        $cityData = States::where('name', $city)->orWhere('name_arabic', $city)->first();



        foreach ($RulesData as $key => $value) {
            
            $filterids = $data['productids'];
            $filterqty = $data['productqty'];
            $filterprice = $data['productprice'];
        	$conditionapplied = DiscountRulesHelper::checkConditions($data,$value->conditions,$value->condition_match_status,$userData,$cityData);
        // 	if($data['userid'] == '181775'){
        // 	    return self::$maxdiscount;
        // 	}
        // 	if($data['userid'] == '171' && $value->id = '303'){
        // 	    return $conditionapplied;
        // 	}
        	if($conditionapplied){
        	$matchpro = [];
	        foreach($value->restrictions as $k => $restriction){
	            if($restriction->restriction_type == 1){
	                if($restriction->select_include_exclude == 1)
	                    $matchpro = array_merge($restriction->brandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
	                else{
	                    $removedata = $restriction->brandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
	                    $bad_keys=array_keys(array_diff_key($filterids,$removedata));
	                    $filterids = array_filter($filterids, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterqty = array_filter($filterqty, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterprice = array_filter($filterprice, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $matchpro = array_diff($matchpro, $removedata);

	                }
	            }
	            if($restriction->restriction_type == 2){
	                if($restriction->select_include_exclude == 1)
	                    $matchpro = array_merge($restriction->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
	                else{
	                    $removedata = $restriction->subtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
	                    $bad_keys=array_keys(array_diff_key($filterids,$removedata));
	                    $filterids = array_filter($filterids, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterqty = array_filter($filterqty, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterprice = array_filter($filterprice, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $matchpro = array_diff($matchpro, $removedata);

	                }
	            }
	            if($restriction->restriction_type == 3){
	                if($restriction->select_include_exclude == 1)
	                    $matchpro = array_merge($restriction->productData->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
	                else{
	                    $removedata = $restriction->productData->whereIn('id', $filterids)->pluck('id')->toArray();
	                    $bad_keys=array_keys(array_diff_key($filterids,$removedata));
	                    $filterids = array_filter($filterids, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterqty = array_filter($filterqty, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterprice = array_filter($filterprice, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $matchpro = array_diff($matchpro, $removedata);

	                }
	            }
	            if($restriction->restriction_type == 4){
	                if($restriction->select_include_exclude == 1)
	                    $matchpro = array_merge($restriction->categoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray(),$matchpro);
	                else{
	                    $removedata = $restriction->categoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
	                    $bad_keys=array_keys(array_diff_key($filterids,$removedata));
	                    $filterids = array_filter($filterids, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterqty = array_filter($filterqty, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $filterprice = array_filter($filterprice, function($k) use ($bad_keys) {return in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
	                    $matchpro = array_diff($matchpro, $removedata);

	                }
	                
	            }
	        }
	        
	        
            // if($value->id == 177)
            // return $value;
            // return false;
            
        	if($value->discount_type == 2){
        	    $amount = 0;
        	    if($value->cart_discount_depend == 1){
        	        $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	        $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	        $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	        if($price){
        	           // if($value->id == '263'){
            	       //     $amount = $value->cart_fixed_amount;
            	       // }
            	       // elseif($value->id == '264'){
            	       //     $amount = $value->cart_fixed_amount;
            	       // }
            	       // else{
            	       //    $amount = $value->cart_fixed_amount*$qty;
            	       // }
            	       $amount = $value->cart_fixed_amount;
        	        }
        	            
        	            
        	       // if($value->id === 232){
        	       //     if($price)
        	       //         $amount = $value->cart_fixed_amount*$qty;
        	       // }
        	        
        	    }
        	    if($value->cart_discount_depend == 2){
        	        if($matchpro){
        	            $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	           // $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	            $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                        $pricearray = array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                        $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                        $qtyarray = array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                        
                        
                        $finalprice = 0;
                        foreach($pricearray as $plkey => $plvalue){
                            // if($lowprice != $plvalue)
                                $finalprice += ($plvalue * $qtyarray[$plkey]);
                        }
                        
                        
        	            $amount = number_format(($value->cart_fixed_amount * $finalprice) / 100,2);
        	            //return $amount;
        	            if($value->cart_maxcap_amount && $value->cart_maxcap_amount < $amount){
        	                $amount = $value->cart_maxcap_amount;
        	            }
        	            $amount = str_replace(',', '', $amount);
        	        }
        	    }
        	    
        	    
        	   // if($value->cart_discount_depend == 3){
        	   //if(isset($data['userid']) && $data['userid'] === '181775'){
        	       
        	       
        	   //     if($matchpro && $value->id == '147'){
        	   //         $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	   //         $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	            
        	   //         $amount = number_format((0.130434783 * $price),2);
        	   //        // return $amount;
        	   //        // if($value->cart_maxcap_amount && $value->cart_maxcap_amount < $amount){
        	   //        //     $amount = $value->cart_maxcap_amount;
        	   //        // }
        	   //         $amount = str_replace(',', '', $amount);
        	   //     }
        	   // }
        	    
        
        	    if($amount > 0){
        	        
            	    $discountdata['cart'][] = [
            	        'id' => $value->id,
            	        'title' => $value->name,
            	        'title_arabic' => $value->name_arabic,
            	        'amount' => $amount
            	    ];
        	    }
        	}
        	
        	if($value->discount_type == 4){
        	    $bad_keys=array_keys(array_diff($filterids,$matchpro));
        	    $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	    $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
        	    
        	    $bulk = $value->bulkdiscount->where('min_quantity', '<=',  $qty)->where('max_quantity', '>=', $qty)->first();
        	   // return $value->bulkdiscount;
        	    $amount = 0;
        	    if($bulk){
        	        //return $bulk;
        	        if($bulk->bulk_discount_type == 1){
        	            $amount = $bulk->discount_amount;
        	        }
        	        if($bulk->bulk_discount_type == 2){
        	            $amount = number_format(($bulk->discount_amount * $price) / 100,2);
        	            if($bulk->max_cap_amount && $bulk->max_cap_amount < $amount){
        	                $amount = $bulk->max_cap_amount;
        	            }
        	        }
        	        if($bulk->bulk_discount_type == 3){
        	            $amount = $bulk->discount_amount * $qty;
        	        }
        	        if($amount > 0){
        	            
            	        $discountdata['bulk'][] = [
                	        'id' => $value->id,
                	        'title' => $value->name,
                	        'title_arabic' => $value->name_arabic,
                	        'amount' => $amount
                	    ];
        	        }
        	    }
        	}
        	
        	
        	if($value->discount_type == 1){
        	    //return $value;
        	    
        	    
        	    if(sizeof($matchpro)){
            	    $bad_keys=array_keys(array_diff($filterids,$matchpro));
            	    $price = array_sum(array_filter($filterprice, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	    $qty = array_sum(array_filter($filterqty, function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
            	    $bogo_status = $value->bogo_status;
            	    
            	    if(sizeof($value->bogodiscount)){
                	    if($value->bogodiscount[0]->recursive == 1){
                	        
                	        //return $value->bogodiscount->first();
                	        
                	        $bogodiscount = $value->bogodiscount->first();
                	        
                	        $discount = false;
                	        $bogoqty = $bogodiscount->quantity;
                	        
                	        if($bogodiscount->min_quantity <= $qty && $bogodiscount->max_quantity >= $qty){
                	            
                	            $discount = true;
                	            
                	        }
                	        elseif($bogodiscount->max_quantity < $qty){
                	            
                	            $nqty = DiscountRulesHelper::recursiveBogo($qty,$bogodiscount->min_quantity,$bogodiscount->max_quantity,abs($bogodiscount->min_quantity - $bogodiscount->max_quantity),$bogoqty,$bogoqty);
                	           
                	            if($nqty){
                	                
                	                $discount = true;
                	                $bogoqty = $nqty;
                	            }
                	        }
                	        if($discount){
                	            
                    	        //return $bogodiscount;
                    	        if($value->bogo_discount_type == 2){
                    	            //return 'multe';
                    	            $bogodiscountproductsData = $bogodiscount->productData->where('status',1)->where('quantity','>=',$bogodiscount->quantity);
                    	            
                    	            foreach($bogodiscountproductsData as $bogodiscountproducts){
                    	                
                    	                
                    	                $brand = $bogodiscountproducts->brand()->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')->first(['id','name','name_arabic','slug','status','brand_image_media']);
                            	        
                            	        
                                        
                            	        $price = 0;
                            	        if($bogodiscount->discount_depend == 2){
                            	            $price = $bogodiscount->fixed_amount;
                            	        }
                            	        if($bogodiscount->discount_depend == 3){
                            	            $pamount = ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price;
                            	            $amount = number_format(($bogodiscount->fixed_amount * $pamount) / 100,2);
                            	            if($bogodiscount->max_cap_amount && $bogodiscount->max_cap_amount < $amount){
                            	                $amount = $bogodiscount->max_cap_amount;
                            	            }
                            	            $price =  number_format($pamount - $amount, 2);
                            	        }
                            	        
                            	        
                            	        //return $bogodiscountproducts;
                            	        $addProduct = [
                        	                'id' => $bogodiscountproducts->id,
                        	                'sku' => $bogodiscountproducts->sku,
                        	                'name' =>$bogodiscountproducts->name,
                        	                'name_arabic' => $bogodiscountproducts->name_arabic,
                        	                'image' => $bogodiscountproducts->featuredImage->image ? 'https://images.tamkeenstores.com.sa/assets/new-media/'.$bogodiscountproducts->featuredImage->image  : '',
                        	                'price' => ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price,
                        	                'regular_price' => $bogodiscountproducts->price,
                        	                'quantity' => $bogoqty,
                        	                'total_quantity' => $bogodiscountproducts->quantity,
                        	                'brand' => $brand,  
                        	                'bogo' => 1,
                        	                'bogo_id' => $value->id,
                        	                'discounted_amount' => $price,
                            	       ];
                            	       
                            	       $discountdata['bogo'][] = $addProduct;
                            	       
                            	       
                    	            }
                    	        }
                    	        else{
                    	            //return 'single';
                        	        //return $bogodiscount;
                        	       // echo $value->id;
                        	        if($bogo_status == 0){
                        	            $bogodiscountproducts = $bogodiscount->productData->where('status',1)->random();
                        	       }
                        	        if($bogo_status == 1){
                        	            $bogodiscountproducts = $bogodiscount->productData->where('status',1)->sortByDesc('price');
                        	        }
                        	        if($bogo_status == 2){
                        	            $bogodiscountproducts = $bogodiscount->productData->where('status',1)->sortBy('price');
                        	        }
                        	        //return $bogodiscountproducts;
                        	        $brand = $bogodiscountproducts->brand()->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')->first(['id','name','name_arabic','slug','status','brand_image_media']);
                        	        $price = 0;
                        	        if($bogodiscount->discount_depend == 2){
                        	            $price = $bogodiscount->fixed_amount;
                        	        }
                        	        if($bogodiscount->discount_depend == 3){
                        	            $pamount = ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price;
                        	            $amount = number_format(($bogodiscount->fixed_amount * $pamount) / 100,2);
                        	            if($bogodiscount->max_cap_amount && $bogodiscount->max_cap_amount < $amount){
                        	                $amount = $bogodiscount->max_cap_amount;
                        	            }
                        	            $price =  number_format($pamount - $amount, 2);
                        	        }
                        	        //return $bogodiscountproducts;
                        	        $addProduct = [
                    	                'id' => $bogodiscountproducts->id,
                    	                'sku' => $bogodiscountproducts->sku,
                    	                'name' =>$bogodiscountproducts->name,
                    	                'name_arabic' => $bogodiscountproducts->name_arabic,
                    	                'image' => $bogodiscountproducts->featuredImage->image ? 'https://images.tamkeenstores.com.sa/assets/new-media/'.$bogodiscountproducts->featuredImage->image  : '',
                    	                'price' => ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price,
                    	                'regular_price' => $bogodiscountproducts->price,
                    	                'quantity' => $bogoqty,
                    	                'total_quantity' => $bogodiscountproducts->quantity,
                    	                'brand' => $brand,  
                    	                'bogo' => 1,
                    	                'bogo_id' => $value->id,
                    	                'discounted_amount' => $price,
                        	       ];
                        	       $discountdata['bogo'][] = $addProduct;
                        	       
                    	        }
                    	    }
                	       // return abs($bogodiscount->min_quantity - $bogodiscount->max_quantity);
                	    }
                	    if($value->bogodiscount[0]->recursive == 0){
                	        //return $qty;
                	        
                    	    $bogodiscount = $value->bogodiscount->where('min_quantity', '<=',  $qty)->where('max_quantity', '>=', $qty)->first();
                    	    if($bogodiscount){
                    	        //return $bogodiscount;
                    	        if($value->bogo_discount_type == 2){
                    	            //return 'multe';
                    	            $bogodiscountproductsData = $bogodiscount->productData->where('status',1)->where('quantity','>=',$bogodiscount->quantity);
                    	            foreach($bogodiscountproductsData as $bogodiscountproducts){
                    	                $brand = $bogodiscountproducts->brand()->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')->first(['id','name','name_arabic','slug','status','brand_image_media']);
                            	        $price = 0;
                            	        if($bogodiscount->discount_depend == 2){
                            	            $price = $bogodiscount->fixed_amount;
                            	        }
                            	        if($bogodiscount->discount_depend == 3){
                            	            $pamount = ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price;
                            	            $amount = number_format(($bogodiscount->fixed_amount * $pamount) / 100,2);
                            	            if($bogodiscount->max_cap_amount && $bogodiscount->max_cap_amount < $amount){
                            	                $amount = $bogodiscount->max_cap_amount;
                            	            }
                            	            $price =  number_format($pamount - $amount, 2);
                            	        }
                            	        //return $bogodiscountproducts;
                            	        $addProduct = [
                        	                'id' => $bogodiscountproducts->id,
                        	                'sku' => $bogodiscountproducts->sku,
                        	                'name' =>$bogodiscountproducts->name,
                        	                'name_arabic' => $bogodiscountproducts->name_arabic,
                        	                'image' => $bogodiscountproducts->featuredImage->image ? 'https://images.tamkeenstores.com.sa/assets/new-media/'.$bogodiscountproducts->featuredImage->image  : '',
                        	                'price' => ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price,
                        	                'regular_price' => $bogodiscountproducts->price,
                        	                'quantity' => $bogodiscount->quantity,
                        	                'total_quantity' => $bogodiscountproducts->quantity,
                        	                'brand' => $brand,  
                        	                'bogo' => 1,
                        	                'bogo_id' => $value->id,
                        	                'discounted_amount' => $price,
                            	       ];
                            	       $discountdata['bogo'][] = $addProduct;
                    	            }
                    	        }
                    	        else{
                    	            //return 'single';
                        	        //return $bogodiscount;
                        	        if($bogo_status == 0){
                        	            if($bogodiscount->productData->where('status',1)->where('quantity','>=',$bogodiscount->quantity)->isNotEmpty())
                        	                $bogodiscountproducts = $bogodiscount->productData->where('status',1)->where('quantity','>=',$bogodiscount->quantity)->random();
                        	            else
                        	                $bogodiscountproducts = false;
                        	        }
                        	        if($bogo_status == 1){
                        	            $bogodiscountproducts = $bogodiscount->productData->where('status',1)->where('quantity','>=',$bogodiscount->quantity)->sortByDesc('price')->first();
                        	        }
                        	        if($bogo_status == 2){
                        	            $bogodiscountproducts = $bogodiscount->productData->where('status',1)->where('quantity','>=',$bogodiscount->quantity)->sortBy('price')->first();
                        	        }
                        	        if($bogodiscountproducts){
                            	        //return $bogodiscountproducts;
                            	        $brand = $bogodiscountproducts->brand()->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')->first(['id','name','name_arabic','slug','status','brand_image_media']);
                            	        $price = 0;
                            	        if($bogodiscount->discount_depend == 2){
                            	            $price = $bogodiscount->fixed_amount;
                            	        }
                            	        if($bogodiscount->discount_depend == 3){
                            	            $pamount = ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price;
                            	            $amount = number_format(($bogodiscount->fixed_amount * $pamount) / 100,2);
                            	            if($bogodiscount->max_cap_amount && $bogodiscount->max_cap_amount < $amount){
                            	                $amount = $bogodiscount->max_cap_amount;
                            	            }
                            	            $price =  number_format($pamount - $amount, 2);
                            	        }
                            	        //return $bogodiscountproducts;
                            	        $addProduct = [
                        	                'id' => $bogodiscountproducts->id,
                        	                'sku' => $bogodiscountproducts->sku,
                        	                'name' =>$bogodiscountproducts->name,
                        	                'name_arabic' => $bogodiscountproducts->name_arabic,
                        	                'image' => $bogodiscountproducts->featuredImage->image ? 'https://images.tamkeenstores.com.sa/assets/new-media/'.$bogodiscountproducts->featuredImage->image  : '',
                        	                'price' => ($bogodiscountproducts->sale_price) ? $bogodiscountproducts->sale_price : $bogodiscountproducts->price,
                        	                'regular_price' => $bogodiscountproducts->price,
                        	                'quantity' => $bogodiscount->quantity,
                        	                'total_quantity' => $bogodiscountproducts->quantity,
                        	                'brand' => $brand,  
                        	                'bogo' => 1,
                        	                'bogo_id' => $value->id,
                        	                'discounted_amount' => $price,
                            	       ];
                            	       $discountdata['bogo'][] = $addProduct;
                        	        }
                    	        }
                    	    }
                	    }
            	    }
            	    
            	   // if($data['userid'] == '181775'){
                //         print_r($discountdata);die;
                //     }
        	    }
        	}
        	}
        }
        
         
        return $discountdata;
    }
    
    static function recursiveBogo($qty,$min,$max,$diffrence,$bogoqty,$mainbogoqty){
        $min = $max + 1;
        $max = $min + $diffrence;
        $bogoqty = $bogoqty + $mainbogoqty;
        if($min <= $qty && $max >= $qty){
            return $bogoqty;
        }
        elseif($max < $qty){
            return DiscountRulesHelper::recursiveBogo($qty,$min,$max,$diffrence,$bogoqty,$mainbogoqty);
        }
        
        return false;
    }
    
    static function checkConditions($filters,$conditions,$conditiontype,$userData,$cityData){
       
        // $userData = false;
        // $cityData = false;
        // if($filters['userid']){
        //     $userData = User::with('OrdersData')->where('id',$filters['userid'])->first();
        // }
        
        // if($filters['city']){
        //     $cityData = States::where('name',$filters['city'])->orWhere('name_arabic',$filters['city'])->first();
        // }
        // return $filters;die;
        $conditionmatch = 0;
        foreach ($conditions as $key => $condition) {
            if($condition->condition_type == 1){
                // brands
                $brandsin = $condition->brandsData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    //return $brandsin->toArray();
                    $bad_keys=array_keys(array_diff($filters['productids'],$brandsin->toArray()));
                    //return [$brandsin->toArray(), $bad_keys, $filters['productids'],array_diff_key($filters['productids'],$brandsin->toArray())];
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qtyarray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    $lowprice = false;
                    $add = true;
                    foreach($filters['productprice'] as $pkey => $pvalue){
                        if($lowprice){
                            if($lowprice > $pvalue)
                                $lowprice = $pvalue;
                        }
                        else{
                            $lowprice = $pvalue;
                        }
                    }
                    
                    $finalprice = 0;
                    foreach($pricearray as $plkey => $plvalue){
                        // if($lowprice != $plvalue)
                            $finalprice += ($plvalue * $qtyarray[$plkey]);
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
                        
                    if($condition->min_amount && $condition->min_amount > $finalprice)
                        $add = false;
                        
                    if($condition->max_amount && $condition->max_amount < $finalprice)
                        $add = false;
                    // if($condition->rule_id == '263'){
                    //     //return $brandsin->toArray();
                    //     $bad_keys=array_keys(array_diff($filters['productids'],$brandsin->toArray()));
                    //     //return [$brandsin->toArray(), $bad_keys, $filters['productids'],array_diff_key($filters['productids'],$brandsin->toArray())];
                    //     $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    //     $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    //     $qtyarray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     $lowprice = false;
                    //     $add = true;
                    //     foreach($filters['productprice'] as $pkey => $pvalue){
                    //         if($lowprice){
                    //             if($lowprice > $pvalue)
                    //                 $lowprice = $pvalue;
                    //         }
                    //         else{
                    //             $lowprice = $pvalue;
                    //         }
                    //     }
                    //     $finalprice = 0;
                    //     foreach($pricearray as $plkey => $plvalue){
                    //         $finalprice += ($plvalue * $qtyarray[$plkey]);
                    //     }
                    //     if(array_search($lowprice, $pricearray) !== false){
                    //         if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                    //         $add = false;
                    //         if($condition->select_quantity == 2 && $qty > $condition->quantity)
                    //             $add = false;
                    //         if($condition->select_quantity == 3 && $qty != $condition->quantity)
                    //             $add = false;
                    //         if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                    //             $add = false;
                    //         if($condition->select_quantity == 5 && $qty < $condition->quantity)
                    //             $add = false;
                                
                    //         if($condition->min_amount && $condition->min_amount > $finalprice)
                    //             $add = false;
                                
                    //         if($condition->max_amount && $condition->max_amount < $finalprice)
                    //             $add = false;
                    //     }
                    //     else{
                    //         $add = false;
                    //     }
                        
                        
                    //     //return [$bad_keys,$price,$qty,$filters['productprice'],$lowprice,$pricearray];
                        
                    // }
                    // // elseif($condition->rule_id == '264'){
                    // //     //return $brandsin->toArray();
                    // //     $bad_keys=array_keys(array_diff($filters['productids'],$brandsin->toArray()));
                    // //     //return [$brandsin->toArray(), $bad_keys, $filters['productids'],array_diff_key($filters['productids'],$brandsin->toArray())];
                    // //     $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    // //     $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    // //     // $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    // //     $qty = sizeof($brandsin->toArray());
                    // //     $lowprice = false;
                    // //     $add = true;
                    // //     foreach($filters['productprice'] as $pkey => $pvalue){
                    // //         if($lowprice){
                    // //             if($lowprice > $pvalue)
                    // //                 $lowprice = $pvalue;
                    // //         }
                    // //         else{
                    // //             $lowprice = $pvalue;
                    // //         }
                    // //     }
                    // //     if(array_search($lowprice, $pricearray) !== false){
                    // //         if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                    // //         $add = false;
                    // //         if($condition->select_quantity == 2 && $qty > $condition->quantity)
                    // //             $add = false;
                    // //         if($condition->select_quantity == 3 && $qty != $condition->quantity)
                    // //             $add = false;
                    // //         if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                    // //             $add = false;
                    // //         if($condition->select_quantity == 5 && $qty < $condition->quantity)
                    // //             $add = false;
                                
                    // //         if($condition->min_amount && $condition->min_amount > $price)
                    // //             $add = false;
                                
                    // //         if($condition->max_amount && $condition->max_amount < $price)
                    // //             $add = false;
                    // //     }
                    // //     else{
                    // //         $add = false;
                    // //     }
                    // //     // return [$bad_keys,$price,$qty,$filters['productprice'],$lowprice,$pricearray,$add];
                    // // }
                    // else{
                    //     // $bad_keys=array_keys(array_diff_key($filters['productids'],$brandsin->toArray()));
                    //     // $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    //     // $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                        
                    //     // $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     // $qtyarray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     // $add = true;
                        
                    //     // if($condition->rule_id == '204'){
                    //     //     $priceArray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     //     $qtyArray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                            
                    //     //     $priceArrayAmount = 0;
                    //     //     if(sizeof($priceArray) > 0){
                    //     //         foreach($priceArray as $k => $priceData){
                    //     //             $priceArrayAmount += $priceData*$qtyArray[$k];
                    //     //         }
                                
                    //     //     }
                    //     //     $price = $priceArrayAmount;
                            
                    //     // }
                        
                    //     // $finalprice = 0;
                    //     // foreach($pricearray as $plkey => $plvalue){
                    //     //     $finalprice += ($plvalue * $qtyarray[$plkey]);
                    //     // }
                        
                        
                    //     //return $brandsin->toArray();
                    //     $bad_keys=array_keys(array_diff($filters['productids'],$brandsin->toArray()));
                    //     //return [$brandsin->toArray(), $bad_keys, $filters['productids'],array_diff_key($filters['productids'],$brandsin->toArray())];
                    //     $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    //     $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    //     $qtyarray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    //     $lowprice = false;
                    //     $add = true;
                    //     foreach($filters['productprice'] as $pkey => $pvalue){
                    //         if($lowprice){
                    //             if($lowprice > $pvalue)
                    //                 $lowprice = $pvalue;
                    //         }
                    //         else{
                    //             $lowprice = $pvalue;
                    //         }
                    //     }
                    //     $finalprice = 0;
                    //     foreach($pricearray as $plkey => $plvalue){
                    //         $finalprice += ($plvalue * $qtyarray[$plkey]);
                    //     }
                        
                    //     if($condition->select_quantity == 1 && $qty >= $condition->quantity)
                    //         $add = false;
                    //     if($condition->select_quantity == 2 && $qty > $condition->quantity)
                    //         $add = false;
                    //     if($condition->select_quantity == 3 && $qty != $condition->quantity)
                    //         $add = false;
                    //     if($condition->select_quantity == 4 && $qty <= $condition->quantity)
                    //         $add = false;
                    //     if($condition->select_quantity == 5 && $qty < $condition->quantity)
                    //         $add = false;
                            
                    //     if($condition->min_amount && $condition->min_amount > $finalprice)
                    //         $add = false;
                            
                    //     if($condition->max_amount && $condition->max_amount < $finalprice)
                    //         $add = false;
                    // }
                    
                    
                    
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
                    $bad_keys=array_keys(array_diff($filters['productids'],$subtagsin->toArray()));
                    $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                    $qtyarray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                    $lowprice = false;
                    $add = true;
                    foreach($filters['productprice'] as $pkey => $pvalue){
                        if($lowprice){
                            if($lowprice > $pvalue)
                                $lowprice = $pvalue;
                        }
                        else{
                            $lowprice = $pvalue;
                        }
                    }
                    
                    $finalprice = 0;
                    foreach($pricearray as $plkey => $plvalue){
                        // if($lowprice != $plvalue)
                            $finalprice += ($plvalue * $qtyarray[$plkey]);
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
                        
                    if($condition->min_amount && $condition->min_amount > $finalprice)
                        $add = false;
                        
                    if($condition->max_amount && $condition->max_amount < $finalprice)
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
                // if($condition->rule_id == 303)
                // echo $condition->rule_id;
                // Categories
                $categoiesin = $condition->categoriesData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id');
                if($condition->select_include_exclude == 1){
                    if(sizeof($categoiesin->toArray()) > 0){
                        $bad_keys=array_keys(array_diff($filters['productids'],$categoiesin->toArray()));
                        
                        $price = array_sum(array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                        $pricearray = array_filter($filters['productprice'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                        $qty = array_sum(array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY));
                        $qtyarray = array_filter($filters['productqty'], function($k) use ($bad_keys) {return !in_array($k, $bad_keys);}, ARRAY_FILTER_USE_KEY);
                        $lowprice = false;
                        $add = true;
                        // if($condition->rule_id == 303 && $filters['userid'] == 171){
                        //     print_r(array_keys(array_diff($filters['productids'], $categoiesin->toArray())));
                        //     // print_r($pricearray);
                        //     // print_r($condition->categoriesData->pluck('productname')->flatten()->whereIn('id', $filters['productids'])->pluck('id'));
                        //     // print_r($condition->categoriesData->pluck('productname')->flatten()->pluck('id'));
                        //     print_r($filters['productids']);
                        //     print_r($bad_keys);
                        //     print_r($categoiesin->toArray());die;
                        // }
                        foreach($filters['productprice'] as $pkey => $pvalue){
                            if($lowprice){
                                if($lowprice > $pvalue)
                                    $lowprice = $pvalue;
                            }
                            else{
                                $lowprice = $pvalue;
                            }
                        }
                        
                        $finalprice = 0;
                        foreach($pricearray as $plkey => $plvalue){
                            // if($lowprice != $plvalue)
                                $finalprice += ($plvalue * $qtyarray[$plkey]);
                        }
                        // if($condition->rule_id == '303' && $filters['userid'] == '171'){
                        //     return $condition->rule_id;
                        // }
                        // return $finalprice;
                        
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
                            
                        if($condition->min_amount && $condition->min_amount > $finalprice)
                            $add = false;
                            
                        if($condition->max_amount && $condition->max_amount < $finalprice)
                            $add = false;
                        
                        
                        if($add)
                            $conditionmatch += 1;
                    }
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
                    if(isset($userData->OrdersData) && $userData->OrdersDataDiscountRule->where('status','>=',0)->where('status','=<',4)->count() == '0'){
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

            // pickup store warehouse
            // if($condition->condition_type == 17){
            //     // print_r($filters);die;
            //     if(isset($filters['store_id'])){
            //         $result = array_search($filters['store_id'], explode(',', $condition->warehouse_id));
                    
            //         if($condition->select_include_exclude == 1 && $result > -1){
            //             $conditionmatch += 1;
            //         }
            //         elseif($condition->select_include_exclude != 1 && ($result === false || $result < 0)){
            //             $conditionmatch += 1;
            //         }
            //     }
            // }
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
        // print_r($conditionmatch);die;
    }
}