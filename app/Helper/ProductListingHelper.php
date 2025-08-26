<?php

namespace App\Helper;
use Request;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\SubTags;
use App\Models\GeneralSettingProduct;
use App\Models\FreeGift;
use App\Models\States;
use App\Models\FrequentlyBoughtTogether;
use App\Models\ExpressDelivery;
use App\Models\FlashSale;
use App\Models\Warehouse;
use App\Models\Badge;
use App\Models\LiveStock;
use Carbon\Carbon;
use DB;

class ProductListingHelper
{   
    static function productDataRegionalNew($filters = [], $Productfaq = false, $mobimage = false){
        // print_r($mobimage == true ? 1 :0);die;
        $data = [];
        $take = isset($filters['take']) ? $filters['take'] : 20;
        $pageNumber =  isset($filters['page']) ? $filters['page'] : 1;
        $maincat = isset($filters['cat_id']) ? $filters['cat_id'] : false;
        $filtercat = isset($filters['filter_cat_id']) ? $filters['filter_cat_id'] : false;
        $filterbrands = isset($filters['filter_brand_id']) ? $filters['filter_brand_id'] : false;
        $filtertags = isset($filters['filter_tag_id']) ? $filters['filter_tag_id'] : false;
        $filtermin = isset($filters['filter_min']) ? $filters['filter_min'] : false;
        $filtermax = isset($filters['filter_max']) ? $filters['filter_max'] : false;
        $filterreview = isset($filters['filter_review']) ? $filters['filter_review'] : false;
        $views = isset($filters['views']) ? $filters['views'] : false;
        $productbyid = isset($filters['productbyid']) ? $filters['productbyid'] : false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        
        $new = isset($filters['new']) ? $filters['new'] : false;
        $search = isset($filters['search']) ? $filters['search'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;
        
        $filtertagsall = false;
        $filtertagsprice = false;
        if($filtertags){
            $subtags = SubTags::where('tag_id','!=', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsall = $subtags;
            
            // print_r($filtertagsall);
            
            $subtags = SubTags::where('tag_id', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsprice = $subtags;
            
            // print_r($filtertagsprice);
            // die;
        }
        
        
        // print_r($filtercat);die();
        $products = Product
        ::select('products.id','name', 'name_arabic','trendyol_price','vatonuspromo','short_description', 'slug','custom_badge_en','custom_badge_ar', 'products.sku', 'price', 'sale_price', 'stock_data.qty as quantity', 'feature_image', 'brands', 'best_seller', 'low_in_stock', 'top_selling','discounttypestatus','discountcondition','discountvalue','discountvaluecap','promotional_price', 'promo_title_arabic', 'promo_title', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        ->with('featuredImage:id,image')
          //,'productcategory:id'
        ->when($maincat, function ($q) use ($maincat) {
            //return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //    return $query->where('productcategories.id', $maincat);
            //});
          return $q->join('product_categories', function ($join) use ($maincat) {
           return $join->on('product_categories.product_id', '=', 'products.id')
                 ->where('product_categories.category_id', '=', $maincat);
        	});
        })
        
        ->when($new, function ($q) use ($new) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use ($search) {
            return $query->where(function($q) use($search){
                return $q
                ->where('products.meta_tag_en', 'like', "%$search%")
                ->orWhere('products.meta_tag_ar', 'like', "%$search%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        ->when($Productfaq == true, function ($q) {
            return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        })
        ->when($mobimage == true, function ($q) {
            return $q->with('brand:id,brand_app_image_media,name,name_arabic','brand.BrandMediaAppImage:id,image');
        })
        ->when($mobimage == false, function ($q) {
            return $q->with('brand:id,brand_image_media,name,name_arabic','brand.BrandMediaImage:id,image');
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid){
            return $q->whereIn('products.id', $productbyid);
        })
         
       	->leftJoin(DB::raw('livestock as stock_data'), function ($join) {
            $join->on('stock_data.sku', '=', 'products.sku')
                 ->where('stock_data.city', '=', 'OLN1');
        })
        ->leftJoin('product_review', 'product_review.product_sku', '=', 'products.sku')
        //  ->leftJoin(DB::raw("(SELECT sku, 
        //                CASE 
        //                    WHEN SUM(qty) > 10 THEN 10
        //                    WHEN SUM(qty) > 1 THEN SUM(qty)
        //                    ELSE 0
        //                END AS quantity
          //          FROM livestock
          //          GROUP BY sku) stock_data"), function($join) {
         //   $join->on('stock_data.sku', '=', 'products.sku');
        //})
        ->groupBy([
        'products.id', 'name', 'name_arabic', 'trendyol_price', 'vatonuspromo',
        'short_description', 'slug', 'custom_badge_en', 'custom_badge_ar',
        'products.sku', 'price', 'sale_price', 'stock_data.qty',
        'feature_image', 'brands', 'best_seller', 'low_in_stock',
        'top_selling', 'discounttypestatus', 'discountcondition',
        'discountvalue', 'discountvaluecap', 'promotional_price',
        'promo_title_arabic', 'promo_title', 'products.created_at'
    ])
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        ->where('stock_data.qty', '>', 1);
        if(isset($filters['filters'])){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
            // ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
            ->when($mobimage == false, function ($q) {
                return $q->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->when($mobimage == true, function ($q) {
                return $q->with('BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->whereIn('id', $brandids)
            ->orderBy('sorting', 'asc')
            ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
            
            $data['tags'] = Tag
            ::with(['childs' => function ($q) use($ids) {
                return $q->whereHas('tagProducts', function ($query) use ($ids) {
                    return $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function ($query) use ($ids) {
                    return $query->where('product_id', $ids);
             })
            ->where('status', 1)
            ->get();
            // $mindata = $products;
            // $maxdata = $products;
            // $data['max'] = $maxdata->orderBy('price', 'desc')->first()->price;
            // $data['min'] = $mindata->orderBy('price', 'asc')->first()->price;
            
            // lef
        }
        $products = $products
        ->when($filtercat, function ($q) use ($filtercat) {
            return $q->whereHas('productcategory', function ($query) use ($filtercat) {
                return $query->where(function($a) use($filtercat){
                    return $a->whereIn('productcategories.id', $filtercat)->orWhereIn('productcategories.name', $filtercat);
                });
            });
        })
        ->when($filterbrands, function ($q) use ($filterbrands) {
            return $q->whereHas('brand', function ($query) use ($filterbrands) {
                return $query->where(function($a) use($filterbrands){
                    return $a->whereIn('brands.id', $filterbrands)->orWhereIn('brands.name', $filterbrands);
                });
            });
        })
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->where(function($a) use($filtertags){
        //             return $a->whereIn('sub_tags.id', $filtertags)->orWhereIn('sub_tags.name', $filtertags);
        //         });
        //     });
        // })
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        ->when($filtertagsprice, function ($q) use ($filtertagsprice) {
            return $q->whereHas('tags', function ($query) use ($filtertagsprice) {
                return $query->where(function($a) use($filtertagsprice){
                    return $a->whereIn('sub_tags.id', $filtertagsprice)->orWhereIn('sub_tags.name', $filtertagsprice);
                });
            });
        })
        
        ->when($filtermin, function ($q) use ($filtermin) {
            return $q->where('products.sale_price', '>=', $filtermin);
        })
        ->when($filtermax, function ($q) use ($filtermax) {
            return $q->where('products.sale_price', '<=', $filtermax);
        })
        ->when($filterreview, function ($q) use ($filterreview) {
            //print_r(implode(' or rating = ', $filterreview));die;
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
            //return $q->whereRaw('round(AVG(rating)) IN ("'.implode('","', $filterreview).'")');
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when(!$sort, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        if(isset($filters['filters'])){
            if(sizeof($products->pluck('sale_price')->toArray()) >= 1){
                $data['min'] = min($products->pluck('sale_price')->toArray());
                $data['max'] = max($products->pluck('sale_price')->toArray());
            }
        }
        $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        return $data;
    }
    
    static function productDataRegional($filters = [], $Productfaq = false, $mobimage = false, $city = 'abha'){
        // print_r($mobimage == true ? 1 :0);die;
        $data = [];
        $take = isset($filters['take']) ? $filters['take'] : 20;
        $pageNumber =  isset($filters['page']) ? $filters['page'] : 1;
        $maincat = isset($filters['cat_id']) ? $filters['cat_id'] : false;
        $filtercat = isset($filters['filter_cat_id']) ? $filters['filter_cat_id'] : false;
        $filterbrands = isset($filters['filter_brand_id']) ? $filters['filter_brand_id'] : false;
        $filtertags = isset($filters['filter_tag_id']) ? $filters['filter_tag_id'] : false;
        $filtermin = isset($filters['filter_min']) ? $filters['filter_min'] : false;
        $filtermax = isset($filters['filter_max']) ? $filters['filter_max'] : false;
        $filterreview = isset($filters['filter_review']) ? $filters['filter_review'] : false;
        $views = isset($filters['views']) ? $filters['views'] : false;
        $productbyid = isset($filters['productbyid']) ? $filters['productbyid'] : false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        
        $new = isset($filters['new']) ? $filters['new'] : false;
        $search = isset($filters['search']) ? $filters['search'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;

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

        
        $filtertagsall = false;
        $filtertagsprice = false;
        if($filtertags){
            $subtags = SubTags::where('tag_id','!=', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsall = $subtags;
            
            $subtags = SubTags::where('tag_id', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsprice = $subtags;
        }
        
        $products = Product
        ::select('products.id', 'name', 'name_arabic','vatonuspromo','short_description', 'slug','custom_badge_en','custom_badge_ar', 'products.sku', 'price', 'pre_order', 'no_of_days', 'sale_price', 'stock_data.quantity', 'feature_image', 'brands', 'best_seller', 'low_in_stock', 'top_selling','warranty','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat','promotional_price', 'promo_title_arabic', 'promo_title', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        ->with('featuredImage:id,image',
        // 'productcategory:id',
            'liveStockData:id,sku,ln_sku,qty,city_code,city',
            'liveStockData.warehouseData:id,ln_code,show_in_express,status',
        'liveStockData.warehouseData.cityData:id,name,name_arabic')
        ->when($maincat, function ($q) use ($maincat) {
            // return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //     return $query->where('productcategories.id', $maincat);
            // });
            return $q->join('product_categories', function ($join) use ($maincat) {
                return $join->on('product_categories.product_id', '=', 'products.id')
                      ->where('product_categories.category_id', '=', $maincat);
                 });
        })
        
        ->when($new, function ($q) use ($new) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use ($search) {
            return $query->where(function($q) use($search){
                return $q
                ->where('products.meta_tag_en', 'like', "%$search%")
                ->orWhere('products.meta_tag_ar', 'like', "%$search%")
                ->orWhere('products.name', 'like', "%$search%")
                ->orWhere('products.name_arabic', 'like', "%$search%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->whereIn('sub_tag.id', $filtertags);
        //     });
        // })
        ->when($Productfaq == true, function ($q) {
            return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        })
        ->when($mobimage == true, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_app_image_media,name,name_arabic','brand.BrandMediaAppImage:id,image');
        })
        ->when($mobimage == false, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_image_media,name,name_arabic','brand.BrandMediaImage:id,image');
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid){
            return $q->whereIn('products.id', $productbyid);
        })
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        ->leftJoin(DB::raw("(SELECT 
            qty_table.sku, 
            CASE 
                WHEN total_qty > 10 THEN 10
                WHEN total_qty > 0 THEN total_qty
                ELSE 0
            END AS quantity
        FROM (
            SELECT 
                livestock.sku,
                GREATEST(SUM(CASE 
                                WHEN livestock.city NOT IN ('OLN1', 'KUW101') THEN livestock.qty 
                                ELSE 0 
                            END) - 3, 0) 
                + 
                SUM(CASE 
                        WHEN livestock.city IN ('OLN1', 'KUW101') THEN livestock.qty 
                        ELSE 0 
                    END) 
                AS total_qty
            FROM livestock 
            INNER JOIN warehouse 
                ON livestock.city = warehouse.ln_code 
            WHERE warehouse.status = 1 
            AND warehouse.show_in_express = 1 
            AND livestock.city in (".implode(',', $exp).")
            GROUP BY livestock.sku
        ) as qty_table) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })
        ->groupBy('products.id', 'stock_data.quantity')
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        // ->where('products.free_gift', 0)
        ->where('stock_data.quantity', '>=', 1);
        if(isset($filters['filters'])){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
            // ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
            ->when($mobimage == false, function ($q) {
                return $q->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->when($mobimage == true, function ($q) {
                return $q->with('BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->whereIn('id', $brandids)
            ->orderBy('sorting', 'asc')
            ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
            
            $data['tags'] = Tag
            ::with(['childs' => function ($q) use($ids) {
                return $q->whereHas('tagProducts', function ($query) use ($ids) {
                    return $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function ($query) use ($ids) {
                    return $query->where('product_id', $ids);
             })
            ->where('status', 1)
            ->get();
            // $mindata = $products;
            // $maxdata = $products;
            // $data['max'] = $maxdata->orderBy('price', 'desc')->first()->price;
            // $data['min'] = $mindata->orderBy('price', 'asc')->first()->price;
        }
        $products = $products
        ->when($filtercat, function ($q) use ($filtercat) {
            return $q->whereHas('productcategory', function ($query) use ($filtercat) {
                return $query->where(function($a) use($filtercat){
                    return $a->whereIn('productcategories.id', $filtercat)->orWhereIn('productcategories.name', $filtercat);
                });
            });
        })
        ->when($filterbrands, function ($q) use ($filterbrands) {
            return $q->whereHas('brand', function ($query) use ($filterbrands) {
                return $query->where(function($a) use($filterbrands){
                    return $a->whereIn('brands.id', $filterbrands)->orWhereIn('brands.name', $filterbrands);
                });
            });
        })
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->where(function($a) use($filtertags){
        //             return $a->whereIn('sub_tags.id', $filtertags)->orWhereIn('sub_tags.name', $filtertags);
        //         });
        //     });
        // })
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        ->when($filtertagsprice, function ($q) use ($filtertagsprice) {
            return $q->whereHas('tags', function ($query) use ($filtertagsprice) {
                return $query->where(function($a) use($filtertagsprice){
                    return $a->whereIn('sub_tags.id', $filtertagsprice)->orWhereIn('sub_tags.name', $filtertagsprice);
                });
            });
        })
        
        ->when($filtermin, function ($q) use ($filtermin) {
            return $q->where('products.sale_price', '>=', $filtermin);
        })
        ->when($filtermax, function ($q) use ($filtermax) {
            return $q->where('products.sale_price', '<=', $filtermax);
        })
        ->when($filterreview, function ($q) use ($filterreview) {
            //print_r(implode(' or rating = ', $filterreview));die;
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
            //return $q->whereRaw('round(AVG(rating)) IN ("'.implode('","', $filterreview).'")');
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when(!$sort, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        if(isset($filters['filters'])){
            if(sizeof($products->pluck('sale_price')->toArray()) >= 1){
                $data['min'] = min($products->pluck('sale_price')->toArray());
                $data['max'] = max($products->pluck('sale_price')->toArray());
            }
        }
        $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        return $data;
    }


    // New functions for testing
    static function productDataRegionalNewTesting($filters = [], $Productfaq = false, $mobimage = false){
        $data = [];
        $take = $filters['take'] ?? 20;
        $pageNumber = $filters['page'] ?? 1;
        $maincat = $filters['cat_id'] ?? false;
        $filtercat = $filters['filter_cat_id'] ?? false;
        $filterbrands = $filters['filter_brand_id'] ?? false;
        $filtertags = $filters['filter_tag_id'] ?? false;
        $filtermin = $filters['filter_min'] ?? false;
        $filtermax = $filters['filter_max'] ?? false;
        $filterreview = $filters['filter_review'] ?? false;
        $views = $filters['views'] ?? false;
        $productbyid = $filters['productbyid'] ?? false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        // $sort = $filters['sort'] ?? 'default_value';
        $new = $filters['new'] ?? false;
        $search = $filters['search'] ?? false;
        $rating = $filters['rating'] ?? false;
    
        // Get the tags
        $filtertagsall = false;
        $filtertagsprice = false;
        if ($filtertags) {
            // Get all subtags for non-price tags
            $subtags = SubTags::where('tag_id', '!=', 44)
                ->whereIn('id', $filtertags)
                ->orWhereIn('name', $filtertags)
                ->pluck('id')->toArray();
            if (count($subtags)) $filtertagsall = $subtags;
    
            // Get subtags for price tags
            $subtags = SubTags::where('tag_id', 44)
                ->whereIn('id', $filtertags)
                ->orWhereIn('name', $filtertags)
                ->pluck('id')->toArray();
            if (count($subtags)) $filtertagsprice = $subtags;
        }
    
        // Main query
        $products = Product::select('products.id', 'name', 'name_arabic', 'trendyol_price', 'vatonuspromo', 'short_description', 'slug', 'custom_badge_en', 'custom_badge_ar', 'products.sku', 'price', 'sale_price', 'stock_data.quantity', 'feature_image', 'brands', 'best_seller', 'low_in_stock', 'top_selling', 'discounttypestatus', 'discountcondition', 'discountvalue', 'discountvaluecap', 'promotional_price', 'promo_title_arabic', 'promo_title', 'products.created_at', 
        DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        ->with('featuredImage:id,image')
        // , 'productcategory:id'
        ->when($maincat, function ($q) use ($maincat) {
            // return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //     return $query->where('productcategories.id', $maincat);
            // });
            return $q->join('product_categories', function ($join) use ($maincat) {
                return $join->on('product_categories.product_id', '=', 'products.id')
                      ->where('product_categories.category_id', '=', $maincat);
                 });
        })
        ->when($new, function ($q) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use($search) {
            return $query->where(function($q) use($search) {
                $q->where('products.meta_tag_en', 'like', "%$search%")
                    ->orWhere('products.meta_tag_ar', 'like', "%$search%");
            });
        })
        ->when($rating, function ($q) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        ->when($Productfaq, function ($q) {
            return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        })
        ->when($mobimage, function ($q) {
            return $q->with('brand:id,brand_app_image_media,name,name_arabic', 'brand.BrandMediaAppImage:id,image');
        })
        ->when(!$mobimage, function ($q) {
            return $q->with('brand:id,brand_image_media,name,name_arabic', 'brand.BrandMediaImage:id,image');
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid) {
            return $q->whereIn('products.id', $productbyid);
        })
        ->leftJoin('product_review', 'product_review.product_sku', '=', 'products.sku')
        ->leftJoin(DB::raw("(SELECT sku, CASE WHEN SUM(qty) > 10 THEN 10 WHEN SUM(qty) > 1 THEN SUM(qty) ELSE 0 END AS quantity FROM livestock GROUP BY sku) stock_data"), 'stock_data.sku', '=', 'products.sku')
        ->groupBy('products.id')
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        ->where('stock_data.quantity', '>', 1);
    
        // Apply filters to the products query
        if ($filtercat) {
            $products->whereHas('productcategory', function($query) use ($filtercat) {
                $query->whereIn('productcategories.id', $filtercat)
                    ->orWhereIn('productcategories.name', $filtercat);
            });
        }
        
        if ($filterbrands) {
            $products->whereHas('brand', function($query) use ($filterbrands) {
                $query->whereIn('brands.id', $filterbrands)
                    ->orWhereIn('brands.name', $filterbrands);
            });
        }
        
        if ($filtertagsall) {
            $products->whereHas('tags', function($query) use ($filtertagsall) {
                $query->whereIn('sub_tags.id', $filtertagsall);
            });
        }
        
        if ($filtertagsprice) {
            $products->whereHas('tags', function($query) use ($filtertagsprice) {
                $query->whereIn('sub_tags.id', $filtertagsprice);
            });
        }
        
        if ($filtermin) {
            $products->where('products.sale_price', '>=', $filtermin);
        }
        
        if ($filtermax) {
            $products->where('products.sale_price', '<=', $filtermax);
        }
        
        if ($filterreview) {
            $products->havingRaw('rating = ' . implode(' or rating = ', $filterreview));
        }
        
        if ($sort) {
            $products->orderBy($sort[0], $sort[1]);
        } else {
            $products->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        }
        
        // Filters for brand and tags
        if (isset($filters['filters'])) {
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand::whereIn('id', $brandids)
                ->where('status', 1)
                ->where('show_in_front', 1)
                ->with($mobimage ? 'BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details' : 'BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
                ->orderBy('sorting', 'asc')
                ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
        
            $data['tags'] = Tag::with(['childs' => function($q) use ($ids) {
                $q->whereHas('tagProducts', function($query) use ($ids) {
                    $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function($query) use ($ids) {
                $query->whereIn('product_id', $ids);
            })
            ->where('status', 1)
            ->get();
        }

    
        // Pagination
        $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        return $data;
    }

    // New functions for testing

    
    static function productData($filters = [], $Productfaq = false, $mobimage = false){
        // print_r($mobimage == true ? 1 :0);die;
        $data = [];
        $take = isset($filters['take']) ? $filters['take'] : 20;
        $pageNumber =  isset($filters['page']) ? $filters['page'] : 1;
        $maincat = isset($filters['cat_id']) ? $filters['cat_id'] : false;
        $filtercat = isset($filters['filter_cat_id']) ? $filters['filter_cat_id'] : false;
        $filterbrands = isset($filters['filter_brand_id']) ? $filters['filter_brand_id'] : false;
        $filtertags = isset($filters['filter_tag_id']) ? $filters['filter_tag_id'] : false;
        $filtermin = isset($filters['filter_min']) ? $filters['filter_min'] : false;
        $filtermax = isset($filters['filter_max']) ? $filters['filter_max'] : false;
        $filterreview = isset($filters['filter_review']) ? $filters['filter_review'] : false;
        $views = isset($filters['views']) ? $filters['views'] : false;
        $productbyid = isset($filters['productbyid']) ? $filters['productbyid'] : false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        
        $new = isset($filters['new']) ? $filters['new'] : false;
        $search = isset($filters['search']) ? $filters['search'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;
        
        $filtertagsall = false;
        $filtertagsprice = false;
        if($filtertags){
            $subtags = SubTags::where('tag_id','!=', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsall = $subtags;
            
            // print_r($filtertagsall);
            
            $subtags = SubTags::where('tag_id', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsprice = $subtags;
            
            // print_r($filtertagsprice);
            // die;
        }
        
        
        // print_r($filtercat);die();
        $products = Product
        ::select('products.id', 'name', 'name_arabic','vatonuspromo','short_description', 'slug','custom_badge_en','custom_badge_ar', 'sku', 'price', 'pre_order', 'no_of_days', 'sale_price', 'feature_image', 'quantity', 'brands', 'best_seller', 'low_in_stock', 'top_selling','warranty','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat','promotional_price', 'promo_title_arabic', 'promo_title', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        // ,title,title_arabic,alt,alt_arabic,details
        ->with('featuredImage:id,image')
        // ,'productcategory:id'
        ->when($maincat, function ($q) use ($maincat) {
            // return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //     return $query->where('productcategories.id', $maincat);
            // });
            return $q->join('product_categories', function ($join) use ($maincat) {
                return $join->on('product_categories.product_id', '=', 'products.id')
                      ->where('product_categories.category_id', '=', $maincat);
                 });
        })
        
        ->when($new, function ($q) use ($new) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use ($search) {
            return $query->where(function($q) use($search){
                return $q
                ->where('products.meta_tag_en', 'like', "%$search%")
                ->orWhere('products.meta_tag_ar', 'like', "%$search%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->whereIn('sub_tag.id', $filtertags);
        //     });
        // })
        ->when($Productfaq == true, function ($q) {
            return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        })
        ->when($mobimage == true, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_app_image_media,name,name_arabic','brand.BrandMediaAppImage:id,image,title,title_arabic');
        })
        ->when($mobimage == false, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_image_media,name,name_arabic','brand.BrandMediaImage:id,image,title,title_arabic');
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid){
            return $q->whereIn('products.id', $productbyid);
        })
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
       
        ->groupBy('products.id')
        // ->orderBy('sort', 'asc')
        // ->orderBy('price', 'asc')
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        // ->where('products.free_gift', 0)
        ->where('products.quantity', '>=', 1);
        if(isset($filters['filters'])){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
            // ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
            ->when($mobimage == false, function ($q) {
                return $q->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->when($mobimage == true, function ($q) {
                return $q->with('BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->whereIn('id', $brandids)
            ->orderBy('sorting', 'asc')
            ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
            
            $data['tags'] = Tag
            ::
                with(['childs' => function ($q) use($ids) {
                return $q->whereHas('tagProducts', function ($query) use ($ids) {
                    return $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function ($query) use ($ids) {
                    return $query->where('product_id', $ids);
             })
            ->
            where('status', 1)->orWhere('id', 42)
            ->get();
            // print_r($data['tags']);die();
            
            // $mindata = $products;
            // $maxdata = $products;
            // $data['max'] = $maxdata->orderBy('price', 'desc')->first()->price;
            // $data['min'] = $mindata->orderBy('price', 'asc')->first()->price;
            
            
        }
        $products = $products
        ->when($filtercat, function ($q) use ($filtercat) {
            return $q->whereHas('productcategory', function ($query) use ($filtercat) {
                return $query->where(function($a) use($filtercat){
                    return $a->whereIn('productcategories.id', $filtercat)->orWhereIn('productcategories.name', $filtercat);
                });
            });
        })
        ->when($filterbrands, function ($q) use ($filterbrands) {
            return $q->whereHas('brand', function ($query) use ($filterbrands) {
                return $query->where(function($a) use($filterbrands){
                    return $a->whereIn('brands.id', $filterbrands)->orWhereIn('brands.name', $filterbrands);
                });
            });
        })
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->where(function($a) use($filtertags){
        //             return $a->whereIn('sub_tags.id', $filtertags)->orWhereIn('sub_tags.name', $filtertags);
        //         });
        //     });
        // })
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        ->when($filtertagsprice, function ($q) use ($filtertagsprice) {
            return $q->whereHas('tags', function ($query) use ($filtertagsprice) {
                return $query->where(function($a) use($filtertagsprice){
                    return $a->whereIn('sub_tags.id', $filtertagsprice);
                });
            });
        })
        
        ->when($filtermin, function ($q) use ($filtermin) {
            return $q->where('products.sale_price', '>=', $filtermin);
        })
        ->when($filtermax, function ($q) use ($filtermax) {
            return $q->where('products.sale_price', '<=', $filtermax);
        })
        ->when($filterreview, function ($q) use ($filterreview) {
            //print_r(implode(' or rating = ', $filterreview));die;
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
            //return $q->whereRaw('round(AVG(rating)) IN ("'.implode('","', $filterreview).'")');
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when(!$sort, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        if(isset($filters['filters'])){
            if(sizeof($products->pluck('sale_price')->toArray()) >= 1){
                $data['min'] = min($products->pluck('sale_price')->toArray());
                $data['max'] = max($products->pluck('sale_price')->toArray());
            }
        }
        $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        return $data;
    }
    
    static function productDataNew($filters = [], $Productfaq = false, $mobimage = false){
        // print_r($mobimage == true ? 1 :0);die;
        $data = [];
        $take = isset($filters['take']) ? $filters['take'] : 20;
        $pageNumber =  isset($filters['page']) ? $filters['page'] : 1;
        $maincat = isset($filters['cat_id']) ? $filters['cat_id'] : false;
        $filtercat = isset($filters['filter_cat_id']) ? $filters['filter_cat_id'] : false;
        $filterbrands = isset($filters['filter_brand_id']) ? $filters['filter_brand_id'] : false;
        $filtertags = isset($filters['filter_tag_id']) ? $filters['filter_tag_id'] : false;
        $filtermin = isset($filters['filter_min']) ? $filters['filter_min'] : false;
        $filtermax = isset($filters['filter_max']) ? $filters['filter_max'] : false;
        $filterreview = isset($filters['filter_review']) ? $filters['filter_review'] : false;
        $views = isset($filters['views']) ? $filters['views'] : false;
        $productbyid = isset($filters['productbyid']) ? $filters['productbyid'] : false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        
        $new = isset($filters['new']) ? $filters['new'] : false;
        $search = isset($filters['search']) ? $filters['search'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;
        
        $filtertagsall = false;
        $filtertagsprice = false;
        if($filtertags){
            $subtags = SubTags::where('tag_id','!=', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsall = $subtags;
            
            // print_r($filtertagsall);
            
            $subtags = SubTags::where('tag_id', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsprice = $subtags;
            
            // print_r($filtertagsprice);
            // die;
        }
        
        
        // print_r($filtercat);die();
        $products = Product
        ::select('products.id', 'name', 'name_arabic','vatonuspromo','short_description', 'slug','custom_badge_en','custom_badge_ar', 'sku', 'price', 'pre_order', 'no_of_days', 'sale_price', 'feature_image', 'quantity', 'brands', 'best_seller', 'low_in_stock', 'top_selling','warranty','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat','promotional_price', 'promo_title_arabic', 'promo_title', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        // ,title,title_arabic,alt,alt_arabic,details
        ->with('featuredImage:id,image')
        // ,'productcategory:id'
        ->when($maincat, function ($q) use ($maincat) {
            // return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //     return $query->where('productcategories.id', $maincat);
            // });
            return $q->join('product_categories', function ($join) use ($maincat) {
                return $join->on('product_categories.product_id', '=', 'products.id')
                      ->where('product_categories.category_id', '=', $maincat);
                 });
        })
        
        ->when($new, function ($q) use ($new) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use ($search) {
            return $query->where(function($q) use($search){
                return $q
                ->where('products.meta_tag_en', 'like', "%$search%")
                ->orWhere('products.meta_tag_ar', 'like', "%$search%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->whereIn('sub_tag.id', $filtertags);
        //     });
        // })
        ->when($Productfaq == true, function ($q) {
            return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        })
        ->when($mobimage == true, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_app_image_media,name,name_arabic','brand.BrandMediaAppImage:id,image,title,title_arabic');
        })
        ->when($mobimage == false, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_image_media,name,name_arabic','brand.BrandMediaImage:id,image,title,title_arabic');
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid){
            return $q->whereIn('products.id', $productbyid);
        })
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
       
        ->groupBy('products.id')
        // ->orderBy('sort', 'asc')
        // ->orderBy('price', 'asc')
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        // ->where('products.free_gift', 0)
        ->where('products.quantity', '>=', 1);
        if(isset($filters['filters'])){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
            // ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
            ->when($mobimage == false, function ($q) {
                return $q->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->when($mobimage == true, function ($q) {
                return $q->with('BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->whereIn('id', $brandids)
            ->orderBy('sorting', 'asc')
            ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
            
            $data['tags'] = Tag
            ::
                with(['childs' => function ($q) use($ids) {
                return $q->whereHas('tagProducts', function ($query) use ($ids) {
                    return $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function ($query) use ($ids) {
                    return $query->where('product_id', $ids);
             })
            ->
            where('status', 1)->orWhere('id', 42)
            ->get();
            // print_r($data['tags']);die();
            
            // $mindata = $products;
            // $maxdata = $products;
            // $data['max'] = $maxdata->orderBy('price', 'desc')->first()->price;
            // $data['min'] = $mindata->orderBy('price', 'asc')->first()->price;
            
            
        }
        $products = $products
        ->when($filtercat, function ($q) use ($filtercat) {
            return $q->whereHas('productcategory', function ($query) use ($filtercat) {
                return $query->where(function($a) use($filtercat){
                    return $a->whereIn('productcategories.id', $filtercat)->orWhereIn('productcategories.name', $filtercat);
                });
            });
        })
        ->when($filterbrands, function ($q) use ($filterbrands) {
            return $q->whereHas('brand', function ($query) use ($filterbrands) {
                return $query->where(function($a) use($filterbrands){
                    return $a->whereIn('brands.id', $filterbrands)->orWhereIn('brands.name', $filterbrands);
                });
            });
        })
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->where(function($a) use($filtertags){
        //             return $a->whereIn('sub_tags.id', $filtertags)->orWhereIn('sub_tags.name', $filtertags);
        //         });
        //     });
        // })
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        ->when($filtertagsprice, function ($q) use ($filtertagsprice) {
            return $q->whereHas('tags', function ($query) use ($filtertagsprice) {
                return $query->where(function($a) use($filtertagsprice){
                    return $a->whereIn('sub_tags.id', $filtertagsprice);
                });
            });
        })
        
        ->when($filtermin, function ($q) use ($filtermin) {
            return $q->where('products.sale_price', '>=', $filtermin);
        })
        ->when($filtermax, function ($q) use ($filtermax) {
            return $q->where('products.sale_price', '<=', $filtermax);
        })
        ->when($filterreview, function ($q) use ($filterreview) {
            //print_r(implode(' or rating = ', $filterreview));die;
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
            //return $q->whereRaw('round(AVG(rating)) IN ("'.implode('","', $filterreview).'")');
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when(!$sort, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        if(isset($filters['filters'])){
            if(sizeof($products->pluck('sale_price')->toArray()) >= 1){
                $data['min'] = min($products->pluck('sale_price')->toArray());
                $data['max'] = max($products->pluck('sale_price')->toArray());
            }
        }
        $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        return $data;
    }
    
    // cart
    static function productFreeGiftsCart($id,$city = false, $subtotal = 0){
        $freegiftCart = FreeGift
        ::select(['free_gift.id', 'discount_type','allowed_gifts', 'add_free_gift_item', 'allow_delete', 'amount_type'])
        ->with('freegiftlist:id,free_gift_id,discount,product_id','freegiftlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image,brands,free_gift','freegiftlist.productdetail.featuredImage:id,image','freegiftlist.productdetail.brand:id,name,name_arabic','freegiftlist.productdetail.liveStockSum')
        ->where('free_gift.status', 1)
        //->withSum('freegiftlist.productdetail.liveStocks', 'qty')
        ->where('show_on', 0)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'prodata.sku');
        // })
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a) use ($subtotal) {
            return $a->where(function ($query) {
                    return $query->where('restriction_pages', 2)
                        ->whereNull('min_amount')
                        ->orWhereRaw('IF(sale_price > 0, sale_price, price) >= free_gift.min_amount')
                        ->whereNull('max_amount')
                        ->orWhereRaw('IF(sale_price > 0, sale_price, price) <= free_gift.max_amount');
                })
                ->orWhere(function ($query) use ($subtotal) {
                    return $query->where('restriction_pages', 1)
                        ->whereNull('min_amount')
                        ->orWhereRaw($subtotal . ' >= free_gift.min_amount')
                        ->whereNull('max_amount')
                        ->orWhereRaw($subtotal . ' <= free_gift.max_amount');
                });
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        return $freegiftCart;
    }

    static function productFreeGiftsRegional($id,$city = false){
        
        $freegift = FreeGift
        ::select(['free_gift.id', 'discount_type','allowed_gifts', 'add_free_gift_item', 'allow_delete', 'amount_type'])
        ->with('freegiftlist:id,free_gift_id,discount,product_id','freegiftlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image,brands,free_gift','freegiftlist.productdetail.featuredImage:id,image','freegiftlist.productdetail.brand:id,name,name_arabic','freegiftlist.productdetail.liveStockSum')
        ->where('free_gift.status', 1)
        //->withSum('freegiftlist.productdetail.liveStocks', 'qty')
        ->where('show_on', 1)
        ->where('restriction_pages', 2)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'prodata.sku');
        // })
        //->where(function($g){
        //    return $g->orWhere('prodata.free_gift', 1)->orWhere('prodata.quantity', '>=',1);
        //})
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a){
            return $a->whereNull('min_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) >= free_gift.min_amount');
        })
        ->where(function($a){
            return $a->whereNull('max_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) <= free_gift.max_amount');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($freegift);die();
        return $freegift;
    }
    
     static function productFreeGiftsRegionalNew($id,$city = false){
        
        $freegift = FreeGift
        ::select(['free_gift.id', 'discount_type','allowed_gifts', 'image', 'add_free_gift_item', 'allow_delete', 'amount_type'])
        ->with('freegiftlist:id,free_gift_id,discount,product_id','freegiftlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image,brands,free_gift','freegiftlist.productdetail.featuredImage:id,image','freegiftlist.productdetail.brand:id,name,name_arabic','freegiftlist.productdetail.liveStockSum')
        ->where('free_gift.status', 1)
        //->withSum('freegiftlist.productdetail.liveStocks', 'qty')
        ->where('show_on', 1)
        ->where('restriction_pages', 2)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
        //     $join->on('stock_data.sku', '=', 'prodata.sku');
        // })
        // ->where(function($g){
        //     return $g->orWhere('prodata.free_gift', 1);
        //     // ->orWhere('prodata.quantity', '>=',1);
        // })
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a){
            return $a->whereNull('min_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) >= free_gift.min_amount');
        })
        ->where(function($a){
            return $a->whereNull('max_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) <= free_gift.max_amount');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($freegift);die();
        return $freegift;
    }
    
    static function productFreeGifts($id,$city = false){
        
        $freegift = FreeGift
        ::select(['free_gift.id', 'discount_type','allowed_gifts', 'add_free_gift_item', 'allow_delete', 'amount_type'])
        ->with('freegiftlist:id,free_gift_id,discount,product_id','freegiftlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image,brands,free_gift','freegiftlist.productdetail.featuredImage:id,image','freegiftlist.productdetail.brand:id,name,name_arabic')
        ->where('free_gift.status', 1)
        ->where('show_on', 1)
        ->where('restriction_pages', 2)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        ->where(function($g){
            return $g->orWhere('prodata.free_gift', 1)->orWhere('prodata.quantity', '>=',1);
        })
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a){
            return $a->whereNull('min_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) >= free_gift.min_amount');
        })
        ->where(function($a){
            return $a->whereNull('max_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) <= free_gift.max_amount');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($freegift);die();
        return $freegift;
    }
    
    static function productFlashSale($id) {
        $flash = FlashSale::
        with('featuredImage:id,image,title,title_arabic', 'featuredImageApp:id,image,title,title_arabic')
        ->where('status', 1)
        ->where('left_quantity', '>=', 1)
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($a) use ($id){
            return $a->whereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',1)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('brandsData.productname',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('brandsData.productname',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            })->orWhereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',2)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('subtagsData.tagProducts',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('subtagsData.tagProducts',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            })->orWhereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',3)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('productData',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('productData',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            })->orWhereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',4)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('categoriesData.productname',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('categoriesData.productname',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            });
        })
        ->first(['id', 'image', 'image_app', 'discount_type', 'discount_amount', 'end_date', 'left_quantity', 'name', 'name_arabic']);
        
        return $flash;
    }
    
    static function productFlashSaleNew($id) {
        $flash = FlashSale::
        with('featuredImage:id,image,title,title_arabic', 'featuredImageApp:id,image,title,title_arabic')
        ->where('status', 1)
        ->where('left_quantity', '>=', 1)
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($a) use ($id){
            return $a->whereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',1)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('brandsData.productname',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('brandsData.productname',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            })->orWhereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',2)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('subtagsData.tagProducts',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('subtagsData.tagProducts',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            })->orWhereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',3)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('productData',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('productData',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            })->orWhereHas('restrictions', function ($b) use ($id) {
                return $b->where('restriction_type',4)->where(function($c) use ($id){
                    return $c->where(function($d) use ($id){
                        return $d->where('select_include_exclude',1)->whereHas('categoriesData.productname',function($e) use ($id){
                            return $e->where('products.id',$id);
                        });
                    })->orWhere(function($d) use ($id){
                        return $d->where('select_include_exclude',2)->whereHas('categoriesData.productname',function($e) use ($id){
                            return $e->where('products.id', '!=' ,$id);
                        });
                    });
                });
            });
        })
        ->first(['id', 'image', 'image_app', 'discount_type', 'discount_amount', 'end_date', 'left_quantity', 'name', 'name_arabic']);
        
        return $flash;
    }
    
    static function productFBTRegional($id,$city = false){
        
        $fbt = FrequentlyBoughtTogether
        ::select(['fbt.id','fbt.name', 'fbt.name_arabic', 'discount_type','amount_type','cities_restriction','include_cities','exclude_cities', 'show_on_thumbnail'])
        ->with('fbtlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image','fbtlist.productdetail.featuredImage:id,image,title,title_arabic','fbtlist.productdetail.liveStockSum')
        ->where('fbt.status', 1)
        //->withSum('fbtlist.productdetail.liveStocks', 'qty')
        ->where('show_on_product', 1)
        ->where('restriction_pages', 2)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a){
            return $a->whereNull('min_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) >= fbt.min_amount');
        })
        ->where(function($a){
            return $a->whereNull('max_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) <= fbt.max_amount');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($fbt);die();
        return $fbt;
    }
    
     static function productFBTRegionalNew($id,$city = false){
        
        $fbt = FrequentlyBoughtTogether
        ::select(['fbt.id','fbt.name', 'fbt.name_arabic', 'discount_type','amount_type','cities_restriction','include_cities','exclude_cities', 'show_on_thumbnail'])
        ->with('fbtlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image','fbtlist.productdetail.featuredImage:id,image,title,title_arabic','fbtlist.productdetail.liveStockSum')
        ->where('fbt.status', 1)
        //->withSum('fbtlist.productdetail.liveStocks', 'qty')
        ->where('show_on_product', 1)
        ->where('restriction_pages', 2)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a){
            return $a->whereNull('min_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) >= fbt.min_amount');
        })
        ->where(function($a){
            return $a->whereNull('max_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) <= fbt.max_amount');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($fbt);die();
        return $fbt;
    }

    static function productFBT($id,$city = false){
        
        $fbt = FrequentlyBoughtTogether
        ::select(['fbt.id','fbt.name', 'fbt.name_arabic', 'discount_type','amount_type','cities_restriction','include_cities','exclude_cities', 'show_on_thumbnail'])
        ->with('fbtlist.productdetail:id,name,name_arabic,sku,slug,price,sale_price,quantity,feature_image','fbtlist.productdetail.featuredImage:id,image,title,title_arabic')
        ->where('fbt.status', 1)
        ->where('show_on_product', 1)
        ->where('restriction_pages', 2)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        ->leftJoin('states', function($join) use($city) {
            $join->on('states.name', '=', DB::raw('"'.$city.'"'))->orOn('states.name_arabic', '=', DB::raw('"'.$city.'"'));
        })
        ->where(function($a){
            return $a->where('cities_restriction',1)->orWhere(function($query){
                return $query->whereRaw('(include_cities is null OR find_in_set(states.id,include_cities))')->whereRaw('(exclude_cities is null OR not find_in_set(states.id,exclude_cities))');
            });
        })
        ->where(function($a){
            return $a->whereNull('min_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) >= fbt.min_amount');
        })
        ->where(function($a){
            return $a->whereNull('max_amount')->orWhereRaw('IF(sale_price > 0, sale_price,price) <= fbt.max_amount');
        })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('tags.tagProducts', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($fbt);die();
        return $fbt;
    }
    
    static function productExpressDeliveryRegionalNewUpdated($products_id,$city = false){
        if (empty($products_id)) {
            return [];
        }
    
        $excludedWarehouses = ['OLN1', 'KUW101'];
    
        // Single query to get all product data
        $query = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
            ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
            ->leftJoin('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
            ->leftJoin('states as s', 'wc.city_id', '=', 's.id')
            ->select(
                'p.id as product_id',
                DB::raw('MAX(w.id) as id'),
                // DB::raw('MAX(w.express_name) as title'),
                // DB::raw('MAX(w.express_name_arabic) as title_arabic'),
                // DB::raw('MAX(w.express_days) as num_of_days'),
                // DB::raw('MAX(w.express_price) as price'),
                DB::raw('SUM(CASE WHEN w.ln_code IN ("OLN1", "KUW101") THEN livestock.qty ELSE 0 END) as special_qty'),
                DB::raw('SUM(CASE WHEN w.ln_code NOT IN ("OLN1", "KUW101") THEN livestock.qty ELSE 0 END) as regular_qty')
            )
            ->whereIn('p.id', $products_id)
            ->where('w.status', 1)
            ->where('w.show_in_express', 1)
            ->groupBy('p.id');
    
        // Filter by city if provided
        if ($city) {
            $query->where(function ($q) use ($city) {
                $q->where('s.name', $city)
                    ->orWhere('s.name_arabic', $city);
            });
        }
    
        $results = $query->get();
    
        // Process the results
        $data = [];
        foreach ($results as $result) {
            $totalQty = $result->regular_qty;
    
            // For Jeddah, only count special warehouses (OLN1, KUW101)
            if ($city === 'Jeddah' || $city === 'جدة') {
                $totalQty = $result->special_qty;
            } else {
                // Subtract 3 from regular quantity and add special quantity
                $totalQty = max(0, $totalQty - 3) + $result->special_qty;
            }
    
            // Limit quantity to a maximum of 10
            $totalQty = min($totalQty, 10);
    
            if ($totalQty > 0) {
                // $data[$result->product_id] = [
                //     'expressdeliveryData' => [
                //         'id' => $result->id,
                //         'title' => $result->title,
                //         'title_arabic' => $result->title_arabic,
                //         'num_of_days' => $result->num_of_days,
                //         'price' => $result->price,
                //         'qty' => $totalQty,
                //     ]
                // ];
                $data[$result->product_id] = [
                    'id' => $result->id,
                    // 'title' => $result->title,
                    // 'title_arabic' => $result->title_arabic,
                    // 'num_of_days' => $result->num_of_days,
                    // 'price' => $result->price,
                    'qty' => $totalQty,
                ];
            }
        }
    
        return $data;
    }
    
    static function productExpressDeliveryRegionalNew($id,$city = false){
        $excludedWarehouses = ['OLN1', 'KUW101'];
        $exp = false;

        // Normalize input by trimming and converting to lowercase
        $normalizedCity = trim(mb_strtolower($city, 'UTF-8'));
        $normalizedCityEN = $city;

        // Check if the city is Jeddah (both Arabic & English)
        if ($normalizedCityEN === 'Jeddah' || $normalizedCity === 'جدة') {
            // Sum quantities for only OLN1 and KUW101 for Jeddah
            $exp = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
                ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
                ->select(
                    DB::raw('MAX(w.id) as id'),
                    DB::raw('MAX(w.express_name) as title'),
                    DB::raw('MAX(w.express_name_arabic) as title_arabic'),
                    DB::raw('MAX(w.express_days) as num_of_days'),
                    DB::raw('MAX(w.express_price) as price'),
                    DB::raw('SUM(livestock.qty) as qty')
                )
                ->whereIn('w.ln_code', $excludedWarehouses)
                ->where('w.status', 1)
                ->where('w.show_in_express', 1)
                ->where('p.id', $id)
                ->groupBy('livestock.sku')
                ->havingRaw('SUM(livestock.qty) >= 1')
                ->first();

            if ($exp && $exp->qty > 10) {
                $exp->qty = 10;
            }
            return $exp;
        }
        else {
            // For other cities, sum all warehouses except OLN1 and KUW101, subtract 3, and add OLN1 and KUW101 quantities
            $exp = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
                ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
                ->join('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
                ->join('states as s', 'wc.city_id', '=', 's.id')
                ->select(
                    DB::raw('MAX(w.id) as id'),
                    DB::raw('MAX(w.express_name) as title'),
                    DB::raw('MAX(w.express_name_arabic) as title_arabic'),
                    DB::raw('MAX(w.express_days) as num_of_days'),
                    DB::raw('MAX(w.express_price) as price'),
                    DB::raw('SUM(livestock.qty) as qty')
                )
                ->where('w.status', 1)
                ->where('w.show_in_express', 1)
                ->whereNotIn('w.ln_code', $excludedWarehouses)
                ->where('p.id', $id)
                ->where(function ($query) use ($city) {
                    $query->where('s.name', $city)
                          ->orWhere('s.name_arabic', $city);
                })
                ->groupBy('livestock.sku')
                ->havingRaw('SUM(livestock.qty) >= 1')
                ->first();

            $quantitySum = $exp ? $exp->qty : 0;

            // Subtract 3 from the quantity
            $quantitySum = max(0, $quantitySum - 3);

            // Add quantities from OLN1 and KUW101
            $specialQuantities = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
                ->select(DB::raw('SUM(livestock.qty) as qty'))
                ->whereIn('w.ln_code', $excludedWarehouses)
                ->where('w.status', 1)
                ->where('w.show_in_express', 1)
                ->where('livestock.ln_sku', $id)
                ->first();

            $quantitySum += $specialQuantities ? $specialQuantities->qty : 0;

            // Limit the final quantity to a maximum of 10
            if ($exp) {
                $exp->qty = min($quantitySum, 10);
                if ($exp->qty == 0) {
                    $exp = null;
                }
            }
        }

        return $exp;

    }
    
    static function productExpressDeliveryRegional($id,$city = false){
        
        // $exp = Warehouse::
        // select(['warehouse.id','warehouse.express_name as title', 'warehouse.express_name_arabic as title_arabic', 'warehouse.express_days as num_of_days','warehouse.express_price as price', 'stock.qty'])
        // ->where('warehouse.status', 1)
        // ->where('warehouse.show_in_express', 1)
        // ->join('products as prodata', function($join) use($id) {
        //     $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        // })
        // ->join('livestock as stock', function($join) use($id) {
        //     $join->on('stock.city', 'warehouse.ln_code')
        //     ->on('stock.ln_sku', '=', 'prodata.sku');
        // })
        // ->where(function($query) use ($city){
        //     return $query->whereHas('cityData', function($q) use($city){
        //         $q->where('states.name', $city)
        //         ->orWhere('states.name_arabic', $city);
        //     });
        // })
        // ->where(function($query) use ($id) {
        //     return $query
        //     ->whereHas('liveStocksData', function($q) use ($id) {
        //         return $q->where('qty', '>=', 1)
        //         ->whereHas('productData', function($e) use ($id) {
        //             return $e->where('id', $id);
        //         });
        //     });
        // })
        // ->first();
        
        $expWarhouse = Warehouse::
            whereHas('cityData', function ($query) use ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('states.name', $city)
                    ->orWhere('states.name_arabic', $city);
                });
            })
            ->where('warehouse.status', 1)
            ->where('warehouse.show_in_express', 1)
            ->pluck('ln_code')->toArray();
        
        $exp = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
            ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
            ->join('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
            ->join('states as s', 'wc.city_id', '=', 's.id')
            ->select(
                DB::raw('MAX(w.id) as id'),
                DB::raw('MAX(w.express_name) as title'),
                DB::raw('MAX(w.express_name_arabic) as title_arabic'),
                DB::raw('MAX(w.express_days) as num_of_days'),
                DB::raw('MAX(w.express_price) as price'),
                DB::raw('SUM(livestock.qty) as qty')
                // DB::raw('CASE 
                //             WHEN SUM(livestock.qty) > 10 THEN 10 
                //             WHEN SUM(livestock.qty) > 1 THEN SUM(livestock.qty) 
                //             ELSE 0 
                //          END as qty')
            )
            ->where('w.status', 1)
            ->where('w.show_in_express', 1)
            ->where('p.id', $id)
            ->where(function ($query) use ($city) {
                $query->where('s.name', $city)
                ->orWhere('s.name_arabic', $city);
            })
            ->groupBy('livestock.sku')
            ->havingRaw('SUM(livestock.qty) >= 1');
        //print_r($expWarhouse);die;
        // if(array_search('OLN1', $expWarhouse) !== false || array_search('KUW101', $expWarhouse) !== false){
        //     $exp = $exp->select([
        //         'warehouse.id',
        //         'warehouse.express_name as title',
        //         'warehouse.express_name_arabic as title_arabic',
        //         'warehouse.express_days as num_of_days',
        //         'warehouse.express_price as price',
        //         DB::raw('CASE 
        //                     WHEN stock.qty > 10 THEN 10
        //                     WHEN stock.qty >= 1 THEN stock.qty
        //                     ELSE 0
        //                 END as qty')
        //     ])
        //     ->whereHas('liveStocksData', function ($query) use ($id) {
        //         $query->where('qty', '>=', 1)
        //               ->whereHas('productData', function ($q) use ($id) {
        //                   $q->where('id', $id);
        //               });
        //     });
        // }
        // else{
        //     $exp = $exp->select([
        //         'warehouse.id',
        //         'warehouse.express_name as title',
        //         'warehouse.express_name_arabic as title_arabic',
        //         'warehouse.express_days as num_of_days',
        //         'warehouse.express_price as price',
        //         DB::raw('CASE 
        //                     WHEN stock.qty > 10 THEN 10
        //                     WHEN stock.qty > 1 THEN stock.qty - 3
        //                     ELSE 0
        //                 END as qty')
        //     ])
        //     ->whereHas('liveStocksData', function ($query) use ($id) {
        //         $query->where('qty', '>', 1)
        //               ->whereHas('productData', function ($q) use ($id) {
        //                   $q->where('id', $id);
        //               });
        //     });
        // }
        $exp = $exp->first();
        
        if($exp && (array_search('OLN1', $expWarhouse) === false && array_search('KUW101', $expWarhouse) === false)) {
            $exp->qty = $exp->qty >= 3 ? $exp->qty - 3 : 0;
        }
        if($exp && $exp->qty > 10) {
            $exp->qty = 10;
        }
        if($exp && $exp->qty == 0) {
            $exp = null;
        }

        return $exp;
    }


    static function productExpressDelivery($id,$city = false){
        
        $fbt = ExpressDelivery
        ::select(['express_deliveries.id','express_deliveries.title', 'express_deliveries.title_arabic', 'express_deliveries.num_of_days','express_deliveries.price'])
        ->where('express_deliveries.status', 1)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        ->where(function($query) use ($city){
            return $query->whereHas('citydata', function($q) use($city){
                $q->where('states.name', $city)
                ->orWhere('states.name_arabic', $city);
            });
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($fbt);die();
        return $fbt;
    }
    
     static function productExpressDeliveryNew($id,$city = false){
        
        $fbt = ExpressDelivery
        ::select(['express_deliveries.id','express_deliveries.title', 'express_deliveries.title_arabic', 'express_deliveries.num_of_days','express_deliveries.price'])
        ->where('express_deliveries.status', 1)
        ->join('products as prodata', function($join) use($id) {
            $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        })
        ->where(function($query) use ($city){
            return $query->whereHas('citydata', function($q) use($city){
                $q->where('states.name', $city)
                ->orWhere('states.name_arabic', $city);
            });
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($fbt);die();
        return $fbt;
    }
    
    static function productBadge($id,$city = false,$mobbadge = false,$webbadge = false){
        
        $fbt = Badge::select(['image_media'])->with('BadgeSlider:id,image,title,title_arabic')
        ->where('status', 1)
        ->when($mobbadge == true, function ($q) {
            return $q->where('for_app', 1);
        })
        ->when($webbadge == true, function ($q) {
            return $q->where('for_web', 1);
        })
        // ->join('products as prodata', function($join) use($id) {
        //     $join->on('prodata.id', '=', DB::raw('"'.$id.'"'));
        // })
        // ->leftJoin('states', function($join) use($city) {
        //     $join->on('states.name', '=', DB::raw('"'.$city.'"'));
        // })
        // ->where(function($query) use ($id){
        //     return $query->whereHas('citydata', function($q) use($id){
        //         $q->where('products.id', $id);
        //     });
        // })
        ->where(function($a){
            return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        })
        ->where(function($a){
            return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->where('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->where('products.id', $id);
            })
            ;
        })
        ->first();
        // print_r($fbt);die();
        return $fbt;
    }
    //filter
    static function testBrandCatData($filters = [], $Productfaq = false, $mobimage = false, $city = 'abha'){
        // print_r($mobimage == true ? 1 :0);die;
        $data = [];
        $take = isset($filters['take']) ? $filters['take'] : 20;
        $pageNumber =  isset($filters['page']) ? $filters['page'] : 1;
        $maincat = isset($filters['cat_id']) ? $filters['cat_id'] : false;
        //brand
        $mainbrand = isset($filters['b_id']) ? $filters['b_id'] : false;
        //
        $filtercat = isset($filters['filter_cat_id']) ? $filters['filter_cat_id'] : false;
        $filterbrands = isset($filters['filter_brand_id']) ? $filters['filter_brand_id'] : false;
        $filtertags = isset($filters['filter_tag_id']) ? $filters['filter_tag_id'] : false;
        $filtermin = isset($filters['filter_min']) ? $filters['filter_min'] : false;
        $filtermax = isset($filters['filter_max']) ? $filters['filter_max'] : false;
        $filterreview = isset($filters['filter_review']) ? $filters['filter_review'] : false;
        $views = isset($filters['views']) ? $filters['views'] : false;
        $productbyid = isset($filters['productbyid']) ? $filters['productbyid'] : false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        
        $new = isset($filters['new']) ? $filters['new'] : false;
        $search = isset($filters['search']) ? $filters['search'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;

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

        
        $filtertagsall = false;
        $filtertagsprice = false;
        if($filtertags){
            $subtags = SubTags::where('tag_id','!=', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsall = $subtags;
            
            // print_r($filtertagsall);
            
            $subtags = SubTags::where('tag_id', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsprice = $subtags;
            
            // print_r($filtertagsprice);
            // die;
        }
        
        
        // print_r($filtercat);die();
        $products = Product
        ::select('products.id', 'name', 'name_arabic','short_description', 'slug','custom_badge_en','custom_badge_ar', 'products.sku', 'price', 'pre_order', 'no_of_days', 'sale_price', 'stock_data.quantity', 'feature_image', 'brands', 'best_seller', 'low_in_stock', 'top_selling','warranty','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat','promotional_price', 'promo_title_arabic', 'promo_title', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        ->with('featuredImage:id,image',
        // 'productcategory:id',
        'liveStockData.warehouseData.cityData')
        ->when($mainbrand, function ($q) use ($mainbrand) {
                return $q->where('products.brands', $mainbrand);
        })
        ->when($maincat, function ($q) use ($maincat) {
            // return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //     return $query->where('productcategories.id', $maincat);
            // });
            return $q->join('product_categories', function ($join) use ($maincat) {
                return $join->on('product_categories.product_id', '=', 'products.id')
                      ->where('product_categories.category_id', '=', $maincat);
                 });
        })
        
        ->when($new, function ($q) use ($new) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use ($search) {
            return $query->where(function($q) use($search){
                return $q
                ->where('products.meta_tag_en', 'like', "%$search%")
                ->orWhere('products.meta_tag_ar', 'like', "%$search%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->whereIn('sub_tag.id', $filtertags);
        //     });
        // })
        ->when($Productfaq == true, function ($q) {
            return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        })
        ->when($mobimage == true, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_app_image_media,name,name_arabic','brand.BrandMediaAppImage:id,image');
        })
        ->when($mobimage == false, function ($q) {
            // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
            return $q->with('brand:id,brand_image_media,name,name_arabic','brand.BrandMediaImage:id,image');
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid){
            return $q->whereIn('products.id', $productbyid);
        })
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        ->leftJoin(DB::raw("(SELECT 
            qty_table.sku, 
            CASE 
                WHEN total_qty > 10 THEN 10
                WHEN total_qty > 0 THEN total_qty
                ELSE 0
            END AS quantity
        FROM (
            SELECT 
                livestock.sku,
                GREATEST(SUM(CASE 
                                WHEN livestock.city NOT IN ('OLN1', 'KUW101') THEN livestock.qty 
                                ELSE 0 
                            END) - 3, 0) 
                + 
                SUM(CASE 
                        WHEN livestock.city IN ('OLN1', 'KUW101') THEN livestock.qty 
                        ELSE 0 
                    END) 
                AS total_qty
            FROM livestock 
            INNER JOIN warehouse 
                ON livestock.city = warehouse.ln_code 
            WHERE warehouse.status = 1 
            AND warehouse.show_in_express = 1 
            AND livestock.city in (".implode(',', $exp).")
            GROUP BY livestock.sku
        ) as qty_table) stock_data"), function($join) {
            $join->on('stock_data.sku', '=', 'products.sku');
        })
        ->groupBy('products.id', 'stock_data.quantity')
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        // ->where('products.free_gift', 0)
        ->where('stock_data.quantity', '>', 1);
        if(isset($filters['filters'])){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
            // ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
            ->when($mobimage == false, function ($q) {
                return $q->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->when($mobimage == true, function ($q) {
                return $q->with('BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details');
            })
            ->whereIn('id', $brandids)
            ->orderBy('sorting', 'asc')
            ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
            
            $data['tags'] = Tag
            ::with(['childs' => function ($q) use($ids) {
                return $q->whereHas('tagProducts', function ($query) use ($ids) {
                    return $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function ($query) use ($ids) {
                    return $query->where('product_id', $ids);
             })
            ->where('status', 1)
            ->get();
            // $mindata = $products;
            // $maxdata = $products;
            // $data['max'] = $maxdata->orderBy('price', 'desc')->first()->price;
            // $data['min'] = $mindata->orderBy('price', 'asc')->first()->price;
            
            // lef
        }
        $products = $products
        ->when($filtercat, function ($q) use ($filtercat) {
            return $q->whereHas('productcategory', function ($query) use ($filtercat) {
                return $query->where(function($a) use($filtercat){
                    return $a->whereIn('productcategories.id', $filtercat)->orWhereIn('productcategories.name', $filtercat);
                });
            });
        })
        ->when($filterbrands, function ($q) use ($filterbrands) {
            return $q->whereHas('brand', function ($query) use ($filterbrands) {
                return $query->where(function($a) use($filterbrands){
                    return $a->whereIn('brands.id', $filterbrands)->orWhereIn('brands.name', $filterbrands);
                });
            });
        })
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->where(function($a) use($filtertags){
        //             return $a->whereIn('sub_tags.id', $filtertags)->orWhereIn('sub_tags.name', $filtertags);
        //         });
        //     });
        // })
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        ->when($filtertagsprice, function ($q) use ($filtertagsprice) {
            return $q->whereHas('tags', function ($query) use ($filtertagsprice) {
                return $query->where(function($a) use($filtertagsprice){
                    return $a->whereIn('sub_tags.id', $filtertagsprice)->orWhereIn('sub_tags.name', $filtertagsprice);
                });
            });
        })
        
        ->when($filtermin, function ($q) use ($filtermin) {
            return $q->where('products.sale_price', '>=', $filtermin);
        })
        ->when($filtermax, function ($q) use ($filtermax) {
            return $q->where('products.sale_price', '<=', $filtermax);
        })
        ->when($filterreview, function ($q) use ($filterreview) {
            //print_r(implode(' or rating = ', $filterreview));die;
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
            //return $q->whereRaw('round(AVG(rating)) IN ("'.implode('","', $filterreview).'")');
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when(!$sort, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        if(isset($filters['filters'])){
            if(sizeof($products->pluck('sale_price')->toArray()) >= 1){
                $data['min'] = min($products->pluck('sale_price')->toArray());
                $data['max'] = max($products->pluck('sale_price')->toArray());
            }
        }
        $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        return $data;
    }
    
    //clone
    static function productDataRegionalCopy($filters = [], $Productfaq = false, $mobimage = false){
        // print_r($mobimage == true ? 1 :0);die;
        $data = [];
        $take = isset($filters['take']) ? $filters['take'] : 20;
        $pageNumber =  isset($filters['page']) ? $filters['page'] : 1;
        $maincat = isset($filters['cat_id']) ? $filters['cat_id'] : false;
        $filtercat = isset($filters['filter_cat_id']) ? $filters['filter_cat_id'] : false;
        $filterbrands = isset($filters['filter_brand_id']) ? $filters['filter_brand_id'] : false;
        $filtertags = isset($filters['filter_tag_id']) ? $filters['filter_tag_id'] : false;
        $filtermin = isset($filters['filter_min']) ? $filters['filter_min'] : false;
        $filtermax = isset($filters['filter_max']) ? $filters['filter_max'] : false;
        $filterreview = isset($filters['filter_review']) ? $filters['filter_review'] : false;
        $views = isset($filters['views']) ? $filters['views'] : false;
        $productbyid = isset($filters['productbyid']) ? $filters['productbyid'] : false;
        $sort = isset($filters['sort']) ? explode('-', $filters['sort']) : false;
        
        $new = isset($filters['new']) ? $filters['new'] : false;
        $search = isset($filters['search']) ? $filters['search'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;
        
        $filtertagsall = false;
        $filtertagsprice = false;
        if($filtertags){
            $subtags = SubTags::where('tag_id','!=', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsall = $subtags;
            
            // print_r($filtertagsall);
            
            $subtags = SubTags::where('tag_id', 44)->where(function($a) use($filtertags){
                return $a->whereIn('id', $filtertags)->orWhereIn('name', $filtertags);
            })->pluck('id')->toArray();
            if(sizeof($subtags))
            $filtertagsprice = $subtags;
            
            // print_r($filtertagsprice);
            // die;
        }
        
        //with column
        //lang
        $langType = isset($filters['lang']) ? $filters['lang'] : 'ar';
        //
        $withColumnName = ($langType == 'en') ? 'title' : 'title_arabic';
        $withAltColumnName = ($langType == 'en') ? 'alt' : 'alt_arabic';
        //question/answer
        $questionColumn = ($langType == 'en') ? 'question' : 'question_arabic';
        $answerColumn = ($langType == 'en') ? 'answer' : 'answer_arabic';
        //
        $columnName = ($langType == 'en') ? 'name' : 'name_arabic';
        $columnProName = ($langType == 'en') ? 'products.name' : 'products.name_arabic';
        $columnCustomBadge = ($langType == 'en') ? 'custom_badge_en' : 'custom_badge_ar';
        //
        // promotion column
        $promotion_column = ($langType == 'en') ? 'products.pormotion' : 'products.pormotion_arabic';
        $promotion_column2 = ($langType == 'en') ? 'products.badge_left' : 'products.badge_left_arabic';
        $promotion_column3 = ($langType == 'en') ? 'products.badge_right' : 'products.badge_right_arabic';
        $promotion_column4 = ($langType == 'en') ? 'products.promo_title' : 'products.promo_title_arabic';
        
        // print_r($filtercat);die();
        $products = Product
        // ::select('products.id', 'name', 'name_arabic','short_description', 'slug','custom_badge_en','custom_badge_ar', 'products.sku', 'price', 'pre_order', 'no_of_days', 'sale_price', 'stock_data.quantity', 'feature_image', 'brands', 'best_seller', 'low_in_stock', 'top_selling','warranty','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'))
        ::select('products.id','name', 'name_arabic', 'short_description', 'slug',$columnCustomBadge, 'price', 'sale_price','stock_data.quantity', 'feature_image', 'brands', 
        // 'best_seller', 'low_in_stock', 'top_selling','discounttypestatus','discountcondition','discountvalue','discountvaluecap','pricetypevat',
        'products.cdn_image',
        $promotion_column,
        'products.pormotion_color',
        $promotion_column2,
        'products.badge_left_color',
        $promotion_column3,
        'products.badge_right_color', 
        'products.vatonuspromo',
        'products.promotional_price',
        $promotion_column4,
        'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'
            
        ), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"))
        // ->addSelect([
        //     'savetype' => GeneralSettingProduct::select('discount_type')->limit(1),
        //     'newtype' => GeneralSettingProduct::select('new_badge_days')->limit(1)
        // ])
        // ,title,title_arabic,alt,alt_arabic,details
        ->with('featuredImage:id,image')
        ->when($maincat, function ($q) use ($maincat) {
            // return $q->whereHas('productcategory', function ($query) use ($maincat) {
            //     return $query->where('productcategories.id', $maincat);
            // });
            return $q->join('product_categories', function ($join) use ($maincat) {
                return $join->on('product_categories.product_id', '=', 'products.id')
                      ->where('product_categories.category_id', '=', $maincat);
                 });
        })
        
        ->when($new, function ($q) use ($new) {
            return $q->where('products.created_at', '>=', Carbon::now()->subDays(14)->toDateTimeString());
        })
        ->when($search, function ($query) use ($search) {
            return $query->where(function($q) use($search){
                return $q
                ->where('products.meta_tag_en', 'like', "%$search%")
                ->orWhere('products.meta_tag_ar', 'like', "%$search%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
        })
        
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->whereIn('sub_tag.id', $filtertags);
        //     });
        // })
         // ->when($Productfaq == true, function ($q) {
        //     return $q->with('questions:id,title,question,question_arabic,answer,answer_arabic');
        // })
        ->when($Productfaq == true, function ($q) use ($langType, $questionColumn, $answerColumn) {
            return $q->with([
                'brand' => function ($query) use ($langType, $columnName) {
                    $query->select('id', 'title', DB::raw("CASE WHEN '$langType' = 'en' THEN question ELSE question_arabic END as $questionColumn"), DB::raw("CASE WHEN '$langType' = 'en' THEN answer ELSE answer_arabic END as $answerColumn"));
                },
            ]);
        })
        // ->when($mobimage == true, function ($q) {
        //     // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
        //     return $q->with('brand:id,brand_app_image_media,name,name_arabic','brand.BrandMediaAppImage:id,image');
        // })
        ->when($mobimage == true, function ($q) use ($langType, $columnName) {
            return $q->with([
                'brand' => function ($query) use ($langType, $columnName) {
                    $query->select(
                        'id', 
                        'brand_app_image_media', 
                        DB::raw("CASE WHEN ? = 'en' THEN name ELSE name_arabic END as $columnName")
                    )->setBindings([$langType]);
                },
                'brand.BrandMediaAppImage:id,image'
            ]);
        })
        // ->when($mobimage == false, function ($q) {
        //     // ,title,title_arabic,alt,alt_arabic,details,status,name,name_arabic,slug,
        //     return $q->with('brand:id,brand_image_media,name,name_arabic','brand.BrandMediaImage:id,image');
        // })
        ->when($mobimage == false, function ($q) use ($langType, $columnName) {
            return $q->with([
                'brand' => function ($query) use ($langType, $columnName) {
                    $query->select(
                        'id', 
                        'brand_image_media', 
                        DB::raw("CASE WHEN ? = 'en' THEN name ELSE name_arabic END as $columnName")
                    )->setBindings([$langType]);
                },
                'brand.BrandMediaImage:id,image'
            ]);
        })
        ->when($views, function ($q) {
            return $q->orderBy('view_product', 'desc');
        })
        ->when($productbyid, function ($q) use ($productbyid){
            return $q->whereIn('products.id', $productbyid);
        })
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        // ->leftJoin(DB::raw("(select sku,sum(qty) as quantity from livestock group by sku) stock_data"), function($join) {
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
        ->groupBy('products.id')
        // ->orderBy('sort', 'asc')
        // ->orderBy('price', 'asc')
        ->where('products.status', 1)
        ->where('products.price', '>', 0)
        // ->where('products.free_gift', 0)
        ->where('stock_data.quantity', '>', 1);
        if(isset($filters['filters'])){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
            // ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
            // ->when($mobimage == false, function ($q) {
            //     return $q->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details');
            // })
            // ->when($mobimage == true, function ($q) {
            //     return $q->with('BrandMediaAppImage:id,image,title,title_arabic,alt,alt_arabic,details');
            // })
            ->when($mobimage == false, function ($q) use ($langType, $withColumnName, $withAltColumnName) {
                return $q->with([
                    'BrandMediaImage' => function ($query) use ($langType, $withColumnName, $withAltColumnName) {
                        $query->select('id', 'image', DB::raw("CASE WHEN '$langType' = 'en' THEN title ELSE title_arabic END as $withColumnName"),DB::raw("CASE WHEN '$langType' = 'en' THEN alt ELSE alt_arabic END as $withAltColumnName") , 'details');
                    }
                ]);
            })
            ->when($mobimage == true, function ($q) {
                 return $q->with([
                    'BrandMediaAppImage' => function ($query) use ($langType, $withColumnName, $withAltColumnName) {
                        $query->select('id', 'image', DB::raw("CASE WHEN '$langType' = 'en' THEN title ELSE title_arabic END as $withColumnName"),DB::raw("CASE WHEN '$langType' = 'en' THEN alt ELSE alt_arabic END as $withAltColumnName") , 'details');
                    }
                ]);
            })
            ->whereIn('id', $brandids)
            ->orderBy('sorting', 'asc')
            // ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media']);
            ->get(['id', 'slug', 'status', 'show_in_front', 'brand_image_media', 'brand_app_image_media',DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName")]);
            
            $data['tags'] = Tag
            ::with(['childs' => function ($q) use($ids) {
                return $q->whereHas('tagProducts', function ($query) use ($ids) {
                    return $query->whereIn('product_id', $ids);
                });
            }])
            ->whereHas('childs.tagProducts', function ($query) use ($ids) {
                    return $query->where('product_id', $ids);
             })
            ->where('status', 1)
            ->get();
            // $mindata = $products;
            // $maxdata = $products;
            // $data['max'] = $maxdata->orderBy('price', 'desc')->first()->price;
            // $data['min'] = $mindata->orderBy('price', 'asc')->first()->price;
            
            // lef
        }
        $products = $products
        ->when($filtercat, function ($q) use ($filtercat) {
            return $q->whereHas('productcategory', function ($query) use ($filtercat) {
                return $query->where(function($a) use($filtercat){
                    return $a->whereIn('productcategories.id', $filtercat)->orWhereIn('productcategories.name', $filtercat);
                });
            });
        })
        ->when($filterbrands, function ($q) use ($filterbrands) {
            return $q->whereHas('brand', function ($query) use ($filterbrands) {
                return $query->where(function($a) use($filterbrands){
                    return $a->whereIn('brands.id', $filterbrands)->orWhereIn('brands.name', $filterbrands);
                });
            });
        })
        // ->when($filtertags, function ($q) use ($filtertags) {
        //     return $q->whereHas('tags', function ($query) use ($filtertags) {
        //         return $query->where(function($a) use($filtertags){
        //             return $a->whereIn('sub_tags.id', $filtertags)->orWhereIn('sub_tags.name', $filtertags);
        //         });
        //     });
        // })
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        ->when($filtertagsprice, function ($q) use ($filtertagsprice) {
            return $q->whereHas('tags', function ($query) use ($filtertagsprice) {
                return $query->where(function($a) use($filtertagsprice){
                    return $a->whereIn('sub_tags.id', $filtertagsprice)->orWhereIn('sub_tags.name', $filtertagsprice);
                });
            });
        })
        
        ->when($filtermin, function ($q) use ($filtermin) {
            return $q->where('products.sale_price', '>=', $filtermin);
        })
        ->when($filtermax, function ($q) use ($filtermax) {
            return $q->where('products.sale_price', '<=', $filtermax);
        })
        ->when($filterreview, function ($q) use ($filterreview) {
            //print_r(implode(' or rating = ', $filterreview));die;
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
            //return $q->whereRaw('round(AVG(rating)) IN ("'.implode('","', $filterreview).'")');
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when(!$sort, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        if(isset($filters['filters'])){
            if(sizeof($products->pluck('sale_price')->toArray()) >= 1){
                $data['min'] = min($products->pluck('sale_price')->toArray());
                $data['max'] = max($products->pluck('sale_price')->toArray());
            }
            $data['products'] = $products->paginate($take, ['*'], 'page', $pageNumber);
        }
        else{
            $data['products']['data'] = $products->limit($take)->get();
        }
        return $data;
    }
    
}