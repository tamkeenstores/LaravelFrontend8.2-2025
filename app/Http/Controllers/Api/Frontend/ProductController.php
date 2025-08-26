<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Helper\ProductListingHelper;
use App\Helper\ProductListingHelperNew;
use App\Models\GeneralSettingProduct;
use App\Models\CategoryProduct;
use App\Models\Productcategory;
use App\Models\Wishlists;
use App\Jobs\ProductViewJob;
use App\Models\CacheStores;
use DB;
use App\Models\Warehouse;
use App\Models\LiveStock;
use Illuminate\Support\Facades\Cache;


class ProductController extends Controller
{
    public function ProductDiscountType() {
        $seconds = 86400; // 24 hours cache
        $lang = $request->lang ?? 'ar';
        $cacheKey = "product_disocunt_type_{$lang}"; // Added version
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                $GeneralSettingProduct = GeneralSettingProduct::first(['discount_type']);
                $response = [
                    'data' => $GeneralSettingProduct,
                ];
                 // Cache the complete response
                Cache::put($cacheKey, $response, $seconds);
            } catch (\Exception $e) {
                Log::error("Product Discount Type API Error: " . $e->getMessage());
                $response = [
                    'error' => 'Failed to load product discount type data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
            }
            return response()->json($response, 500);
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    // public function ProductDetailPageRegionalBackupWithCache($slug,$city='jeddah') {
    //     $productData = [];
    //     $seconds = 86400;
    //     // print_r($slug);die;
    //     if(Cache::has('prodetail_'.$slug)){
    //         $product = Cache::get('prodetail_'.$slug);
    //         $newpro = Product::where('slug',$slug)
    //         // ->leftJoin(DB::raw("(select sku,city,sum(qty) as quantity from livestock group by sku, city) stock_data"), function($join) {
    //         //     $join->on('stock_data.sku', '=', 'products.sku');
    //         // })
    //         ->leftJoin(DB::raw("(select livestock.sku, sum(livestock.qty) as quantity 
    //                              from livestock 
    //                              inner join warehouse 
    //                              on livestock.city = warehouse.ln_code 
    //                              where warehouse.status = 1 
    //                              and warehouse.show_in_express = 1 
    //                              group by livestock.sku) stock_data"), function($join) {
    //             $join->on('stock_data.sku', '=', 'products.sku');
    //         })
    //         ->select('slug', 'stock_data.quantity', 'price', 'sale_price')->first();
    //         $product->quantity = $newpro->quantity;
    //         $product->price = $newpro->price;
    //         $product->sale_price = $newpro->sale_price;
    //     }
    //     else{
    //         // $product = 
    //         CacheStores::create([
    //             'key' => 'prodetail_'.$slug,
    //             'type' => 'prodata'
    //         ]);
    //         $product = Cache::remember('prodetail_'.$slug, $seconds, function () use ($slug) {
    //             return Product::where('products.slug', $slug)
    //             ->select(
    //                 'products.id',
    //                 'products.name',
    //                 'products.name_arabic',
    //                 'products.description',
    //                 'products.description_arabic',
    //                 'products.short_description',
    //                 'products.slug',
    //                 'products.price',
    //                 'products.sale_price',
    //                 'products.sku',
    //                 'products.mpn',
    //                 'stock_data.quantity',
    //                 'products.meta_title_en',
    //                 'products.meta_title_ar',
    //                 'products.meta_tag_en',
    //                 'products.meta_tag_ar',
    //                 'products.meta_canonical_en',
    //                 'products.meta_canonical_ar',
    //                 'products.meta_description_en',
    //                 'products.meta_description_ar',
    //                 'products.brands',
    //                 'products.pre_order',
    //                 'products.feature_image',
    //                 'products.status',
    //                 'products.related_type',
    //                 'products.no_of_days',
    //                 'products.warranty',
    //                 'products.discounttypestatus',
    //                 'products.discountcondition',
    //                 'products.discountvalue',
    //                 'products.discountvaluecap',
    //                 'products.pricetypevat',
    //                 DB::raw('COUNT(product_review.id) as totalrating'),
    //                 DB::raw('ROUND(SUM(product_review.rating) / COUNT(product_review.id)) as rating')
    //             )
    //             ->addSelect([
    //                 'savetype' => GeneralSettingProduct::selectRaw('discount_type')
    //             ])
    //             // ->leftJoin(DB::raw("(select sku, city, sum(qty) as quantity from livestock group by sku, city) stock_data"), function($join) {
    //             //     $join->on('stock_data.sku', '=', 'products.sku');
    //             // })
    //             // ->leftJoin('warehouse', function($join) {
    //             //     $join->on('stock_data.city', '=', 'warehouse.ln_code');
    //             // })
    //             // ->where('warehouse.status', '=', 1)
    //             // ->where('warehouse.show_in_express', '=', 1)
    //             ->leftJoin(DB::raw("(select livestock.sku, sum(livestock.qty) as quantity 
    //                                  from livestock 
    //                                  inner join warehouse 
    //                                  on livestock.city = warehouse.ln_code 
    //                                  where warehouse.status = 1 
    //                                  and warehouse.show_in_express = 1 
    //                                  group by livestock.sku) stock_data"), function($join) {
    //                 $join->on('stock_data.sku', '=', 'products.sku');
    //             })
    //             ->with(
    //                 'questions',
    //                 'reviews.UserData',
    //                 'brand:id,name,name_arabic,brand_image_media',
    //                 'brand.BrandMediaImage:id,image',
    //                 'featuredImage:id,image',
    //                 'features:id,product_id,feature_en,feature_ar,feature_image_link',
    //                 'productcategory:id,name,name_arabic',
    //                 'gallery:id,product_id,image',
    //                 'gallery.galleryImage:id,image',
    //                 'productrelatedbrand:id,name',
    //                 'productrelatedcategory:id,name',
    //                 'specs:id,product_id,heading_en,heading_ar',
    //                 'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar',
    //                 'upsale'
    //             )
    //             ->where('products.status', 1)
    //             ->leftJoin('product_review', function($join) {
    //                 $join->on('product_review.product_sku', '=', 'products.sku')
    //                      ->where('product_review.status', 1);
    //             })
    //             ->groupBy('products.id', 'stock_data.quantity')
    //             ->first();
    //         });
    //     }
    //     $upsaleproductData = [];
    //     if(isset($product) && $product->upsale){
    //         // print_r($product->upsale->values());die();
    //         $upsales = $product->upsale->values();
    //         // print_r($upsales);die();
    //         if(sizeof($upsales) > 0){
    //             $upsalefilters = ['take' => 8, 'page' => 1, 'productbyid' => $upsales->pluck('id')->toArray()];
    //             $upsaleproductData = ProductListingHelper::productDataRegional($upsalefilters);
    //         }
    //             // print_r($upsaleproductData);die();
            
    //     }
    //     if (isset($product) && $product->related_type == 1) {
    //         $relatedbrands = $product->productrelatedbrand->values();
    //         foreach($relatedbrands as $relatedbrand){
    //             $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
    //             $productData = ProductListingHelper::productDataRegional($filters);
    //             // print_r($relatedbrand->id);die();
    //         }
    //     }
    //     elseif(isset($product) && $product->related_type == 0) {
    //         $relatedcategories = $product->productrelatedcategory->values();
    //         foreach($relatedcategories as $relatedcat){
    //             $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
    //             $productData = ProductListingHelper::productDataRegional($filters);
    //         }
    //     }
    //     else {
    //         $productData = null;
    //     }
    //     $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
    //     // $highestviewpros = ProductListingHelper::productDataRegional($filterpros);
        
    //     if($product){
    //         $id = $product->id;
    //         // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
    //         // $fbtData = ProductListingHelper::productFBT($id,$city);
    //         ProductViewJob::dispatch($id);
    //     }

    //     if($product) {
    //         // $breadPro = Product::where('slug', $slug)->first(['id']);
    //         $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
    //         $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
    //     }   
    //     else {
    //         $breadPro = Product::where('slug', $slug)->first(['id']);
    //         $finalCats = '';
    //         if($breadPro) {
    //             $proCats = Categoryproduct::where('product_id', $breadPro->id)->pluck('category_id')->toArray();
    //             $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->orderBy('created_at', 'DESC')->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->first();
    //         }
    //         // $finalCats = [];
    //     }
        
    //     $faqschema = [];
    //     $faqschemaar = [];
        
    //     if(isset($product) && $product->questions){
    //         $faqschemaquestions = $product->questions;
    //         // dd($faqschemaquestions);die();
    //         if(sizeof($faqschemaquestions) > 0){
    //             foreach($faqschemaquestions as $key => $faqschemaData){
    //                 $faqschemaitem = array (
    //                     "@type" => "Question",
    //                     "name" => $faqschemaData->question,
    //                     "acceptedAnswer" => array(
    //                         "@type"=> "Answer",
    //                         "text"=> $faqschemaData->answer,
    //                     ),
    //                 );
    //                 $faqschemaitemar = array (
    //                     "@type" => "Question",
    //                     "name" => $faqschemaData->question_arabic,
    //                     "acceptedAnswer" => array(
    //                         "@type"=> "Answer",
    //                         "text"=> $faqschemaData->answer_arabic,
    //                     ),
    //                 );
    //                 $faqschema[] = $faqschemaitem;
    //                 $faqschemaar[] = $faqschemaitemar;
    //             }
    //         }
    //     }
        
    //     $reviewschema = [];
    //     if(isset($product) && $product->reviews){
            
            
    //         $faqschemareviews = $product->reviews;
    //         // dd($faqschemaquestions);die();
    //         if(sizeof($faqschemareviews) > 0){
    //             foreach($faqschemareviews as $key => $faqschemaData){
    //                 $faqschemaitem = array (
    //                     "@type" => "Review",
    //                     "datePublished" => $faqschemaData->created_at->format('Y-m-d'),
    //                     "reviewBody" => $faqschemaData->title,
    //                     "name" => $faqschemaData->rating > 1 ? "Value purchase" : "Not a happy camper",
    //                     "reviewRating" => array(
    //                         "@type"=> "Rating",
    //                         "bestRating"=> "5",
    //                         "ratingValue"=> $faqschemaData->rating,
    //                         "worstRating"=> "1",
    //                     ),
    //                 );
    //                 $reviewschema[] = $faqschemaitem;
    //             }
    //         }
    //     }
        
    //     $response = [
    //         'data' => $product,
    //         // 'rating' => $rating,
    //         'productdata' => $productData,
    //         'upsaleproductData' => $upsaleproductData,
    //         // 'highestviewedpros' => $highestviewpros,
    //         'breadcrumbs' => $finalCats,
    //         'faqschema' => $faqschema,
    //         'faqschemaar' => $faqschemaar,
    //         'reviewschema' => $reviewschema,
    //         // 'freegiftdata' => $FreeGiftData,
    //         // 'fbtdata' => $fbtData,
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }

