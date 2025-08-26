<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Helper\ProductListingHelper;
use App\Models\GeneralSettingProduct;
use App\Models\CategoryProduct;
use App\Models\Productcategory;
use App\Models\PriceAlert;
use App\Models\StockAlert;
use App\Models\Wishlists;
use App\Jobs\ProductViewJob;
use DB;

class ProductController extends Controller
{
    public function ProductDetailPageMobile(Request $request,$slug) {
        $productData = [];
        $product = Product::where('slug',$slug)
        ->select('products.id', 'name', 'name_arabic','description','description_arabic', 'slug', 'price', 'sale_price', 'sku', 'quantity', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar',
        'meta_canonical_en', 'meta_canonical_ar', 'meta_description_en', 'meta_description_ar', 'brands', 'pre_order', 'feature_image', 'products.status', 'related_type', 'no_of_days'
        ,DB::raw('COUNT(product_review.id) as totalrating'),DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'))
        ->addSelect([
            'savetype' => GeneralSettingProduct::selectRaw(DB::raw('discount_type as discount_type'))
        ])
        ->with('brand:id,name,name_arabic,brand_app_image_media', 'brand.BrandMediaAppImage:id,image' ,'featuredImage:id,image',
        'features:id,product_id,feature_en,feature_ar,feature_image_link',
        'gallery:id,product_id,image', 'gallery.galleryImage:id,image',
        'productrelatedbrand:id,name', 'productrelatedcategory:id,name', 'specs:id,product_id,heading_en,heading_ar',
        'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar', 'questions:id,title,question,question_arabic,answer,answer_arabic', 'reviews:id,product_sku,rating,title,review,user_id,created_at',
        'reviews.UserData:id,first_name,last_name,created_at')
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })->groupBy('products.id')->first();
            
        if ($product->related_type == 1) {
            $relatedbrands = $product->productrelatedbrand->values();
            foreach($relatedbrands as $relatedbrand){
                $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                $mobimage = true;
                $productData = ProductListingHelper::productData($filters,false,$mobimage);
                // print_r($relatedbrand->id);die();
            }
        }
        elseif($product->related_type == 0) {
            $relatedcategories = $product->productrelatedcategory->values();
            foreach($relatedcategories as $relatedcat){
                $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                $mobimage = true;
                $productData = ProductListingHelper::productData($filters,false,$mobimage);
            }
        }
        else {
            $productData = null;
        }
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        $mobimage = true;
        $highestviewpros = ProductListingHelper::productData($filterpros,false,$mobimage);
        
        $id = $product->id;
        // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
        // $fbtData = ProductListingHelper::productFBT($id,$city);
        ProductViewJob::dispatch($id);

        // if($product) {
        //     $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
        //     $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        // }   else {
        //     $finalCats = [];
        // }
        
        // product extra data
        $FreeGiftData = ProductListingHelper::productFreeGifts($product->id,$request['city']);
        $fbtData = ProductListingHelper::productFBT($product->id,$request['city']);
        $expressdeliveryData = ProductListingHelper::productExpressDelivery($product->id,$request['city']);
        $mobbadge = true;
        $badgeData = ProductListingHelper::productBadge($product->id,$request['city'],$mobbadge);
        $flashDatapro = ProductListingHelper::productFlashSale($product->id);
                    // print_r($flashData);die;

        // price alert
        $pricealertData = false;
        if($request['user_id'] != '') {
            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($pricealert){
                $pricealertData = true;
            }
        }

        // stock alert
        $stockalertData = false;
        if($request['user_id'] != '') {
            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($stockalert){
                $stockalertData = true;
            }
        }

        // wishlist
        $wishlistDataa = false;
        if($request['user_id'] != '') {
            $wishlists = Wishlists::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($wishlists){
                $wishlistDataa = true;
            }
        }
        
