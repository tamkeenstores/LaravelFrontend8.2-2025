<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\FlashSale;
use App\Models\Brand;
use App\Models\BrandLandingPage;
use App\Models\HomePage;
use App\Models\Slider;
use App\Helper\ProductListingHelper;
use DB;

use Illuminate\Support\Facades\Cache;
use App\Models\CacheStores;

class HomePageController extends Controller
{

    // Sec One
    public function SecOne() {
        $seconds = 86400;
        
        // Cache::forget('homesecone');
        if(Cache::has('homesecone'))
            $response = Cache::get('homesecone');
        else{
            $firstSec = HomePage::first(['cat_view_all', 'cat_heading', 'cat_heading_arabic', 'categories_top', 'categories_top_status']);
            if($firstSec->categories_top_status == 1){
                $categoryids = explode(',',$firstSec->categories_top);
                // 'brand_link', 'mobile_image_media', 'icon'
                // withCount('productCount')->
                $category = Productcategory::select('id', 'name', 'name_arabic', 'slug', 'web_image_media')->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
                // with('WebMediaImage:id,image,desktop,mobile')->
                // ,'MobileMediaAppImage:id,image,desktop,mobile'
            }else{
                $category = [];
            }
    
            $response = [
                'first_sec_fields' => $firstSec,
                'first_sec_categories' => $category,
            ];
            CacheStores::create([
                'key' => 'homesecone',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecone', $seconds, function () use ($response) {
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

    // Sec One Regional
    public function SecOneRegional() {
        $seconds = 86400;
        
        // Cache::forget('homesecone');
        if(Cache::has('homesecone'))
            $response = Cache::get('homesecone');
        else{
            $firstSec = HomePage::first(['cat_view_all', 'cat_heading', 'cat_heading_arabic', 'categories_top', 'categories_top_status']);
            if($firstSec->categories_top_status == 1){
                $categoryids = explode(',',$firstSec->categories_top);
                // 'brand_link', 'mobile_image_media', 'icon'
                // withCount('productCount')->
                $category = Productcategory::select('id', 'name', 'name_arabic', 'slug', 'web_image_media','selltype','sellvalue')->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
                // with('WebMediaImage:id,image,desktop,mobile')->
                // ,'MobileMediaAppImage:id,image,desktop,mobile'
            }else{
                $category = [];
            }
    
            $response = [
                'first_sec_fields' => $firstSec,
                'first_sec_categories' => $category,
            ];
            CacheStores::create([
                'key' => 'homesecone',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecone', $seconds, function () use ($response) {
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

    // Sec Two
    public function SecTwo() {
        $seconds = 86400;
        // Cache::forget('homesectwo');
        if(Cache::has('homesectwo'))
            $response = Cache::get('homesectwo');
        else{
            $secondSec = HomePage::select(['pro_first_view_all', 'pro_first_heading', 'pro_first_heading_arabic', 'products_first_status', 'products_first'])->first();

            if($secondSec->products_first_status == 1 && isset($secondSec->products_first)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $secondSec->products_first);
                $productFirstData = ProductListingHelper::productData($filters);
            }else{
                $productFirstData = [];
            }
    
            $response = [
                'second_sec_fields' => $secondSec,
                'productSecondtData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesectwo',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwo', $seconds, function () use ($response) {
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

    // Sec Two Regional
    public function SecTwoRegional() {
        $seconds = 86400;
        // Cache::forget('homesectwo');
        if(Cache::has('homesectwo'))
            $response = Cache::get('homesectwo');
        else{
            $secondSec = HomePage::select(['pro_first_view_all', 'pro_first_heading', 'pro_first_heading_arabic', 'products_first_status', 'products_first'])->first();

            if($secondSec->products_first_status == 1 && isset($secondSec->products_first)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $secondSec->products_first);
                $productFirstData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productFirstData = [];
            }
    
            $response = [
                'second_sec_fields' => $secondSec,
                'productSecondtData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesectwo',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwo', $seconds, function () use ($response) {
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

    // Sec Three 
    public function SecThree() {
        $seconds = 86400;
        
        // Cache::forget('homesecthree');
        if(Cache::has('homesecthree'))
            $response = Cache::get('homesecthree');
        else{
            $thirdSec = HomePage::select(['pro_second_view_all', 'pro_second_heading', 'pro_second_heading_arabic', 'products_second_status', 'products_second'])->first();

            if($thirdSec->products_second_status == 1 && isset($thirdSec->products_second)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirdSec->products_second);
                $productThirdData = ProductListingHelper::productData($filters);
            }else{
                $productThirdData = [];
            }
    
            $response = [
                'third_sec_fields' => $thirdSec,
                'productThirdtData' => $productThirdData,
            ];
            CacheStores::create([
                'key' => 'homesecthree',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthree', $seconds, function () use ($response) {
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

    // Sec Three Regional
    public function SecThreeRegional() {
        $seconds = 86400;
        
        // Cache::forget('homesecthree');
        if(Cache::has('homesecthree'))
            $response = Cache::get('homesecthree');
        else{
            $thirdSec = HomePage::select(['pro_second_view_all', 'pro_second_heading', 'pro_second_heading_arabic', 'products_second_status', 'products_second'])->first();

            if($thirdSec->products_second_status == 1 && isset($thirdSec->products_second)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirdSec->products_second);
                $productThirdData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productThirdData = [];
            }
    
            $response = [
                'third_sec_fields' => $thirdSec,
                'productThirdtData' => $productThirdData,
            ];
            CacheStores::create([
                'key' => 'homesecthree',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthree', $seconds, function () use ($response) {
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

    // Sec Four 
    public function SecFour() {
        $seconds = 86400;
        // Cache::forget('homesecfour');
        if(Cache::has('homesecfour'))
            $response = Cache::get('homesecfour');
        else{
            $fourthSec = HomePage::select(['brands_middle_status', 'brands_middle', 'brand_view_all'])->first();

            if($fourthSec->brands_middle_status == 1){
                $brandids = explode(',',$fourthSec->brands_middle);
                $brands = Brand::withCount('productname')->with([
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    // $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon','image_link_app')
                    // 'icon',
                    $categories = Productcategory::select('name', 'name_arabic', 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
    
            $response = [
                'fourth_sec_fields' => $fourthSec,
                'brandThirdData' => $brands,
            ];
            CacheStores::create([
                'key' => 'homesecfour',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfour', $seconds, function () use ($response) {
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

    // Sec Four Regional
    public function SecFourRegional() {
        $seconds = 86400;
        // Cache::forget('homesecfour');
        if(Cache::has('homesecfour'))
            $response = Cache::get('homesecfour');
        else{
            $fourthSec = HomePage::select(['brands_middle_status', 'brands_middle', 'brand_view_all'])->first();

            if($fourthSec->brands_middle_status == 1){
                $brandids = explode(',',$fourthSec->brands_middle);
                $brands = Brand::withCount('productname')->with([
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    // $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon','image_link_app')
                    // 'icon',
                    $categories = Productcategory::select('name', 'name_arabic', 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
    
            $response = [
                'fourth_sec_fields' => $fourthSec,
                'brandThirdData' => $brands,
            ];
            CacheStores::create([
                'key' => 'homesecfour',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfour', $seconds, function () use ($response) {
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

    // Sec Five
    public function SecFive() {
        $seconds = 86400;
        // Cache::forget('homesecfive');
        if(Cache::has('homesecfive'))
            $response = Cache::get('homesecfive');
        else{
            $fifthSec = HomePage::select(['pro_third_view_all', 'pro_third_heading', 'pro_third_heading_arabic', 'products_third_status', 'products_third'])->first();

            if($fifthSec->products_third_status == 1 && isset($fifthSec->products_third)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $fifthSec->products_third);
                $productthirdData = ProductListingHelper::productData($filters);
            }else{
                $productthirdData = [];
            }
    
            $response = [
                'fifth_sec_fields' => $fifthSec,
                'productFifthData' => $productthirdData,
            ];
            CacheStores::create([
                'key' => 'homesecfive',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfive', $seconds, function () use ($response) {
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

    // Sec Five Regional
    public function SecFiveRegional() {
        $seconds = 86400;
        // Cache::forget('homesecfive');
        if(Cache::has('homesecfive'))
            $response = Cache::get('homesecfive');
        else{
            $fifthSec = HomePage::select(['pro_third_view_all', 'pro_third_heading', 'pro_third_heading_arabic', 'products_third_status', 'products_third'])->first();

            if($fifthSec->products_third_status == 1 && isset($fifthSec->products_third)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $fifthSec->products_third);
                $productthirdData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productthirdData = [];
            }
    
            $response = [
                'fifth_sec_fields' => $fifthSec,
                'productFifthData' => $productthirdData,
            ];
            CacheStores::create([
                'key' => 'homesecfive',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfive', $seconds, function () use ($response) {
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

    // Sec Six
    public function SecSix() {
        $seconds = 86400;
        // Cache::forget('homesecsix');
        if(Cache::has('homesecsix'))
            $response = Cache::get('homesecsix');
        else{
            $sixthSec = HomePage::select(['pro_fourth_view_all', 'pro_fourth_heading', 'pro_fourth_heading_arabic', 'products_fourth_status', 'products_fourth'])->first();

            if($sixthSec->products_fourth_status == 1 && isset($sixthSec->products_fourth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $sixthSec->products_fourth);
                $productfourthData = ProductListingHelper::productData($filters);
            }else{
                $productfourthData = [];
            }
    
            $response = [
                'sixth_sec_fields' => $sixthSec,
                'productSixthData' => $productfourthData,
            ];
            CacheStores::create([
                'key' => 'homesecsix',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecsix', $seconds, function () use ($response) {
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

    // Sec Six Regional
    public function SecSixRegional() {
        $seconds = 86400;
        // Cache::forget('homesecsix');
        if(Cache::has('homesecsix'))
            $response = Cache::get('homesecsix');
        else{
            $sixthSec = HomePage::select(['pro_fourth_view_all', 'pro_fourth_heading', 'pro_fourth_heading_arabic', 'products_fourth_status', 'products_fourth'])->first();

            if($sixthSec->products_fourth_status == 1 && isset($sixthSec->products_fourth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $sixthSec->products_fourth);
                $productfourthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productfourthData = [];
            }
    
            $response = [
                'sixth_sec_fields' => $sixthSec,
                'productSixthData' => $productfourthData,
            ];
            CacheStores::create([
                'key' => 'homesecsix',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecsix', $seconds, function () use ($response) {
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

    // Sec Seven
    public function SecSeven() {
        
        $seconds = 86400;
        // Cache::forget('homesecseven');
        if(Cache::has('homesecseven'))
            $response = Cache::get('homesecseven');
        else{
            $seventhSec = HomePage::select(['pro_fifth_view_all', 'pro_fifth_heading', 'pro_fifth_heading_arabic', 'products_fifth_status', 'products_fifth'])->first();

            if($seventhSec->products_fifth_status == 1 && isset($seventhSec->products_fifth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $seventhSec->products_fifth);
                $productfifthData = ProductListingHelper::productData($filters);
            }else{
                $productfifthData = [];
            }
    
            $response = [
                'seventh_sec_fields' => $seventhSec,
                'productSeventhData' => $productfifthData,
            ];
            CacheStores::create([
                'key' => 'homesecseven',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecseven', $seconds, function () use ($response) {
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

    // Sec Seven Regional
    public function SecSevenRegional() {
        
        $seconds = 86400;
        // Cache::forget('homesecseven');
        if(Cache::has('homesecseven'))
            $response = Cache::get('homesecseven');
        else{
            $seventhSec = HomePage::select(['pro_fifth_view_all', 'pro_fifth_heading', 'pro_fifth_heading_arabic', 'products_fifth_status', 'products_fifth'])->first();

            if($seventhSec->products_fifth_status == 1 && isset($seventhSec->products_fifth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $seventhSec->products_fifth);
                $productfifthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productfifthData = [];
            }
    
            $response = [
                'seventh_sec_fields' => $seventhSec,
                'productSeventhData' => $productfifthData,
            ];
            CacheStores::create([
                'key' => 'homesecseven',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecseven', $seconds, function () use ($response) {
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

    // Sec Eight 
    public function SecEight() {
        $seconds = 86400;
        // Cache::forget('homeseceight');
        if(Cache::has('homeseceight'))
            $response = Cache::get('homeseceight');
        else{
            $eightSec = HomePage::select(['pro_sixth_view_all', 'pro_sixth_heading', 'pro_sixth_heading_arabic', 'products_sixth_status', 'products_sixth'])->first();

            if($eightSec->products_sixth_status == 1 && isset($eightSec->products_sixth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $eightSec->products_sixth);
                $productsixthData = ProductListingHelper::productData($filters);
            }else{
                $productsixthData = [];
            }
    
            $response = [
                'eight_sec_fields' => $eightSec,
                'productEightData' => $productsixthData,
            ];
            CacheStores::create([
                'key' => 'homeseceight',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceight', $seconds, function () use ($response) {
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

    // Sec Eight Regional
    public function SecEightRegional() {
        $seconds = 86400;
        // Cache::forget('homeseceight');
        if(Cache::has('homeseceight'))
            $response = Cache::get('homeseceight');
        else{
            $eightSec = HomePage::select(['pro_sixth_view_all', 'pro_sixth_heading', 'pro_sixth_heading_arabic', 'products_sixth_status', 'products_sixth'])->first();

            if($eightSec->products_sixth_status == 1 && isset($eightSec->products_sixth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $eightSec->products_sixth);
                $productsixthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productsixthData = [];
            }
    
            $response = [
                'eight_sec_fields' => $eightSec,
                'productEightData' => $productsixthData,
            ];
            CacheStores::create([
                'key' => 'homeseceight',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceight', $seconds, function () use ($response) {
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

    // Sec Nine
    public function SecNine() {
        
        $seconds = 86400;
        // Cache::forget('homesecnine');
        if(Cache::has('homesecnine'))
            $response = Cache::get('homesecnine');
        else{
            $nineSec = HomePage::select(['pro_seventh_view_all', 'pro_seventh_heading', 'pro_seventh_heading_arabic', 'products_seventh_status', 'products_seventh'])->first();

            if($nineSec->products_seventh_status == 1 && isset($nineSec->products_seventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $nineSec->products_seventh);
                $productseventhData = ProductListingHelper::productData($filters);
            }else{
                $productseventhData = [];
            }
    
            $response = [
                'nine_sec_fields' => $nineSec,
                'productNineData' => $productseventhData,
            ];
            CacheStores::create([
                'key' => 'homesecnine',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecnine', $seconds, function () use ($response) {
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

    // Sec Nine Regional
    public function SecNineRegional() {
        
        $seconds = 86400;
        // Cache::forget('homesecnine');
        if(Cache::has('homesecnine'))
            $response = Cache::get('homesecnine');
        else{
            $nineSec = HomePage::select(['pro_seventh_view_all', 'pro_seventh_heading', 'pro_seventh_heading_arabic', 'products_seventh_status', 'products_seventh'])->first();

            if($nineSec->products_seventh_status == 1 && isset($nineSec->products_seventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $nineSec->products_seventh);
                $productseventhData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productseventhData = [];
            }
    
            $response = [
                'nine_sec_fields' => $nineSec,
                'productNineData' => $productseventhData,
            ];
            CacheStores::create([
                'key' => 'homesecnine',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecnine', $seconds, function () use ($response) {
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

    // Sec Ten
    public function SecTen() {
        $seconds = 86400;
        // Cache::forget('homesecten');
        if(Cache::has('homesecten'))
            $response = Cache::get('homesecten');
        else{
            $tenSec = HomePage::select(['pro_eigth_view_all', 'pro_eigth_heading', 'pro_eigth_heading_arabic', 'products_eigth_status', 'products_eigth'])->first();

            if($tenSec->products_eigth_status == 1 && isset($tenSec->products_eigth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $tenSec->products_eigth);
                $producteigthData = ProductListingHelper::productData($filters);
            }else{
                $producteigthData = [];
            }
    
            $response = [
                'ten_sec_fields' => $tenSec,
                'productTenData' => $producteigthData,
            ];
            CacheStores::create([
                'key' => 'homesecten',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecten', $seconds, function () use ($response) {
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

     // Sec Ten Regional
    public function SecTenRegional() {
        $seconds = 86400;
        // Cache::forget('homesecten');
        if(Cache::has('homesecten'))
            $response = Cache::get('homesecten');
        else{
            $tenSec = HomePage::select(['pro_eigth_view_all', 'pro_eigth_heading', 'pro_eigth_heading_arabic', 'products_eigth_status', 'products_eigth'])->first();

            if($tenSec->products_eigth_status == 1 && isset($tenSec->products_eigth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $tenSec->products_eigth);
                $producteigthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $producteigthData = [];
            }
    
            $response = [
                'ten_sec_fields' => $tenSec,
                'productTenData' => $producteigthData,
            ];
            CacheStores::create([
                'key' => 'homesecten',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecten', $seconds, function () use ($response) {
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

    // Sec Eleven
    public function SecEleven() {
        
        $seconds = 86400;
        // Cache::forget('homeseceleven');
        if(Cache::has('homeseceleven'))
            $response = Cache::get('homeseceleven');
        else{
            $elevenSec = HomePage::select(['pro_nineth_view_all', 'pro_nineth_heading', 'pro_nineth_heading_arabic', 'products_nineth_status', 'products_nineth'])->first();

            if($elevenSec->products_nineth_status == 1 && isset($elevenSec->products_nineth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $elevenSec->products_nineth);
                $productninethData = ProductListingHelper::productData($filters);
            }
            else{
                $productninethData = [];
            }
    
            $response = [
                'eleven_sec_fields' => $elevenSec,
                'productElevenData' => $productninethData,
            ];
            CacheStores::create([
                'key' => 'homeseceleven',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceleven', $seconds, function () use ($response) {
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

     // Sec Eleven Regional
    public function SecElevenRegional() {
        
        $seconds = 86400;
        // Cache::forget('homeseceleven');
        if(Cache::has('homeseceleven'))
            $response = Cache::get('homeseceleven');
        else{
            $elevenSec = HomePage::select(['pro_nineth_view_all', 'pro_nineth_heading', 'pro_nineth_heading_arabic', 'products_nineth_status', 'products_nineth'])->first();

            if($elevenSec->products_nineth_status == 1 && isset($elevenSec->products_nineth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $elevenSec->products_nineth);
                $productninethData = ProductListingHelper::productDataRegional($filters);
            }
            else{
                $productninethData = [];
            }
    
            $response = [
                'eleven_sec_fields' => $elevenSec,
                'productElevenData' => $productninethData,
            ];
            CacheStores::create([
                'key' => 'homeseceleven',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceleven', $seconds, function () use ($response) {
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

    // Sec Twelve
    public function SecTwelve() {
        $seconds = 86400;
        // Cache::forget('homesectwelve');
        if(Cache::has('homesectwelve'))
            $response = Cache::get('homesectwelve');
        else{
            $twelveSec = HomePage::select(['pro_tenth_view_all', 'pro_tenth_heading', 'pro_tenth_heading_arabic', 'products_tenth_status', 'products_tenth'])->first();

            if($twelveSec->products_tenth_status == 1 && isset($twelveSec->products_tenth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $twelveSec->products_tenth);
                $producttenthData = ProductListingHelper::productData($filters);
            }
            else{
                $producttenthData = [];
            }
    
            $response = [
                'twelve_sec_fields' => $twelveSec,
                'productTwelveData' => $producttenthData,
            ];
            CacheStores::create([
                'key' => 'homesectwelve',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwelve', $seconds, function () use ($response) {
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

     // Sec Twelve Regional
    public function SecTwelveRegional() {
        $seconds = 86400;
        // Cache::forget('homesectwelve');
        if(Cache::has('homesectwelve'))
            $response = Cache::get('homesectwelve');
        else{
            $twelveSec = HomePage::select(['pro_tenth_view_all', 'pro_tenth_heading', 'pro_tenth_heading_arabic', 'products_tenth_status', 'products_tenth'])->first();

            if($twelveSec->products_tenth_status == 1 && isset($twelveSec->products_tenth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $twelveSec->products_tenth);
                $producttenthData = ProductListingHelper::productDataRegional($filters);
            }
            else{
                $producttenthData = [];
            }
    
            $response = [
                'twelve_sec_fields' => $twelveSec,
                'productTwelveData' => $producttenthData,
            ];
            CacheStores::create([
                'key' => 'homesectwelve',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwelve', $seconds, function () use ($response) {
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

    // Sec Thirteen
    public function SecThirteen() {
        $seconds = 86400;
        // Cache::forget('homesecthirteen');
        if(Cache::has('homesecthirteen'))
            $response = Cache::get('homesecthirteen');
        else{
            $thirteenSec = HomePage::select(['pro_eleventh_view_all', 'pro_eleventh_heading', 'pro_eleventh_heading_arabic', 'products_eleventh_status', 'products_eleventh'])->first();

            if($thirteenSec->products_eleventh_status == 1 && isset($thirteenSec->products_eleventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirteenSec->products_eleventh);
                $producteleventhData = ProductListingHelper::productData($filters);
            }
            else{
                $producteleventhData = [];
            }
    
            $response = [
                'thirteen_sec_fields' => $thirteenSec,
                'productThirteenData' => $producteleventhData,
            ];
            CacheStores::create([
                'key' => 'homesecthirteen',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthirteen', $seconds, function () use ($response) {
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

    // Sec Thirteen Regional
    public function SecThirteenRegional() {
        $seconds = 86400;
        // Cache::forget('homesecthirteen');
        if(Cache::has('homesecthirteen'))
            $response = Cache::get('homesecthirteen');
        else{
            $thirteenSec = HomePage::select(['pro_eleventh_view_all', 'pro_eleventh_heading', 'pro_eleventh_heading_arabic', 'products_eleventh_status', 'products_eleventh'])->first();

            if($thirteenSec->products_eleventh_status == 1 && isset($thirteenSec->products_eleventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirteenSec->products_eleventh);
                $producteleventhData = ProductListingHelper::productDataRegional($filters);
            }
            else{
                $producteleventhData = [];
            }
    
            $response = [
                'thirteen_sec_fields' => $thirteenSec,
                'productThirteenData' => $producteleventhData,
            ];
            CacheStores::create([
                'key' => 'homesecthirteen',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthirteen', $seconds, function () use ($response) {
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


    public function HomePageFrontend(Request $request) {
        $seconds = 86400;
        
        if(Cache::has('homemeta'))
            $response = Cache::get('homemeta');
        else{
            $homepageselectedData = HomePage::select(['meta_title_en', 'meta_title_ar','meta_description_en', 'meta_description_ar'])->first();
            
            $response = [
                'homepageData'=> $homepageselectedData,
            ];    
                
            CacheStores::create([
                'key' => 'homemeta',
                'type' => 'homepage'
            ]);
            Cache::remember('homemeta', $seconds, function () use ($response) {
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
    
    public function HomePagePartOne(Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        $type8 = 8;
        Cache::forget('homesliders_'.$type3);
        if(Cache::has('homesliders_'.$type3))
            $mainsliders = Cache::get('homesliders_'.$type3);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type3,
                'type' => 'slider'
            ]);
            $mainsliders = Cache::remember('homesliders_'.$type3, $seconds, function () use ($type3) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type3)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // right slider
        if(Cache::has('homesliders_'.$type8))
            $rightSliders = Cache::get('homesliders_'.$type8);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type8,
                'type' => 'slider'
            ]);
            $rightSliders = Cache::remember('homesliders_'.$type8, $seconds, function () use ($type8) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type8)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // three images
        if(Cache::has('threeimages'))
            $threeImages = Cache::get('threeimages');
        else{
            CacheStores::create([
                'key' => 'threeimages',
                'type' => 'slider'
            ]);
            $threeImages = Cache::remember('threeimages', $seconds, function () {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', 9)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        if(Cache::has('homesecone'))
            $homesecone = Cache::get('homesecone');
        else{
            $firstSec = HomePage::first(['cat_view_all', 'cat_heading', 'cat_heading_arabic', 'categories_top', 'categories_top_status']);
            if($firstSec->categories_top_status == 1){
                $categoryids = explode(',',$firstSec->categories_top);
                $category = Productcategory::select('id', 'name', 'name_arabic', 'slug', 'web_image_media','selltype','sellvalue')->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
            }else{
                $category = [];
            }
    
            $homesecone = [
                'first_sec_fields' => $firstSec,
                'first_sec_categories' => $category,
            ];
            CacheStores::create([
                'key' => 'homesecone',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecone', $seconds, function () use ($homesecone) {
                return $homesecone;
            });
        }
        
        
        if(Cache::has('homesectwo'))
            $homesectwo = Cache::get('homesectwo');
        else{
            $secondSec = HomePage::select(['pro_first_view_all', 'pro_first_heading', 'pro_first_heading_arabic', 'products_first_status', 'products_first'])->first();

            if($secondSec->products_first_status == 1 && isset($secondSec->products_first)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $secondSec->products_first);
                $productFirstData = ProductListingHelper::productData($filters);
            }else{
                $productFirstData = [];
            }
    
            $homesectwo = [
                'second_sec_fields' => $secondSec,
                'productSecondtData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesectwo',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwo', $seconds, function () use ($homesectwo) {
                return $homesectwo;
            });
        }
         //after sec category
        if(Cache::has('homesecaftercat'))
            $homeaftercatsec = Cache::get('homesecaftercat');
        else{
            $afterCatSec = HomePage::select(['pro_twelveth_view_all', 'pro_twelveth_heading', 'pro_twelveth_heading_arabic', 'products_twelveth_status', 'products_twelveth','products_twelveth_date'])->first();

            if($afterCatSec->products_twelveth_status == 1 && isset($afterCatSec->products_twelveth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $afterCatSec->products_twelveth);
                $productFirstData = ProductListingHelper::productData($filters);
            }else{
                $productFirstData = [];
            }
    
            $homeaftercatsec = [
                'afterCatSec' => $afterCatSec,
                'productAfterSectData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesecaftercat',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecaftercat', $seconds, function () use ($homeaftercatsec) {
                return $homeaftercatsec;
            });
        }
        $afterSecCat = HomePage::with('BannerImageOne:id,image','BannerImageTwo:id,image','BannerImageThird:id,image','BannerImageFourth:id,image')->select(['banner_image1', 'banner_image1_link','banner_image2','banner_image2_link',
                                'banner_image3','banner_image3_link','banner_image4','banner_image4_link','banner_first_status','banner_second_status','banner_first_heading','banner_first_heading_arabic',
                                'banner_second_heading','banner_second_heading_arabic'])->first();
                                
        $afterCatSecSlider1 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 10)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
        $afterCatSecSlider2 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 11)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        $afterCatSecSlider3 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 12)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
        $afterCatSecSlider4 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 13)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
                                
        $response = [
            'data' => [
                'mainsliders' => $mainsliders,
                'rightSliders' => $rightSliders,
                'threeImages' => $threeImages,
                'homesecone' => $homesecone,
                'homesectwo' => $homesectwo,
                'homesecaftercat' => $homeaftercatsec,
                'afterSecCat' => $afterSecCat,
                'afterSecCatSlider1' => $afterCatSecSlider1,
                'afterSecCatSlider2' => $afterCatSecSlider2,
                'afterSecCatSlider3' => $afterCatSecSlider3,
                'afterSecCatSlider4' => $afterCatSecSlider4,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    //Regional
    public function HomePagePartOneRegional(Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        $type8 = 8;
        Cache::forget('homesliders_'.$type3);
        if(Cache::has('homesliders_'.$type3))
            $mainsliders = Cache::get('homesliders_'.$type3);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type3,
                'type' => 'slider'
            ]);
            $mainsliders = Cache::remember('homesliders_'.$type3, $seconds, function () use ($type3) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type3)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // right slider
        if(Cache::has('homesliders_'.$type8))
            $rightSliders = Cache::get('homesliders_'.$type8);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type8,
                'type' => 'slider'
            ]);
            $rightSliders = Cache::remember('homesliders_'.$type8, $seconds, function () use ($type8) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type8)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // three images
        if(Cache::has('threeimages'))
            $threeImages = Cache::get('threeimages');
        else{
            CacheStores::create([
                'key' => 'threeimages',
                'type' => 'slider'
            ]);
            $threeImages = Cache::remember('threeimages', $seconds, function () {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', 9)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        if(Cache::has('homesecone'))
            $homesecone = Cache::get('homesecone');
        else{
            $firstSec = HomePage::first(['cat_view_all', 'cat_heading', 'cat_heading_arabic', 'categories_top', 'categories_top_status']);
            if($firstSec->categories_top_status == 1){
                $categoryids = explode(',',$firstSec->categories_top);
                $category = Productcategory::select('id', 'name', 'name_arabic', 'slug', 'web_image_media','selltype','sellvalue')->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
            }else{
                $category = [];
            }
    
            $homesecone = [
                'first_sec_fields' => $firstSec,
                'first_sec_categories' => $category,
            ];
            CacheStores::create([
                'key' => 'homesecone',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecone', $seconds, function () use ($homesecone) {
                return $homesecone;
            });
        }
        
        
        if(Cache::has('homesectwo'))
            $homesectwo = Cache::get('homesectwo');
        else{
            $secondSec = HomePage::select(['pro_first_view_all', 'pro_first_heading', 'pro_first_heading_arabic', 'products_first_status', 'products_first'])->first();

            if($secondSec->products_first_status == 1 && isset($secondSec->products_first)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $secondSec->products_first);
                $productFirstData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productFirstData = [];
            }
    
            $homesectwo = [
                'second_sec_fields' => $secondSec,
                'productSecondtData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesectwo',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwo', $seconds, function () use ($homesectwo) {
                return $homesectwo;
            });
        }
         //after sec category
        if(Cache::has('homesecaftercat'))
            $homeaftercatsec = Cache::get('homesecaftercat');
        else{
            $afterCatSec = HomePage::select(['pro_twelveth_view_all', 'pro_twelveth_heading', 'pro_twelveth_heading_arabic', 'products_twelveth_status', 'products_twelveth','products_twelveth_date'])->first();

            if($afterCatSec->products_twelveth_status == 1 && isset($afterCatSec->products_twelveth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $afterCatSec->products_twelveth);
                $productFirstData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productFirstData = [];
            }
    
            $homeaftercatsec = [
                'afterCatSec' => $afterCatSec,
                'productAfterSectData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesecaftercat',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecaftercat', $seconds, function () use ($homeaftercatsec) {
                return $homeaftercatsec;
            });
        }
        $afterSecCat = HomePage::with('BannerImageOne:id,image','BannerImageTwo:id,image','BannerImageThird:id,image','BannerImageFourth:id,image')->select(['banner_image1', 'banner_image1_link','banner_image2','banner_image2_link',
                                'banner_image3','banner_image3_link','banner_image4','banner_image4_link','banner_first_status','banner_second_status','banner_first_heading','banner_first_heading_arabic',
                                'banner_second_heading','banner_second_heading_arabic'])->first();
                                
        $afterCatSecSlider1 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 10)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
        $afterCatSecSlider2 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 11)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        $afterCatSecSlider3 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 12)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
        $afterCatSecSlider4 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 13)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
                                
        $response = [
            'data' => [
                'mainsliders' => $mainsliders,
                'rightSliders' => $rightSliders,
                'threeImages' => $threeImages,
                'homesecone' => $homesecone,
                'homesectwo' => $homesectwo,
                'homesecaftercat' => $homeaftercatsec,
                'afterSecCat' => $afterSecCat,
                'afterSecCatSlider1' => $afterCatSecSlider1,
                'afterSecCatSlider2' => $afterCatSecSlider2,
                'afterSecCatSlider3' => $afterCatSecSlider3,
                'afterSecCatSlider4' => $afterCatSecSlider4,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
     public function HomePagePartOneRegionalNew($city, Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        $type8 = 8;
        Cache::forget('homesliders_'.$type3);
        if(Cache::has('homesliders_'.$type3))
            $mainsliders = Cache::get('homesliders_'.$type3);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type3,
                'type' => 'slider'
            ]);
            $mainsliders = Cache::remember('homesliders_'.$type3, $seconds, function () use ($type3) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type3)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // right slider
        if(Cache::has('homesliders_'.$type8))
            $rightSliders = Cache::get('homesliders_'.$type8);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type8,
                'type' => 'slider'
            ]);
            $rightSliders = Cache::remember('homesliders_'.$type8, $seconds, function () use ($type8) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type8)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // three images
        if(Cache::has('threeimages'))
            $threeImages = Cache::get('threeimages');
        else{
            CacheStores::create([
                'key' => 'threeimages',
                'type' => 'slider'
            ]);
            $threeImages = Cache::remember('threeimages', $seconds, function () {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', 9)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type')->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        if(Cache::has('homesecone'))
            $homesecone = Cache::get('homesecone');
        else{
            $firstSec = HomePage::first(['cat_view_all', 'cat_heading', 'cat_heading_arabic', 'categories_top', 'categories_top_status']);
            if($firstSec->categories_top_status == 1){
                $categoryids = explode(',',$firstSec->categories_top);
                $category = Productcategory::select('id', 'name', 'name_arabic', 'slug', 'web_image_media','selltype','sellvalue')->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
            }else{
                $category = [];
            }
    
            $homesecone = [
                'first_sec_fields' => $firstSec,
                'first_sec_categories' => $category,
            ];
            CacheStores::create([
                'key' => 'homesecone',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecone', $seconds, function () use ($homesecone) {
                return $homesecone;
            });
        }
        
        
        if(Cache::has('homesectwo'))
            $homesectwo = Cache::get('homesectwo');
        else{
            $secondSec = HomePage::select(['pro_first_view_all', 'pro_first_heading', 'pro_first_heading_arabic', 'products_first_status', 'products_first'])->first();

            if($secondSec->products_first_status == 1 && isset($secondSec->products_first)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $secondSec->products_first);
                $productFirstData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productFirstData = [];
            }
    
            $homesectwo = [
                'second_sec_fields' => $secondSec,
                'productSecondtData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesectwo',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwo', $seconds, function () use ($homesectwo) {
                return $homesectwo;
            });
        }
         //after sec category
        if(Cache::has('homesecaftercat'))
            $homeaftercatsec = Cache::get('homesecaftercat');
        else{
            $afterCatSec = HomePage::select(['pro_twelveth_view_all', 'pro_twelveth_heading', 'pro_twelveth_heading_arabic', 'products_twelveth_status', 'products_twelveth','products_twelveth_date'])->first();

            if($afterCatSec->products_twelveth_status == 1 && isset($afterCatSec->products_twelveth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $afterCatSec->products_twelveth);
                $productFirstData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productFirstData = [];
            }
    
            $homeaftercatsec = [
                'afterCatSec' => $afterCatSec,
                'productAfterSectData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesecaftercat',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecaftercat', $seconds, function () use ($homeaftercatsec) {
                return $homeaftercatsec;
            });
        }
        $afterSecCat = HomePage::with('BannerImageOne:id,image','BannerImageTwo:id,image','BannerImageThird:id,image','BannerImageFourth:id,image')->select(['banner_image1', 'banner_image1_link','banner_image2','banner_image2_link',
                                'banner_image3','banner_image3_link','banner_image4','banner_image4_link','banner_first_status','banner_second_status','banner_first_heading','banner_first_heading_arabic',
                                'banner_second_heading','banner_second_heading_arabic'])->first();
                                
        $afterCatSecSlider1 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 10)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
        $afterCatSecSlider2 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 11)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        $afterCatSecSlider3 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 12)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
        $afterCatSecSlider4 = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                            ->where('position', 13)
                            ->where('status', 1)
                            ->select('id', 'name', 'name_ar', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type')->get();
        
                                
        $response = [
            'data' => [
                'mainsliders' => $mainsliders,
                'rightSliders' => $rightSliders,
                'threeImages' => $threeImages,
                'homesecone' => $homesecone,
                'homesectwo' => $homesectwo,
                'homesecaftercat' => $homeaftercatsec,
                'afterSecCat' => $afterSecCat,
                'afterSecCatSlider1' => $afterCatSecSlider1,
                'afterSecCatSlider2' => $afterCatSecSlider2,
                'afterSecCatSlider3' => $afterCatSecSlider3,
                'afterSecCatSlider4' => $afterCatSecSlider4,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function HomePagePartTwo(Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        
        if(Cache::has('homesecthree'))
            $homesecthree = Cache::get('homesecthree');
        else{
            $thirdSec = HomePage::select(['pro_second_view_all', 'pro_second_heading', 'pro_second_heading_arabic', 'products_second_status', 'products_second'])->first();

            if($thirdSec->products_second_status == 1 && isset($thirdSec->products_second)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirdSec->products_second);
                $productThirdData = ProductListingHelper::productData($filters);
            }else{
                $productThirdData = [];
            }
    
            $homesecthree = [
                'third_sec_fields' => $thirdSec,
                'productThirdtData' => $productThirdData,
            ];
            CacheStores::create([
                'key' => 'homesecthree',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthree', $seconds, function () use ($homesecthree) {
                return $homesecthree;
            });
        }
        
        if(Cache::has('homesecfour'))
            $homesecfour = Cache::get('homesecfour');
        else{
            $fourthSec = HomePage::select(['brands_middle_status', 'brands_middle', 'brand_view_all'])->first();

            if($fourthSec->brands_middle_status == 1){
                $brandids = explode(',',$fourthSec->brands_middle);
                $brands = Brand::withCount('productname')->with([
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    // $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon','image_link_app')
                    // 'icon',
                    $categories = Productcategory::select('name', 'name_arabic', 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
            $type5 = 5;
            $midsliders = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type5)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
    
            $homesecfour = [
                'fourth_sec_fields' => $fourthSec,
                'brandThirdData' => $brands,
                'middleSliderThree' => $midsliders,
            ];
            CacheStores::create([
                'key' => 'homesecfour',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfour', $seconds, function () use ($homesecfour) {
                return $homesecfour;
            });
        }
        
        $type2 = 2;
        Cache::forget('homesliders_'.$type2);
        if(Cache::has('homesliders_'.$type2))
            $topsliders = Cache::get('homesliders_'.$type2);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type2,
                'type' => 'slider'
            ]);
            $topsliders = Cache::remember('homesliders_'.$type2, $seconds, function () use ($type2) {
                // , 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic'
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type2)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecfive'))
            $homesecfive = Cache::get('homesecfive');
        else{
            $fifthSec = HomePage::select(['pro_third_view_all', 'pro_third_heading', 'pro_third_heading_arabic', 'products_third_status', 'products_third'])->first();

            if($fifthSec->products_third_status == 1 && isset($fifthSec->products_third)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $fifthSec->products_third);
                $productthirdData = ProductListingHelper::productData($filters);
            }else{
                $productthirdData = [];
            }
    
            $homesecfive = [
                'fifth_sec_fields' => $fifthSec,
                'productFifthData' => $productthirdData,
            ];
            CacheStores::create([
                'key' => 'homesecfive',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfive', $seconds, function () use ($homesecfive) {
                return $homesecfive;
            });
        }
        
        if(Cache::has('homesecsix'))
            $homesecsix = Cache::get('homesecsix');
        else{
            $sixthSec = HomePage::select(['pro_fourth_view_all', 'pro_fourth_heading', 'pro_fourth_heading_arabic', 'products_fourth_status', 'products_fourth'])->first();

            if($sixthSec->products_fourth_status == 1 && isset($sixthSec->products_fourth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $sixthSec->products_fourth);
                $productfourthData = ProductListingHelper::productData($filters);
            }else{
                $productfourthData = [];
            }
    
            $homesecsix = [
                'sixth_sec_fields' => $sixthSec,
                'productSixthData' => $productfourthData,
            ];
            CacheStores::create([
                'key' => 'homesecsix',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecsix', $seconds, function () use ($homesecsix) {
                return $homesecsix;
            });
        }
        
        $BSData = [];
        $filters = [];
        $BSProcategories = Productcategory::with('bestSellerCategory')->where('best_seller',1)->get(['id','best_seller','name','name_arabic','slug']);
        if($BSProcategories){
            foreach($BSProcategories as $k => $BSProcategory){
                    // $BSData[$BSProcategory->id]['category'] = $BSProcategory;
                    
                    $BSData[$BSProcategory->id]['category'] = [
                        'id'=>$BSProcategory->id,
                        'name'=>$BSProcategory->name,
                        'name_arabic'=>$BSProcategory->name_arabic,
                        'slug'=>$BSProcategory->slug,
                        ]; 
                if(isset($BSProcategory->bestSellerCategory) && $BSProcategory->bestSellerCategory){
                    $filters = ['take' => 6, 'page' => 1];
                    $filters['productbyid'] = $BSProcategory->bestSellerCategory->pluck('id')->toArray();
                    $BSData[$BSProcategory->id]['prodata'] = ProductListingHelper::productData($filters);
                }else{
                    $BSData[$BSProcategory->id]['prodata'] = [];
                }
            }
        }
        
            
        $response = [
            'data' => [
                'homesecthree' => $homesecthree,
                'homesecfour' => $homesecfour,
                'topsliders' => $topsliders,
                'homesecfive' => $homesecfive,
                'homesecsix' => $homesecsix,
                'bsdata' => $BSData,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    //Regional
    public function HomePagePartTwoRegional(Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        
        if(Cache::has('homesecthree'))
            $homesecthree = Cache::get('homesecthree');
        else{
            // echo 'die';
            // die();
            $thirdSec = HomePage::select(['pro_second_view_all', 'pro_second_heading', 'pro_second_heading_arabic', 'products_second_status', 'products_second'])->first();

            if($thirdSec->products_second_status == 1 && isset($thirdSec->products_second)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirdSec->products_second);
                $productThirdData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productThirdData = [];
            }
    
            $homesecthree = [
                'third_sec_fields' => $thirdSec,
                'productThirdtData' => $productThirdData,
            ];
            CacheStores::create([
                'key' => 'homesecthree',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthree', $seconds, function () use ($homesecthree) {
                return $homesecthree;
            });
        }
        
        if(Cache::has('homesecfour'))
            $homesecfour = Cache::get('homesecfour');
        else{
            $fourthSec = HomePage::select(['brands_middle_status', 'brands_middle', 'brand_view_all'])->first();

            if($fourthSec->brands_middle_status == 1){
                $brandids = explode(',',$fourthSec->brands_middle);
                $brands = Brand::withCount('productname')->with([
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    // $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon','image_link_app')
                    // 'icon',
                    $categories = Productcategory::select('name', 'name_arabic', 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
            $type5 = 5;
            $midsliders = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type5)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
    
            $homesecfour = [
                'fourth_sec_fields' => $fourthSec,
                'brandThirdData' => $brands,
                'middleSliderThree' => $midsliders,
            ];
            CacheStores::create([
                'key' => 'homesecfour',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfour', $seconds, function () use ($homesecfour) {
                return $homesecfour;
            });
        }
        
        $type2 = 2;
        Cache::forget('homesliders_'.$type2);
        if(Cache::has('homesliders_'.$type2))
            $topsliders = Cache::get('homesliders_'.$type2);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type2,
                'type' => 'slider'
            ]);
            $topsliders = Cache::remember('homesliders_'.$type2, $seconds, function () use ($type2) {
                // , 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic'
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type2)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecfive'))
            $homesecfive = Cache::get('homesecfive');
        else{
            $fifthSec = HomePage::select(['pro_third_view_all', 'pro_third_heading', 'pro_third_heading_arabic', 'products_third_status', 'products_third'])->first();

            if($fifthSec->products_third_status == 1 && isset($fifthSec->products_third)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $fifthSec->products_third);
                $productthirdData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productthirdData = [];
            }
    
            $homesecfive = [
                'fifth_sec_fields' => $fifthSec,
                'productFifthData' => $productthirdData,
            ];
            CacheStores::create([
                'key' => 'homesecfive',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfive', $seconds, function () use ($homesecfive) {
                return $homesecfive;
            });
        }
        
        if(Cache::has('homesecsix'))
            $homesecsix = Cache::get('homesecsix');
        else{
            $sixthSec = HomePage::select(['pro_fourth_view_all', 'pro_fourth_heading', 'pro_fourth_heading_arabic', 'products_fourth_status', 'products_fourth'])->first();

            if($sixthSec->products_fourth_status == 1 && isset($sixthSec->products_fourth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $sixthSec->products_fourth);
                $productfourthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productfourthData = [];
            }
    
            $homesecsix = [
                'sixth_sec_fields' => $sixthSec,
                'productSixthData' => $productfourthData,
            ];
            CacheStores::create([
                'key' => 'homesecsix',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecsix', $seconds, function () use ($homesecsix) {
                return $homesecsix;
            });
        }
        
        $BSData = [];
        $filters = [];
        $BSProcategories = Productcategory::with('bestSellerCategory')->where('best_seller',1)->get(['id','best_seller','name','name_arabic','slug']);
        if($BSProcategories){
            foreach($BSProcategories as $k => $BSProcategory){
                    // $BSData[$BSProcategory->id]['category'] = $BSProcategory;
                    
                    $BSData[$BSProcategory->id]['category'] = [
                        'id'=>$BSProcategory->id,
                        'name'=>$BSProcategory->name,
                        'name_arabic'=>$BSProcategory->name_arabic,
                        'slug'=>$BSProcategory->slug,
                        ]; 
                if(isset($BSProcategory->bestSellerCategory) && $BSProcategory->bestSellerCategory){
                    $filters = ['take' => 6, 'page' => 1];
                    $filters['productbyid'] = $BSProcategory->bestSellerCategory->pluck('id')->toArray();
                    $BSData[$BSProcategory->id]['prodata'] = ProductListingHelper::productDataRegional($filters);
                }else{
                    $BSData[$BSProcategory->id]['prodata'] = [];
                }
            }
        }
        
            
        $response = [
            'data' => [
                'homesecthree' => $homesecthree,
                'homesecfour' => $homesecfour,
                'topsliders' => $topsliders,
                'homesecfive' => $homesecfive,
                'homesecsix' => $homesecsix,
                'bsdata' => $BSData,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
      public function HomePagePartTwoRegionalNew($city, Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        
        if(Cache::has('homesecthree'))
            $homesecthree = Cache::get('homesecthree');
        else{
            // echo 'die';
            // die();
            $thirdSec = HomePage::select(['pro_second_view_all', 'pro_second_heading', 'pro_second_heading_arabic', 'products_second_status', 'products_second'])->first();

            if($thirdSec->products_second_status == 1 && isset($thirdSec->products_second)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirdSec->products_second);
                $productThirdData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productThirdData = [];
            }
    
            $homesecthree = [
                'third_sec_fields' => $thirdSec,
                'productThirdtData' => $productThirdData,
            ];
            CacheStores::create([
                'key' => 'homesecthree',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthree', $seconds, function () use ($homesecthree) {
                return $homesecthree;
            });
        }
        
        if(Cache::has('homesecfour'))
            $homesecfour = Cache::get('homesecfour');
        else{
            $fourthSec = HomePage::select(['brands_middle_status', 'brands_middle', 'brand_view_all'])->first();

            if($fourthSec->brands_middle_status == 1){
                $brandids = explode(',',$fourthSec->brands_middle);
                $brands = Brand::withCount('productname')->with([
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    // $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon','image_link_app')
                    // 'icon',
                    $categories = Productcategory::select('name', 'name_arabic', 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
            $type5 = 5;
            $midsliders = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type5)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
    
            $homesecfour = [
                'fourth_sec_fields' => $fourthSec,
                'brandThirdData' => $brands,
                'middleSliderThree' => $midsliders,
            ];
            CacheStores::create([
                'key' => 'homesecfour',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfour', $seconds, function () use ($homesecfour) {
                return $homesecfour;
            });
        }
        
        $type2 = 2;
        Cache::forget('homesliders_'.$type2);
        if(Cache::has('homesliders_'.$type2))
            $topsliders = Cache::get('homesliders_'.$type2);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type2,
                'type' => 'slider'
            ]);
            $topsliders = Cache::remember('homesliders_'.$type2, $seconds, function () use ($type2) {
                // , 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic'
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type2)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecfive'))
            $homesecfive = Cache::get('homesecfive');
        else{
            $fifthSec = HomePage::select(['pro_third_view_all', 'pro_third_heading', 'pro_third_heading_arabic', 'products_third_status', 'products_third'])->first();

            if($fifthSec->products_third_status == 1 && isset($fifthSec->products_third)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $fifthSec->products_third);
                $productthirdData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productthirdData = [];
            }
    
            $homesecfive = [
                'fifth_sec_fields' => $fifthSec,
                'productFifthData' => $productthirdData,
            ];
            CacheStores::create([
                'key' => 'homesecfive',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfive', $seconds, function () use ($homesecfive) {
                return $homesecfive;
            });
        }
        
        if(Cache::has('homesecsix'))
            $homesecsix = Cache::get('homesecsix');
        else{
            $sixthSec = HomePage::select(['pro_fourth_view_all', 'pro_fourth_heading', 'pro_fourth_heading_arabic', 'products_fourth_status', 'products_fourth'])->first();

            if($sixthSec->products_fourth_status == 1 && isset($sixthSec->products_fourth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $sixthSec->products_fourth);
                $productfourthData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productfourthData = [];
            }
    
            $homesecsix = [
                'sixth_sec_fields' => $sixthSec,
                'productSixthData' => $productfourthData,
            ];
            CacheStores::create([
                'key' => 'homesecsix',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecsix', $seconds, function () use ($homesecsix) {
                return $homesecsix;
            });
        }
        
        $BSData = [];
        $filters = [];
        $BSProcategories = Productcategory::with('bestSellerCategory')->where('best_seller',1)->get(['id','best_seller','name','name_arabic','slug']);
        if($BSProcategories){
            foreach($BSProcategories as $k => $BSProcategory){
                    // $BSData[$BSProcategory->id]['category'] = $BSProcategory;
                    
                    $BSData[$BSProcategory->id]['category'] = [
                        'id'=>$BSProcategory->id,
                        'name'=>$BSProcategory->name,
                        'name_arabic'=>$BSProcategory->name_arabic,
                        'slug'=>$BSProcategory->slug,
                        ]; 
                if(isset($BSProcategory->bestSellerCategory) && $BSProcategory->bestSellerCategory){
                    $filters = ['take' => 6, 'page' => 1];
                    $filters['productbyid'] = $BSProcategory->bestSellerCategory->pluck('id')->toArray();
                    $BSData[$BSProcategory->id]['prodata'] = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
                }else{
                    $BSData[$BSProcategory->id]['prodata'] = [];
                }
            }
        }
        
            
        $response = [
            'data' => [
                'homesecthree' => $homesecthree,
                'homesecfour' => $homesecfour,
                'topsliders' => $topsliders,
                'homesecfive' => $homesecfive,
                'homesecsix' => $homesecsix,
                'bsdata' => $BSData,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function HomePagePartThree(Request $request) {
        
        $seconds = 86400;
        $type4 = 4;
        Cache::forget('homesliders_'.$type4);
        if(Cache::has('homesliders_'.$type4))
            $middlesliders = Cache::get('homesliders_'.$type4);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type4,
                'type' => 'slider'
            ]);
            $middlesliders = Cache::remember('homesliders_'.$type4, $seconds, function () use ($type4) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type4)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type','sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecseven'))
            $homesecseven = Cache::get('homesecseven');
        else{
            $seventhSec = HomePage::select(['pro_fifth_view_all', 'pro_fifth_heading', 'pro_fifth_heading_arabic', 'products_fifth_status', 'products_fifth'])->first();

            if($seventhSec->products_fifth_status == 1 && isset($seventhSec->products_fifth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $seventhSec->products_fifth);
                $productfifthData = ProductListingHelper::productData($filters);
            }else{
                $productfifthData = [];
            }
    
            $homesecseven = [
                'seventh_sec_fields' => $seventhSec,
                'productSeventhData' => $productfifthData,
            ];
            CacheStores::create([
                'key' => 'homesecseven',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecseven', $seconds, function () use ($homesecseven) {
                return $homesecseven;
            });
        }
        
        
        if(Cache::has('homeseceight'))
            $homeseceight = Cache::get('homeseceight');
        else{
            $eightSec = HomePage::select(['pro_sixth_view_all', 'pro_sixth_heading', 'pro_sixth_heading_arabic', 'products_sixth_status', 'products_sixth'])->first();

            if($eightSec->products_sixth_status == 1 && isset($eightSec->products_sixth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $eightSec->products_sixth);
                $productsixthData = ProductListingHelper::productData($filters);
            }else{
                $productsixthData = [];
            }
    
            $homeseceight = [
                'eight_sec_fields' => $eightSec,
                'productEightData' => $productsixthData,
            ];
            CacheStores::create([
                'key' => 'homeseceight',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceight', $seconds, function () use ($homeseceight) {
                return $homeseceight;
            });
        }
        
        if(Cache::has('homesecnine'))
            $homesecnine = Cache::get('homesecnine');
        else{
            $nineSec = HomePage::select(['pro_seventh_view_all', 'pro_seventh_heading', 'pro_seventh_heading_arabic', 'products_seventh_status', 'products_seventh'])->first();

            if($nineSec->products_seventh_status == 1 && isset($nineSec->products_seventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $nineSec->products_seventh);
                $productseventhData = ProductListingHelper::productData($filters);
            }else{
                $productseventhData = [];
            }
    
            $homesecnine = [
                'nine_sec_fields' => $nineSec,
                'productNineData' => $productseventhData,
            ];
            CacheStores::create([
                'key' => 'homesecnine',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecnine', $seconds, function () use ($homesecnine) {
                return $homesecnine;
            });
        }
        
        
        $type6 = 6;
        Cache::forget('homesliders_'.$type6);
        if(Cache::has('homesliders_'.$type6))
            $bottomsliders = Cache::get('homesliders_'.$type6);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type6,
                'type' => 'slider'
            ]);
            $bottomsliders = Cache::remember('homesliders_'.$type6, $seconds, function () use ($type6) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type6)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type', 'timer')->get();
            });
        }
        
        
        if(Cache::has('homesecten'))
            $homesecten = Cache::get('homesecten');
        else{
            $tenSec = HomePage::select(['pro_eigth_view_all', 'pro_eigth_heading', 'pro_eigth_heading_arabic', 'products_eigth_status', 'products_eigth'])->first();

            if($tenSec->products_eigth_status == 1 && isset($tenSec->products_eigth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $tenSec->products_eigth);
                $producteigthData = ProductListingHelper::productData($filters);
            }else{
                $producteigthData = [];
            }
    
            $homesecten = [
                'ten_sec_fields' => $tenSec,
                'productTenData' => $producteigthData,
            ];
            CacheStores::create([
                'key' => 'homesecten',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecten', $seconds, function () use ($homesecten) {
                return $homesecten;
            });
        }
        
        
        if(Cache::has('homeseceleven'))
            $homeseceleven = Cache::get('homeseceleven');
        else{
            $elevenSec = HomePage::select(['pro_nineth_view_all', 'pro_nineth_heading', 'pro_nineth_heading_arabic', 'products_nineth_status', 'products_nineth'])->first();

            if($elevenSec->products_nineth_status == 1 && isset($elevenSec->products_nineth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $elevenSec->products_nineth);
                $productninethData = ProductListingHelper::productData($filters);
            }
            else{
                $productninethData = [];
            }
    
            $homeseceleven = [
                'eleven_sec_fields' => $elevenSec,
                'productElevenData' => $productninethData,
            ];
            CacheStores::create([
                'key' => 'homeseceleven',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceleven', $seconds, function () use ($homeseceleven) {
                return $homeseceleven;
            });
        }
        
        
        if(Cache::has('homesectwelve'))
            $homesectwelve = Cache::get('homesectwelve');
        else{
            $twelveSec = HomePage::select(['pro_tenth_view_all', 'pro_tenth_heading', 'pro_tenth_heading_arabic', 'products_tenth_status', 'products_tenth'])->first();

            if($twelveSec->products_tenth_status == 1 && isset($twelveSec->products_tenth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $twelveSec->products_tenth);
                $producttenthData = ProductListingHelper::productData($filters);
            }
            else{
                $producttenthData = [];
            }
    
            $homesectwelve = [
                'twelve_sec_fields' => $twelveSec,
                'productTwelveData' => $producttenthData,
            ];
            CacheStores::create([
                'key' => 'homesectwelve',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwelve', $seconds, function () use ($homesectwelve) {
                return $homesectwelve;
            });
        }
        
        
        if(Cache::has('homesecthirteen'))
            $homesecthirteen = Cache::get('homesecthirteen');
        else{
            $thirteenSec = HomePage::select(['pro_eleventh_view_all', 'pro_eleventh_heading', 'pro_eleventh_heading_arabic', 'products_eleventh_status', 'products_eleventh'])->first();

            if($thirteenSec->products_eleventh_status == 1 && isset($thirteenSec->products_eleventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirteenSec->products_eleventh);
                $producteleventhData = ProductListingHelper::productData($filters);
            }
            else{
                $producteleventhData = [];
            }
    
            $homesecthirteen = [
                'thirteen_sec_fields' => $thirteenSec,
                'productThirteenData' => $producteleventhData,
            ];
            CacheStores::create([
                'key' => 'homesecthirteen',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthirteen', $seconds, function () use ($homesecthirteen) {
                return $homesecthirteen;
            });
        }
        
            
        $response = [
            'data' => [
                'middlesliders' => $middlesliders,
                'homesecseven' => $homesecseven,
                'homeseceight' => $homeseceight,
                'homesecnine' => $homesecnine,
                'bottomsliders' => $bottomsliders,
                'homesecten' => $homesecten,
                'homeseceleven' => $homeseceleven,
                'homesectwelve' => $homesectwelve,
                'homesecthirteen' => $homesecthirteen,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    //Regional
    public function HomePagePartThreeRegional(Request $request) {
        
        $seconds = 86400;
        $type4 = 4;
        Cache::forget('homesliders_'.$type4);
        if(Cache::has('homesliders_'.$type4))
            $middlesliders = Cache::get('homesliders_'.$type4);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type4,
                'type' => 'slider'
            ]);
            $middlesliders = Cache::remember('homesliders_'.$type4, $seconds, function () use ($type4) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type4)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type','sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecseven'))
            $homesecseven = Cache::get('homesecseven');
        else{
            $seventhSec = HomePage::select(['pro_fifth_view_all', 'pro_fifth_heading', 'pro_fifth_heading_arabic', 'products_fifth_status', 'products_fifth'])->first();

            if($seventhSec->products_fifth_status == 1 && isset($seventhSec->products_fifth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $seventhSec->products_fifth);
                $productfifthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productfifthData = [];
            }
    
            $homesecseven = [
                'seventh_sec_fields' => $seventhSec,
                'productSeventhData' => $productfifthData,
            ];
            CacheStores::create([
                'key' => 'homesecseven',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecseven', $seconds, function () use ($homesecseven) {
                return $homesecseven;
            });
        }
        
        
        if(Cache::has('homeseceight'))
            $homeseceight = Cache::get('homeseceight');
        else{
            $eightSec = HomePage::select(['pro_sixth_view_all', 'pro_sixth_heading', 'pro_sixth_heading_arabic', 'products_sixth_status', 'products_sixth'])->first();

            if($eightSec->products_sixth_status == 1 && isset($eightSec->products_sixth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $eightSec->products_sixth);
                $productsixthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productsixthData = [];
            }
    
            $homeseceight = [
                'eight_sec_fields' => $eightSec,
                'productEightData' => $productsixthData,
            ];
            CacheStores::create([
                'key' => 'homeseceight',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceight', $seconds, function () use ($homeseceight) {
                return $homeseceight;
            });
        }
        
        if(Cache::has('homesecnine'))
            $homesecnine = Cache::get('homesecnine');
        else{
            $nineSec = HomePage::select(['pro_seventh_view_all', 'pro_seventh_heading', 'pro_seventh_heading_arabic', 'products_seventh_status', 'products_seventh'])->first();

            if($nineSec->products_seventh_status == 1 && isset($nineSec->products_seventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $nineSec->products_seventh);
                $productseventhData = ProductListingHelper::productDataRegional($filters);
            }else{
                $productseventhData = [];
            }
    
            $homesecnine = [
                'nine_sec_fields' => $nineSec,
                'productNineData' => $productseventhData,
            ];
            CacheStores::create([
                'key' => 'homesecnine',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecnine', $seconds, function () use ($homesecnine) {
                return $homesecnine;
            });
        }
        
        
        $type6 = 6;
        Cache::forget('homesliders_'.$type6);
        if(Cache::has('homesliders_'.$type6))
            $bottomsliders = Cache::get('homesliders_'.$type6);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type6,
                'type' => 'slider'
            ]);
            $bottomsliders = Cache::remember('homesliders_'.$type6, $seconds, function () use ($type6) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type6)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type', 'timer')->get();
            });
        }
        
        
        if(Cache::has('homesecten'))
            $homesecten = Cache::get('homesecten');
        else{
            $tenSec = HomePage::select(['pro_eigth_view_all', 'pro_eigth_heading', 'pro_eigth_heading_arabic', 'products_eigth_status', 'products_eigth'])->first();

            if($tenSec->products_eigth_status == 1 && isset($tenSec->products_eigth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $tenSec->products_eigth);
                $producteigthData = ProductListingHelper::productDataRegional($filters);
            }else{
                $producteigthData = [];
            }
    
            $homesecten = [
                'ten_sec_fields' => $tenSec,
                'productTenData' => $producteigthData,
            ];
            CacheStores::create([
                'key' => 'homesecten',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecten', $seconds, function () use ($homesecten) {
                return $homesecten;
            });
        }
        
        
        if(Cache::has('homeseceleven'))
            $homeseceleven = Cache::get('homeseceleven');
        else{
            $elevenSec = HomePage::select(['pro_nineth_view_all', 'pro_nineth_heading', 'pro_nineth_heading_arabic', 'products_nineth_status', 'products_nineth'])->first();

            if($elevenSec->products_nineth_status == 1 && isset($elevenSec->products_nineth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $elevenSec->products_nineth);
                $productninethData = ProductListingHelper::productDataRegional($filters);
            }
            else{
                $productninethData = [];
            }
    
            $homeseceleven = [
                'eleven_sec_fields' => $elevenSec,
                'productElevenData' => $productninethData,
            ];
            CacheStores::create([
                'key' => 'homeseceleven',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceleven', $seconds, function () use ($homeseceleven) {
                return $homeseceleven;
            });
        }
        
        
        if(Cache::has('homesectwelve'))
            $homesectwelve = Cache::get('homesectwelve');
        else{
            $twelveSec = HomePage::select(['pro_tenth_view_all', 'pro_tenth_heading', 'pro_tenth_heading_arabic', 'products_tenth_status', 'products_tenth'])->first();

            if($twelveSec->products_tenth_status == 1 && isset($twelveSec->products_tenth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $twelveSec->products_tenth);
                $producttenthData = ProductListingHelper::productDataRegional($filters);
            }
            else{
                $producttenthData = [];
            }
    
            $homesectwelve = [
                'twelve_sec_fields' => $twelveSec,
                'productTwelveData' => $producttenthData,
            ];
            CacheStores::create([
                'key' => 'homesectwelve',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwelve', $seconds, function () use ($homesectwelve) {
                return $homesectwelve;
            });
        }
        
        
        if(Cache::has('homesecthirteen'))
            $homesecthirteen = Cache::get('homesecthirteen');
        else{
            $thirteenSec = HomePage::select(['pro_eleventh_view_all', 'pro_eleventh_heading', 'pro_eleventh_heading_arabic', 'products_eleventh_status', 'products_eleventh'])->first();

            if($thirteenSec->products_eleventh_status == 1 && isset($thirteenSec->products_eleventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirteenSec->products_eleventh);
                $producteleventhData = ProductListingHelper::productDataRegional($filters);
            }
            else{
                $producteleventhData = [];
            }
    
            $homesecthirteen = [
                'thirteen_sec_fields' => $thirteenSec,
                'productThirteenData' => $producteleventhData,
            ];
            CacheStores::create([
                'key' => 'homesecthirteen',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthirteen', $seconds, function () use ($homesecthirteen) {
                return $homesecthirteen;
            });
        }
        
            
        $response = [
            'data' => [
                'middlesliders' => $middlesliders,
                'homesecseven' => $homesecseven,
                'homeseceight' => $homeseceight,
                'homesecnine' => $homesecnine,
                'bottomsliders' => $bottomsliders,
                'homesecten' => $homesecten,
                'homeseceleven' => $homeseceleven,
                'homesectwelve' => $homesectwelve,
                'homesecthirteen' => $homesecthirteen,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function HomePagePartThreeRegionalNew($city, Request $request) {
        
        $seconds = 86400;
        $type4 = 4;
        Cache::forget('homesliders_'.$type4);
        if(Cache::has('homesliders_'.$type4))
            $middlesliders = Cache::get('homesliders_'.$type4);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type4,
                'type' => 'slider'
            ]);
            $middlesliders = Cache::remember('homesliders_'.$type4, $seconds, function () use ($type4) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type4)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type','sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecseven'))
            $homesecseven = Cache::get('homesecseven');
        else{
            $seventhSec = HomePage::select(['pro_fifth_view_all', 'pro_fifth_heading', 'pro_fifth_heading_arabic', 'products_fifth_status', 'products_fifth'])->first();

            if($seventhSec->products_fifth_status == 1 && isset($seventhSec->products_fifth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $seventhSec->products_fifth);
                $productfifthData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productfifthData = [];
            }
    
            $homesecseven = [
                'seventh_sec_fields' => $seventhSec,
                'productSeventhData' => $productfifthData,
            ];
            CacheStores::create([
                'key' => 'homesecseven',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecseven', $seconds, function () use ($homesecseven) {
                return $homesecseven;
            });
        }
        
        
        if(Cache::has('homeseceight'))
            $homeseceight = Cache::get('homeseceight');
        else{
            $eightSec = HomePage::select(['pro_sixth_view_all', 'pro_sixth_heading', 'pro_sixth_heading_arabic', 'products_sixth_status', 'products_sixth'])->first();

            if($eightSec->products_sixth_status == 1 && isset($eightSec->products_sixth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $eightSec->products_sixth);
                $productsixthData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productsixthData = [];
            }
    
            $homeseceight = [
                'eight_sec_fields' => $eightSec,
                'productEightData' => $productsixthData,
            ];
            CacheStores::create([
                'key' => 'homeseceight',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceight', $seconds, function () use ($homeseceight) {
                return $homeseceight;
            });
        }
        
        if(Cache::has('homesecnine'))
            $homesecnine = Cache::get('homesecnine');
        else{
            $nineSec = HomePage::select(['pro_seventh_view_all', 'pro_seventh_heading', 'pro_seventh_heading_arabic', 'products_seventh_status', 'products_seventh'])->first();

            if($nineSec->products_seventh_status == 1 && isset($nineSec->products_seventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $nineSec->products_seventh);
                $productseventhData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $productseventhData = [];
            }
    
            $homesecnine = [
                'nine_sec_fields' => $nineSec,
                'productNineData' => $productseventhData,
            ];
            CacheStores::create([
                'key' => 'homesecnine',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecnine', $seconds, function () use ($homesecnine) {
                return $homesecnine;
            });
        }
        
        
        $type6 = 6;
        Cache::forget('homesliders_'.$type6);
        if(Cache::has('homesliders_'.$type6))
            $bottomsliders = Cache::get('homesliders_'.$type6);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type6,
                'type' => 'slider'
            ]);
            $bottomsliders = Cache::remember('homesliders_'.$type6, $seconds, function () use ($type6) {
                return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type6)
                    ->where('status', 1)
                    ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type', 'timer')->get();
            });
        }
        
        
        if(Cache::has('homesecten'))
            $homesecten = Cache::get('homesecten');
        else{
            $tenSec = HomePage::select(['pro_eigth_view_all', 'pro_eigth_heading', 'pro_eigth_heading_arabic', 'products_eigth_status', 'products_eigth'])->first();

            if($tenSec->products_eigth_status == 1 && isset($tenSec->products_eigth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $tenSec->products_eigth);
                $producteigthData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }else{
                $producteigthData = [];
            }
    
            $homesecten = [
                'ten_sec_fields' => $tenSec,
                'productTenData' => $producteigthData,
            ];
            CacheStores::create([
                'key' => 'homesecten',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecten', $seconds, function () use ($homesecten) {
                return $homesecten;
            });
        }
        
        
        if(Cache::has('homeseceleven'))
            $homeseceleven = Cache::get('homeseceleven');
        else{
            $elevenSec = HomePage::select(['pro_nineth_view_all', 'pro_nineth_heading', 'pro_nineth_heading_arabic', 'products_nineth_status', 'products_nineth'])->first();

            if($elevenSec->products_nineth_status == 1 && isset($elevenSec->products_nineth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $elevenSec->products_nineth);
                $productninethData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }
            else{
                $productninethData = [];
            }
    
            $homeseceleven = [
                'eleven_sec_fields' => $elevenSec,
                'productElevenData' => $productninethData,
            ];
            CacheStores::create([
                'key' => 'homeseceleven',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceleven', $seconds, function () use ($homeseceleven) {
                return $homeseceleven;
            });
        }
        
        
        if(Cache::has('homesectwelve'))
            $homesectwelve = Cache::get('homesectwelve');
        else{
            $twelveSec = HomePage::select(['pro_tenth_view_all', 'pro_tenth_heading', 'pro_tenth_heading_arabic', 'products_tenth_status', 'products_tenth'])->first();

            if($twelveSec->products_tenth_status == 1 && isset($twelveSec->products_tenth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $twelveSec->products_tenth);
                $producttenthData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }
            else{
                $producttenthData = [];
            }
    
            $homesectwelve = [
                'twelve_sec_fields' => $twelveSec,
                'productTwelveData' => $producttenthData,
            ];
            CacheStores::create([
                'key' => 'homesectwelve',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwelve', $seconds, function () use ($homesectwelve) {
                return $homesectwelve;
            });
        }
        
        
        if(Cache::has('homesecthirteen'))
            $homesecthirteen = Cache::get('homesecthirteen');
        else{
            $thirteenSec = HomePage::select(['pro_eleventh_view_all', 'pro_eleventh_heading', 'pro_eleventh_heading_arabic', 'products_eleventh_status', 'products_eleventh'])->first();

            if($thirteenSec->products_eleventh_status == 1 && isset($thirteenSec->products_eleventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirteenSec->products_eleventh);
                $producteleventhData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
            }
            else{
                $producteleventhData = [];
            }
    
            $homesecthirteen = [
                'thirteen_sec_fields' => $thirteenSec,
                'productThirteenData' => $producteleventhData,
            ];
            CacheStores::create([
                'key' => 'homesecthirteen',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthirteen', $seconds, function () use ($homesecthirteen) {
                return $homesecthirteen;
            });
        }
        
            
        $response = [
            'data' => [
                'middlesliders' => $middlesliders,
                'homesecseven' => $homesecseven,
                'homeseceight' => $homeseceight,
                'homesecnine' => $homesecnine,
                'bottomsliders' => $bottomsliders,
                'homesecten' => $homesecten,
                'homeseceleven' => $homeseceleven,
                'homesectwelve' => $homesectwelve,
                'homesecthirteen' => $homesecthirteen,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    //third optimize-clone
     public function HomePagePartThreeRegionalCopy(Request $request) {
        
        $seconds = 86400;
        $type4 = 4;
        $langType = $request->lang ? $request->lang : 'ar';
        $deviceType = $request->device_type ? $request->device_type : 'mobile';
        $columnName = ($langType == 'en') ? 'name' : 'name_arabic';
        $columnName2 = ($langType == 'en') ? 'name' : 'name_ar';
        $proFifthHeadingColumn = ($langType == 'en') ? 'pro_fifth_heading' : 'pro_fifth_heading_arabic';
        $proSixthHeadingColumn = ($langType == 'en') ? 'pro_sixth_heading' : 'pro_sixth_heading_arabic';
        $proSeventhHeadingColumn = ($langType == 'en') ? 'pro_seventh_heading' : 'pro_seventh_heading_arabic';
        $proELeventhHeadingColumn = ($langType == 'en') ? 'pro_eleventh_heading' : 'pro_eleventh_heading_arabic';
        $proTenthHeadingColumn = ($langType == 'en') ? 'pro_tenth_heading' : 'pro_tenth_heading_arabic';
        $proNineHeadingColumn = ($langType == 'en') ? 'pro_nineth_heading' : 'pro_nineth_heading_arabic';
        $proEightHeadingColumn = ($langType == 'en') ? 'pro_eigth_heading' : 'pro_eigth_heading_arabic';
        //
        
        Cache::forget('homesliders_'.$type4);
        if(Cache::has('homesliders_'.$type4))
            $middlesliders = Cache::get('homesliders_'.$type4);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type4,
                'type' => 'slider'
            ]);
            $middlesliders = Cache::remember('homesliders_'.$type4, $seconds, function () use ($type4, $deviceType, $langType, $columnName2) {
                return Slider::with(
                     $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image' 
                        : 'featuredImageApp:id,image'
                    )
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type4)
                    ->where('status', 1)
                    ->select('id', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName2") , 'slider_type','sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecseven'))
            $homesecseven = Cache::get('homesecseven');
        else{
            $seventhSec = HomePage::select(['pro_fifth_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_fifth_heading ELSE pro_fifth_heading_arabic END as $proFifthHeadingColumn"), 'products_fifth_status', 'products_fifth'])->first();

            if($seventhSec->products_fifth_status == 1 && isset($seventhSec->products_fifth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $seventhSec->products_fifth);
                $productfifthData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productfifthData = [];
            }
    
            $homesecseven = [
                'seventh_sec_fields' => $seventhSec,
                'productSeventhData' => $productfifthData,
            ];
            CacheStores::create([
                'key' => 'homesecseven',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecseven', $seconds, function () use ($homesecseven) {
                return $homesecseven;
            });
        }
        
        
        if(Cache::has('homeseceight'))
            $homeseceight = Cache::get('homeseceight');
        else{
            $eightSec = HomePage::select(['pro_sixth_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_sixth_heading ELSE pro_sixth_heading_arabic END as $proSixthHeadingColumn") ,'products_sixth_status','products_sixth'])->first();

            if($eightSec->products_sixth_status == 1 && isset($eightSec->products_sixth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $eightSec->products_sixth);
                $productsixthData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productsixthData = [];
            }
    
            $homeseceight = [
                'eight_sec_fields' => $eightSec,
                'productEightData' => $productsixthData,
            ];
            CacheStores::create([
                'key' => 'homeseceight',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceight', $seconds, function () use ($homeseceight) {
                return $homeseceight;
            });
        }
        
        if(Cache::has('homesecnine'))
            $homesecnine = Cache::get('homesecnine');
        else{
            $nineSec = HomePage::select(['pro_seventh_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_seventh_heading ELSE pro_seventh_heading_arabic END as $proSeventhHeadingColumn"), 'products_seventh_status', 'products_seventh'])->first();

            if($nineSec->products_seventh_status == 1 && isset($nineSec->products_seventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $nineSec->products_seventh);
                $productseventhData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productseventhData = [];
            }
    
            $homesecnine = [
                'nine_sec_fields' => $nineSec,
                'productNineData' => $productseventhData,
            ];
            CacheStores::create([
                'key' => 'homesecnine',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecnine', $seconds, function () use ($homesecnine) {
                return $homesecnine;
            });
        }
        
        
        $type6 = 6;
        Cache::forget('homesliders_'.$type6);
        if(Cache::has('homesliders_'.$type6))
            $bottomsliders = Cache::get('homesliders_'.$type6);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type6,
                'type' => 'slider'
            ]);
            $bottomsliders = Cache::remember('homesliders_'.$type6, $seconds, function () use ($type6, $deviceType, $langType, $columnName2) {
                return Slider::with(
                     $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image' 
                        : 'featuredImageApp:id,image'
                    )
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type6)
                    ->where('status', 1)
                    ->select('id', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName2") , 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type', 'timer')->get();
            });
        }
        
        
        if(Cache::has('homesecten'))
            $homesecten = Cache::get('homesecten');
        else{
            $tenSec = HomePage::select(['pro_eigth_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_eigth_heading ELSE pro_eigth_heading_arabic END as $proEightHeadingColumn") , 'products_eigth_status', 'products_eigth'])->first();

            if($tenSec->products_eigth_status == 1 && isset($tenSec->products_eigth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $tenSec->products_eigth);
                $producteigthData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $producteigthData = [];
            }
    
            $homesecten = [
                'ten_sec_fields' => $tenSec,
                'productTenData' => $producteigthData,
            ];
            CacheStores::create([
                'key' => 'homesecten',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecten', $seconds, function () use ($homesecten) {
                return $homesecten;
            });
        }
        
        
        if(Cache::has('homeseceleven'))
            $homeseceleven = Cache::get('homeseceleven');
        else{
            $elevenSec = HomePage::select(['pro_nineth_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_nineth_heading ELSE pro_nineth_heading_arabic END as $proNineHeadingColumn"), 'products_nineth_status', 'products_nineth'])->first();

            if($elevenSec->products_nineth_status == 1 && isset($elevenSec->products_nineth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $elevenSec->products_nineth);
                $productninethData = ProductListingHelper::productDataRegionalCopy($filters);
            }
            else{
                $productninethData = [];
            }
    
            $homeseceleven = [
                'eleven_sec_fields' => $elevenSec,
                'productElevenData' => $productninethData,
            ];
            CacheStores::create([
                'key' => 'homeseceleven',
                'type' => 'homepage'
            ]);
            Cache::remember('homeseceleven', $seconds, function () use ($homeseceleven) {
                return $homeseceleven;
            });
        }
        
        
        if(Cache::has('homesectwelve'))
            $homesectwelve = Cache::get('homesectwelve');
        else{
            $twelveSec = HomePage::select(['pro_tenth_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_tenth_heading ELSE pro_tenth_heading_arabic END as $proTenthHeadingColumn") , 'products_tenth_status', 'products_tenth'])->first();

            if($twelveSec->products_tenth_status == 1 && isset($twelveSec->products_tenth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $twelveSec->products_tenth);
                $producttenthData = ProductListingHelper::productDataRegionalCopy($filters);
            }
            else{
                $producttenthData = [];
            }
    
            $homesectwelve = [
                'twelve_sec_fields' => $twelveSec,
                'productTwelveData' => $producttenthData,
            ];
            CacheStores::create([
                'key' => 'homesectwelve',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwelve', $seconds, function () use ($homesectwelve) {
                return $homesectwelve;
            });
        }
        
        
        if(Cache::has('homesecthirteen'))
            $homesecthirteen = Cache::get('homesecthirteen');
        else{
            $thirteenSec = HomePage::select(['pro_eleventh_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_eleventh_heading ELSE pro_eleventh_heading_arabic END as $proELeventhHeadingColumn"), 'products_eleventh_status', 'products_eleventh'])->first();

            if($thirteenSec->products_eleventh_status == 1 && isset($thirteenSec->products_eleventh)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirteenSec->products_eleventh);
                $producteleventhData = ProductListingHelper::productDataRegionalCopy($filters);
            }
            else{
                $producteleventhData = [];
            }
    
            $homesecthirteen = [
                'thirteen_sec_fields' => $thirteenSec,
                'productThirteenData' => $producteleventhData,
            ];
            CacheStores::create([
                'key' => 'homesecthirteen',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthirteen', $seconds, function () use ($homesecthirteen) {
                return $homesecthirteen;
            });
        }
        
            
        $response = [
            'data' => [
                'middlesliders' => $middlesliders,
                'homesecseven' => $homesecseven,
                'homeseceight' => $homeseceight,
                'homesecnine' => $homesecnine,
                'bottomsliders' => $bottomsliders,
                'homesecten' => $homesecten,
                'homeseceleven' => $homeseceleven,
                'homesectwelve' => $homesectwelve,
                'homesecthirteen' => $homesecthirteen,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    //

    //two optimize clone
    public function HomePagePartTwoRegionalCopy(Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        $langType = $request->lang ? $request->lang : 'ar';
        $deviceType = $request->device_type ? $request->device_type : 'mobile';
        $columnName = ($langType == 'en') ? 'name' : 'name_arabic';
        $columnName2 = ($langType == 'en') ? 'name' : 'name_ar';
        $proHeadingColumn = ($langType == 'en') ? 'pro_second_heading' : 'pro_second_heading_arabic';
        $prothirdHeadingColumn = ($langType == 'en') ? 'pro_third_heading' : 'pro_third_heading_arabic';
        $profourthHeadingColumn = ($langType == 'en') ? 'pro_fourth_heading' : 'pro_fourth_heading_arabic';
        
        if(Cache::has('homesecthree'))
            $homesecthree = Cache::get('homesecthree');
        else{
            // echo 'die';
            // die();
            $thirdSec = HomePage::select(['pro_second_view_all', DB::raw("CASE WHEN '$langType' = 'en' THEN pro_second_heading ELSE pro_second_heading_arabic END as $proHeadingColumn") , 'products_second_status', 'products_second'])->first();

            if($thirdSec->products_second_status == 1 && isset($thirdSec->products_second)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $thirdSec->products_second);
                $productThirdData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productThirdData = [];
            }
    
            $homesecthree = [
                'third_sec_fields' => $thirdSec,
                'productThirdtData' => $productThirdData,
            ];
            CacheStores::create([
                'key' => 'homesecthree',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecthree', $seconds, function () use ($homesecthree) {
                return $homesecthree;
            });
        }
        
        if(Cache::has('homesecfour'))
            $homesecfour = Cache::get('homesecfour');
        else{
            $fourthSec = HomePage::select(['brands_middle_status', 'brands_middle', 'brand_view_all'])->first();

            if($fourthSec->brands_middle_status == 1){
                $brandids = explode(',',$fourthSec->brands_middle);
                $brands = Brand::withCount('productname')->with([
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id',DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName"),'slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    // $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon','image_link_app')
                    // 'icon',
                    $categories = Productcategory::select(DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName") , 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
            $type5 = 5;
            $midsliders = Slider::with(
                     $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image' 
                        : 'featuredImageApp:id,image'
                    )
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type5)
                    ->where('status', 1)
                    ->select('id',DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName2"), 'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
    
            $homesecfour = [
                'fourth_sec_fields' => $fourthSec,
                'brandThirdData' => $brands,
                'middleSliderThree' => $midsliders,
            ];
            CacheStores::create([
                'key' => 'homesecfour',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfour', $seconds, function () use ($homesecfour) {
                return $homesecfour;
            });
        }
        
        $type2 = 2;
        Cache::forget('homesliders_'.$type2);
        if(Cache::has('homesliders_'.$type2))
            $topsliders = Cache::get('homesliders_'.$type2);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type2,
                'type' => 'slider'
            ]);
            $topsliders = Cache::remember('homesliders_'.$type2, $seconds, function () use ($type2, $deviceType, $langType , $columnName2) {
                // , 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic'
                return Slider::with(
                     $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image' 
                        : 'featuredImageApp:id,image'
                    )
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type2)
                    ->where('status', 1)
                    ->select('id', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName2"),'slider_type', 'sorting'
                    , 'image_web','image_mobile', 'custom_link', 'redirection_type','timer')->get();
            });
        }
        
        if(Cache::has('homesecfive'))
            $homesecfive = Cache::get('homesecfive');
        else{
            $fifthSec = HomePage::select(['pro_third_view_all', DB::raw("CASE WHEN '$langType' = 'en' THEN pro_third_heading ELSE pro_third_heading_arabic END as $prothirdHeadingColumn"),'products_third_status', 'products_third'])->first();

            if($fifthSec->products_third_status == 1 && isset($fifthSec->products_third)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $fifthSec->products_third);
                $productthirdData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productthirdData = [];
            }
    
            $homesecfive = [
                'fifth_sec_fields' => $fifthSec,
                'productFifthData' => $productthirdData,
            ];
            CacheStores::create([
                'key' => 'homesecfive',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecfive', $seconds, function () use ($homesecfive) {
                return $homesecfive;
            });
        }
        
        if(Cache::has('homesecsix'))
            $homesecsix = Cache::get('homesecsix');
        else{
            $sixthSec = HomePage::select(['pro_fourth_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN pro_fourth_heading ELSE pro_fourth_heading_arabic END as $profourthHeadingColumn"), 'products_fourth_status', 'products_fourth'])->first();

            if($sixthSec->products_fourth_status == 1 && isset($sixthSec->products_fourth)){
                $filters = ['take' => 6, 'page' => 1];
                $filters['productbyid'] = explode(',', $sixthSec->products_fourth);
                $productfourthData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productfourthData = [];
            }
    
            $homesecsix = [
                'sixth_sec_fields' => $sixthSec,
                'productSixthData' => $productfourthData,
            ];
            CacheStores::create([
                'key' => 'homesecsix',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecsix', $seconds, function () use ($homesecsix) {
                return $homesecsix;
            });
        }
        
        $BSData = [];
        $filters = [];
        $BSProcategories = Productcategory::with('bestSellerCategory')->where('best_seller',1)->get(['id','best_seller',DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName") ,'slug']);
        if($BSProcategories){
            foreach($BSProcategories as $k => $BSProcategory){
                    // $BSData[$BSProcategory->id]['category'] = $BSProcategory;
                    
                    $BSData[$BSProcategory->id]['category'] = [
                        'id'=>$BSProcategory->id,
                        // 'name'=>$BSProcategory->name,
                        // 'name_arabic'=>$BSProcategory->name_arabic,
                        $columnName => $langType == 'en' ? $BSProcategory->name : $BSProcategory->name_arabic,
                        'slug'=>$BSProcategory->slug,
                        ]; 
                if(isset($BSProcategory->bestSellerCategory) && $BSProcategory->bestSellerCategory){
                    $filters = ['take' => 6, 'page' => 1];
                    $filters['productbyid'] = $BSProcategory->bestSellerCategory->pluck('id')->toArray();
                    $BSData[$BSProcategory->id]['prodata'] = ProductListingHelper::productDataRegionalCopy($filters);
                }else{
                    $BSData[$BSProcategory->id]['prodata'] = [];
                }
            }
        }
        
            
        $response = [
            'data' => [
                'homesecthree' => $homesecthree,
                'homesecfour' => $homesecfour,
                'topsliders' => $topsliders,
                'homesecfive' => $homesecfive,
                'homesecsix' => $homesecsix,
                'bsdata' => $BSData,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    //

    //one clone
    public function HomePagePartOneRegionalCopy(Request $request) {
        
        $seconds = 86400;
        $type3 = 3;
        $type8 = 8;
        $langType = $request->lang ? $request->lang : 'ar';
        $deviceType = $request->device_type ? $request->device_type : 'mobile';
        // dd($deviceType);
        $columnName = ($langType == 'en') ? 'name' : 'name_ar';
        $columnName2 = ($langType == 'en') ? 'name' : 'name_arabic';
        $columnSec1 = ($langType == 'en') ? 'cat_heading' : 'cat_heading_arabic';
        $columnPro = ($langType == 'en') ? 'pro_first_heading' : 'pro_first_heading_arabic';
        $columnPro12 = ($langType == 'en') ? 'pro_twelveth_heading' : 'pro_twelveth_heading_arabic';
        //device
        $deviceColumn = $deviceType == 'web' ? 'image_web' : 'image_mobile';
        //
        
        Cache::forget('homesliders_'.$type3);
        if(Cache::has('homesliders_'.$type3))
            $mainsliders = Cache::get('homesliders_'.$type3);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type3,
                'type' => 'slider'
            ]);
            $mainsliders = Cache::remember('homesliders_'.$type3, $seconds, function () use ($type3, $langType, $columnName, $deviceType, $deviceColumn) {
                return Slider::with(
                    $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image' 
                        : 'featuredImageApp:id,image'
                    )
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type3)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select(
                        'id', 'slider_type', 'sorting', $deviceColumn , 'custom_link', 'redirection_type',
                        DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName") 
                    )
                    ->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // right slider
        if(Cache::has('homesliders_'.$type8))
            $rightSliders = Cache::get('homesliders_'.$type8);
        else{
            CacheStores::create([
                'key' => 'homesliders_'.$type8,
                'type' => 'slider'
            ]);
            $rightSliders = Cache::remember('homesliders_'.$type8, $seconds, function () use ($type8, $langType,$columnName, $deviceType, $deviceColumn) {
                return Slider::with(
                     $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image'
                        : 'featuredImageApp:id,image',
                    'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', $type8)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , $deviceColumn, 'custom_link', 'redirection_type',
                     DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName")
                    )->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        // three images
        if(Cache::has('threeimages'))
            $threeImages = Cache::get('threeimages');
        else{
            CacheStores::create([
                'key' => 'threeimages',
                'type' => 'slider'
            ]);
            $threeImages = Cache::remember('threeimages', $seconds, function () use ($langType, $columnName, $deviceType, $deviceColumn) {
                return Slider::with(
                    $deviceType == 'web' 
                        ? 'featuredImageWeb:id,image'
                        : 'featuredImageApp:id,image',
                    'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic', 'cat:id,slug,name,name_arabic')
                    ->orderBy('sorting', 'asc') 
                    ->where('position', 9)
                    ->where('status', 1)
                    // 'alt', 'alt_ar', 'position','timer
                    ->select('id', 'slider_type', 'sorting', 'product_id', 'brand_id', 'category_id'
                    , $deviceColumn, 'custom_link', 'redirection_type',
                     DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName") 
                    )->get();
                // return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
                //     ->orderBy('sorting', 'asc') 
                //     ->where('position', $type3)
                //     ->where('status', 1)
                //     // 'alt', 'alt_ar', 'position'
                //     ->select('id', 'name', 'name_ar', 'slider_type', 'sorting'
                //     ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer')->get();
            });
        }
        
        if(Cache::has('homesecone'))
            $homesecone = Cache::get('homesecone');
        else{
            $firstSec = HomePage::first(['cat_view_all',DB::raw("CASE WHEN '$langType' = 'en' THEN cat_heading ELSE cat_heading_arabic END as $columnSec1"), 'categories_top', 'categories_top_status']);
            if($firstSec->categories_top_status == 1){
                $categoryids = explode(',',$firstSec->categories_top);
                // $category = Productcategory::select('id', 'slug', 'web_image_media','selltype','sellvalue', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName2"))->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
                $category = Productcategory::select('id', 'slug', 'web_image_media', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_arabic END as $columnName2"))->with('WebMediaImage:id,image')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $firstSec->categories_top)")->where('status',1)->get();
            }else{
                $category = [];
            }
    
            $homesecone = [
                'first_sec_fields' => $firstSec,
                'first_sec_categories' => $category,
            ];
            CacheStores::create([
                'key' => 'homesecone',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecone', $seconds, function () use ($homesecone) {
                return $homesecone;
            });
        }
        
        
        if(Cache::has('homesectwo'))
            $homesectwo = Cache::get('homesectwo');
        else{
            $secondSec = HomePage::select(['pro_first_view_all', DB::raw("CASE WHEN '$langType' = 'en' THEN pro_first_heading ELSE pro_first_heading_arabic END as $columnPro"), 'products_first_status', 'products_first'])->first();

            if($secondSec->products_first_status == 1 && isset($secondSec->products_first)){
                $filters = ['take' => 6, 'page' => 1 , 'lang' => $langType];
                $filters['productbyid'] = explode(',', $secondSec->products_first);
                $productFirstData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productFirstData = [];
            }
    
            $homesectwo = [
                'second_sec_fields' => $secondSec,
                'productSecondtData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesectwo',
                'type' => 'homepage'
            ]);
            Cache::remember('homesectwo', $seconds, function () use ($homesectwo) {
                return $homesectwo;
            });
        }
         //after sec category
        if(Cache::has('homesecaftercat'))
            $homeaftercatsec = Cache::get('homesecaftercat');
        else{
            $afterCatSec = HomePage::select(['pro_twelveth_view_all', DB::raw("CASE WHEN '$langType' = 'en' THEN pro_twelveth_heading ELSE pro_twelveth_heading_arabic END as $columnPro12"),'products_twelveth_status', 'products_twelveth','products_twelveth_date'])->first();

            if($afterCatSec->products_twelveth_status == 1 && isset($afterCatSec->products_twelveth)){
                $filters = ['take' => 6, 'page' => 1 , 'lang' => $langType];
                $filters['productbyid'] = explode(',', $afterCatSec->products_twelveth);
                $productFirstData = ProductListingHelper::productDataRegionalCopy($filters);
            }else{
                $productFirstData = [];
            }
    
            $homeaftercatsec = [
                'afterCatSec' => $afterCatSec,
                'productAfterSectData' => $productFirstData,
            ];
            CacheStores::create([
                'key' => 'homesecaftercat',
                'type' => 'homepage'
            ]);
            Cache::remember('homesecaftercat', $seconds, function () use ($homeaftercatsec) {
                return $homeaftercatsec;
            });
        }
        // $afterSecCat = HomePage::with('BannerImageOne:id,image','BannerImageTwo:id,image','BannerImageThird:id,image','BannerImageFourth:id,image')->select(['banner_image1', 'banner_image1_link','banner_image2','banner_image2_link',
        //                         'banner_image3','banner_image3_link','banner_image4','banner_image4_link','banner_first_status','banner_second_status','banner_first_heading','banner_first_heading_arabic',
        //                         'banner_second_heading','banner_second_heading_arabic'])->first();
        $banner2column = ($langType == 'en') ? 'banner_second_heading' : 'banner_second_heading_arabic';
        $banner1column = ($langType == 'en') ? 'banner_first_heading' : 'banner_first_heading_arabic';
        $afterSecCat = HomePage::with('BannerImageOne:id,image','BannerImageTwo:id,image','BannerImageThird:id,image','BannerImageFourth:id,image')->select(['banner_image1', 'banner_image1_link','banner_image2','banner_image2_link',
                                'banner_image3','banner_image3_link','banner_image4','banner_image4_link','banner_first_status','banner_second_status'
                                ,DB::raw("CASE WHEN '$langType' = 'en' THEN banner_first_heading ELSE banner_first_heading_arabic END as $banner1column")
                                ,DB::raw("CASE WHEN '$langType' = 'en' THEN banner_second_heading ELSE banner_second_heading_arabic END as $banner2column")])->first();
                                
        $afterCatSecSlider1 = Slider::with(
                            $deviceType == 'web' 
                                ? 'featuredImageWeb:id,image'
                                : 'featuredImageApp:id,image'
                            )
                            ->where('position', 10)
                            ->where('status', 1)
                            ->select('id', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName"))->get();
        
        $afterCatSecSlider2 = Slider::with(
                            $deviceType == 'web' 
                                ? 'featuredImageWeb:id,image'
                                : 'featuredImageApp:id,image'
                            )
                            ->where('position', 11)
                            ->where('status', 1)
                            ->select('id', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName"))->get();
        $afterCatSecSlider3 = Slider::with(
                            $deviceType == 'web' 
                                    ? 'featuredImageWeb:id,image'
                                    : 'featuredImageApp:id,image'
                            )
                            ->where('position', 12)
                            ->where('status', 1)
                            ->select('id', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName"))->get();
        
        $afterCatSecSlider4 = Slider::with(
                                $deviceType == 'web' 
                                    ? 'featuredImageWeb:id,image'
                                    : 'featuredImageApp:id,image'
                                )
                            ->where('position', 13)
                            ->where('status', 1)
                            ->select('id', 'slider_type'
                            , 'image_web','image_mobile', 'position','status','custom_link','redirection_type', DB::raw("CASE WHEN '$langType' = 'en' THEN name ELSE name_ar END as $columnName"))->get();
        
                                
        $response = [
            'data' => [
                'mainsliders' => $mainsliders,
                'rightSliders' => $rightSliders,
                'threeImages' => $threeImages,
                'homesecone' => $homesecone,
                'homesectwo' => $homesectwo,
                'homesecaftercat' => $homeaftercatsec,
                'afterSecCat' => $afterSecCat,
                'afterSecCatSlider1' => $afterCatSecSlider1,
                'afterSecCatSlider2' => $afterCatSecSlider2,
                'afterSecCatSlider3' => $afterCatSecSlider3,
                'afterSecCatSlider4' => $afterCatSecSlider4,
            ]
            
        ];  
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    //

    //four optimize-clone
    public function HomePageFrontendCopy(Request $request) {
        $langType = $request->lang ? $request->lang : 'ar';
        $columnName = ($langType == 'en') ? 'meta_title_en' : 'meta_title_ar';
        $columnName2 = ($langType == 'en') ? 'meta_description_en' : 'meta_description_ar';
        // $homepageData = HomePage::first();
        // ,'categories_top_status','brands_middle_status', 'cat_view_all', 'cat_heading', 'cat_heading_arabic', 'brand_view_all', 'pro_first_view_all', 'pro_first_heading', 'pro_first_heading_arabic', 'pro_second_view_all', 'pro_second_heading', 'pro_second_heading_arabic', 'pro_third_view_all', 'pro_third_heading', 'pro_third_heading_arabic', 'pro_fourth_view_all', 'pro_fourth_heading', 'pro_fourth_heading_arabic', 'pro_fifth_view_all', 'pro_fifth_heading', 'pro_fifth_heading_arabic', 'products_first_status','products_second_status','products_third_status','products_fourth_status','products_fifth_status'
        $homepageselectedData = HomePage::select([DB::raw("CASE WHEN '$langType' = 'en' THEN meta_title_en ELSE meta_title_ar END as $columnName"),DB::raw("CASE WHEN '$langType' = 'en' THEN meta_description_en ELSE meta_description_ar END as $columnName2") ])->first();
        // if($homepageData->categories_top_status == 1){
        //     $categoryids = explode(',',$homepageData->categories_top);
        //     $category = Productcategory::select('id', 'name', 'name_arabic', 'slug', 'icon', 'brand_link', 'web_image_media', 'mobile_image_media', 'icon')->withCount('productCount')->with('WebMediaImage:id,image,desktop,mobile','MobileMediaAppImage:id,image,desktop,mobile')->whereIn('id',$categoryids)->orderByRaw("FIELD(id, $homepageData->categories_top)")->where('status',1)->get();
        // }else{
        //     $category = [];
        // }
        
        // // 'category' => function ($que) {
        // //         return $que->select('name', 'name_arabic','slug', 'icon', 'brand_link');   
        // //     },
        
        
        // // orderByRaw("FIELD(id, $homepageData->brands_middle)")->
        // if($homepageData->brands_middle_status == 1){
        //     $brandids = explode(',',$homepageData->brands_middle);
        //     $brands = Brand::withCount('productname')->with([
        //     'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
        //     foreach ($brands as $brand) {
        //         $categories = Productcategory::select('name', 'name_arabic', 'slug', 'icon', 'brand_link')
        //             ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
        //             ->where('brand_category.brand_id', $brand->id)
        //             ->limit(4)
        //             ->get();
        //         $brand->categories = $categories;
        //     }
        // }else{
        //     $brands = [];
        // }
        
        // if($homepageData->products_first_status == 1 && isset($homepageData->products_first)){
        //     $filters = ['take' => 6, 'page' => 1];
        //     $filters['productbyid'] = explode(',', $homepageData->products_first);
        //     $productFirstData = ProductListingHelper::productData($filters);
        // }else{
        //     $productFirstData =[];
        // }
        
        
        // if($homepageData->products_second_status == 1 && isset($homepageData->products_second)){
        //     $filters = ['take' => 6, 'page' => 1];
        //     $filters['productbyid'] = explode(',', $homepageData->products_second);
        //     $productSecondtData = ProductListingHelper::productData($filters);
        // }else{
        //     $productSecondtData =[];
        // }
        
        
        // if($homepageData->products_third_status == 1 && isset($homepageData->products_third)){
        //     $filters = ['take' => 6, 'page' => 1];
        //     $filters['productbyid'] = explode(',', $homepageData->products_third);
        //     $productThirdData = ProductListingHelper::productData($filters);
        // }else{
        //     $productThirdData =[];
        // }
        
        
        // if($homepageData->products_fourth_status == 1 && isset($homepageData->products_fourth)){
        //     $filters = ['take' => 6, 'page' => 1];
        //     $filters['productbyid'] = explode(',', $homepageData->products_fourth);
        //     $productFourthData = ProductListingHelper::productData($filters);
        // }else{
        //     $productFourthData =[];
        // }
        
        // if($homepageData->products_fifth_status == 1 && isset($homepageData->products_fifth)){
        //     $filters = ['take' => 6, 'page' => 1];
        //     $filters['productbyid'] = explode(',', $homepageData->products_fifth);
        //     $productfifthData = ProductListingHelper::productData($filters);
        // }else{
        //     $productfifthData =[];
        // }



        // // Flash Sale start
        // $flash = FlashSale::
        // with('featuredImage:id,image,title,title_arabic,alt,alt_arabic', 'redirectionbrand:id,slug', 'redirectionproduct:id,slug', 'redirectioncategory:id,slug')
        // ->where('status', 1)
        // ->where('left_quantity', '>=', 1)
        // ->where(function($a){
        //     return $a->whereNull('start_date')->orWhereDate('start_date', '<=', DB::raw('CURDATE()'));
        // })
        // ->where(function($a){
        //     return $a->whereNull('end_date')->orWhereDate('end_date', '>=', DB::raw('CURDATE()'));
        // })
        // ->take(3)
        // ->get(['image', 'redirection_type', 'redirection_products', 'redirection_categories', 'redirection_brands', 'end_date']);
        // Flash Sale end
        
        $response = [
            'homepageData'=> $homepageselectedData,
            // 'brands' => $brands,
            // 'categories' => $category,
            // 'productFirstData' => $productFirstData,
            // 'productSecondData' => $productSecondtData,
            // 'productThirdData' => $productThirdData,
            // 'productFourthData' => $productFourthData,
            // 'productFifthData' => $productfifthData,
            // 'flash' => $flash
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    //
}