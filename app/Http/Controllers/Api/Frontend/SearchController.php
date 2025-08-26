<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Models\CategoryProduct;
use App\Models\SaveSearch;
use App\Models\GeneralSettingProduct;
use DB;
use App\Helper\ProductListingHelper;
use App\Helper\ProductListingHelperNew;
use App\Models\Wishlists;

use App\Models\CacheStores;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function searchRegional(Request $request)
    {
        $query = $request->input('q');
        $requestdata = $request->all();
        $seconds = 86400;
        
        
        // if(Cache::has('searchpro_'.$query))
        //     $products = Cache::get('searchpro_'.$query);
        // else{
            // CacheStores::create([
            //     'key' => 'searchpro_'.$query,
            //     'type' => 'prodata'
            // ]);
            $products = Cache::remember('searchpro_'.$query, $seconds, function () use ($query) {
                return Product::where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })

                ->where('status', 1)
                ->where('hide_on_frontend', 0)
                ->where('stock_data.quantity', '>=', 1) 
                ->select('id', 'name', 'name_arabic', 'slug', 'price', 'sale_price', 'products.sku', 'meta_tag_en', 'meta_tag_ar', 'status', 'brands', 'feature_image', 'best_seller'
                ,'free_gift', 'low_in_stock', 'stock_data.quantity', 'top_selling', 'created_at')
                ->with('brand:id,name,name_arabic,slug', 'brand.BrandMediaImage:id,image', 'featuredImage:id,image')
                // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
                //     $join->on('stock_data.sku', '=', 'products.sku');
                // })
                ->leftJoin(DB::raw("(SELECT sku, 
                            CASE 
                                WHEN quantity > 10 THEN 10
                                WHEN quantity > 1 THEN quantity
                                ELSE 0
                            END AS quantity
                     FROM (
                         SELECT sku, 
                                SUM(qty) AS quantity
                         FROM livestock
                         GROUP BY sku
                     ) AS stock_data) stock_data"), function($join) {
                    $join->on('stock_data.sku', '=', 'products.sku');
                })
                ->orderByRaw("CASE WHEN brands = '22' THEN 0 ELSE 1 END")
                ->inRandomOrder()
                ->orderBy('sort', 'asc')
                ->limit(10)->get();
            });
        // }
        
        
        if(Cache::has('searchcat_'.$query))
            $cats = Cache::get('searchcat_'.$query);
        else{
            // CacheStores::create([
            //     'key' => 'searchcat_'.$query,
            //     'type' => 'catdata'
            // ]);
            $cats = Cache::remember('searchcat_'.$query, $seconds, function () use ($query) {
                return Productcategory::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('menu', 1)
                ->where('status', 1)
                ->whereNotNull('parent_id')
                ->orderByRaw('-sort DESC')
                // ->orderBy('sort', 'asc')
                ->select('id', 'name', 'name_arabic', 'slug','icon', 'image_link_app', 'sort')
                //->with('BrandMediaImage:id,image')
                //->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }
        
        
        if(Cache::has('searchbrand_'.$query))
            $brands = Cache::get('searchbrand_'.$query);
        else{
            // CacheStores::create([
            //     'key' => 'searchbrand_'.$query,
            //     'type' => 'branddata'
            // ]);
            $brands = Cache::remember('searchbrand_'.$query, $seconds, function () use ($query) {
                return Brand::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('status', 1)
                ->where('show_in_front', 1)
                ->select('id', 'name', 'name_arabic', 'slug','brand_image_media')
                ->with('BrandMediaImage:id,image')
                ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }

        
        
        
        
        
        $productData = $products->pluck('id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        if(isset($productData) && sizeof($productData)) {
            
            foreach($productData as $id){
                $extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false, 'expressdeliveryData' => false);
                $wishlistData = false;
                
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $extra_multi_data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$city);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSale($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$city,$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    
                    $edata = $extra_multi_data[$id];
                    // CacheStores::create([
                    //     'key' => 'extradata_'.$id.'_'.$city,
                    //     'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                
                $express = ProductListingHelper::productExpressDeliveryRegional($id,$city);
                if($express)
                    $extra_multi_data[$id]['expressdeliveryData'] = $express;
                
                
                    
                    
                    
                    
                if(isset($requestdata['user_id'])) {
                    $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    if($wishlist) {
                        $wishlistData = true;
                    }
                }
                $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
            }
        }
        
        
        $response = [
            'products' => $products,
            'cats' => $cats,
            'brands' => $brands,
            'extra_multi_data' => $extra_multi_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function searchRegionalNew(Request $request)
    {
        $query = $request->input('q');
        $requestdata = $request->all();
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        $seconds = 86400;
        $langType = $request->lang ? $request->lang : 'ar';
        $columnName = ($langType == 'en') ? 'products.name' : 'products.name_arabic';
        $columnName2 = ($langType == 'en') ? 'products.meta_tag_en' : 'products.meta_tag_ar';
        $columnName3 = ($langType == 'en') ? 'name' : 'name_arabic';
        
        $columnName4 = ($langType == 'en') ? 'products.badge_left' : 'products.badge_left_arabic';
        $columnName5 = ($langType == 'en') ? 'products.badge_right' : 'products.badge_right_arabic';
        $columnName6 = ($langType == 'en') ? 'products.promo_title' : 'products.promo_title_arabic';
        $columnName7 = ($langType == 'en') ? 'products.custom_badge_en' : 'products.custom_badge_ar';
        
        $cacheKey1 = 'searchpro'.'_'.$query.'_'.$langType;
        $cacheKey2 = 'searchcat'.'_'.$query.'_'.$langType;
        $cacheKey3 = 'searchbrand'.'_'.$query.'_'.$langType;
        
        // if(Cache::has($cacheKey1))
        //     $products = Cache::get('searchpro_'.$query);
        // else{
        //     CacheStores::create([
        //         'key' => $cacheKey1,
        //         'type' => 'prodata'
        //     ]);
            $products = Cache::remember($cacheKey1, $seconds, function () use ($query, $city,$langType, $columnName,$columnName3,$columnName4,$columnName5,$columnName6,$columnName7) {
            return Product::where(function ($queryBuilder) use ($query) {
                return $queryBuilder
                ->where('products.meta_tag_en', 'like', "%$query%")
                ->orWhere('products.meta_tag_ar', 'like', "%$query%");
                
                // $queryBuilder->where('products.name', 'like', "%$query%")
                //     ->orWhere('products.sku', 'like', "%$query%")
                //     ->orWhere('products.name_arabic', 'like', "%$query%");
            })
            ->where('products.status', 1)
            ->where('products.hide_on_frontend', 0)
            ->select(
                'products.id',$columnName,
                'products.slug', 'products.price','products.sale_price', 'products.sku','products.promotional_price',
                'products.pormotion_color','products.badge_left_color','products.badge_right_color',$columnName4,$columnName5,$columnName6,$columnName7,'products.vatonuspromo',
                'products.status', 'products.brands', 'products.feature_image', 'products.best_seller',
                'products.free_gift', 'products.low_in_stock', 'products.top_selling', 'products.created_at',
                DB::raw('IFNULL(stock_data.quantity, 0) as quantity')
            )
            ->addSelect([
                'savetype' => GeneralSettingProduct::selectRaw(DB::raw('discount_type as discount_type'))
            ])
            ->with([
            'brand' => function ($query) use ($langType, $columnName3) {
                $query->select(
                    'id',
                    DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName3"),
                    'slug'
                );
            },
            'featuredImage:id,image'
            ])
            ->leftJoin(DB::raw("(SELECT summed_data.sku, 
                                    CASE 
                                        WHEN summed_data.final_qty > 10 THEN 10
                                        WHEN summed_data.final_qty > 0 THEN summed_data.final_qty
                                        ELSE 0
                                    END AS quantity
                                FROM (
                                    SELECT livestock.sku, 
                                        SUM(
                                            CASE 
                                                WHEN warehouse.ln_code NOT IN ('KUW101', 'OLN1') THEN livestock.qty 
                                                ELSE 0 
                                            END
                                        ) - 3 +
                                        SUM(
                                            CASE 
                                                WHEN warehouse.ln_code IN ('KUW101', 'OLN1') THEN livestock.qty 
                                                ELSE 0 
                                            END
                                        ) AS final_qty
                                    FROM livestock
                                    INNER JOIN warehouse 
                                    ON livestock.city = warehouse.ln_code
                                    WHERE warehouse.status = 1 
                                    AND warehouse.show_in_express = 1
                                    GROUP BY livestock.sku
                                ) AS summed_data) stock_data"), function ($join) {
                $join->on('stock_data.sku', '=', 'products.sku');
            })
            ->when($city === 'jeddah' || $city === '', function ($query) {
                $query->whereRaw('1 = 1'); // Conditional logic placeholder if other filters need to be added for Jeddah.
            })
            ->orderByRaw("CASE WHEN products.brands = '22' THEN 0 ELSE 1 END")
            ->inRandomOrder()
            ->groupBy('products.id', 'stock_data.quantity')
            ->orderBy('sort', 'asc')
            ->limit(10)
            ->get();
        });


        // }
        
        
        if(Cache::has($cacheKey2))
            $cats = Cache::get($cacheKey2);
        else{
            // CacheStores::create([
            //     'key' => $cacheKey2,
            //     'type' => 'catdata'
            // ]);
            $cats = Cache::remember($cacheKey2, $seconds, function () use ($query,$columnName3) {
                return Productcategory::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('menu', 1)
                ->where('status', 1)
                ->whereNotNull('parent_id')
                ->orderByRaw('-sort DESC')
                // ->orderBy('sort', 'asc')
                ->select('id', $columnName3, 'slug','icon', 'image_link_app', 'sort')
                //->with('BrandMediaImage:id,image')
                //->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }
        
        
        if(Cache::has($cacheKey3))
            $brands = Cache::get($cacheKey3);
        else{
            // CacheStores::create([
            //     'key' => $cacheKey3,
            //     'type' => 'branddata'
            // ]);
            $brands = Cache::remember($cacheKey3, $seconds, function () use ($query,$columnName3) {
                return Brand::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('status', 1)
                ->where('show_in_front', 1)
                ->select('id', $columnName3, 'slug')
                ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }
        
        $productData = $products->pluck('id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        if(isset($productData) && sizeof($productData)) {
            
            foreach($productData as $id){
                $extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false, 'expressdeliveryData' => false);
                $wishlistData = false;
                
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $extra_multi_data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$city);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSaleNew($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$city,$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    
                    $edata = $extra_multi_data[$id];
                    // CacheStores::create([
                    //     'key' => 'extradata_'.$id.'_'.$city,
                    //     'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                
                $express = ProductListingHelper::productExpressDeliveryRegionalNew($id,$city);
                if($express)
                    $extra_multi_data[$id]['expressdeliveryData'] = $express;
                
                
                    
                    
                    
                    
                if(isset($requestdata['user_id'])) {
                    $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    if($wishlist) {
                        $wishlistData = true;
                    }
                }
                $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
            }
        }
        
        
        $response = [
            'products' => $products,
            'cats' => $cats,
            'brands' => $brands,
            'extra_multi_data' => $extra_multi_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function searchRegionalNewUpdated(Request $request)
    {
        $query = $request->input('q');
        $requestdata = $request->all();
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        $seconds = 86400;
        $langType = $request->lang ? $request->lang : 'ar';
        $columnName = ($langType == 'en') ? 'products.name' : 'products.name_arabic';
        $columnName2 = ($langType == 'en') ? 'products.meta_tag_en' : 'products.meta_tag_ar';
        $columnName3 = ($langType == 'en') ? 'name' : 'name_arabic';
        
        $columnName4 = ($langType == 'en') ? 'products.badge_left' : 'products.badge_left_arabic';
        $columnName5 = ($langType == 'en') ? 'products.badge_right' : 'products.badge_right_arabic';
        $columnName6 = ($langType == 'en') ? 'products.promo_title' : 'products.promo_title_arabic';
        $columnName7 = ($langType == 'en') ? 'products.custom_badge_en' : 'products.custom_badge_ar';
        
        $cacheKey1 = 'search_updatedpro5'.'_'.$query.'_'.$langType;
        $cacheKey2 = 'search_updatedcat'.'_'.$query.'_'.$langType;
        $cacheKey3 = 'search_updatedbrand'.'_'.$query.'_'.$langType;
        
        // if(Cache::has($cacheKey1))
        //     $products = Cache::get('searchpro_'.$query);
        // else{
        //     CacheStores::create([
        //         'key' => $cacheKey1,
        //         'type' => 'prodata'
        //     ]);
            //$products = Cache::remember($cacheKey1, $seconds, function () use ($query, $city,$langType, $columnName,$columnName3,$columnName4,$columnName5,$columnName6,$columnName7) {

                $filters = [
                    'take'    => 10,
                    'page'    => $requestdata['page'] ?? 1,
                    'filters' => false,
                    'searchHeader' => $query
                ];
                $maindata = ProductListingHelperNew::productData($filters, false, false, $city);

                $products = $maindata['products']['data'];
        //});

        // }
            // print_r(json_encode($products));die;
        
        
        if(Cache::has($cacheKey2))
            $cats = Cache::get($cacheKey2);
        else{
            // CacheStores::create([
            //     'key' => $cacheKey2,
            //     'type' => 'catdata'
            // ]);
            $cats = Cache::remember($cacheKey2, $seconds, function () use ($query,$columnName3) {
                return Productcategory::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('menu', 1)
                ->where('status', 1)
                ->whereNotNull('parent_id')
                ->orderByRaw('-sort DESC')
                // ->orderBy('sort', 'asc')
                ->select('id', $columnName3, 'slug','icon', 'image_link_app', 'sort')
                //->with('BrandMediaImage:id,image')
                //->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }
        
        
        if(Cache::has($cacheKey3))
            $brands = Cache::get($cacheKey3);
        else{
            // CacheStores::create([
            //     'key' => $cacheKey3,
            //     'type' => 'branddata'
            // ]);
            $brands = Cache::remember($cacheKey3, $seconds, function () use ($query,$columnName3) {
                return Brand::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->with('BrandMediaImage:id,image')
                ->where('status', 1)
                ->where('show_in_front', 1)
                ->select('id', $columnName3, 'slug', 'brand_image_media')
                ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }
        
        $productData = $products->pluck('id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        if(isset($productData) && sizeof($productData)) {
            
            foreach($productData as $id){
                $extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false, 'expressdeliveryData' => false);
                $wishlistData = false;
                
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $extra_multi_data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    
                    // $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$city);
                    // if($freeGiftData)
                    //     $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    // $flash = ProductListingHelper::productFlashSaleNew($id);
                    // if($flash)
                    //     $extra_multi_data[$id]['flashData'] = $flash;
                    // $mobbadge = true;
                    // $badgeData = ProductListingHelper::productBadge($id,$city,$mobbadge);
                    // if($badgeData)
                    //     $extra_multi_data[$id]['badgeData'] = $badgeData;
                    
                    $edata = $extra_multi_data[$id];
                    // CacheStores::create([
                    //     'key' => 'extradata_'.$id.'_'.$city,
                    //     'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                
                $express = ProductListingHelper::productExpressDeliveryRegionalNew($id,$city);
                if($express)
                    $extra_multi_data[$id]['expressdeliveryData'] = $express;
                
                
                    
                    
                    
                    
                if(isset($requestdata['user_id'])) {
                    $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    if($wishlist) {
                        $wishlistData = true;
                    }
                }
                $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
            }
        }
        
        
        $response = [
            'products' => $products,
            'cats' => $cats,
            'brands' => $brands,
            'extra_multi_data' => $extra_multi_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $requestdata = $request->all();
        $seconds = 86400;
        
        
        if(Cache::has('searchpro_'.$query))
            $products = Cache::get('searchpro_'.$query);
        else{
            // CacheStores::create([
            //     'key' => 'searchpro_'.$query,
            //     'type' => 'prodata'
            // ]);
            $products = Cache::remember('searchpro_'.$query, $seconds, function () use ($query) {
                return Product::where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('status', 1)
                ->where('quantity', '>=', 1) 
                ->select('id', 'name', 'name_arabic', 'slug', 'price', 'sale_price', 'sku', 'meta_tag_en', 'meta_tag_ar', 'status', 'brands', 'feature_image', 'best_seller'
                ,'free_gift', 'low_in_stock', 'top_selling', 'created_at')
                ->with('brand:id,name,name_arabic,slug', 'brand.BrandMediaImage:id,image', 'featuredImage:id,image')
                //->orderByRaw("CASE WHEN brands = '22' THEN 0 ELSE 1 END")
                // ->inRandomOrder()
                ->orderBy('sort', 'asc')
                ->limit(10)->get();
            });
        }
        
        
        if(Cache::has('searchcat_'.$query))
            $cats = Cache::get('searchcat_'.$query);
        else{
            // CacheStores::create([
            //     'key' => 'searchcat_'.$query,
            //     'type' => 'catdata'
            // ]);
            $cats = Cache::remember('searchcat_'.$query, $seconds, function () use ($query) {
                return Productcategory::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('menu', 1)
                ->where('status', 1)
                ->whereNotNull('parent_id')
                ->orderByRaw('-sort DESC')
                // ->orderBy('sort', 'asc')
                ->select('id', 'name', 'name_arabic', 'slug','icon', 'image_link_app', 'sort')
                //->with('BrandMediaImage:id,image')
                //->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }
        
        
        if(Cache::has('searchbrand_'.$query))
            $brands = Cache::get('searchbrand_'.$query);
        else{
            // CacheStores::create([
            //     'key' => 'searchbrand_'.$query,
            //     'type' => 'branddata'
            // ]);
            $brands = Cache::remember('searchbrand_'.$query, $seconds, function () use ($query) {
                return Brand::
                where(function ($queryBuilder) use ($query) {
                    $queryBuilder
                    ->where('meta_tag_en', 'like', "%$query%")
                    ->orWhere('meta_tag_ar', 'like', "%$query%");
                })
                ->where('status', 1)
                ->where('show_in_front', 1)
                ->select('id', 'name', 'name_arabic', 'slug','brand_image_media')
                ->with('BrandMediaImage:id,image')
                ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                ->limit(6)
                ->get();
            });
        }

        
        
        
        
        
        $productData = $products->pluck('id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        $city = isset($requestdata['city']) ? $requestdata['city'] : false;
        if(isset($productData) && sizeof($productData)) {
            
            foreach($productData as $id){
                $extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false);
                $wishlistData = false;
                
                if(Cache::has('extradata_'.$id.'_'.$city))
                    $extra_multi_data[$id] = Cache::get('extradata_'.$id.'_'.$city);
                else{
                    
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$city);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSale($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$city,$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    
                    
                    $edata = $extra_multi_data[$id];
                    // CacheStores::create([
                    //     'key' => 'extradata_'.$id.'_'.$city,
                    //     'type' => 'extradata'
                    // ]);
                    Cache::remember('extradata_'.$id.'_'.$city, $seconds, function () use ($edata) {
                        return $edata;
                    });
                }
                
                
                    
                    
                    
                    
                if(isset($requestdata['user_id'])) {
                    $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    if($wishlist) {
                        $wishlistData = true;
                    }
                }
                $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
            }
        }
        
        
        $response = [
            'products' => $products,
            'cats' => $cats,
            'brands' => $brands,
            'extra_multi_data' => $extra_multi_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function CreateSaveSearch(Request $request) {
        $savesearch = SaveSearch::create([
            'user_id' => isset($request->user_id) ? $request->user_id : null,
            'key' =>  isset($request->key) ? $request->key : null,
        ]);
        
        $response = [
            'success' => 'true',
            'message' => 'Save Search Added Successfully!',
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
