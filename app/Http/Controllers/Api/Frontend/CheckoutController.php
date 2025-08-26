<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\GeneralSetting;
use App\Models\States;
use App\Helper\ProductListingHelper;
use App\Models\Warehouse;
use DB;
class CheckoutController extends Controller
{
    public function recheckdataRegionalNew(Request $request){
        $requestdata = $request->all();
        $checkids = $requestdata['productids'];
        $city = $requestdata['city'];
        $exp = Warehouse::
            whereHas('cityData', function ($query) use ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('states.name', $city)
                      ->orWhere('states.name_arabic', $city);
                });
            })
            ->where('warehouse.status', 1)
            ->where('warehouse.show_in_express', 1)
            ->pluck('ln_code')->toArray();
        $exp[] = 'OLN1';
        $exp[] = 'KUW101';
        $exp = array_map(function($code) {
            return "'" . $code . "'";
        }, $exp);
        // and livestock.city in (".implode(',', $exp).")
        $products = Product::select('products.id', 'price', 'sale_price', 'flash_sale_price', 'flash_sale_expiry', 'promotional_price', 'promo_title_arabic', 'promo_title', 'save_type', 'stock_data.quantity')
        // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'products.sku');
        // })
        ->leftJoin(DB::raw("(select livestock.sku, 
                        CASE 
                            WHEN SUM(livestock.qty) > 10 THEN 10
                            WHEN SUM(livestock.qty) >= 1 THEN SUM(livestock.qty)
                            ELSE 0
                        END AS quantity
                             from livestock 
                             inner join warehouse 
                             on livestock.city = warehouse.ln_code 
                             where warehouse.status = 1 
                             and warehouse.show_in_express = 1
                             and livestock.city in (".implode(',', $exp).")
                             group by livestock.sku) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })
        ->whereIn('products.id', $checkids)->get();
        $data = [];
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        foreach($checkids as $id){
            $data[$id] = array('freegiftData'=>false,'fbtdata'=>false);
            $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$city);
            if($freeGiftData)
                $data[$id]['freegiftData'] = $freeGiftData;
            $fbtData = ProductListingHelper::productFBTRegional($id,$city);
            if($fbtData)
                $data[$id]['fbtdata'] = $fbtData;
        }
        $response = [
            'data' => $products,
            'extraData' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    // New duplicate for local work
    public function recheckdataRegionalNewDuplicate(Request $request) {
        $requestdata = $request->all();
        $checkids = $requestdata['productids'];
        $city = $requestdata['city'];
        
        // Get warehouse codes for the city (existing code remains the same)
        $exp = Warehouse::
            whereHas('cityData', function ($query) use ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('states.name', $city)
                      ->orWhere('states.name_arabic', $city);
                });
            })
            ->where('warehouse.status', 1)
            ->where('warehouse.show_in_express', 1)
            ->pluck('ln_code')->toArray();
        
        $exp[] = 'OLN1';
        $exp[] = 'KUW101';
        $exp = array_map(function($code) {
            return "'" . $code . "'";
        }, $exp);
        
        // Get product data with stock information (existing code remains the same)
        $products = Product::select('products.id', 'price', 'sale_price', 'flash_sale_price', 'flash_sale_expiry', 
                                   'promotional_price', 'promo_title_arabic', 'promo_title', 'save_type', 'stock_data.quantity')
            ->leftJoin(DB::raw("(select livestock.sku, 
                            CASE 
                                WHEN SUM(livestock.qty) > 10 THEN 10
                                WHEN SUM(livestock.qty) >= 1 THEN SUM(livestock.qty)
                                ELSE 0
                            END AS quantity
                                 from livestock 
                                 inner join warehouse 
                                 on livestock.city = warehouse.ln_code 
                                 where warehouse.status = 1 
                                 and warehouse.show_in_express = 1
                                 and livestock.city in (".implode(',', $exp).")
                                 group by livestock.sku) stock_data"), function($join) {
                $join->on('stock_data.sku', '=', 'products.sku');
            })
            ->whereIn('products.id', $checkids)->get();
        
        $data = [];
        foreach($checkids as $id) {
            $data[$id] = [
                'freegiftData' => false,
                'fbtdata' => false
            ];
            
            // 1. Get existing free gift data
            $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id, $city);
            
            // 2. Get product-relation free gifts
            $productRelationFreeGifts = Product::where('id', $id)
                ->with([
                    'multiFreeGiftData:id,product_id,free_gift_sku,free_gift_qty',
                    'multiFreeGiftData.productSkuData:id,sku,name,name_arabic,sale_price,price,brands,slug,pre_order,feature_image,no_of_days',
                    'multiFreeGiftData.productSkuData.featuredImage:id,image',
                    'multiFreeGiftData.productSkuData.brand:id,name,name_arabic'
                ])
                ->first(['id']);
            
            // 3. Combine both free gift types into a single structure
            $combinedFreeGifts = [];
            
            // Add existing free gifts if they exist
            if ($freeGiftData) {
                $combinedFreeGifts['promotional_free_gifts'] = $freeGiftData;
            }
            
            // Add product-relation free gifts if they exist
            if ($productRelationFreeGifts && $productRelationFreeGifts->multiFreeGiftData->isNotEmpty()) {
                $combinedFreeGifts['product_relation_free_gifts'] = $productRelationFreeGifts->multiFreeGiftData->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'free_gift_sku' => $item->free_gift_sku,
                        'free_gift_qty' => $item->free_gift_qty,
                        'product_sku_data' => $item->productSkuData
                    ];
                })->toArray();
            }
            
            // Only set freegiftData if we have any free gifts
            if (!empty($combinedFreeGifts)) {
                $data[$id]['freegiftData'] = $combinedFreeGifts;
            }
            
            // Existing FBT check
            $fbtData = ProductListingHelper::productFBTRegional($id, $city);
            if($fbtData) {
                $data[$id]['fbtdata'] = $fbtData;
            }
        }
        
        $response = [
            'data' => $products,
            'extraData' => $data
        ];
        
        $responsejson = json_encode($response);
        $data = gzencode($responsejson, 9);
        
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function recheckdataRegional(Request $request){
        $requestdata = $request->all();
        $checkids = $requestdata['productids'];
        $city = $requestdata['city'];
        $exp = Warehouse::
            whereHas('cityData', function ($query) use ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('states.name', $city)
                      ->orWhere('states.name_arabic', $city);
                });
            })
            ->where('warehouse.status', 1)
            ->where('warehouse.show_in_express', 1)
            ->pluck('ln_code')->toArray();
        $exp[] = 'OLN1';
        $exp[] = 'KUW101';
        $exp = array_map(function($code) {
            return "'" . $code . "'";
        }, $exp);
        // and livestock.city in (".implode(',', $exp).")
        $products = Product::select('products.id', 'price', 'sale_price', 'stock_data.quantity')
        // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'products.sku');
        // })
        ->leftJoin(DB::raw("(select livestock.sku, 
                        CASE 
                            WHEN SUM(livestock.qty) > 10 THEN 10
                            WHEN SUM(livestock.qty) >= 1 THEN SUM(livestock.qty)
                            ELSE 0
                        END AS quantity
                             from livestock 
                             inner join warehouse 
                             on livestock.city = warehouse.ln_code 
                             where warehouse.status = 1 
                             and warehouse.show_in_express = 1
                             and livestock.city in (".implode(',', $exp).")
                             group by livestock.sku) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })
        ->whereIn('products.id', $checkids)->get();
        $data = [];
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        foreach($checkids as $id){
            $data[$id] = array('freegiftData'=>false,'fbtdata'=>false);
            $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$city);
            if($freeGiftData)
                $data[$id]['freegiftData'] = $freeGiftData;
            $fbtData = ProductListingHelper::productFBTRegionalNew($id,$city);
            if($fbtData)
                $data[$id]['fbtdata'] = $fbtData;
        }
        $response = [
            'data' => $products,
            'extraData' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function recheckdata(Request $request){
        $requestdata = $request->all();
        $checkids = $requestdata['productids'];
        $products = Product::select('id', 'price', 'sale_price', 'quantity')->whereIn('id', $checkids)->get();
        $data = [];
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        foreach($checkids as $id){
            $data[$id] = array('freegiftData'=>false,'fbtdata'=>false);
            $freeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            if($freeGiftData)
                $data[$id]['freegiftData'] = $freeGiftData;
            $fbtData = ProductListingHelper::productFBT($id,$city);
            if($fbtData)
                $data[$id]['fbtdata'] = $fbtData;
        }
        $response = [
            'data' => $products,
            'extraData' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function checkPaymentMethod(Request $request) {
        $requestdata = $request->all();
        $filterids = $requestdata['productids'];
        $orderAmount = $requestdata['orderamount'];
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        $cityData = false;
        if($city)
            $cityData = States::where('name',$city)->orWhere('name_arabic',$city)->first();
        $data = array('hyperpay_status'=> false,'applepay_status'=> false,'tasheel_status'=>false,'tabby_status'=>false,'tamara_status'=>false,'cod_status'=>false,'madfu_status'=>false,'mispay_status'=>false);
        $GeneralSetting = GeneralSetting::with('paymentsetting.hyperpaybrandsData.productname',
        'paymentsetting.hyperpaysubtagsData.tagProducts',
        'paymentsetting.hyperpayproductData',
        'paymentsetting.hyperpaycategoriesData.productname',
        'paymentsetting.applepaybrandsData.productname',
        'paymentsetting.applepaysubtagsData.tagProducts',
        'paymentsetting.applepayproductData',
        'paymentsetting.applepaycategoriesData.productname',
        'paymentsetting.tasheelbrandsData.productname',
        'paymentsetting.tasheelsubtagsData.tagProducts',
        'paymentsetting.tasheelproductData',
        'paymentsetting.tasheelcategoriesData.productname',
        'paymentsetting.tabbybrandsData.productname',
        'paymentsetting.tabbysubtagsData.tagProducts',
        'paymentsetting.tabbyproductData',
        'paymentsetting.tabbycategoriesData.productname',
        'paymentsetting.tamarabrandsData.productname',
        'paymentsetting.tamarasubtagsData.tagProducts',
        'paymentsetting.tamaraproductData',
        'paymentsetting.tamaracategoriesData.productname',
        'paymentsetting.codbrandsData.productname',
        'paymentsetting.codsubtagsData.tagProducts',
        'paymentsetting.codcategoriesData.productname',
        'paymentsetting.codcityData',
        'paymentsetting.madfubrandsData.productname',
        'paymentsetting.madfusubtagsData.tagProducts',
        'paymentsetting.madfuproductData',
        'paymentsetting.madfucategoriesData.productname',
        'paymentsetting.mispaybrandsData.productname',
        'paymentsetting.mispaysubtagsData.tagProducts',
        'paymentsetting.mispayproductData',
        'paymentsetting.mispaycategoriesData.productname',
        'paymentsetting.clickpaybrandsData.productname',
        'paymentsetting.clickpaysubtagsData.tagProducts',
        'paymentsetting.clickpayproductData',
        'paymentsetting.clickpaycategoriesData.productname',
        'paymentsetting.clickpayApplepaybrandsData.productname',
        'paymentsetting.clickpayApplepaysubtagsData.tagProducts',
        'paymentsetting.clickApplepayproductData',
        'paymentsetting.clickpayApplepaycategoriesData.productname')->first();
        
        // Hyperpay 
        if($GeneralSetting->paymentsetting->hyperpay_status == 1){
            // Hyperpay Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->hyperpay_min_value == null && $GeneralSetting->paymentsetting->hyperpay_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->hyperpay_min_value && $GeneralSetting->paymentsetting->hyperpay_min_value >= $orderAmount)
                $conditionmatch += 1;
            // Hyperpay Max Value Condition
            if($GeneralSetting->paymentsetting->hyperpay_max_value && $GeneralSetting->paymentsetting->hyperpay_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // Hyperpay Brand Condition    
            if($GeneralSetting->paymentsetting->hyperpay_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->hyperpaybrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // Hyperpay Product Condition
            if($GeneralSetting->paymentsetting->hyperpay_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->hyperpayproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // Hyperpay SubTags Condition
            if($GeneralSetting->paymentsetting->hyperpay_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->hyperpaysubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // Hyperpay SubTags Condition
            if($GeneralSetting->paymentsetting->hyperpay_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->hyperpaycategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['hyperpay_status'] = true;
            }
            
        }
        
        
        // Applepay
        if($GeneralSetting->paymentsetting->applepay_status == 1){
            // applepay Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->applepay_min_value == null && $GeneralSetting->paymentsetting->applepay_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->applepay_min_value && $GeneralSetting->paymentsetting->applepay_min_value >= $orderAmount)
                $conditionmatch += 1;
            // applepay Max Value Condition
            if($GeneralSetting->paymentsetting->applepay_max_value && $GeneralSetting->paymentsetting->applepay_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // applepay Brand Condition    
            if($GeneralSetting->paymentsetting->applepay_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->applepaybrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // applepay Product Condition
            if($GeneralSetting->paymentsetting->applepay_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->applepayproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // applepay SubTags Condition
            if($GeneralSetting->paymentsetting->applepay_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->applepaysubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // applepay SubTags Condition
            if($GeneralSetting->paymentsetting->applepay_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->applepaycategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['applepay_status'] = true;
            }
            
        }
        
        // Tasheel
        if($GeneralSetting->paymentsetting->tasheel_status == 1){
            // tasheel Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->tasheel_min_value == null && $GeneralSetting->paymentsetting->tasheel_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->tasheel_min_value && $GeneralSetting->paymentsetting->tasheel_min_value >= $orderAmount)
                $conditionmatch += 1;
            // tasheel Max Value Condition
            if($GeneralSetting->paymentsetting->tasheel_max_value && $GeneralSetting->paymentsetting->tasheel_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // tasheel Brand Condition    
            if($GeneralSetting->paymentsetting->tasheel_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->tasheelbrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tasheel Product Condition
            if($GeneralSetting->paymentsetting->tasheel_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->tasheelproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tasheel SubTags Condition
            if($GeneralSetting->paymentsetting->tasheel_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->tasheelsubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tasheel SubTags Condition
            if($GeneralSetting->paymentsetting->tasheel_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->tasheelcategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['tasheel_status'] = true;
            }
            
        }
        
        // tabby
        if($GeneralSetting->paymentsetting->tabby_status == 1){
            // tabby Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->tabby_min_value == null && $GeneralSetting->paymentsetting->tabby_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->tabby_min_value && $GeneralSetting->paymentsetting->tabby_min_value >= $orderAmount)
                $conditionmatch += 1;
            // tabby Max Value Condition
            if($GeneralSetting->paymentsetting->tabby_max_value && $GeneralSetting->paymentsetting->tabby_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // tabby Brand Condition    
            if($GeneralSetting->paymentsetting->tabby_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->tabbybrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tabby Product Condition
            if($GeneralSetting->paymentsetting->tabby_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->tabbyproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tabby SubTags Condition
            if($GeneralSetting->paymentsetting->tabby_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->tabbysubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tabby SubTags Condition
            if($GeneralSetting->paymentsetting->tabby_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->tabbycategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['tabby_status'] = true;
            }
            
        }
        
        // tamara
        if($GeneralSetting->paymentsetting->tamara_status == 1){
            // tamara Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->tamara_min_value == null && $GeneralSetting->paymentsetting->tamara_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->tamara_min_value && $GeneralSetting->paymentsetting->tamara_min_value >= $orderAmount)
                $conditionmatch += 1;
            // tamara Max Value Condition
            if($GeneralSetting->paymentsetting->tamara_max_value && $GeneralSetting->paymentsetting->tamara_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // tamara Brand Condition    
            if($GeneralSetting->paymentsetting->tamara_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->tamarabrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tamara Product Condition
            if($GeneralSetting->paymentsetting->tamara_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->tamaraproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tamara SubTags Condition
            if($GeneralSetting->paymentsetting->tamara_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->tamarasubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // tamara SubTags Condition
            if($GeneralSetting->paymentsetting->tamara_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->tamaracategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['tamara_status'] = true;
            }
            
        }
        
        // cod
        if($GeneralSetting->paymentsetting->cod_status == 1){
            // cod Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->cod_min_value == null && $GeneralSetting->paymentsetting->cod_max_value == null)
              $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->cod_min_value && $GeneralSetting->paymentsetting->cod_min_value >= $orderAmount)
                $conditionmatch += 1;
            // cod Max Value Condition
            if($GeneralSetting->paymentsetting->cod_max_value && $GeneralSetting->paymentsetting->cod_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // cod Brand Condition    
            if($GeneralSetting->paymentsetting->cod_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->codbrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // cod Product Condition
            if($GeneralSetting->paymentsetting->cod_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->codproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // cod SubTags Condition
            if($GeneralSetting->paymentsetting->cod_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->codsubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // cod SubTags Condition
            if($GeneralSetting->paymentsetting->cod_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->codcategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            $conditionmatchCityStatus = true;
            if($GeneralSetting->paymentsetting->cod_city_id){
                if($cityData){
                    $ConditionData = $GeneralSetting->paymentsetting->codcityData()->where('id', $cityData->id)->first();
                    if($ConditionData){
                        $conditionmatchCityStatus = true;
                    }else{
                        $conditionmatchCityStatus = false;
                    }
                }
            }
            
            // print_r($cityData);
            // die();
            if($conditionmatch >= 1 && ($conditionmatchStatus && $conditionmatchCityStatus)){
                $data['cod_status'] = true;
            }
            
        }
        
        
        // madfu
        if($GeneralSetting->paymentsetting->madfu_status == 1){
            // madfu Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->madfu_min_value == null && $GeneralSetting->paymentsetting->madfu_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->madfu_min_value && $GeneralSetting->paymentsetting->madfu_min_value >= $orderAmount)
                $conditionmatch += 1;
            // madfu Max Value Condition
            if($GeneralSetting->paymentsetting->madfu_max_value && $GeneralSetting->paymentsetting->madfu_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // madfu Brand Condition    
            if($GeneralSetting->paymentsetting->madfu_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->madfubrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // madfu Product Condition
            if($GeneralSetting->paymentsetting->madfu_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->madfuproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // madfu SubTags Condition
            if($GeneralSetting->paymentsetting->madfu_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->madfusubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // madfu SubTags Condition
            if($GeneralSetting->paymentsetting->madfu_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->madfucategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['madfu_status'] = true;
            }
            
        }
        
        // mispay
        if($GeneralSetting->paymentsetting->mispay_status == 1){
            // mispay Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->mispay_min_value == null && $GeneralSetting->paymentsetting->mispay_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->mispay_min_value && $GeneralSetting->paymentsetting->mispay_min_value >= $orderAmount)
                $conditionmatch += 1;
            // mispay Max Value Condition
            if($GeneralSetting->paymentsetting->mispay_max_value && $GeneralSetting->paymentsetting->mispay_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // mispay Brand Condition    
            if($GeneralSetting->paymentsetting->mispay_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->mispaybrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // mispay Product Condition
            if($GeneralSetting->paymentsetting->mispay_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->mispayproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // mispay SubTags Condition
            if($GeneralSetting->paymentsetting->mispay_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->mispaysubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // mispay SubTags Condition
            if($GeneralSetting->paymentsetting->mispay_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->mispaycategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['mispay_status'] = true;
            }
            
        }
        
        //clickpay
        if($GeneralSetting->paymentsetting->clickpay_status == 1){
            // clickpay Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->clickpay_min_value == null && $GeneralSetting->paymentsetting->clickpay_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->clickpay_min_value && $GeneralSetting->paymentsetting->clickpay_min_value >= $orderAmount)
                $conditionmatch += 1;
            // clickpay Max Value Condition
            if($GeneralSetting->paymentsetting->clickpay_max_value && $GeneralSetting->paymentsetting->clickpay_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // clickpay Brand Condition    
            if($GeneralSetting->paymentsetting->clickpay_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->clickpaybrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // clickpay Product Condition
            if($GeneralSetting->paymentsetting->clickpay_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->clickpayproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // clickpay SubTags Condition
            if($GeneralSetting->paymentsetting->clickpay_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->clickpaysubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // clickpay SubTags Condition
            if($GeneralSetting->paymentsetting->clickpay_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->clickpaycategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['clickpay_status'] = true;
            }
            
        }
        
         //clickpay(applepay)
        if($GeneralSetting->paymentsetting->clickpay_applepay_status == 1){
            // clickpay Min Value Condition
            $conditionmatch = 0;
            $conditionmatchStatus = true;
            if($GeneralSetting->paymentsetting->clickpay_applepay_min_value == null && $GeneralSetting->paymentsetting->clickpay_applepay_max_value == null)
               $conditionmatch += 1; 
            if($GeneralSetting->paymentsetting->clickpay_applepay_min_value && $GeneralSetting->paymentsetting->clickpay_applepay_min_value >= $orderAmount)
                $conditionmatch += 1;
            // clickpay Max Value Condition
            if($GeneralSetting->paymentsetting->clickpay_applepay_max_value && $GeneralSetting->paymentsetting->clickpay_applepay_max_value >= $orderAmount)
                $conditionmatch += 1;
            
            // clickpay Brand Condition    
            if($GeneralSetting->paymentsetting->clickpay_applepay_exclude_type == 1){
                $ConditionData = $GeneralSetting->paymentsetting->clickpayApplepaybrandsData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // clickpay Product Condition
            if($GeneralSetting->paymentsetting->clickpay_applepay_exclude_type == 2){
                $ConditionData = $GeneralSetting->paymentsetting->clickApplepayproductData()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // clickpay SubTags Condition
            if($GeneralSetting->paymentsetting->clickpay_applepay_exclude_type == 3){
                $ConditionData = $GeneralSetting->paymentsetting->clickpayApplepaysubtagsData->pluck('tagProducts')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
            
            // clickpay SubTags Condition
            if($GeneralSetting->paymentsetting->clickpay_applepay_exclude_type == 4){
                $ConditionData = $GeneralSetting->paymentsetting->clickpayApplepaycategoriesData->pluck('productname')->flatten()->whereIn('id', $filterids)->pluck('id')->toArray();
                if(empty($ConditionData)){
                    $conditionmatchStatus = true;
                }else{
                    $conditionmatchStatus = false;
                }
            }
                
            if($conditionmatch >= 1 && $conditionmatchStatus){
                $data['clickpay_applepay_status'] = true;
            }
            
        }
        
        $response = [
            'data' => $data,
            // 'GeneralSettingData' => $GeneralSetting->paymentsetting,
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