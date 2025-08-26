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

class ProductListingHelperNew
{
    static function productData($filters = [], $Productfaq = false, $mobimage = false, $city = 'Jeddah'){
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
        $searchHeader = isset($filters['searchHeader']) ? $filters['searchHeader'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;
        
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

        $getCity = States::where('name', $city)
        ->orWhere('name_arabic', $city)
        ->first(['id', 'name', 'name_arabic']);

        $defaultWarehouses = ['OLN1', 'KUW101'];
        $warehouseArray = $defaultWarehouses;

        if (($searchHeader || $search) && $getCity) {
            $cityWarehouses = Warehouse::where('show_in_stock', 1)
                ->where('status', 1)
                ->where('waybill_city', $getCity->id)
                ->pluck('ln_code')
                ->toArray();

            if (!empty($cityWarehouses)) {
                $warehouseArray = array_unique(array_merge($cityWarehouses, $defaultWarehouses));
            }
        }
        //print_r($warehouseArray);die;
        
        $products = Product
        ::select('products.id','products.name', 'products.name_arabic','trendyol_price','vatonuspromo','short_description', 'products.slug','custom_badge_en','custom_badge_ar', 'products.sku', 'products.price', 'products.sale_price', 'flash_sale_price', 'flash_sale_expiry', DB::raw('SUM(stock_data.qty) as quantity'), 'feature_image', 'brands', 'best_seller', 'low_in_stock', 'top_selling','discounttypestatus','discountcondition','discountvalue','discountvaluecap','promotional_price', 'promo_title_arabic', 'promo_title', 'badge_left', 'badge_left_arabic', 'badge_left_color', 'badge_right', 'badge_right_arabic', 'badge_right_color', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'),DB::raw("'1' as savetype"),DB::raw("'0' as newtype"), 'specification_image', 'save_type', 'specification_image_one', 'specification_image_two', 'specification_image_three', 'specification_image_four', 'specification_image_five' , 'specification_image_six', 'gift_image', 'hide_on_frontend'
        ,'cashback_amount',
        'cashback_title',
        'cashback_title_arabic'
        )
        ->with('featuredImage:id,image',
        'multiFreeGiftData:id,product_id,free_gift_sku,free_gift_qty',
        'multiFreeGiftData.productSkuData:id,sku,name,name_arabic,sale_price,price,brands,slug,pre_order,feature_image,no_of_days',
        'multiFreeGiftData.productSkuData.featuredImage:id,image',
        'multiFreeGiftData.productSkuData.brand:id,name,name_arabic'
        )
        ->when($maincat, function ($q) use ($maincat) {
            
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
                // ->orWhere('products.sku', 'like', "%$search%");
            });
            
        })