    public function ProductDetailPageRegional($slug,$city='Jeddah') {
        $productData = [];
        $product = Product::where('products.slug', $slug)
        ->select(
            'products.id',
            'products.name',
            'products.name_arabic',
            'products.description',
            'products.description_arabic',
            'products.short_description',
            'products.slug',
            'products.custom_badge_en',
            'products.custom_badge_ar',
            'products.price',
            'products.sale_price',
            'products.sku',
            'products.mpn',
            'stock_data.quantity',
            'products.meta_title_en',
            'products.meta_title_ar',
            'products.meta_tag_en',
            'products.meta_tag_ar',
            'products.meta_canonical_en',
            'products.meta_canonical_ar',
            'products.meta_description_en',
            'products.meta_description_ar',
            'products.brands',
            'products.pre_order',
            'products.feature_image',
            'products.status',
            'products.related_type',
            'products.no_of_days',
            'products.warranty',
            'products.discounttypestatus',
            'products.discountcondition',
            'products.discountvalue',
            'products.discountvaluecap',
            'products.pricetypevat',
            'products.vatonuspromo',
            'products.promotional_price',
            'products.promo_title_arabic',
            'products.promo_title',
            'products.product_video',
            DB::raw('COUNT(product_review.id) as totalrating'),
            DB::raw('ROUND(SUM(product_review.rating) / COUNT(product_review.id)) as rating')
        )
        ->addSelect([
            'savetype' => GeneralSettingProduct::selectRaw('discount_type')
        ])
        // ->leftJoin(DB::raw("(select livestock.sku, sum(livestock.qty) as quantity 
        //                      from livestock 
        //                      inner join warehouse 
        //                      on livestock.city = warehouse.ln_code 
        //                      where warehouse.status = 1 
        //                      and warehouse.show_in_express = 1 
        //                      group by livestock.sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'products.sku');
        // })
        ->leftJoin(DB::raw("(SELECT summed_data.sku, 
                            CASE 
                                WHEN summed_data.total_qty > 10 THEN 10
                                WHEN summed_data.total_qty > 1 THEN summed_data.total_qty
                                ELSE 0
                            END AS quantity
                     FROM (
                         SELECT livestock.sku, 
                                SUM(livestock.qty) AS total_qty
                         FROM livestock
                         INNER JOIN warehouse 
                         ON livestock.city = warehouse.ln_code
                         WHERE warehouse.status = 1 
                         AND warehouse.show_in_express = 1
                         GROUP BY livestock.sku
                     ) AS summed_data) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })

