<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Helper\ProductListingHelper;
use App\Jobs\CategoryViewJob;
use App\Models\User;
use App\Models\Wishlists;

class CategoryController extends Controller
{
    public function CatProductsMobile($slug,Request $request) {
        $requestdata = $request->all();

        $category = Productcategory
        ::with(['filtercategory' => function ($q) {
            $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id','name', 'name_arabic', 'icon', 'image_link_app');
        }, 'filtercategory.parentData:id,name,name_arabic,image_link_app' , 'child:name,name_arabic,id,slug,parent_id,image_link_app','WebMediaImage:id,image'])
        ->where('slug', $slug)->first(['id', 'slug', 'name', 'name_arabic', 'image_link_app','meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media']);
        $filters = ['take' => 10, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'cat_id' => $category->id, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $Productfaq = true;
        $mobimage = true;
        $productData = ProductListingHelper::productData($filters, $Productfaq, $mobimage); 
        $id = $category->id;

        // Breadcrumbs
        $breads = [];
        $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
        if(isset($breadcrumb) && $breadcrumb != null) {
            $breads['breadcrumb'] = $breadcrumb;
            $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
            if(isset($childcat) && $childcat != null) {
                $breads['childcat'] = $childcat;
                $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                if(isset($parentcat) && $parentcat != null) {
                    $breads['parentcat'] = $parentcat;
                }
            }
        }


        // Product Extra Data multi
        $extra_multi_data = [];
        if(isset($requestdata['city'])) {
            if(isset($productData['products']) && sizeof($productData['products'])) {
                $products_id = $productData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $extra_multi_data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false);
                    $wishlistData = false;
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$requestdata['city']);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSale($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    // $fbtData = ProductListingHelper::productFBT($id,$requestdata['city']);
                    // if($fbtData)
                    //     $extra_multi_data[$id]['fbtData'] = $fbtData;
                    // $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,$requestdata['city']);
                    // if($expressdeliveryData)
                    //     $extra_multi_data[$id]['expressdeliveryData'] = $expressdeliveryData;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$requestdata['city'],$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    if(isset($requestdata['user_id'])) {
                        $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                    }
                    $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                }
            }
        }

        // Wishlists Data 
        // $wishlist_data = [];
        // if(isset($requestdata['user_id'])) {
        //     if(isset($productData['products']) && sizeof($productData['products'])) {
        //         $products_id = $productData['products']->pluck('id')->toArray();
        //         foreach($products_id as $wid){
        //             $wishlist_data[$wid] = array('wishlist'=>false);
        //             $wishlistData = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$wid)->first();
        //             if($wishlistData)
        //                 $wishlist_data[$wid]['wishlist'] = true;
        //         }
        //     }
        // }


        CategoryViewJob::dispatch($category->id);
        $response = [
            'category' => $category,
            'productData' => $productData,
            'breadcrumb' => $breads,
            'extra_multi_data' => $extra_multi_data,
            // 'wishlist_data' => $wishlist_data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function MobCategoryListing() {
        $cats = Productcategory::whereNotIn('id', [107,108,109])
        ->select('id', 'name', 'name_arabic', 'slug', 'parent_id', 'mobile_image_media', 'sort')
        ->where('menu', 1)
        ->where('status', 1)
        ->whereNotNull('parent_id')
        ->orderBy('sort', 'ASC')
        ->with('MobileMediaAppImage:id,image')->get();
        
        $response = [
            'category' => $cats,
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