<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\FlashSale;
use App\Models\Brand;
use App\Models\MobileHomePage;
use App\Models\Slider;
use App\Models\Wishlists;
use App\Models\PriceAlert;
use App\Models\StockAlert;
use App\Helper\ProductListingHelper;
use DB;

class HomePageApiController extends Controller
{
    public function HomePageFrontend(Request $request) {
        
        $first_pro_link_viewall = false;
        $second_pro_link_viewall = false;
        $third_pro_link_viewall = false;
        $fourth_pro_link_viewall = false;
        $fifth_pro_link_viewall = false;
        
        $homepageData = MobileHomePage::first();
        $imagestatus = $homepageData->images_status == 1 ? true : false;
        $servicestatus = $homepageData->services_status == 1 ? true : false;
        $homepageselectedData = MobileHomePage::select(['id','cat_sec_heading', 'cat_sec_heading_arabic','cats_view_all_link', 'cats_view_all_link_arabic', 'cat_viewall_link','cat_sec_status',
        'brands_heading','brands_heading_arabic', 'brands_view_all_link', 'brands_view_all_link_arabic','brand_link_viewall','brands_status', 'first_pro_heading', 'first_pro_heading_arabic',
        'first_pro_view_all_link', 'first_pro_view_all_link_arabic', 'first_pro_link_viewall','first_pro_status', 'second_pro_heading', 'second_pro_heading_arabic','second_pro_view_all_link',
        'second_pro_view_all_link_arabic','second_pro_link_viewall','first_pro_view_all_link_arabic','first_pro_link_viewall',
        'second_pro_status', 'flash_sale_heading','flash_sale_heading_arabic','flash_sale_view_all', 'flash_sale_view_all_arabic','flash_sale_link_viewall','flash_sale_status', 'images_heading', 'images_heading_arabic', 'image_view_all',
        'image_view_all_arabic','image_link_viewall','images_status','first_text_editor_status','third_pro_heading', 'third_pro_heading_arabic', 'third_pro_view_all_link',
        'third_pro_view_all_link_arabic', 'third_pro_link_viewall','third_pro_status', 'second_text_editor_status', 'services_heading', 'services_heading_arabic', 'services_view_all',
        'services_view_all_arabic','services_link_viewall','services_status', 'third_text_editor_status','fourth_pro_heading', 'fourth_pro_heading_arabic', 'fourth_pro_view_all_link',
        'fourth_pro_view_all_link_arabic', 'fourth_pro_link_viewall','fourth_pro_status','fifth_pro_heading', 'fifth_pro_heading_arabic', 'fifth_pro_view_all_link',
        'fifth_pro_view_all_link_arabic', 'fifth_pro_link_viewall','fifth_pro_status'])
        ->when($imagestatus == true, function ($q) {
            return $q->with('images:id,mobile_home_page_id,link,image,image_arabic', 'images.FeaturedImage:id,image', 'images.FeaturedImageArabic:id,image');
        })
        ->when($servicestatus == true, function ($q) {
            return $q->with('services:id,mobile_home_page_id,link,image,image_arabic', 'services.FeaturedImage:id,image', 'services.FeaturedImageArabic:id,image');
        })->first();

        if($homepageData->first_pro_status == 1) {
            $first_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->first_pro_link_viewall)->where('status',1)->first();
        }