        ->with(
            'questions',
            'reviews.UserData',
            'brand:id,name,name_arabic,brand_image_media',
            'brand.BrandMediaImage:id,image',
            'featuredImage:id,image',
            'features:id,product_id,feature_en,feature_ar,feature_image_link',
            'productcategory:id,name,name_arabic',
            'gallery:id,product_id,image',
            'gallery.galleryImage:id,image',
            'productrelatedbrand:id,name',
            'productrelatedcategory:id,name',
            'specs:id,product_id,heading_en,heading_ar',
            'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar',
            'upsale'
        )
        ->where('products.status', 1)
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku')
                 ->where('product_review.status', 1);
        })
        ->groupBy('products.id', 'stock_data.quantity')
        ->first();
        $upsaleproductData = [];
        if(isset($product) && $product->upsale){
            // print_r($product->upsale->values());die();
            $upsales = $product->upsale->values();
            // print_r($upsales);die();
            if(sizeof($upsales) > 0){
                $upsalefilters = ['take' => 8, 'page' => 1, 'productbyid' => $upsales->pluck('id')->toArray()];
                $upsaleproductData = ProductListingHelper::productDataRegional($upsalefilters);
            }
                // print_r($upsaleproductData);die();
            
        }
        if (isset($product) && $product->related_type == 1) {
            $relatedbrands = $product->productrelatedbrand->values();
            foreach($relatedbrands as $relatedbrand){
                $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                $productData = ProductListingHelper::productDataRegional($filters);
                // print_r($relatedbrand->id);die();
            }
        }
        elseif(isset($product) && $product->related_type == 0) {
            $relatedcategories = $product->productrelatedcategory->values();
            foreach($relatedcategories as $relatedcat){
                $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                $productData = ProductListingHelper::productDataRegional($filters);
            }
        }
        else {
            $productData = null;
        }
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        // $highestviewpros = ProductListingHelper::productDataRegional($filterpros);
        
        if($product){
            $id = $product->id;
            // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            // $fbtData = ProductListingHelper::productFBT($id,$city);
            ProductViewJob::dispatch($id);
        }

        if($product) {
            // $breadPro = Product::where('slug', $slug)->first(['id']);
            $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
            $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        }   
        else {
            $breadPro = Product::where('slug', $slug)->first(['id']);
            $finalCats = '';
            if($breadPro) {
                $proCats = Categoryproduct::where('product_id', $breadPro->id)->pluck('category_id')->toArray();
                $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->orderBy('created_at', 'DESC')->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->first();
            }
            // $finalCats = [];
        }
        
        $faqschema = [];
        $faqschemaar = [];
        
        if(isset($product) && $product->questions){
            $faqschemaquestions = $product->questions;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemaquestions) > 0){
                foreach($faqschemaquestions as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer,
                        ),
                    );
                    $faqschemaitemar = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question_arabic,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer_arabic,
                        ),
                    );
                    $faqschema[] = $faqschemaitem;
                    $faqschemaar[] = $faqschemaitemar;
                }
            }
        }
        
        $reviewschema = [];
        if(isset($product) && $product->reviews){
            
            
            $faqschemareviews = $product->reviews;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemareviews) > 0){
                foreach($faqschemareviews as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Review",
                        "datePublished" => $faqschemaData->created_at->format('Y-m-d'),
                        "reviewBody" => $faqschemaData->title,
                        "name" => $faqschemaData->rating > 1 ? "Value purchase" : "Not a happy camper",
                        "reviewRating" => array(
                            "@type"=> "Rating",
                            "bestRating"=> "5",
                            "ratingValue"=> $faqschemaData->rating,
                            "worstRating"=> "1",
                        ),
                    );
                    $reviewschema[] = $faqschemaitem;
                }
            }
        }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'upsaleproductData' => $upsaleproductData,
            // 'highestviewedpros' => $highestviewpros,
            'breadcrumbs' => $finalCats,
            'faqschema' => $faqschema,
            'faqschemaar' => $faqschemaar,
            'reviewschema' => $reviewschema,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    // this is testing function
    public function ProductDetailPageRegionalNewCopy($slug,$city='Jeddah') {
        $productData = [];
        $seconds = 86400;
        if(Cache::has('prodetail_'.$slug)){
            // print_r('test');die();
            $product = Cache::get('prodetail_'.$slug);
            $proQty = $this->getProductPriceQty($product->sku, $city);
            // print_r($proQty);
            // echo '<br>';
            // print($product->quantity);
            // echo '<br>';
            $prData = Product::where('id', $product->id)->select('price','sale_price', 'id')->first()->toArray();
            $product->quantity = isset($proQty['quantity']) ? $proQty['quantity'] : 0;
            $product->sale_price = isset($prData['sale_price']) ? $prData['sale_price'] : 0;
            $product->price = isset($prData['price']) ? $prData['price'] : 0;
            // print($product->quantity);
            // die;
        }
        else{
            // print_r('test1');die();
            // CacheStores::create([
                // 'key' => 'prodetail_'.$slug,
                // 'type' => 'prodata'
            // ]);
            $product = Cache::remember('prodetail_'.$slug, $seconds, function () use ($slug) {
                return Product::where('products.slug', $slug)
                ->select(
                    'products.id',
                    'products.name',
                    'products.name_arabic',
                    'products.description',
                    'products.description_arabic',
                    'products.short_description',
                    'products.slug',
                    'products.custom_badge_en',
                    'products.custom_badge_ar',
                    'products.price',
                    'products.sale_price',
                    'products.sku',
                    'products.mpn',
                    'stock_data.quantity',
                    'products.meta_title_en',
                    'products.meta_title_ar',
                    'products.meta_tag_en',
                    'products.meta_tag_ar',
                    'products.meta_canonical_en',
                    'products.meta_canonical_ar',
                    'products.meta_description_en',
                    'products.meta_description_ar',
                    'products.brands',
                    'products.pre_order',
                    'products.feature_image',
                    'products.status',
                    'products.related_type',
                    'products.no_of_days',
                    'products.warranty',
                    'products.discounttypestatus',
                    'products.discountcondition',
                    'products.discountvalue',
                    'products.discountvaluecap',
                    'products.pricetypevat',
                    'products.vatonuspromo',
                    'trendyol_price',
                    'promotional_price',
                    'promo_title_arabic',
                    'promo_title',
                    DB::raw('COUNT(product_review.id) as totalrating'),
                    DB::raw('ROUND(SUM(product_review.rating) / COUNT(product_review.id)) as rating')
                )
                ->addSelect([ 
                    'savetype' => GeneralSettingProduct::selectRaw('discount_type') 
                ])
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
                
                ->with(
                    'questions',
                    'reviews.UserData',
                    'brand:id,name,name_arabic,brand_image_media',
                    'brand.BrandMediaImage:id,image',
                    'featuredImage:id,image',
                    'features:id,product_id,feature_en,feature_ar,feature_image_link',
                    'productcategory:id,name,name_arabic',
                    'gallery:id,product_id,image',
                    'gallery.galleryImage:id,image',
                    'productrelatedbrand:id,name',
                    'productrelatedcategory:id,name',
                    'specs:id,product_id,heading_en,heading_ar',
                    'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar',
                    'upsale',
                    'liveStockData.warehouseData.cityData'
                )
                ->where('products.status', 1)
                ->leftJoin('product_review', function($join) {
                    $join->on('product_review.product_sku', '=', 'products.sku')
                         ->where('product_review.status', 1);
                })
                ->groupBy('products.id', 'stock_data.quantity')
                ->first();
            });

            if (isset($product)) {
                if ($city === 'Jeddah' || $city === 'جد' ) {
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
        }


        $upsaleproductData = null;
        $productData = null;
        if(isset($product) && $product->upsale){
            if(Cache::has('prodetail_upsale_'.$slug)){
                $upsaleproductData = Cache::get('prodetail_upsale_'.$slug);
            }
            else {
                $upsales = $product->upsale->values();
                if(sizeof($upsales) > 0){
                    $upsalefilters = ['take' => 8, 'page' => 1, 'productbyid' => $upsales->pluck('id')->toArray()];
                    $upsaleproductData = ProductListingHelper::productDataRegionalNew($upsalefilters, false, false, $city);

                    // CacheStores::create([
                        // 'key' => 'prodetail_upsale_'.$slug,
                        // 'type' => 'prodetail_upsale'
                    // ]);

                    Cache::remember('prodetail_upsale_'.$slug, $seconds, function () use ($upsaleproductData) {
                        return $upsaleproductData;
                    });
                }
            }
        }

        if(Cache::has('prodetail_related_'.$slug)){
            $productData = Cache::get('prodetail_related_'.$slug);
        }
        else {
            if (isset($product) && $product->related_type == 1) {
                $relatedbrands = $product->productrelatedbrand->values();
                foreach($relatedbrands as $relatedbrand){
                    $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                    $productData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
                }
            }
            elseif(isset($product) && $product->related_type == 0) {
                $relatedcategories = $product->productrelatedcategory->values();
                foreach($relatedcategories as $relatedcat){
                    $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                    $productData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
                }
            }
            else {
                $productData = null;
            }

            // CacheStores::create([
                // 'key' => 'prodetail_related_'.$slug,
                // 'type' => 'prodetail_related'
            // ]);

            Cache::remember('prodetail_related_'.$slug, $seconds, function () use ($productData) {
                return $productData;
            });
        }

        
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        // $highestviewpros = ProductListingHelper::productDataRegional($filterpros);
        
        if($product){
            $id = $product->id;
            // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            // $fbtData = ProductListingHelper::productFBT($id,$city);
            ProductViewJob::dispatch($id);
        }

        if($product) {
            // $breadPro = Product::where('slug', $slug)->first(['id']);
            $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
            $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        }   
        else {
            $breadPro = Product::where('slug', $slug)->first(['id']);
            $finalCats = '';
            if($breadPro) {
                $proCats = Categoryproduct::where('product_id', $breadPro->id)->pluck('category_id')->toArray();
                $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->orderBy('created_at', 'DESC')->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->first();
            }
            // $finalCats = [];
        }
        
        $faqschema = [];
        $faqschemaar = [];
        
        if(isset($product) && $product->questions){
            $faqschemaquestions = $product->questions;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemaquestions) > 0){
                foreach($faqschemaquestions as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer,
                        ),
                    );
                    $faqschemaitemar = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question_arabic,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer_arabic,
                        ),
                    );
                    $faqschema[] = $faqschemaitem;
                    $faqschemaar[] = $faqschemaitemar;
                }
            }
        }
        
        $reviewschema = [];
        if(isset($product) && $product->reviews){
            
            
            $faqschemareviews = $product->reviews;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemareviews) > 0){
                foreach($faqschemareviews as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Review",
                        "datePublished" => $faqschemaData->created_at->format('Y-m-d'),
                        "reviewBody" => $faqschemaData->title,
                        "name" => $faqschemaData->rating > 1 ? "Value purchase" : "Not a happy camper",
                        "reviewRating" => array(
                            "@type"=> "Rating",
                            "bestRating"=> "5",
                            "ratingValue"=> $faqschemaData->rating,
                            "worstRating"=> "1",
                        ),
                    );
                    $reviewschema[] = $faqschemaitem;
                }
            }
        }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'upsaleproductData' => $upsaleproductData,
            // 'highestviewedpros' => $highestviewpros,
            'breadcrumbs' => $finalCats,
            'faqschema' => $faqschema,
            'faqschemaar' => $faqschemaar,
            'reviewschema' => $reviewschema,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);    
    }

    // this is live function
    public function ProductDetailPageRegionalNewTesting($slug,$city='Jeddah') {
        $productData = [];
        $seconds = 86400;
        if(Cache::has('prodetail_'.$slug)){
            // print_r('test');die();
            $product = Cache::get('prodetail_'.$slug);
            $proQty = $this->getProductPriceQty($product->sku, $city);
            // print_r($proQty);
            // echo '<br>';
            // print($product->quantity);
            // echo '<br>';
            $prData = Product::where('id', $product->id)->select('price','sale_price', 'id')->first()->toArray();
            $product->quantity = isset($proQty['quantity']) ? $proQty['quantity'] : 0;
            $product->sale_price = isset($prData['sale_price']) ? $prData['sale_price'] : 0;
            $product->price = isset($prData['price']) ? $prData['price'] : 0;
            // print($product->quantity);
            // die;
        }
        else{
            // print_r('test1');die();
            // CacheStores::create([
                // 'key' => 'prodetail_'.$slug,
                // 'type' => 'prodata'
            // ]);
            $product = Cache::remember('prodetail_'.$slug, $seconds, function () use ($slug) {
                return Product::where('products.slug', $slug)
                ->select(
                    'products.id',
                    'products.name',
                    'products.name_arabic',
                    'products.description',
                    'products.description_arabic',
                    'products.short_description',
                    'products.slug',
                    'products.custom_badge_en',
                    'products.custom_badge_ar',
                    'products.price',
                    'products.sale_price',
                    'products.sku',
                    'products.mpn',
                    'stock_data.quantity',
                    'products.meta_title_en',
                    'products.meta_title_ar',
                    'products.meta_tag_en',
                    'products.meta_tag_ar',
                    'products.meta_canonical_en',
                    'products.meta_canonical_ar',
                    'products.meta_description_en',
                    'products.meta_description_ar',
                    'products.brands',
                    'products.pre_order',
                    'products.feature_image',
                    'products.status',
                    'products.related_type',
                    'products.no_of_days',
                    'products.discounttypestatus',
                    'products.discountcondition',
                    'products.discountvalue',
                    'products.discountvaluecap',
                    'products.pricetypevat',
                    // 'products.vatonuspromo',
                    // 'trendyol_price',
                    'promotional_price',
                    'promo_title_arabic',
                    'promo_title',
                    DB::raw('COUNT(product_review.id) as totalrating'),
                    DB::raw('ROUND(SUM(product_review.rating) / COUNT(product_review.id)) as rating'),
                    'products.flash_sale_price', 
                    'products.flash_sale_expiry',
                    'products.save_type',
                    'products.cashback_amount',
                    'products.cashback_title',
                    'products.cashback_title_arabic',
                    'products.eligible_for_pickup'
                )
                ->addSelect([ 
                    'savetype' => GeneralSettingProduct::selectRaw('discount_type') 
                ])
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
                
                ->with(
                    'questions',
                    'reviews.UserData',
                    'brand:id,name,name_arabic,brand_image_media,slug',
                    'brand.BrandMediaImage:id,image',
                    'featuredImage:id,image',
                    'features:id,product_id,feature_en,feature_ar,feature_image_link',
                    'productcategory:id,name,name_arabic',
                    'gallery:id,product_id,image',
                    'gallery.galleryImage:id,image',
                    'productrelatedbrand:id,name',
                    'productrelatedcategory:id,name',
                    'specs:id,product_id,heading_en,heading_ar',
                    'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar',
                    'upsale:id',
                    'multiFreeGiftData:id,product_id,free_gift_sku,free_gift_qty',
                    'multiFreeGiftData.productSkuData:id,sku,name,name_arabic,sale_price,price,brands,slug,pre_order,feature_image,no_of_days',
                    'multiFreeGiftData.productSkuData.featuredImage:id,image',
                    'multiFreeGiftData.productSkuData.brand:id,name,name_arabic'
                    // 'liveStockData.warehouseData.cityData',
                )
                ->where('products.status', 1)
                ->leftJoin('product_review', function($join) {
                    $join->on('product_review.product_sku', '=', 'products.sku')
                         ->where('product_review.status', 1);
                })
                ->groupBy('products.id', 'stock_data.quantity')
                ->first();
            });

            if ($product) {
                // First define your constants
                $jeddahCodes = ['KUW101', 'OLN1'];
                $jeddahNames = ['Jeddah', 'جة'];
    
                if (in_array($city, $jeddahNames)) {
                    // Sum quantities for Jeddah
                    $product->quantity = (int)DB::table('livestock')
                        ->where('ln_sku', $product->sku)
                        ->whereIn('city', $jeddahCodes)
                        ->sum('qty');
                } else {
                    // For other cities
                    $product->quantity = (int)DB::table('livestock as lsd')
                        ->join('warehouse as w', 'lsd.city', '=', 'w.ln_code')
                        ->join('warehouse_city as wc', 'w.id', '=', 'wc.warehouse_id')
                        ->join('states', 'wc.city_id', '=', 'states.id')
                        ->where('lsd.ln_sku', $product->sku)
                        ->where(function($query) use ($city) {
                            $query->where('states.name', $city)
                                  ->orWhere('states.name_arabic', $city);
                        })
                        ->sum('lsd.qty');
    
                    // Apply adjustments
                    $product->quantity = max(0, $product->quantity - 3);
                    
                    // Add Jeddah quantities
                    $product->quantity += (int)DB::table('livestock')
                        ->where('ln_sku', $product->sku)
                        ->whereIn('city', $jeddahCodes)
                        ->sum('qty');
                }
    
                // Apply cap
                $product->quantity = min(10, $product->quantity);
            }
        }


        $upsaleproductData = null;
        $productData = null;
        if(isset($product) && $product->upsale){
            if(Cache::has('prodetail_upsale_'.$slug)){
                $upsaleproductData = Cache::get('prodetail_upsale_'.$slug);
            }
            else {
                $upsales = $product->upsale->values();
                if(sizeof($upsales) > 0){
                    $upsalefilters = ['take' => 8, 'page' => 1, 'productbyid' => $upsales->pluck('id')->toArray()];
                    $upsaleproductData = ProductListingHelperNew::productData($upsalefilters, false, false, $city);

                    // CacheStores::create([
                        // 'key' => 'prodetail_upsale_'.$slug,
                        // 'type' => 'prodetail_upsale'
                    // ]);

                    Cache::remember('prodetail_upsale_'.$slug, $seconds, function () use ($upsaleproductData) {
                        return $upsaleproductData;
                    });
                }
            }
        }

        if(Cache::has('prodetail_related_'.$slug)){
            $productData = Cache::get('prodetail_related_'.$slug);
        }
        else {
            if (isset($product) && $product->related_type == 1) {
                $relatedbrands = $product->productrelatedbrand->values();
                foreach($relatedbrands as $relatedbrand){
                    $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                    $productData = ProductListingHelperNew::productData($filters, false, false, $city);
                }
            }
            elseif(isset($product) && $product->related_type == 0) {
                $relatedcategories = $product->productrelatedcategory->values();
                foreach($relatedcategories as $relatedcat){
                    $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                    $productData = ProductListingHelperNew::productData($filters, false, false, $city);
                }
            }
            else {
                $productData = null;
            }

            // CacheStores::create([
                // 'key' => 'prodetail_related_'.$slug,
                // 'type' => 'prodetail_related'
            // ]);

            Cache::remember('prodetail_related_'.$slug, $seconds, function () use ($productData) {
                return $productData;
            });
        }

        
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        // $highestviewpros = ProductListingHelper::productDataRegional($filterpros);
        
        if($product){
            $id = $product->id;
            // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            // $fbtData = ProductListingHelper::productFBT($id,$city);
            ProductViewJob::dispatch($id);
        }

        if($product) {
            // $breadPro = Product::where('slug', $slug)->first(['id']);
            $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
            $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        }   
        else {
            $breadPro = Product::where('slug', $slug)->first(['id']);
            $finalCats = '';
            if($breadPro) {
                $proCats = Categoryproduct::where('product_id', $breadPro->id)->pluck('category_id')->toArray();
                $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->orderBy('created_at', 'DESC')->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->first();
            }
            // $finalCats = [];
        }
        
        $faqschema = [];
        $faqschemaar = [];
        
        if(isset($product) && $product->questions){
            $faqschemaquestions = $product->questions;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemaquestions) > 0){
                foreach($faqschemaquestions as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer,
                        ),
                    );
                    $faqschemaitemar = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question_arabic,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer_arabic,
                        ),
                    );
                    $faqschema[] = $faqschemaitem;
                    $faqschemaar[] = $faqschemaitemar;
                }
            }
        }
        
        $reviewschema = [];
        if(isset($product) && $product->reviews){
            
            
            $faqschemareviews = $product->reviews;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemareviews) > 0){
                foreach($faqschemareviews as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Review",
                        "datePublished" => $faqschemaData->created_at->format('Y-m-d'),
                        "reviewBody" => $faqschemaData->title,
                        "name" => $faqschemaData->rating > 1 ? "Value purchase" : "Not a happy camper",
                        "reviewRating" => array(
                            "@type"=> "Rating",
                            "bestRating"=> "5",
                            "ratingValue"=> $faqschemaData->rating,
                            "worstRating"=> "1",
                        ),
                    );
                    $reviewschema[] = $faqschemaitem;
                }
            }
        }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'upsaleproductData' => $upsaleproductData,
            // 'highestviewedpros' => $highestviewpros,
            'breadcrumbs' => $finalCats,
            'faqschema' => $faqschema,
            'faqschemaar' => $faqschemaar,
            'reviewschema' => $reviewschema,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);    
    }

    // function for quantity and price
    // public function getProductPriceQty($sku, $city) {
    //     // Fetch product price and sale_price
    //     $productData = LiveStock::join('products as p', 'livestock.ln_sku', '=', 'p.sku')
    //         ->select('p.price', 'p.sale_price')
    //         ->where('p.sku', $sku)
    //         ->first();

    //     $price = $productData->price ?? 0;
    //     $salePrice = $productData->sale_price ?? 0;

    //     // Base query for all city types
    //     $baseQuery = LiveStock::where('ln_sku', $sku);

    //     // Handle Jeddah-specific warehouses
    //     $jeddahQty = $baseQuery->whereIn('city', ['KUW101', 'OLN1'])->sum('qty');
    
    //     if ($city === 'Jeddah' || $city === 'جد') {
    //         return [
    //             'quantity' => min($jeddahQty, 10),
    //             'price' => $price,
    //             'sale_price' => $salePrice,
    //         ];
           
    //     }

    //     // Handle other cities
    //     $cityQty = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
    //         ->join('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
    //         ->join('states as s', 'wc.city_id', '=', 's.id')
    //         ->where('w.status', 1)
    //         ->where('livestock.ln_sku', $sku)
    //         ->where(function ($query) use ($city) {
    //             $query->where('s.name', $city)
    //                   ->orWhere('s.name_arabic', $city);
    //         })
    //         ->sum('livestock.qty');

    //     // Adjust quantity and ensure non-negative result
    //     $adjustedCityQty = min($cityQty - 3, 0);

    //     // Total quantity, limited to max 10
    //     return [
    //         'quantity' => max(($adjustedCityQty + $jeddahQty), 10),
    //         'price' => $price,
    //         'sale_price' => $salePrice,
    //     ];
    // }
    
    
    public function getProductPriceQty($sku, $city) {
        // Fetch product price and sale_price
        $productData = LiveStock::join('products as p', 'livestock.ln_sku', '=', 'p.sku')
            ->select('p.price', 'p.sale_price')
            ->where('p.sku', $sku)
            ->first();
    
        $price = $productData->price ?? 0;
        $salePrice = $productData->sale_price ?? 0;
    
        // Jeddah-specific warehouses
        $jeddahQty = LiveStock::where('ln_sku', $sku)
            ->whereIn('city', ['KUW101', 'OLN1'])
            ->sum('qty');
    
        if ($city === 'Jeddah' || $city === 'جة') {
            return [
                'quantity' => min($jeddahQty, 10),
                'price' => $price,
                'sale_price' => $salePrice,
            ];
        }
    
        // Other cities
        $cityQty = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
            ->join('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
            ->join('states as s', 'wc.city_id', '=', 's.id')
            ->where('w.status', 1)
            ->where('livestock.ln_sku', $sku)
            ->where(function ($query) use ($city) {
                $query->where('s.name', $city)
                      ->orWhere('s.name_arabic', $city);
            })
            ->sum('livestock.qty');
    
        // Corrected logic
        $adjustedCityQty = max($cityQty - 3, 0);
        $totalQty = min($adjustedCityQty + $jeddahQty, 10);
    
        return [
            'quantity' => $totalQty,
            'price' => $price,
            'sale_price' => $salePrice,
        ];
    }

    
    
    public function ProductDetailPageRegionalNew($slug,$city='Jeddah') {
        // print_r($city);die;
        $productData = [];
        $product = Product::where('products.slug', $slug)
            ->select(
                'products.id',
                'products.name',
                'products.name_arabic',
                'products.description',
                'products.description_arabic',
                'products.short_description',
                'products.slug',
                'products.custom_badge_en',
                'products.custom_badge_ar',
                'products.price',
                'products.sale_price',
                'products.sku',
                'products.mpn',
                'stock_data.quantity',
                'products.meta_title_en',
                'products.meta_title_ar',
                'products.meta_tag_en',
                'products.meta_tag_ar',
                'products.meta_canonical_en',
                'products.meta_canonical_ar',
                'products.meta_description_en',
                'products.meta_description_ar',
                'products.brands',
                'products.pre_order',
                'products.feature_image',
                'products.status',
                'products.related_type',
                'products.no_of_days',
                'products.warranty',
                'products.discounttypestatus',
                'products.discountcondition',
                'products.discountvalue',
                'products.discountvaluecap',
                'products.pricetypevat',
                DB::raw('COUNT(product_review.id) as totalrating'),
                DB::raw('ROUND(SUM(product_review.rating) / COUNT(product_review.id)) as rating')
            )
            ->addSelect([ 
                'savetype' => GeneralSettingProduct::selectRaw('discount_type') 
            ])
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
            ->with(
                'questions',
                'reviews.UserData',
                'brand:id,name,name_arabic,brand_image_media',
                'brand.BrandMediaImage:id,image',
                'featuredImage:id,image',
                'features:id,product_id,feature_en,feature_ar,feature_image_link',
                'productcategory:id,name,name_arabic',
                'gallery:id,product_id,image',
                'gallery.galleryImage:id,image',
                'productrelatedbrand:id,name',
                'productrelatedcategory:id,name',
                'specs:id,product_id,heading_en,heading_ar',
                'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar',
                'upsale',
                'liveStockData.warehouseData.cityData'
            )
            ->where('products.status', 1)
            ->leftJoin('product_review', function($join) {
                $join->on('product_review.product_sku', '=', 'products.sku')
                     ->where('product_review.status', 1);
            })
            ->groupBy('products.id', 'stock_data.quantity')
            ->first();


        if (isset($product)) {
            if ($city === 'Jeddah' || $city === 'جدة' ) {
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

        $upsaleproductData = [];
        if(isset($product) && $product->upsale){
            // print_r($product->upsale->values());die();
            $upsales = $product->upsale->values();
            // print_r($upsales);die();
            if(sizeof($upsales) > 0){
                $upsalefilters = ['take' => 8, 'page' => 1, 'productbyid' => $upsales->pluck('id')->toArray()];
                $upsaleproductData = ProductListingHelper::productDataRegionalNew($upsalefilters, false, false, $city);
            }
                // print_r($upsaleproductData);die();
            
        }
        if (isset($product) && $product->related_type == 1) {
            $relatedbrands = $product->productrelatedbrand->values();
            foreach($relatedbrands as $relatedbrand){
                $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                $productData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
                // print_r($relatedbrand->id);die();
            }
        }
        elseif(isset($product) && $product->related_type == 0) {
            $relatedcategories = $product->productrelatedcategory->values();
            foreach($relatedcategories as $relatedcat){
                $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                $productData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }
        }
        else {
            $productData = null;
        }
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        // $highestviewpros = ProductListingHelper::productDataRegional($filterpros);
        
        if($product){
            $id = $product->id;
            // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            // $fbtData = ProductListingHelper::productFBT($id,$city);
            ProductViewJob::dispatch($id);
        }

        if($product) {
            // $breadPro = Product::where('slug', $slug)->first(['id']);
            $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
            $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        }   
        else {
            $breadPro = Product::where('slug', $slug)->first(['id']);
            $finalCats = '';
            if($breadPro) {
                $proCats = Categoryproduct::where('product_id', $breadPro->id)->pluck('category_id')->toArray();
                $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->orderBy('created_at', 'DESC')->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->first();
            }
            // $finalCats = [];
        }
        
        $faqschema = [];
        $faqschemaar = [];
        
        if(isset($product) && $product->questions){
            $faqschemaquestions = $product->questions;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemaquestions) > 0){
                foreach($faqschemaquestions as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer,
                        ),
                    );
                    $faqschemaitemar = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question_arabic,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer_arabic,
                        ),
                    );
                    $faqschema[] = $faqschemaitem;
                    $faqschemaar[] = $faqschemaitemar;
                }
            }
        }
        
        $reviewschema = [];
        if(isset($product) && $product->reviews){
            
            
            $faqschemareviews = $product->reviews;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemareviews) > 0){
                foreach($faqschemareviews as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Review",
                        "datePublished" => $faqschemaData->created_at->format('Y-m-d'),
                        "reviewBody" => $faqschemaData->title,
                        "name" => $faqschemaData->rating > 1 ? "Value purchase" : "Not a happy camper",
                        "reviewRating" => array(
                            "@type"=> "Rating",
                            "bestRating"=> "5",
                            "ratingValue"=> $faqschemaData->rating,
                            "worstRating"=> "1",
                        ),
                    );
                    $reviewschema[] = $faqschemaitem;
                }
            }
        }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'upsaleproductData' => $upsaleproductData,
            // 'highestviewedpros' => $highestviewpros,
            'breadcrumbs' => $finalCats,
            'faqschema' => $faqschema,
            'faqschemaar' => $faqschemaar,
            'reviewschema' => $reviewschema,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);    
    }

    // Helper function to get the quantity for a given city and product
    public function getCityQuantityForProduct($sku, $city) {
        $cityQty = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
            ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
            ->join('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
            ->join('states as s', 'wc.city_id', '=', 's.id')
            ->where('w.status', 1)
            ->where('w.show_in_express', 1)
            ->where('p.sku', $sku)
            ->where(function ($query) use ($city) {
                $query->where('s.name', $city)
                      ->orWhere('s.name_arabic', $city);
            })
            ->select(DB::raw('SUM(livestock.qty) as qty'))
            ->groupBy('livestock.sku')
            ->first();

        return $cityQty ? $cityQty->qty : 0;
    }

    public function ProductDetailPage($slug,$city='jeddah') {
        $productData = [];
        $seconds = 86400;
        if(Cache::has('prodetail_'.$slug)){
            $product = Cache::get('prodetail_'.$slug);
            $newpro = Product::where('slug',$slug)->select('slug', 'quantity', 'price', 'sale_price')->first();
            $product->quantity = $newpro->quantity;
            $product->price = $newpro->price;
            $product->sale_price = $newpro->sale_price;
        }
        else{
            // $product = 
            // CacheStores::create([
                // 'key' => 'prodetail_'.$slug,
                // 'type' => 'prodata'
            // ]);
            $product = Cache::remember('prodetail_'.$slug, $seconds, function () use ($slug) {
                return Product::where('slug',$slug)
                // ->where('products.status',1)
                ->select('products.id', 'name', 'name_arabic','description','description_arabic','short_description', 'slug','custom_badge_en','custom_badge_ar', 'price', 'sale_price', 'sku','mpn','quantity', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar',
                'meta_canonical_en', 'meta_canonical_ar', 'meta_description_en', 'meta_description_ar', 'brands', 'pre_order', 'feature_image', 'products.status', 'related_type', 'no_of_days','warranty','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat'
                ,DB::raw('COUNT(product_review.id) as totalrating'),DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'))
                ->addSelect([
                    'savetype' => GeneralSettingProduct::selectRaw('discount_type')
                ])
                ->with('questions','reviews.UserData','brand:id,name,name_arabic,brand_image_media', 'brand.BrandMediaImage:id,image' ,'featuredImage:id,image',
                'features:id,product_id,feature_en,feature_ar,feature_image_link',
                'productcategory:id,name,name_arabic', 'gallery:id,product_id,image', 'gallery.galleryImage:id,image',
                'productrelatedbrand:id,name', 'productrelatedcategory:id,name', 'specs:id,product_id,heading_en,heading_ar',
                'specs.specdetails:id,specs_id,specs_en,specs_ar,value_en,value_ar','upsale')
                ->where('products.status',1)->leftJoin('product_review', function($join) {
                    $join->on('product_review.product_sku', '=', 'products.sku')->where('product_review.status',1);
                })->groupBy('products.id')->first();;
            });
        }
        $upsaleproductData = [];
        if(isset($product) && $product->upsale){
            // print_r($product->upsale->values());die();
            $upsales = $product->upsale->values();
            // print_r($upsales);die();
            if(sizeof($upsales) > 0){
                $upsalefilters = ['take' => 8, 'page' => 1, 'productbyid' => $upsales->pluck('id')->toArray()];
                $upsaleproductData = ProductListingHelper::productData($upsalefilters);
            }
                // print_r($upsaleproductData);die();
            
        }
        if (isset($product) && $product->related_type == 1) {
            $relatedbrands = $product->productrelatedbrand->values();
            foreach($relatedbrands as $relatedbrand){
                $filters = ['take' => 8, 'page' => 1, 'filter_brand_id' => [$relatedbrand->id]];
                $productData = ProductListingHelper::productData($filters);
                // print_r($relatedbrand->id);die();
            }
        }
        elseif(isset($product) && $product->related_type == 0) {
            $relatedcategories = $product->productrelatedcategory->values();
            foreach($relatedcategories as $relatedcat){
                $filters = ['take' => 8, 'page' => 1, 'filter_cat_id' => [$relatedcat->id]];
                $productData = ProductListingHelper::productData($filters);
            }
        }
        else {
            $productData = null;
        }
        $filterpros = ['take' => 8, 'page' => 1, 'views' => true];
        // $highestviewpros = ProductListingHelper::productData($filterpros);
        
        if($product){
            $id = $product->id;
            // $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            // $fbtData = ProductListingHelper::productFBT($id,$city);
            ProductViewJob::dispatch($id);
        }

        if($product) {
            // $breadPro = Product::where('slug', $slug)->first(['id']);
            $proCats = Categoryproduct::where('product_id', $product->id)->pluck('category_id')->toArray();
            $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->get();   
        }   
        else {
            $breadPro = Product::where('slug', $slug)->first(['id']);
            $finalCats = '';
            if($breadPro) {
                $proCats = Categoryproduct::where('product_id', $breadPro->id)->pluck('category_id')->toArray();
                $finalCats = Productcategory::whereIn('id', $proCats)->where('status', 1)->orderBy('created_at', 'DESC')->where('menu', 1)->select(['name', 'name_arabic', 'slug'])->first();
            }
            // $finalCats = [];
        }
        
        $faqschema = [];
        $faqschemaar = [];
        
        if(isset($product) && $product->questions){
            $faqschemaquestions = $product->questions;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemaquestions) > 0){
                foreach($faqschemaquestions as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer,
                        ),
                    );
                    $faqschemaitemar = array (
                        "@type" => "Question",
                        "name" => $faqschemaData->question_arabic,
                        "acceptedAnswer" => array(
                            "@type"=> "Answer",
                            "text"=> $faqschemaData->answer_arabic,
                        ),
                    );
                    $faqschema[] = $faqschemaitem;
                    $faqschemaar[] = $faqschemaitemar;
                }
            }
        }
        
        $reviewschema = [];
        if(isset($product) && $product->reviews){
            
            
            $faqschemareviews = $product->reviews;
            // dd($faqschemaquestions);die();
            if(sizeof($faqschemareviews) > 0){
                foreach($faqschemareviews as $key => $faqschemaData){
                    $faqschemaitem = array (
                        "@type" => "Review",
                        "datePublished" => $faqschemaData->created_at->format('Y-m-d'),
                        "reviewBody" => $faqschemaData->title,
                        "name" => $faqschemaData->rating > 1 ? "Value purchase" : "Not a happy camper",
                        "reviewRating" => array(
                            "@type"=> "Rating",
                            "bestRating"=> "5",
                            "ratingValue"=> $faqschemaData->rating,
                            "worstRating"=> "1",
                        ),
                    );
                    $reviewschema[] = $faqschemaitem;
                }
            }
        }
        
        $response = [
            'data' => $product,
            // 'rating' => $rating,
            'productdata' => $productData,
            'upsaleproductData' => $upsaleproductData,
            // 'highestviewedpros' => $highestviewpros,
            'breadcrumbs' => $finalCats,
            'faqschema' => $faqschema,
            'faqschemaar' => $faqschemaar,
            'reviewschema' => $reviewschema,
            // 'freegiftdata' => $FreeGiftData,
            // 'fbtdata' => $fbtData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function ProductDetailPageExtraData($id,$city=false) {
        $seconds = 86400;
        if(Cache::has('extradatadetail_'.$id.'_'.$city))
            $response = Cache::get('extradatadetail_'.$id.'_'.$city);
        else{
            $FreeGiftData = ProductListingHelper::productFreeGifts($id,$city);
            $fbtData = ProductListingHelper::productFBT($id,$city);
            $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,$city);
            $webbadge = true;
            $badgeData = ProductListingHelper::productBadge($id,$city,$webbadge);
            $flash = ProductListingHelper::productFlashSale($id);
            
            $response = [
                'freegiftdata' => $FreeGiftData,
                'fbtdata' => $fbtData,
                'expressdeliveryData' => $expressdeliveryData,
                'badgeData' => $badgeData,
                'flash' => $flash
            ];
            // CacheStores::create([
                // 'key' => 'extradatadetail_'.$id.'_'.$city,
                // 'type' => 'extradata'
            // ]);
            Cache::remember('extradatadetail_'.$id.'_'.$city, $seconds, function () use ($response) {
                return $response;
            });
        }
        
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductDetailPageExtraDataRegional($id,$city='Jeddah') {
        $seconds = 86400;
        if($city == null || $city == 'null') {
            $city = 'Jeddah';
        }
        if(Cache::has('extradatadetail_'.$id.'_'.$city))
            $response = Cache::get('extradatadetail_'.$id.'_'.$city);
        else{
            $FreeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$city);
            $fbtData = ProductListingHelper::productFBTRegional($id,$city);
            // $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegional($id,$city);
            $webbadge = true;
            $badgeData = ProductListingHelper::productBadge($id,$city,$webbadge);
            $flash = ProductListingHelper::productFlashSale($id);
            
            $response = [
                'freegiftdata' => $FreeGiftData,
                'fbtdata' => $fbtData,
                // 'expressdeliveryData' => $expressdeliveryData,
                'badgeData' => $badgeData,
                'flash' => $flash
            ];
            // CacheStores::create([
                // 'key' => 'extradatadetail_'.$id.'_'.$city,
                // 'type' => 'extradata'
            // ]);
            Cache::remember('extradatadetail_'.$id.'_'.$city, $seconds, function () use ($response) {
                return $response;
            });
        }
        $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegional($id,$city);
        //showroom
        // $productSku = Product::where('id',$id)->first()->sku;
        // $showroomArray = Warehouse::whereHas('cityData', function ($query) use ($city) {
        //                     $query->where('name', $city)->orWhere('name_arabic', $city);
        //                 })
        //                 ->whereHas('livestockData', function ($query) use ($productSku) {
        //                     $query->where('sku', $productSku)->where('qty', '!=', 0);
        //                 })
        //                 ->with([
        //                     'livestockData:id,sku,qty,city',
        //                     'showroomData:id,name,name_arabic'
        //                 ])
        //                 ->get()
        //                 ->pluck('showroomData');
        // $response = ['expressdeliveryData' => $expressdeliveryData];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
     public function ProductDetailPageExtraDataRegionalNew($id,$city='Jeddah') {
        $seconds = 86400;
        if($city == null || $city == 'null') {
            $city = 'Jeddah';
        }
        if(Cache::has('extradatadetail_'.$id.'_'.$city))
            $response = Cache::get('extradatadetail_'.$id.'_'.$city);
        else{
            $FreeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$city);
            // $fbtData = ProductListingHelper::productFBTRegionalNew($id,$city);
            // $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegional($id,$city);
            $webbadge = true;
            $badgeData = ProductListingHelper::productBadge($id,$city,$webbadge);
            // $flash = ProductListingHelper::productFlashSaleNew($id);
            
            $response = [
                'freegiftdata' => $FreeGiftData,
                'fbtdata' => false,
                // 'expressdeliveryData' => $expressdeliveryData,
                'badgeData' => $badgeData,
                'flash' => false
            ];
            // CacheStores::create([
                // 'key' => 'extradatadetail_'.$id.'_'.$city,
                // 'type' => 'extradata'
            // ]);
            Cache::remember('extradatadetail_'.$id.'_'.$city, $seconds, function () use ($response) {
                return $response;
            });
        }
        
        $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegionalNew($id,$city);
        $response['expressdeliveryData'] = $expressdeliveryData;
        
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    // public function ProductDetailPageExtraDataRegionalCart(Request $request) {
    //     $seconds = 86400;
    //     $id = $request->productids;
    //     $ids = [];
    //     $newdata = false;
    //     $city = $request->city;
    //     foreach ($id as $key => $val) {
    //         $exp = ProductListingHelper::productExpressDeliveryRegional($val,$city);
    //         if($exp == null || $exp == 'null') {
    //             // print_r($ids[$val]);
    //             print_r($exp);
    //         }
            
    //         $ids[$val] = $exp;
    //     }
    //     $responsejson=json_encode($ids);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
    public function ProductDetailPageExtraDataRegionalCart(Request $request) {
        $city = $request->city;
        $ids = [];
        $isExpressAvailable = true;
    
        foreach ($request->productids as $key => $productId) {
            $isExpress = ProductListingHelper::productExpressDeliveryRegional($productId, $city);
            
            if (!$isExpress || ($isExpress && $isExpress->qty < $request->product_qty[$key])) {
                $isExpressAvailable = false;
            }
            $ids[$productId] = $isExpress;
        }
    
        if (!$isExpressAvailable) {
            $ids = array_fill_keys(array_keys($ids), false);
            // $ids = [];
        }
    
        $compressedData = gzencode(json_encode($ids), 9);
        return response($compressedData)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($compressedData),
            'Content-Encoding' => 'gzip',
        ]);
    }
    
    public function ProductDetailPageExtraDataRegionalCartNew($city, Request $request) {
        // $city = $request->city;
        $ids = [];
        $isExpressAvailable = true;
        $city = $city ? $city : 'Jeddah';
    
        foreach ($request->productids as $key => $productId) {
            $isExpress = ProductListingHelper::productExpressDeliveryRegionalNew($productId, $city);
            
            if (!$isExpress || ($isExpress && $isExpress->qty < $request->product_qty[$key])) {
                $isExpressAvailable = false;
            }
            $ids[$productId] = $isExpress;
        }
    
        if (!$isExpressAvailable) {
            $ids = array_fill_keys(array_keys($ids), false);
            // $ids = [];
        }
    
        $compressedData = gzencode(json_encode($ids), 9);
        return response($compressedData)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($compressedData),
            'Content-Encoding' => 'gzip',
        ]);
    }
    
    public function ProductDetailPageExtraDataCartPopup($id, $city = false, $userid = false) {
        // print_r($city);
        // print_r($userid);die;
        $data = [];
        $products_id = explode(',', $id);
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $idd){
                $fbtData = ProductListingHelper::productFBT($idd,$city);
                if($fbtData)
                    $data[$idd]['fbtData'] = $fbtData;
                    
                if($userid != false) {
                    $wishlistdata = Wishlists::
                    where('user_id', $userid)->where('product_id', $idd)
                    ->first();
                    $data[$idd]['wishlistData'] = $wishlistdata;
                }
            }
        }
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductDetailPageExtraDataCartPopuRegional($id, $city = false, $userid = false) {
        // print_r($city);
        // print_r($userid);die;
        $data = [];
        $products_id = explode(',', $id);
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $idd){
                $fbtData = ProductListingHelper::productFBTRegional($idd,$city);
                if($fbtData)
                    $data[$idd]['fbtData'] = $fbtData;
                    
                if($userid != false) {
                    $wishlistdata = Wishlists::
                    where('user_id', $userid)->where('product_id', $idd)
                    ->first();
                    $data[$idd]['wishlistData'] = $wishlistdata;
                }
            }
        }
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductDetailPageExtraDataCartPopuRegionalNew($id, $city = false, $userid = false) {
        // print_r($city);
        // print_r($userid);die;
        $data = [];
        $products_id = explode(',', $id);
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $idd){
                $fbtData = ProductListingHelper::productFBTRegionalNew($idd,$city);
                if($fbtData)
                    $data[$idd]['fbtData'] = $fbtData;
                    
                if($userid != false) {
                    $wishlistdata = Wishlists::
                    where('user_id', $userid)->where('product_id', $idd)
                    ->first();
                    $data[$idd]['wishlistData'] = $wishlistdata;
                }
            }
        }
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductExtraDatamulti($ids,$city=false) {
        $data = [];
        $products_id = explode(',', $ids);
        $seconds = 86400;
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $id){
                
                // Cache::forget('extradata_'.$id.'_'.$city);
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    $data[$id] = array('fbtdata' => false, 'freegiftdata'=>false,'badgeData'=>false, 'flash'=>false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$city);
                    if($freeGiftData)
                        $data[$id]['freegiftData'] = $freeGiftData;
                        
                    $fbtdata = ProductListingHelper::productFBT($id,$city); 
                    if($fbtdata)
                        $data[$id]['fbtdata'] = $fbtdata;
                        
                    $flash = ProductListingHelper::productFlashSale($id);
                    
                    if($flash)
                        $data[$id]['flash'] = $flash;
                    $badgeData = ProductListingHelper::productBadge($id,$city);
                    if($badgeData)
                        $data[$id]['badgeData'] = $badgeData;
                    $edata = $data[$id];
                    // CacheStores::create([
                        // 'key' => 'extradata_'.$id.'_'.$city,
                        // 'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                    
                    
                    
                // $data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flash'=>false);
                
                //Cache::forget('homeslider'.$type);
                // $freeGiftData = Cache::remember('freegift_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //     return ProductListingHelper::productFreeGifts($id,$city);
                // });
                
                
                    
                // $flash = Cache::remember('flash_yes_'.$id, $seconds, function () use ($id) {
                //     return ProductListingHelper::productFlashSale($id);
                // });
                
                // $fbtData = ProductListingHelper::productFBT($id,$city);
                // if($fbtData)
                //     $data[$id]['fbtData'] = $fbtData;
                // $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,$city);
                // if($expressdeliveryData)
                //     $data[$id]['expressdeliveryData'] = $expressdeliveryData;
                //$cbadge = Cache::get('badgeData_yes_'.$id.'_'.$city);
                ///print_r($cbadge);die;
                // if(Cache::has('badgeData_yes_'.$id.'_'.$city))
                //     $badgeData = Cache::get('badgeData_yes_'.$id.'_'.$city);
                // else{
                //     $data[$id]['badgeapi'] = true;
                //     $badgeData = Cache::remember('badgeData_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //         return ProductListingHelper::productBadge($id,$city);
                //     });
                // }
                // $badgeData = Cache::remember('badgeData_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //     return ProductListingHelper::productBadge($id,$city);
                // });
                // $badgeData = Cache::get('badgeData_yes_'.$id.'_'.$city);
                
            }
        }
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductExtraDatamultiRegional($ids,$city=false) {
        $data = [];
        $products_id = explode(',', $ids);
        $seconds = 86400;
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $id){
                
                // Cache::forget('extradata_'.$id.'_'.$city);
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    $data[$id] = array('fbtdata' => false, 'freegiftdata'=>false,'badgeData'=>false, 'flash'=>false, 'expressdeliveryData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$city);
                    if($freeGiftData)
                        $data[$id]['freegiftData'] = $freeGiftData;
                        
                    $fbtdata = ProductListingHelper::productFBTRegional($id,$city); 
                    if($fbtdata)
                        $data[$id]['fbtdata'] = $fbtdata;
                        
                    $flash = ProductListingHelper::productFlashSale($id);
                    
                    if($flash)
                        $data[$id]['flash'] = $flash;
                    $badgeData = ProductListingHelper::productBadge($id,$city);
                    if($badgeData)
                        $data[$id]['badgeData'] = $badgeData;
                    $edata = $data[$id];
                    // CacheStores::create([
                        // 'key' => 'extradata_'.$id.'_'.$city,
                        // 'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                    
                    
                    
                // $data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flash'=>false);
                
                //Cache::forget('homeslider'.$type);
                // $freeGiftData = Cache::remember('freegift_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //     return ProductListingHelper::productFreeGifts($id,$city);
                // });
                
                
                    
                // $flash = Cache::remember('flash_yes_'.$id, $seconds, function () use ($id) {
                //     return ProductListingHelper::productFlashSale($id);
                // });
                
                // $fbtData = ProductListingHelper::productFBT($id,$city);
                // if($fbtData)
                //     $data[$id]['fbtData'] = $fbtData;
                $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegional($id,$city);
                if($expressdeliveryData)
                    $data[$id]['expressdeliveryData'] = $expressdeliveryData;
                //$cbadge = Cache::get('badgeData_yes_'.$id.'_'.$city);
                ///print_r($cbadge);die;
                // if(Cache::has('badgeData_yes_'.$id.'_'.$city))
                //     $badgeData = Cache::get('badgeData_yes_'.$id.'_'.$city);
                // else{
                //     $data[$id]['badgeapi'] = true;
                //     $badgeData = Cache::remember('badgeData_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //         return ProductListingHelper::productBadge($id,$city);
                //     });
                // }
                // $badgeData = Cache::remember('badgeData_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //     return ProductListingHelper::productBadge($id,$city);
                // });
                // $badgeData = Cache::get('badgeData_yes_'.$id.'_'.$city);
                
            }
        }
        
        $response = [
            'data' => $data,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductExtraDatamultiRegionalNewUpdated($ids,$city=false) {
        $data = [];
        $products_id = explode(',', $ids);
        $seconds = 86400;
        
        // $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegionalNewUpdated($products_id,$city);
        if(Cache::has('extradata_express_'.$ids.'_'.$city))
            $expressdeliveryData = Cache::get('extradata_express_'.$ids.'_'.$city);
        else {
            $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegionalNewUpdated($products_id,$city);
        
            // CacheStores::create([
                // 'key' => 'extradata_express_'.$ids.'_'.$city,
                // 'type' => 'extradata'
            // ]);
            Cache::remember('extradata_express_'.$ids.'_'.$city, $seconds, function () use ($expressdeliveryData) {
                return $expressdeliveryData;
            });
        }
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $id){
                
                // Cache::forget('extradata_'.$id.'_'.$city);
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    $data[$id] = array('fbtdata' => false, 'freegiftdata'=>false,'badgeData'=>false, 'flash'=>false, 'expressdeliveryData' => false);
                    // $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$city);
                    // if($freeGiftData)
                    //     $data[$id]['freegiftData'] = $freeGiftData;
                        
                    // $fbtdata = ProductListingHelper::productFBTRegionalNew($id,$city); 
                    // if($fbtdata)
                    //     $data[$id]['fbtdata'] = $fbtdata;
                        
                    // $flash = ProductListingHelper::productFlashSaleNew($id);
                    // if($flash)
                    //     $data[$id]['flash'] = $flash;
                        
                    // $badgeData = ProductListingHelper::productBadge($id,$city);
                    // if($badgeData)
                    //     $data[$id]['badgeData'] = $badgeData;
                    $edata = $data[$id];
                    // CacheStores::create([
                        // 'key' => 'extradata_'.$id.'_'.$city,
                        // 'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                
                // $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegionalNew($id,$city);
                if($expressdeliveryData && isset($expressdeliveryData[$id]))
                    $data[$id]['expressdeliveryData'] = $expressdeliveryData[$id];
            }
        }
        
        $response = [
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
    
     public function ProductExtraDatamultiRegionalNew($ids,$city=false) {
        $data = [];
        $products_id = explode(',', $ids);
        $seconds = 86400;
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $id){
                
                // Cache::forget('extradata_'.$id.'_'.$city);
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    $data[$id] = array('fbtdata' => false, 'freegiftdata'=>false,'badgeData'=>false, 'flash'=>false, 'expressdeliveryData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$city);
                    if($freeGiftData)
                        $data[$id]['freegiftData'] = $freeGiftData;
                        
                    $fbtdata = ProductListingHelper::productFBTRegionalNew($id,$city); 
                    if($fbtdata)
                        $data[$id]['fbtdata'] = $fbtdata;
                        
                    $flash = ProductListingHelper::productFlashSaleNew($id);
                    
                    if($flash)
                        $data[$id]['flash'] = $flash;
                    $badgeData = ProductListingHelper::productBadge($id,$city);
                    if($badgeData)
                        $data[$id]['badgeData'] = $badgeData;
                    $edata = $data[$id];
                    // CacheStores::create([
                        // 'key' => 'extradata_'.$id.'_'.$city,
                        // 'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                    
                    
                    
                // $data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flash'=>false);
                
                //Cache::forget('homeslider'.$type);
                // $freeGiftData = Cache::remember('freegift_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //     return ProductListingHelper::productFreeGifts($id,$city);
                // });
                
                
                    
                // $flash = Cache::remember('flash_yes_'.$id, $seconds, function () use ($id) {
                //     return ProductListingHelper::productFlashSale($id);
                // });
                
                // $fbtData = ProductListingHelper::productFBT($id,$city);
                // if($fbtData)
                //     $data[$id]['fbtData'] = $fbtData;
                $expressdeliveryData = ProductListingHelper::productExpressDeliveryRegionalNew($id,$city);
                if($expressdeliveryData)
                    $data[$id]['expressdeliveryData'] = $expressdeliveryData;
                //$cbadge = Cache::get('badgeData_yes_'.$id.'_'.$city);
                ///print_r($cbadge);die;
                // if(Cache::has('badgeData_yes_'.$id.'_'.$city))
                //     $badgeData = Cache::get('badgeData_yes_'.$id.'_'.$city);
                // else{
                //     $data[$id]['badgeapi'] = true;
                //     $badgeData = Cache::remember('badgeData_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //         return ProductListingHelper::productBadge($id,$city);
                //     });
                // }
                // $badgeData = Cache::remember('badgeData_yes_'.$id.'_'.$city, $seconds, function () use ($id,$city) {
                //     return ProductListingHelper::productBadge($id,$city);
                // });
                // $badgeData = Cache::get('badgeData_yes_'.$id.'_'.$city);
                
            }
        }
        
        $response = [
            'data' => $data,
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