        ->when($searchHeader, function ($query) use ($searchHeader) {
            return $query->where(function($q) use($searchHeader){
                return $q
                    ->where('products.meta_tag_en', 'like', "%$searchHeader%")
                    ->orWhere('products.meta_tag_ar', 'like', "%$searchHeader%");
                    // ->where('products.name', 'like', "%$searchHeader%")
                    // ->orWhere('products.sku', 'like', "%$searchHeader%");
                    // ->orWhere('products.name_arabic', 'like', "%$searchHeader%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
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
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        
        ->leftJoin(DB::raw('livestock as stock_data'), function ($join) use($warehouseArray) {
            $join->on('stock_data.sku', '=', 'products.sku')
                 ->whereIn('stock_data.city', $warehouseArray);
        })
        ->groupBy(['products.id'])
        
        ->where('products.status', 1)
        ->where('products.hide_on_frontend', 0)
        ->where('products.price', '>', 0)
        ->having(DB::raw('SUM(stock_data.qty)'), '>=', 1);
        // ->havingRaw('SUM(stock_data.qty) > 1');
        // ->where('SUM(stock_data.qty)', '>', 1);
        if(isset($filters['filters']) && $filters['filters']){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
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
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        // ->when($filtertagsall, function ($q) use ($filtertagsall) {
        //     return $q->leftJoin('product_tag as pt', 'pt.product_id', '=', 'products.id')
        //              ->leftJoin('sub_tags as st', 'st.id', '=', 'pt.sub_tag_id')
        //              ->whereIn('st.id', $filtertagsall);
        // })

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
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when($searchHeader, function ($q) {
            return $q->orderByRaw("CASE WHEN products.brands = '22' THEN 0 ELSE 1 END")
            ->inRandomOrder()
            ->groupBy('products.id')
            ->orderBy('sort', 'asc');
        })
        ->when(!$sort && !$searchHeader, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        
        if(isset($filters['filters']) && $filters['filters']){
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

    static function productDataUpdated($filters = [], $Productfaq = false, $mobimage = false){
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
        $searchHeader = isset($filters['searchHeader']) ? $filters['searchHeader'] : false;
        $rating = isset($filters['rating']) ? $filters['rating'] : false;
        
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
        ::select('products.id','products.name', 'products.name_arabic', 'products.slug','products.sku', 'products.price', 'products.sale_price', 'flash_sale_price', 'flash_sale_expiry', DB::raw('SUM(stock_data.qty) as quantity'), 'feature_image', 'brands','promotional_price', 'promo_title_arabic', 'promo_title', 'badge_left', 'badge_left_arabic', 'badge_left_color', 'badge_right', 'badge_right_arabic', 'badge_right_color', 'products.created_at', DB::raw('round(SUM(product_review.rating) / COUNT(product_review.id)) as rating'), DB::raw('COUNT(product_review.id) as totalrating'),'specification_image', 'save_type', 'specification_image_one', 'specification_image_two', 'specification_image_three', 'specification_image_four', 'specification_image_five' , 'specification_image_six', 'gift_image', 'hide_on_frontend'
        ,'cashback_amount',
        'cashback_title',
        'cashback_title_arabic'
        )
        ->with('featuredImage:id,image',
        'multiFreeGiftData:id,product_id,free_gift_sku,free_gift_qty',
        'multiFreeGiftData.productSkuData:id,sku,name,name_arabic,sale_price,price,brands,slug,pre_order,feature_image,no_of_days',
        'multiFreeGiftData.productSkuData.featuredImage:id,image',
        'multiFreeGiftData.productSkuData.brand:id,name,name_arabic'
        )
        ->when($maincat, function ($q) use ($maincat) {
            
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

        ->when($searchHeader, function ($query) use ($searchHeader) {
            return $query->where(function($q) use($searchHeader){
                return $q->where('products.name', 'like', "%$searchHeader%")
                    ->orWhere('products.sku', 'like', "%$searchHeader%")
                    ->orWhere('products.name_arabic', 'like', "%$searchHeader%");
            });
            
        })
        
        ->when($rating, function ($q) use ($rating) {
            return $q->orderBy('rating', 'desc')->having('rating', '>=', 1);
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
        ->leftJoin('product_review', function($join) {
            $join->on('product_review.product_sku', '=', 'products.sku');
        })
        
        ->leftJoin(DB::raw('livestock as stock_data'), function ($join) {
            $join->on('stock_data.sku', '=', 'products.sku')
                 ->whereIn('stock_data.city', ['OLN1', 'KUW101']);
        })
        ->groupBy(['products.id'])
        
        ->where('products.status', 1)
        ->where('products.hide_on_frontend', 0)
        ->where('products.price', '>', 0)
        ->having(DB::raw('SUM(stock_data.qty)'), '>', 1);
        // ->havingRaw('SUM(stock_data.qty) > 1');
        // ->where('SUM(stock_data.qty)', '>', 1);
        if(isset($filters['filters']) && $filters['filters']){
            
            $brandids = $products->pluck('brands')->toArray();
            $ids = $products->pluck('id')->toArray();
            $data['brands'] = Brand
            ::where('status', 1)
            ->where('show_in_front', 1)
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
        ->when($filtertagsall, function ($q) use ($filtertagsall) {
            return $q->whereHas('tags', function ($query) use ($filtertagsall) {
                return $query->where(function($a) use($filtertagsall){
                    return $a->whereIn('sub_tags.id', $filtertagsall);
                });
            });
        })
        // ->when($filtertagsall, function ($q) use ($filtertagsall) {
        //     return $q->leftJoin('product_tag as pt', 'pt.product_id', '=', 'products.id')
        //              ->leftJoin('sub_tags as st', 'st.id', '=', 'pt.sub_tag_id')
        //              ->whereIn('st.id', $filtertagsall);
        // })
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
            return $q->havingRaw('rating = '.implode(' or rating = ', $filterreview));
        })
         ->when($sort, function ($q) use($sort) {
            return $q->orderBy($sort[0], $sort[1]);
        })
        ->when($searchHeader, function ($q) {
            return $q->orderByRaw("CASE WHEN products.brands = '22' THEN 0 ELSE 1 END")
            ->inRandomOrder()
            ->groupBy('products.id')
            ->orderBy('sort', 'asc');
        })
        ->when(!$sort && !$searchHeader, function ($q) {
            return $q->orderBy('sort', 'asc')->orderBy('sale_price', 'asc');
        });
        
        if(isset($filters['filters']) && $filters['filters']){
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