        // Highest View product Extra Data
        $highviewpro_extra_multi_data = [];
        // if(isset($request['city'])) {
            if(isset($highestviewpros['products']) && sizeof($highestviewpros['products'])) {
                $products_id = $highestviewpros['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $highviewpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $highviewpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $highviewpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $highviewpro_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $highviewpro_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $highviewpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $highviewpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        // }
    
        // Related product Extra Data
        $productdata_extra_multi_data = [];
        // if(isset($request['city'])) {
            if(isset($productData['products']) && sizeof($productData['products'])) {
                $products_id = $productData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $productdata_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $productdata_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $productdata_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $productdata_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $productdata_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $productdata_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $productdata_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        // }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'highestviewedpros' => $highestviewpros,
            // 'breadcrumbs' => $finalCats,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
            
            'freegiftdata' => $FreeGiftData,
            'fbtdata' => $fbtData,
            'expressdeliveryData' => $expressdeliveryData,
            'badgeData' => $badgeData,
            'flashData' => $flashDatapro,
            'pricealertData' => $pricealertData,
            'stockalertData' => $stockalertData,
            'wishlistData' => $wishlistDataa,
            'highest_viewpro_extra_data' => $highviewpro_extra_multi_data,
            'productdata_extra_data' => $productdata_extra_multi_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    //regional
    public function ProductDetailPageMobileRegional(Request $request,$slug) {
        $productData = [];
        $product = Product::where('slug',$slug)
        ->select('products.id', 'name', 'name_arabic','description','description_arabic', 'slug', 'price', 'sale_price', 'products.sku', 'stock_data.quantity', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar',
        'meta_canonical_en', 'meta_canonical_ar', 'meta_description_en', 'meta_description_ar', 'brands', 'pre_order', 'feature_image', 'products.status', 'related_type', 'no_of_days'
        ,DB::raw('COUNT(product_review.id) as totalrating'),DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'))
        ->addSelect([
            'savetype' => GeneralSettingProduct::selectRaw(DB::raw('discount_type as discount_type'))
        ])
        ->with('brand:id,name,name_arabic,brand_app_image_media', 'brand.BrandMediaAppImage:id,image' ,'featuredImage:id,image',
        'features:id,product_id,feature_en,feature_ar,feature_image_link',
        'gallery:id,product_id,image', 'gallery.galleryImage:id,image',
        'productrelatedbrand:id,name', 'productrelatedcategory:id,name', 'specs:id,product_id,heading_en,heading_ar',
        'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar', 'questions:id,title,question,question_arabic,answer,answer_arabic', 'reviews:id,product_sku,rating,title,review,user_id,created_at',
        'reviews.UserData:id,first_name,last_name,created_at')
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        // ->leftJoin(DB::raw("(select sku,CASE 
        //                     WHEN SUM(qty) > 10 THEN 10
        //                     WHEN SUM(qty) > 1 THEN SUM(qty)
        //                     ELSE 0
        //                 END AS quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'products.sku');
        // })
        ->leftJoin(DB::raw("(SELECT sku, 
                        CASE 
                            WHEN SUM(qty) > 10 THEN 10
                            WHEN SUM(qty) > 1 THEN SUM(qty)
                            ELSE 0
                        END AS quantity
                    FROM livestock
                    GROUP BY sku) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })
        ->groupBy('products.sku', 'products.id', 'stock_data.quantity')->first();
            
        if ($product->related_type == 1) {
            $relatedbrands = $product->productrelatedbrand->values();
            foreach($relatedbrands as $relatedbrand){
                $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                $mobimage = true;
                $productData = ProductListingHelper::productDataRegional($filters,false,$mobimage);
                // print_r($relatedbrand->id);die();
            }
        }
        elseif($product->related_type == 0) {
            $relatedcategories = $product->productrelatedcategory->values();
            foreach($relatedcategories as $relatedcat){
                $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                $mobimage = true;
                $productData = ProductListingHelper::productDataRegional($filters,false,$mobimage);
            }
        }
        else {
            $productData = null;
        }
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        $mobimage = true;
        $highestviewpros = ProductListingHelper::productDataRegional($filterpros,false,$mobimage);
        
        $id = $product->id;
        // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
        // $fbtData = ProductListingHelper::productFBT($id,$city);
        ProductViewJob::dispatch($id);

        // if($product) {
        //     $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
        //     $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        // }   else {
        //     $finalCats = [];
        // }
        
        // product extra data
        $FreeGiftData = ProductListingHelper::productFreeGiftsRegional($product->id,$request['city']);
        $fbtData = ProductListingHelper::productFBTRegional($product->id,$request['city']);
        $expressdeliveryData = ProductListingHelper::productExpressDelivery($product->id,$request['city']);
        $mobbadge = true;
        $badgeData = ProductListingHelper::productBadge($product->id,$request['city'],$mobbadge);
        $flashDatapro = ProductListingHelper::productFlashSale($product->id);
                    // print_r($flashData);die;

        // price alert
        $pricealertData = false;
        if($request['user_id'] != '') {
            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($pricealert){
                $pricealertData = true;
            }
        }

        // stock alert
        $stockalertData = false;
        if($request['user_id'] != '') {
            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($stockalert){
                $stockalertData = true;
            }
        }

        // wishlist
        $wishlistDataa = false;
        if($request['user_id'] != '') {
            $wishlists = Wishlists::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($wishlists){
                $wishlistDataa = true;
            }
        }
        
        // Highest View product Extra Data
        $highviewpro_extra_multi_data = [];
        // if(isset($request['city'])) {
            if(isset($highestviewpros['products']) && sizeof($highestviewpros['products'])) {
                $products_id = $highestviewpros['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $highviewpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $highviewpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $highviewpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $highviewpro_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $highviewpro_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $highviewpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $highviewpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        // }
    
        // Related product Extra Data
        $productdata_extra_multi_data = [];
        // if(isset($request['city'])) {
            if(isset($productData['products']) && sizeof($productData['products'])) {
                $products_id = $productData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $productdata_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $productdata_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $productdata_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $productdata_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $productdata_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $productdata_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $productdata_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        // }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'highestviewedpros' => $highestviewpros,
            // 'breadcrumbs' => $finalCats,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
            
            'freegiftdata' => $FreeGiftData,
            'fbtdata' => $fbtData,
            'expressdeliveryData' => $expressdeliveryData,
            'badgeData' => $badgeData,
            'flashData' => $flashDatapro,
            'pricealertData' => $pricealertData,
            'stockalertData' => $stockalertData,
            'wishlistData' => $wishlistDataa,
            'highest_viewpro_extra_data' => $highviewpro_extra_multi_data,
            'productdata_extra_data' => $productdata_extra_multi_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
     public function ProductDetailPageMobileRegionalNew(Request $request,$slug, $city) {
        $productData = [];
        $product = Product::where('slug',$slug)
        ->select('products.id', 'name', 'name_arabic','description','description_arabic', 'slug', 'price', 'sale_price', 'products.sku', 'stock_data.quantity', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar',
        'meta_canonical_en', 'meta_canonical_ar', 'meta_description_en', 'meta_description_ar', 'brands', 'pre_order', 'feature_image', 'products.status', 'related_type', 'no_of_days'
        ,DB::raw('COUNT(product_review.id) as totalrating'),DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'))
        ->addSelect([
            'savetype' => GeneralSettingProduct::selectRaw(DB::raw('discount_type as discount_type'))
        ])
        ->with('brand:id,name,name_arabic,brand_app_image_media', 'brand.BrandMediaAppImage:id,image' ,'featuredImage:id,image',
        'features:id,product_id,feature_en,feature_ar,feature_image_link',
        'gallery:id,product_id,image', 'gallery.galleryImage:id,image',
        'productrelatedbrand:id,name', 'productrelatedcategory:id,name', 'specs:id,product_id,heading_en,heading_ar',
        'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar', 'questions:id,title,question,question_arabic,answer,answer_arabic', 'reviews:id,product_sku,rating,title,review,user_id,created_at',
        'reviews.UserData:id,first_name,last_name,created_at')
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        // ->leftJoin(DB::raw("(select sku,CASE 
        //                     WHEN SUM(qty) > 10 THEN 10
        //                     WHEN SUM(qty) > 1 THEN SUM(qty)
        //                     ELSE 0
        //                 END AS quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'products.sku');
        // })
        // ->leftJoin(DB::raw("(SELECT sku, 
        //                 CASE 
        //                     WHEN SUM(qty) > 10 THEN 10
        //                     WHEN SUM(qty) > 1 THEN SUM(qty)
        //                     ELSE 0
        //                 END AS quantity
        //             FROM livestock
        //             GROUP BY sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'products.sku');
        // })
        ->leftJoin(DB::raw("(SELECT summed_data.sku, 
                                CASE 
                                    WHEN summed_data.final_qty > 10 THEN 10
                                    WHEN summed_data.final_qty > 0 THEN summed_data.final_qty
                                    ELSE 0
                                END AS quantity
                             FROM (
                                 SELECT 
                                    livestock.sku, 
                                    SUM(CASE WHEN warehouse.ln_code NOT IN ('OLN1', 'KUW101') THEN livestock.qty ELSE 0 END) - 3 
                                    + SUM(CASE WHEN warehouse.ln_code IN ('OLN1', 'KUW101') THEN livestock.qty ELSE 0 END) 
                                    AS final_qty
                                 FROM livestock
                                 INNER JOIN warehouse 
                                 ON livestock.city = warehouse.ln_code
                                 WHERE warehouse.status = 1 
                                 AND warehouse.show_in_express = 1
                                 GROUP BY livestock.sku
                             ) AS summed_data) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })
        ->groupBy('products.sku', 'products.id', 'stock_data.quantity')
        ->first();
        
        if (isset($product)) {
            if ($city === 'jeddah' || $city === 'جدة' ) {
                // Sum quantities for Jeddah (summing 'KUW101' and 'OLN1')
                $product->quantity = $product->liveStockData->whereIn('city', ['KUW101', 'OLN1'])->sum('qty');
            } else {
                // For other cities (not Jeddah)
                $product->quantity = $product->liveStockData()
                    ->whereHas('warehouseData.cityData', function($query) use ($city) {
                        $query->where('states.name', $city)
                              ->orWhere('states.name_arabic', $city);
                    })
                    ->sum('qty');

                // Ensure the quantity does not go negative
                $customerCityQuantity = $product->quantity - 3;
                $product->quantity = $customerCityQuantity > 0 ? $customerCityQuantity : 0; // Set to 0 if it goes negative

                // $jedh = $product->liveStockData->whereIn('city', ['KUW101', 'OLN1'])->sum('qty');
                $product->quantity += $product->liveStockData->whereIn('city', ['KUW101', 'OLN1'])->sum('qty');
            }

            $product->quantity = $product->quantity >= 10 ? 10 : $product->quantity;
        }
            
        if ($product->related_type == 1) {
            $relatedbrands = $product->productrelatedbrand->values();
            foreach($relatedbrands as $relatedbrand){
                $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                $mobimage = true;
                $productData = ProductListingHelper::productDataRegionalNew($filters,false,$mobimage, $city);
                // print_r($relatedbrand->id);die();
            }
        }
        elseif($product->related_type == 0) {
            $relatedcategories = $product->productrelatedcategory->values();
            foreach($relatedcategories as $relatedcat){
                $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                $mobimage = true;
                $productData = ProductListingHelper::productDataRegionalNew($filters,false,$mobimage, $city);
            }
        }
        else {
            $productData = null;
        }
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        $mobimage = true;
        $highestviewpros = ProductListingHelper::productDataRegionalNew($filterpros,false,$mobimage, $city);
        
        $id = $product->id;
        // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
        // $fbtData = ProductListingHelper::productFBT($id,$city);
        ProductViewJob::dispatch($id);

        // if($product) {
        //     $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
        //     $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        // }   else {
        //     $finalCats = [];
        // }
        
        // product extra data
        $FreeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($product->id,$request['city']);
        $fbtData = ProductListingHelper::productFBTRegionalNew($product->id,$request['city']);
        $expressdeliveryData = ProductListingHelper::productExpressDeliveryNew($product->id,$request['city']);
        $mobbadge = true;
        $badgeData = ProductListingHelper::productBadge($product->id,$request['city'],$mobbadge);
        $flashDatapro = ProductListingHelper::productFlashSaleNew($product->id);
                    // print_r($flashData);die;

        // price alert
        $pricealertData = false;
        if($request['user_id'] != '') {
            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($pricealert){
                $pricealertData = true;
            }
        }

        // stock alert
        $stockalertData = false;
        if($request['user_id'] != '') {
            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($stockalert){
                $stockalertData = true;
            }
        }

        // wishlist
        $wishlistDataa = false;
        if($request['user_id'] != '') {
            $wishlists = Wishlists::where('user_id',$request['user_id'])->where('product_id',$product->id)->first();
            if($wishlists){
                $wishlistDataa = true;
            }
        }
        
        // Highest View product Extra Data
        $highviewpro_extra_multi_data = [];
        // if(isset($request['city'])) {
            if(isset($highestviewpros['products']) && sizeof($highestviewpros['products'])) {
                $products_id = $highestviewpros['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $highviewpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $highviewpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $highviewpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $highviewpro_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $highviewpro_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $highviewpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $highviewpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        // }
    
        // Related product Extra Data
        $productdata_extra_multi_data = [];
        // if(isset($request['city'])) {
            if(isset($productData['products']) && sizeof($productData['products'])) {
                $products_id = $productData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $productdata_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $productdata_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $productdata_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $productdata_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $productdata_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $productdata_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $productdata_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        // }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'highestviewedpros' => $highestviewpros,
            // 'breadcrumbs' => $finalCats,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
            
            'freegiftdata' => $FreeGiftData,
            'fbtdata' => $fbtData,
            'expressdeliveryData' => $expressdeliveryData,
            'badgeData' => $badgeData,
            'flashData' => $flashDatapro,
            'pricealertData' => $pricealertData,
            'stockalertData' => $stockalertData,
            'wishlistData' => $wishlistDataa,
            'highest_viewpro_extra_data' => $highviewpro_extra_multi_data,
            'productdata_extra_data' => $productdata_extra_multi_data
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