        if($homepageData->second_pro_status == 1) {
            $second_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->second_pro_link_viewall)->where('status',1)->first();
        }

        if($homepageData->third_pro_status == 1) {
            $third_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->third_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->fourth_pro_status == 1) {
            $fourth_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->fourth_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->fifth_pro_status == 1) {
            $fifth_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->fifth_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->cat_sec_status == 1){
            $categoryids = explode(',',$homepageData->cats_first_line);
            $firstlinecategory = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'mobile_image_media', 'icon')->withCount('productname')->with('MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $homepageData->cats_first_line)")->where('status',1)->get();
            $secondcategoryids = explode(',',$homepageData->cats_second_line);
            $secondlinecategory = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'mobile_image_media', 'icon')->withCount('productname')->with('MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$secondcategoryids)->orderByRaw("FIELD(id, $homepageData->cats_second_line)")->where('status',1)->get();
        }else{
            $firstlinecategory = [];
            $secondlinecategory = [];
        }
        
        
        if($homepageData->first_pro_status == 1 && isset($homepageData->first_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->first_products);
            $mobimage = true;
            $productFirstData = ProductListingHelper::productData($filters,false, $mobimage);
        }else{
            $productFirstData = [];
        }
        
        if($homepageData->brands_status == 1){
            $brandids = explode(',',$homepageData->brands);
            $brands = Brand::withCount('productname')->with(['BrandMediaAppImage:id,image,desktop,mobile'])->has('category', '>=', 4)->whereIn('id',$brandids)->orderByRaw("FIELD(id, $homepageData->brands)")->where('status',1)->select(['id','name','name_arabic','slug','brand_app_image_media','status'])->get();
            foreach ($brands as $brand) {
                $categories = Productcategory::select('name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'image_link_app')
                    ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                    ->where('brand_category.brand_id', $brand->id)
                    ->limit(4)
                    ->get();
                $brand->categories = $categories;
            }
        }else{
            $brands = [];
        }
        
        
        if($homepageData->second_pro_status == 1 && isset($homepageData->second_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->second_products);
            $mobimage = true;
            $productSecondtData = ProductListingHelper::productData($filters,false, $mobimage);
        }else{
            $productSecondtData = [];
        }
        
        if($homepageData->flash_sale_status == 1){
            $firstflashsaleid = explode(',',$homepageData->first_flash_sale);
            $firstflash = FlashSale::with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'featuredImageApp:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
            ->whereIn('id',$firstflashsaleid)
            // ->orderByRaw("FIELD(id, $homepageData->first_flash_sale)")
            ->where('status', 1)
            ->where('left_quantity', '>=', 1)
            ->where(function($a){
                return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
            })
            ->where(function($a){
                return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
            })
            ->get(['image', 'image_app', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'end_date']);
            
            
        }else{
            $firstflash = [];
        }
        
        if($homepageData->flash_sale_sec_status == 1){
            $secondflashsaleid = explode(',',$homepageData->second_flash_sale);
            $secondflash = FlashSale::with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'featuredImageApp:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
            ->whereIn('id',$secondflashsaleid)
            // ->orderByRaw("FIELD(id, $homepageData->second_flash_sale)")
            ->where('status', 1)
            ->where('left_quantity', '>=', 1)
            ->where(function($a){
                return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
            })
            ->where(function($a){
                return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
            })
            ->get(['image', 'image_app', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'start_date','end_date']);
        }
        else {
            $secondflash = [];
        }
        
        if($homepageData->first_text_editor_status == 1){
            $firsttexteditordata = $homepageData->first_text_editor_data;
            $firsttexteditordataarabic = $homepageData->first_text_editor_data_arabic;
        }else{
            $firsttexteditordata = [];
            $firsttexteditordataarabic = [];
        }
        
        if($homepageData->third_pro_status == 1 && isset($homepageData->third_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->third_products);
            $mobimage = true;
            $productThirdData = ProductListingHelper::productData($filters,false, $mobimage);
        }else{
            $productThirdData =[];
        }
        
        if($homepageData->fourth_pro_status == 1 && isset($homepageData->fourth_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->fourth_products);
            $mobimage = true;
            $productFourthData = ProductListingHelper::productData($filters,false, $mobimage);
        }else{
            $productFourthData =[];
        }
        
        if($homepageData->fifth_pro_status == 1 && isset($homepageData->fifth_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->fifth_products);
            $mobimage = true;
            $productFifthData = ProductListingHelper::productData($filters,false, $mobimage);
        }else{
            $productFifthData =[];
        }
        
        if($homepageData->second_text_editor_status == 1){
            $secondtexteditordata = $homepageData->second_text_editor_data;
            $secondtexteditordataarabic = $homepageData->second_text_editor_data_arabic;
        }else{
            $secondtexteditordata = [];
            $secondtexteditordataarabic = [];
        }
        
        if($homepageData->third_text_editor_status == 1){
            $thirdtexteditordata = $homepageData->third_text_editor_data;
            $thirdtexteditordataarabic = $homepageData->third_text_editor_data_arabic;
        }else{
            $thirdtexteditordata = [];
            $thirdtexteditordataarabic = [];
        }
        
        // 1st Product Extra Data multi
        $firstpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFirstData['products']) && sizeof($productFirstData['products'])) {
                $products_id = $productFirstData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $firstpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $firstpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $firstpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $firstpro_extra_multi_data[$id]['flashData'] = $flashData;
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
                        $firstpro_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $firstpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $firstpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        // 2nd Product Extra Data multi
        $secondpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productSecondtData['products']) && sizeof($productSecondtData['products'])) {
                $products_id = $productSecondtData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $secondpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' => false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $secondpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $secondpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $secondpro_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlistData = false;
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $PriceAlertData = false;
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $StockAlertData = false;
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $secondpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $secondpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $secondpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                }
            }
        }
        
        // 3rd Product Extra Data multi
        $thirdpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productThirdData['products']) && sizeof($productThirdData['products'])) {
                $products_id = $productThirdData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $thirdpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $thirdpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $thirdpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $thirdpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $thirdpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $thirdpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $thirdpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        
        // 4th Product Extra Data multi
        $fourthpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFourthData['products']) && sizeof($productFourthData['products'])) {
                $products_id = $productFourthData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $fourthpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $fourthpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $fourthpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $fourthpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $fourthpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $fourthpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $fourthpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        
        // 5th Product Extra Data multi
        $fifthpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFifthData['products']) && sizeof($productFifthData['products'])) {
                $products_id = $productFifthData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $fifthpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $fifthpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $fifthpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $fifthpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $fifthpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $fifthpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $fifthpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        // $combined_data = array_merge($firstpro_extra_multi_data, $secondpro_extra_multi_data, $thirdpro_extra_multi_data);
        // $combined_data = [
        //     $firstpro_extra_multi_data,
        //     $secondpro_extra_multi_data,
        //     $thirdpro_extra_multi_data
        // ];
        
        $combined_data = [];

        // Merge data from the firstpro_extra_multi_data
        foreach ($firstpro_extra_multi_data as $id => $data) {
            $data['id'] = $id; // Include the ID in the data
            $combined_data[$id] = $data;
        }
        
        // Merge data from the secondpro_extra_multi_data
        foreach ($secondpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the thirdpro_extra_multi_data
        foreach ($thirdpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the fourthpro_extra_multi_data
        foreach ($fourthpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the fifthpro_extra_multi_data
        foreach ($fifthpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        $slidersData = Slider::where('status', 1)
        ->whereRaw("FIND_IN_SET('1', slider_devices)")
        ->orderBy('sorting', 'asc')
        ->with('featuredImageApp:id,image', 'cat:id,name,name_arabic,slug', 'pro:id,name,name_arabic,slug', 'brand:id,name,name_arabic,slug', 'subtag:id,name,name_arabic')
        ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'video_link_web', 'video_link_app', 'video_interval_web', 'video_interval_app', 'slider_devices', 'redirection_type'
        , 'brand_id', 'sub_tag_id', 'product_id', 'category_id', 'custom_link', 'sorting', 'status', 'image_mobile')->get();
        
        
        
        // // wishlist
        // $wishlistData = false;
        // if($user_id != '') {
        //     // print_r($user_id);die;
        //     $wishlists = Wishlists::where('user_id',$user_id)->get();
        //     if($wishlists){
        //         $wishlistData = true;
        //     }
        // }
        
        $homepageData = [
            'homepageData'=> $homepageselectedData,
            'first_pro_link_viewall' => $first_pro_link_viewall,
            'second_pro_link_viewall' => $second_pro_link_viewall,
            'third_pro_link_viewall' => $third_pro_link_viewall,
            'fourth_pro_link_viewall' => $fourth_pro_link_viewall,
            'fifth_pro_link_viewall' => $fifth_pro_link_viewall,
            'firstlinecategories' => $firstlinecategory,
            'secondlinecategories' => $secondlinecategory,
            'productFirstData' => $productFirstData,
            'brands' => $brands,
            'productSecondData' => $productSecondtData,
            'firstflash' => $firstflash,
            'secondflash' => $secondflash,
            'firsttexteditordata' => $firsttexteditordata,
            'firsttexteditordataarabic' => $firsttexteditordataarabic,
            'productThirdData' => $productThirdData,
            'productFourthData' => $productFourthData,
            'productFifthData' => $productFifthData,
            'secondtexteditordata' => $secondtexteditordata,
            'secondtexteditordataarabic' => $secondtexteditordataarabic,
            'thirdtexteditordata' => $thirdtexteditordata,
            'thirdtexteditordataarabic' => $thirdtexteditordataarabic,
            'extra_multi_data' => $combined_data,
            'slidersData' => $slidersData
        ];
        
        $response = [
            'home_page_data' => $homepageData
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
    public function HomePageFrontendRegional(Request $request) {
        
        $first_pro_link_viewall = false;
        $second_pro_link_viewall = false;
        $third_pro_link_viewall = false;
        $fourth_pro_link_viewall = false;
        $fifth_pro_link_viewall = false;
        
        $homepageData = MobileHomePage::first();
        $imagestatus = $homepageData->images_status == 1 ? true : false;
        $servicestatus = $homepageData->services_status == 1 ? true : false;
        $homepageselectedData = MobileHomePage::select(['id','cat_sec_heading', 'cat_sec_heading_arabic','cats_view_all_link', 'cats_view_all_link_arabic', 'cat_viewall_link','cat_sec_status',
        'brands_heading','brands_heading_arabic', 'brands_view_all_link', 'brands_view_all_link_arabic','brand_link_viewall','brands_status', 'first_pro_heading', 'first_pro_heading_arabic',
        'first_pro_view_all_link', 'first_pro_view_all_link_arabic', 'first_pro_link_viewall','first_pro_status', 'second_pro_heading', 'second_pro_heading_arabic','second_pro_view_all_link',
        'second_pro_view_all_link_arabic','second_pro_link_viewall','first_pro_view_all_link_arabic','first_pro_link_viewall',
        'second_pro_status', 'flash_sale_heading','flash_sale_heading_arabic','flash_sale_view_all', 'flash_sale_view_all_arabic','flash_sale_link_viewall','flash_sale_status', 'images_heading', 'images_heading_arabic', 'image_view_all',
        'image_view_all_arabic','image_link_viewall','images_status','first_text_editor_status','third_pro_heading', 'third_pro_heading_arabic', 'third_pro_view_all_link',
        'third_pro_view_all_link_arabic', 'third_pro_link_viewall','third_pro_status', 'second_text_editor_status', 'services_heading', 'services_heading_arabic', 'services_view_all',
        'services_view_all_arabic','services_link_viewall','services_status', 'third_text_editor_status','fourth_pro_heading', 'fourth_pro_heading_arabic', 'fourth_pro_view_all_link',
        'fourth_pro_view_all_link_arabic', 'fourth_pro_link_viewall','fourth_pro_status','fifth_pro_heading', 'fifth_pro_heading_arabic', 'fifth_pro_view_all_link',
        'fifth_pro_view_all_link_arabic', 'fifth_pro_link_viewall','fifth_pro_status'])
        ->when($imagestatus == true, function ($q) {
            return $q->with('images:id,mobile_home_page_id,link,image,image_arabic', 'images.FeaturedImage:id,image', 'images.FeaturedImageArabic:id,image');
        })
        ->when($servicestatus == true, function ($q) {
            return $q->with('services:id,mobile_home_page_id,link,image,image_arabic', 'services.FeaturedImage:id,image', 'services.FeaturedImageArabic:id,image');
        })->first();

        if($homepageData->first_pro_status == 1) {
            $first_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->first_pro_link_viewall)->where('status',1)->first();
        }

        if($homepageData->second_pro_status == 1) {
            $second_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->second_pro_link_viewall)->where('status',1)->first();
        }

        if($homepageData->third_pro_status == 1) {
            $third_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->third_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->fourth_pro_status == 1) {
            $fourth_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->fourth_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->fifth_pro_status == 1) {
            $fifth_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->fifth_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->cat_sec_status == 1){
            $categoryids = explode(',',$homepageData->cats_first_line);
            $firstlinecategory = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'mobile_image_media', 'icon')->withCount('productname')->with('MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $homepageData->cats_first_line)")->where('status',1)->get();
            $secondcategoryids = explode(',',$homepageData->cats_second_line);
            $secondlinecategory = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'mobile_image_media', 'icon')->withCount('productname')->with('MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$secondcategoryids)->orderByRaw("FIELD(id, $homepageData->cats_second_line)")->where('status',1)->get();
        }else{
            $firstlinecategory = [];
            $secondlinecategory = [];
        }
        
        
        if($homepageData->first_pro_status == 1 && isset($homepageData->first_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->first_products);
            $mobimage = true;
            $productFirstData = ProductListingHelper::productDataRegional($filters,false, $mobimage);
        }else{
            $productFirstData = [];
        }
        
        if($homepageData->brands_status == 1){
            $brandids = explode(',',$homepageData->brands);
            $brands = Brand::withCount('productname')->with(['BrandMediaAppImage:id,image,desktop,mobile'])->has('category', '>=', 4)->whereIn('id',$brandids)->orderByRaw("FIELD(id, $homepageData->brands)")->where('status',1)->select(['id','name','name_arabic','slug','brand_app_image_media','status'])->get();
            foreach ($brands as $brand) {
                $categories = Productcategory::select('name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'image_link_app')
                    ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                    ->where('brand_category.brand_id', $brand->id)
                    ->limit(4)
                    ->get();
                $brand->categories = $categories;
            }
        }else{
            $brands = [];
        }
        
        
        if($homepageData->second_pro_status == 1 && isset($homepageData->second_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->second_products);
            $mobimage = true;
            $productSecondtData = ProductListingHelper::productDataRegional($filters,false, $mobimage);
        }else{
            $productSecondtData = [];
        }
        
        if($homepageData->flash_sale_status == 1){
            $firstflashsaleid = explode(',',$homepageData->first_flash_sale);
            $firstflash = FlashSale::with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'featuredImageApp:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
            ->whereIn('id',$firstflashsaleid)
            // ->orderByRaw("FIELD(id, $homepageData->first_flash_sale)")
            ->where('status', 1)
            ->where('left_quantity', '>=', 1)
            ->where(function($a){
                return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
            })
            ->where(function($a){
                return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
            })
            ->get(['image', 'image_app', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'end_date']);
            
            
        }else{
            $firstflash = [];
        }
        
        if($homepageData->flash_sale_sec_status == 1){
            $secondflashsaleid = explode(',',$homepageData->second_flash_sale);
            $secondflash = FlashSale::with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'featuredImageApp:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
            ->whereIn('id',$secondflashsaleid)
            // ->orderByRaw("FIELD(id, $homepageData->second_flash_sale)")
            ->where('status', 1)
            ->where('left_quantity', '>=', 1)
            ->where(function($a){
                return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
            })
            ->where(function($a){
                return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
            })
            ->get(['image', 'image_app', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'start_date','end_date']);
        }
        else {
            $secondflash = [];
        }
        
        if($homepageData->first_text_editor_status == 1){
            $firsttexteditordata = $homepageData->first_text_editor_data;
            $firsttexteditordataarabic = $homepageData->first_text_editor_data_arabic;
        }else{
            $firsttexteditordata = [];
            $firsttexteditordataarabic = [];
        }
        
        if($homepageData->third_pro_status == 1 && isset($homepageData->third_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->third_products);
            $mobimage = true;
            $productThirdData = ProductListingHelper::productDataRegional($filters,false, $mobimage);
        }else{
            $productThirdData =[];
        }
        
        if($homepageData->fourth_pro_status == 1 && isset($homepageData->fourth_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->fourth_products);
            $mobimage = true;
            $productFourthData = ProductListingHelper::productDataRegional($filters,false, $mobimage);
        }else{
            $productFourthData =[];
        }
        
        if($homepageData->fifth_pro_status == 1 && isset($homepageData->fifth_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->fifth_products);
            $mobimage = true;
            $productFifthData = ProductListingHelper::productDataRegional($filters,false, $mobimage);
        }else{
            $productFifthData =[];
        }
        
        if($homepageData->second_text_editor_status == 1){
            $secondtexteditordata = $homepageData->second_text_editor_data;
            $secondtexteditordataarabic = $homepageData->second_text_editor_data_arabic;
        }else{
            $secondtexteditordata = [];
            $secondtexteditordataarabic = [];
        }
        
        if($homepageData->third_text_editor_status == 1){
            $thirdtexteditordata = $homepageData->third_text_editor_data;
            $thirdtexteditordataarabic = $homepageData->third_text_editor_data_arabic;
        }else{
            $thirdtexteditordata = [];
            $thirdtexteditordataarabic = [];
        }
        
        // 1st Product Extra Data multi
        $firstpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFirstData['products']) && sizeof($productFirstData['products'])) {
                $products_id = $productFirstData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $firstpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $firstpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $firstpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $firstpro_extra_multi_data[$id]['flashData'] = $flashData;
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
                        $firstpro_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $firstpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $firstpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        // 2nd Product Extra Data multi
        $secondpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productSecondtData['products']) && sizeof($productSecondtData['products'])) {
                $products_id = $productSecondtData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $secondpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' => false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $secondpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $secondpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $secondpro_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlistData = false;
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $PriceAlertData = false;
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $StockAlertData = false;
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $secondpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $secondpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $secondpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                }
            }
        }
        
        // 3rd Product Extra Data multi
        $thirdpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productThirdData['products']) && sizeof($productThirdData['products'])) {
                $products_id = $productThirdData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $thirdpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $thirdpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $thirdpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $thirdpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $thirdpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $thirdpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $thirdpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        
        // 4th Product Extra Data multi
        $fourthpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFourthData['products']) && sizeof($productFourthData['products'])) {
                $products_id = $productFourthData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $fourthpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $fourthpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $fourthpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $fourthpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $fourthpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $fourthpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $fourthpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        
        // 5th Product Extra Data multi
        $fifthpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFifthData['products']) && sizeof($productFifthData['products'])) {
                $products_id = $productFifthData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $fifthpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $fifthpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $fifthpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSale($id);
                    if($flashData)
                        $fifthpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $fifthpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $fifthpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $fifthpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        // $combined_data = array_merge($firstpro_extra_multi_data, $secondpro_extra_multi_data, $thirdpro_extra_multi_data);
        // $combined_data = [
        //     $firstpro_extra_multi_data,
        //     $secondpro_extra_multi_data,
        //     $thirdpro_extra_multi_data
        // ];
        
        $combined_data = [];

        // Merge data from the firstpro_extra_multi_data
        foreach ($firstpro_extra_multi_data as $id => $data) {
            $data['id'] = $id; // Include the ID in the data
            $combined_data[$id] = $data;
        }
        
        // Merge data from the secondpro_extra_multi_data
        foreach ($secondpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the thirdpro_extra_multi_data
        foreach ($thirdpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the fourthpro_extra_multi_data
        foreach ($fourthpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the fifthpro_extra_multi_data
        foreach ($fifthpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        $slidersData = Slider::where('status', 1)
        ->whereRaw("FIND_IN_SET('1', slider_devices)")
        ->orderBy('sorting', 'asc')
        ->with('featuredImageApp:id,image', 'cat:id,name,name_arabic,slug', 'pro:id,name,name_arabic,slug', 'brand:id,name,name_arabic,slug', 'subtag:id,name,name_arabic')
        ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'video_link_web', 'video_link_app', 'video_interval_web', 'video_interval_app', 'slider_devices', 'redirection_type'
        , 'brand_id', 'sub_tag_id', 'product_id', 'category_id', 'custom_link', 'sorting', 'status', 'image_mobile')->get();
        
        
        
        // // wishlist
        // $wishlistData = false;
        // if($user_id != '') {
        //     // print_r($user_id);die;
        //     $wishlists = Wishlists::where('user_id',$user_id)->get();
        //     if($wishlists){
        //         $wishlistData = true;
        //     }
        // }
        
        $homepageData = [
            'homepageData'=> $homepageselectedData,
            'first_pro_link_viewall' => $first_pro_link_viewall,
            'second_pro_link_viewall' => $second_pro_link_viewall,
            'third_pro_link_viewall' => $third_pro_link_viewall,
            'fourth_pro_link_viewall' => $fourth_pro_link_viewall,
            'fifth_pro_link_viewall' => $fifth_pro_link_viewall,
            'firstlinecategories' => $firstlinecategory,
            'secondlinecategories' => $secondlinecategory,
            'productFirstData' => $productFirstData,
            'brands' => $brands,
            'productSecondData' => $productSecondtData,
            'firstflash' => $firstflash,
            'secondflash' => $secondflash,
            'firsttexteditordata' => $firsttexteditordata,
            'firsttexteditordataarabic' => $firsttexteditordataarabic,
            'productThirdData' => $productThirdData,
            'productFourthData' => $productFourthData,
            'productFifthData' => $productFifthData,
            'secondtexteditordata' => $secondtexteditordata,
            'secondtexteditordataarabic' => $secondtexteditordataarabic,
            'thirdtexteditordata' => $thirdtexteditordata,
            'thirdtexteditordataarabic' => $thirdtexteditordataarabic,
            'extra_multi_data' => $combined_data,
            'slidersData' => $slidersData
        ];
        
        $response = [
            'home_page_data' => $homepageData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
     public function HomePageFrontendRegionalNew($city, Request $request) {
        
        $first_pro_link_viewall = false;
        $second_pro_link_viewall = false;
        $third_pro_link_viewall = false;
        $fourth_pro_link_viewall = false;
        $fifth_pro_link_viewall = false;
        
        $homepageData = MobileHomePage::first();
        $imagestatus = $homepageData->images_status == 1 ? true : false;
        $servicestatus = $homepageData->services_status == 1 ? true : false;
        $homepageselectedData = MobileHomePage::select(['id','cat_sec_heading', 'cat_sec_heading_arabic','cats_view_all_link', 'cats_view_all_link_arabic', 'cat_viewall_link','cat_sec_status',
        'brands_heading','brands_heading_arabic', 'brands_view_all_link', 'brands_view_all_link_arabic','brand_link_viewall','brands_status', 'first_pro_heading', 'first_pro_heading_arabic',
        'first_pro_view_all_link', 'first_pro_view_all_link_arabic', 'first_pro_link_viewall','first_pro_status', 'second_pro_heading', 'second_pro_heading_arabic','second_pro_view_all_link',
        'second_pro_view_all_link_arabic','second_pro_link_viewall','first_pro_view_all_link_arabic','first_pro_link_viewall',
        'second_pro_status', 'flash_sale_heading','flash_sale_heading_arabic','flash_sale_view_all', 'flash_sale_view_all_arabic','flash_sale_link_viewall','flash_sale_status', 'images_heading', 'images_heading_arabic', 'image_view_all',
        'image_view_all_arabic','image_link_viewall','images_status','first_text_editor_status','third_pro_heading', 'third_pro_heading_arabic', 'third_pro_view_all_link',
        'third_pro_view_all_link_arabic', 'third_pro_link_viewall','third_pro_status', 'second_text_editor_status', 'services_heading', 'services_heading_arabic', 'services_view_all',
        'services_view_all_arabic','services_link_viewall','services_status', 'third_text_editor_status','fourth_pro_heading', 'fourth_pro_heading_arabic', 'fourth_pro_view_all_link',
        'fourth_pro_view_all_link_arabic', 'fourth_pro_link_viewall','fourth_pro_status','fifth_pro_heading', 'fifth_pro_heading_arabic', 'fifth_pro_view_all_link',
        'fifth_pro_view_all_link_arabic', 'fifth_pro_link_viewall','fifth_pro_status'])
        ->when($imagestatus == true, function ($q) {
            return $q->with('images:id,mobile_home_page_id,link,image,image_arabic', 'images.FeaturedImage:id,image', 'images.FeaturedImageArabic:id,image');
        })
        ->when($servicestatus == true, function ($q) {
            return $q->with('services:id,mobile_home_page_id,link,image,image_arabic', 'services.FeaturedImage:id,image', 'services.FeaturedImageArabic:id,image');
        })->first();

        if($homepageData->first_pro_status == 1) {
            $first_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->first_pro_link_viewall)->where('status',1)->first();
        }

        if($homepageData->second_pro_status == 1) {
            $second_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->second_pro_link_viewall)->where('status',1)->first();
        }

        if($homepageData->third_pro_status == 1) {
            $third_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->third_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->fourth_pro_status == 1) {
            $fourth_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->fourth_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->fifth_pro_status == 1) {
            $fifth_pro_link_viewall = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug')->where('id', $homepageData->fifth_pro_link_viewall)->where('status',1)->first();
        }
        
        if($homepageData->cat_sec_status == 1){
            $categoryids = explode(',',$homepageData->cats_first_line);
            $firstlinecategory = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'mobile_image_media', 'icon')->withCount('productname')->with('MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $homepageData->cats_first_line)")->where('status',1)->get();
            $secondcategoryids = explode(',',$homepageData->cats_second_line);
            $secondlinecategory = Productcategory::select('id', 'name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'mobile_image_media', 'icon')->withCount('productname')->with('MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$secondcategoryids)->orderByRaw("FIELD(id, $homepageData->cats_second_line)")->where('status',1)->get();
        }else{
            $firstlinecategory = [];
            $secondlinecategory = [];
        }
        
        
        if($homepageData->first_pro_status == 1 && isset($homepageData->first_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->first_products);
            $mobimage = true;
            $productFirstData = ProductListingHelper::productDataRegionalNew($filters,false, $mobimage, $city);
        }else{
            $productFirstData = [];
        }
        
        if($homepageData->brands_status == 1){
            $brandids = explode(',',$homepageData->brands);
            $brands = Brand::withCount('productname')->with(['BrandMediaAppImage:id,image,desktop,mobile'])->has('category', '>=', 4)->whereIn('id',$brandids)->orderByRaw("FIELD(id, $homepageData->brands)")->where('status',1)->select(['id','name','name_arabic','slug','brand_app_image_media','status'])->get();
            foreach ($brands as $brand) {
                $categories = Productcategory::select('name', 'name_arabic', 'name_app', 'name_arabic_app', 'slug', 'icon', 'brand_link', 'image_link_app')
                    ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                    ->where('brand_category.brand_id', $brand->id)
                    ->limit(4)
                    ->get();
                $brand->categories = $categories;
            }
        }else{
            $brands = [];
        }
        
        
        if($homepageData->second_pro_status == 1 && isset($homepageData->second_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->second_products);
            $mobimage = true;
            $productSecondtData = ProductListingHelper::productDataRegionalNew($filters,false, $mobimage, $city);
        }else{
            $productSecondtData = [];
        }
        
        if($homepageData->flash_sale_status == 1){
            $firstflashsaleid = explode(',',$homepageData->first_flash_sale);
            $firstflash = FlashSale::with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'featuredImageApp:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
            ->whereIn('id',$firstflashsaleid)
            // ->orderByRaw("FIELD(id, $homepageData->first_flash_sale)")
            ->where('status', 1)
            ->where('left_quantity', '>=', 1)
            ->where(function($a){
                return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
            })
            ->where(function($a){
                return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
            })
            ->get(['image', 'image_app', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'end_date']);
            
            
        }else{
            $firstflash = [];
        }
        
        if($homepageData->flash_sale_sec_status == 1){
            $secondflashsaleid = explode(',',$homepageData->second_flash_sale);
            $secondflash = FlashSale::with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'featuredImageApp:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
            ->whereIn('id',$secondflashsaleid)
            // ->orderByRaw("FIELD(id, $homepageData->second_flash_sale)")
            ->where('status', 1)
            ->where('left_quantity', '>=', 1)
            ->where(function($a){
                return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
            })
            ->where(function($a){
                return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
            })
            ->get(['image', 'image_app', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'start_date','end_date']);
        }
        else {
            $secondflash = [];
        }
        
        if($homepageData->first_text_editor_status == 1){
            $firsttexteditordata = $homepageData->first_text_editor_data;
            $firsttexteditordataarabic = $homepageData->first_text_editor_data_arabic;
        }else{
            $firsttexteditordata = [];
            $firsttexteditordataarabic = [];
        }
        
        if($homepageData->third_pro_status == 1 && isset($homepageData->third_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->third_products);
            $mobimage = true;
            $productThirdData = ProductListingHelper::productDataRegionalNew($filters,false, $mobimage);
        }else{
            $productThirdData =[];
        }
        
        if($homepageData->fourth_pro_status == 1 && isset($homepageData->fourth_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->fourth_products);
            $mobimage = true;
            $productFourthData = ProductListingHelper::productDataRegionalNew($filters,false, $mobimage);
        }else{
            $productFourthData =[];
        }
        
        if($homepageData->fifth_pro_status == 1 && isset($homepageData->fifth_products)){
            $filters = ['take' => 20, 'page' => 1];
            $filters['productbyid'] = explode(',', $homepageData->fifth_products);
            $mobimage = true;
            $productFifthData = ProductListingHelper::productDataRegionalNew($filters,false, $mobimage);
        }else{
            $productFifthData =[];
        }
        
        if($homepageData->second_text_editor_status == 1){
            $secondtexteditordata = $homepageData->second_text_editor_data;
            $secondtexteditordataarabic = $homepageData->second_text_editor_data_arabic;
        }else{
            $secondtexteditordata = [];
            $secondtexteditordataarabic = [];
        }
        
        if($homepageData->third_text_editor_status == 1){
            $thirdtexteditordata = $homepageData->third_text_editor_data;
            $thirdtexteditordataarabic = $homepageData->third_text_editor_data_arabic;
        }else{
            $thirdtexteditordata = [];
            $thirdtexteditordataarabic = [];
        }
        
        // 1st Product Extra Data multi
        $firstpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFirstData['products']) && sizeof($productFirstData['products'])) {
                $products_id = $productFirstData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $firstpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' =>false,'wishlistData' => false, 'PriceAlertData' => false,
                    'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $firstpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $firstpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $firstpro_extra_multi_data[$id]['flashData'] = $flashData;
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
                        $firstpro_extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                        $firstpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $firstpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        // 2nd Product Extra Data multi
        $secondpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productSecondtData['products']) && sizeof($productSecondtData['products'])) {
                $products_id = $productSecondtData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $secondpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData' => false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $secondpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $secondpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $secondpro_extra_multi_data[$id]['flashData'] = $flashData;
                        if($request['user_id']) {
                            $wishlistData = false;
                            $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($wishlist) {
                                $wishlistData = true;
                            }
                            $PriceAlertData = false;
                            $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($pricealert) {
                                $PriceAlertData = true;
                            }
                            $StockAlertData = false;
                            $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                            if($stockalert) {
                                $StockAlertData = true;
                            }
                        }
                        $secondpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $secondpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $secondpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                }
            }
        }
        
        // 3rd Product Extra Data multi
        $thirdpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productThirdData['products']) && sizeof($productThirdData['products'])) {
                $products_id = $productThirdData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $thirdpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $thirdpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $thirdpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $thirdpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $thirdpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $thirdpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $thirdpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        
        // 4th Product Extra Data multi
        $fourthpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFourthData['products']) && sizeof($productFourthData['products'])) {
                $products_id = $productFourthData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $fourthpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $fourthpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $fourthpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $fourthpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $fourthpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $fourthpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $fourthpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        
        // 5th Product Extra Data multi
        $fifthpro_extra_multi_data = [];
        if(isset($request['city'])) {
            if(isset($productFifthData['products']) && sizeof($productFifthData['products'])) {
                $products_id = $productFifthData['products']->pluck('id')->toArray();
                foreach($products_id as $id){
                    $fifthpro_extra_multi_data[$id] = array('freegiftdata'=>false,'badgeData'=>false,'flashData'=>false,'wishlistData' => false, 'PriceAlertData' => false, 'StockAlertData' => false);
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$request['city']);
                    $wishlistData = false;
                    $PriceAlertData = false;
                    $StockAlertData = false;
                    if($freeGiftData)
                        $fifthpro_extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                        $mobbadge = true;
                        $badgeData = ProductListingHelper::productBadge($id,$request['city'],$mobbadge);
                    if($badgeData)
                        $fifthpro_extra_multi_data[$id]['badgeData'] = $badgeData;
                        $flashData = ProductListingHelper::productFlashSaleNew($id);
                    if($flashData)
                        $fifthpro_extra_multi_data[$id]['flashData'] = $flashData;
                    if($request['user_id']) {
                        $wishlistData = false;
                        $wishlist = Wishlists::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($wishlist) {
                            $wishlistData = true;
                        }
                        $PriceAlertData = false;
                        $pricealert = PriceAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($pricealert) {
                            $PriceAlertData = true;
                        }
                        $StockAlertData = false;
                        $stockalert = StockAlert::where('user_id',$request['user_id'])->where('product_id',$id)->first();
                        if($stockalert) {
                            $StockAlertData = true;
                        }
                    }
                        $fifthpro_extra_multi_data[$id]['wishlistData'] = $wishlistData;
                        $fifthpro_extra_multi_data[$id]['PriceAlertData'] = $PriceAlertData == 1 ? true : false;
                        $fifthpro_extra_multi_data[$id]['StockAlertData'] = $StockAlertData == 1 ? true : false;
                            
                }
            }
        }
        
        // $combined_data = array_merge($firstpro_extra_multi_data, $secondpro_extra_multi_data, $thirdpro_extra_multi_data);
        // $combined_data = [
        //     $firstpro_extra_multi_data,
        //     $secondpro_extra_multi_data,
        //     $thirdpro_extra_multi_data
        // ];
        
        $combined_data = [];

        // Merge data from the firstpro_extra_multi_data
        foreach ($firstpro_extra_multi_data as $id => $data) {
            $data['id'] = $id; // Include the ID in the data
            $combined_data[$id] = $data;
        }
        
        // Merge data from the secondpro_extra_multi_data
        foreach ($secondpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the thirdpro_extra_multi_data
        foreach ($thirdpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the fourthpro_extra_multi_data
        foreach ($fourthpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        // Merge data from the fifthpro_extra_multi_data
        foreach ($fifthpro_extra_multi_data as $id => $data) {
            if (!isset($combined_data[$id])) {
                $data['id'] = $id; // Include the ID in the data
                $combined_data[$id] = $data;
            } else {
                // Merge data if the ID already exists
                $combined_data[$id] = array_merge($combined_data[$id], $data);
            }
        }
        
        $slidersData = Slider::where('status', 1)
        ->whereRaw("FIND_IN_SET('1', slider_devices)")
        ->orderBy('sorting', 'asc')
        ->with('featuredImageApp:id,image', 'cat:id,name,name_arabic,slug', 'pro:id,name,name_arabic,slug', 'brand:id,name,name_arabic,slug', 'subtag:id,name,name_arabic')
        ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'video_link_web', 'video_link_app', 'video_interval_web', 'video_interval_app', 'slider_devices', 'redirection_type'
        , 'brand_id', 'sub_tag_id', 'product_id', 'category_id', 'custom_link', 'sorting', 'status', 'image_mobile')->get();
        
        
        
        // // wishlist
        // $wishlistData = false;
        // if($user_id != '') {
        //     // print_r($user_id);die;
        //     $wishlists = Wishlists::where('user_id',$user_id)->get();
        //     if($wishlists){
        //         $wishlistData = true;
        //     }
        // }
        
        $homepageData = [
            'homepageData'=> $homepageselectedData,
            'first_pro_link_viewall' => $first_pro_link_viewall,
            'second_pro_link_viewall' => $second_pro_link_viewall,
            'third_pro_link_viewall' => $third_pro_link_viewall,
            'fourth_pro_link_viewall' => $fourth_pro_link_viewall,
            'fifth_pro_link_viewall' => $fifth_pro_link_viewall,
            'firstlinecategories' => $firstlinecategory,
            'secondlinecategories' => $secondlinecategory,
            'productFirstData' => $productFirstData,
            'brands' => $brands,
            'productSecondData' => $productSecondtData,
            'firstflash' => $firstflash,
            'secondflash' => $secondflash,
            'firsttexteditordata' => $firsttexteditordata,
            'firsttexteditordataarabic' => $firsttexteditordataarabic,
            'productThirdData' => $productThirdData,
            'productFourthData' => $productFourthData,
            'productFifthData' => $productFifthData,
            'secondtexteditordata' => $secondtexteditordata,
            'secondtexteditordataarabic' => $secondtexteditordataarabic,
            'thirdtexteditordata' => $thirdtexteditordata,
            'thirdtexteditordataarabic' => $thirdtexteditordataarabic,
            'extra_multi_data' => $combined_data,
            'slidersData' => $slidersData
        ];
        
        $response = [
            'home_page_data' => $homepageData
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