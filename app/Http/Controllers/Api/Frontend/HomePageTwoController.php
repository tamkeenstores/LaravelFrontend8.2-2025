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
use App\Models\HomePageTwo;
use App\Models\Slider;
use App\Helper\ProductListingHelperNew;
use DB;

use Illuminate\Support\Facades\Cache;
use App\Models\CacheStores;

class HomePageTwoController extends Controller
{

    // Sec One
    public function homePageTwoSecOne(Request $request)
    {
        $seconds = 86400;
        $lang = $request->lang ?? 'ar';
        $deviceType = $request->device_type === 'desktop' ? 'desktop' : 'mobile';

        // Language/device-specific columns
        $columnMap = [
            'name' => $lang === 'en' ? 'name' : 'name_ar',
            'catName' => $lang === 'en' ? 'name' : 'name_arabic',
            'sec1_image_link' => $lang === 'en' ? 'sec1_image_link' : 'sec1_image_link_ar',
            'sec1_image' => $lang === 'en' ? 'sec1_image' : 'sec1_image_ar',
            'sec3_title' => $lang === 'en' ? 'sec3_category_title' : 'sec3_category_title_ar',
            'sec4_title' => $lang === 'en' ? 'sec4_best_seller_title' : 'sec4_best_seller_title_ar',
            'categoryImage' => $deviceType === 'desktop' ? 'WebMediaImage:id,image' : 'MobileMediaAppImage:id,image',
            'mediaRelation' => $deviceType === 'desktop' ? 'featuredImageWeb:id,image' : 'featuredImageApp:id,image',
            'sliderImage' => $deviceType === 'desktop' ? 'image_web' : 'image_mobile',
            'categoryImageField' => $deviceType === 'desktop' ? 'web_image_media' : 'mobile_image_media',
        ];

        $cacheKey = "homepagetwoseconecachenew_{$lang}_{$deviceType}";

        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } 
        else {
            // Closure to fetch sliders
            $sliderPositions = [
                'sec2SliderLeft' => 14,
                'sec2SliderRight' => 15,
                'sec2SliderMiddleTop' => 16,
                'sec2SliderMiddleBottomRight' => 17,
                'sec2SliderMiddleBottomLeft' => 18,
                'sec5Slider' => 19,
            ];

            $mainsliderData = Slider::with($columnMap['mediaRelation'])
                    // ->where('position', $position)
                    ->where('status', 1)
                    ->orderBy('sorting', 'asc')
                    ->select('id', $columnMap['name'], 'slider_type', $columnMap['sliderImage'], 'custom_link', 'redirection_type', 'position')
                    ->get();
            $sliders = [];
            foreach ($sliderPositions as $key => $position) {
                // $sliders[$key] = $mainsliderData->filter(function($item) use ($position) {
                //     return $item->position == $position;
                // });
                $filtered = $mainsliderData->where('position', $position);
                $sliders[$key] = array_values($filtered->toArray());
                // $sliders[$key] = $mainsliderData->where('position', $position);
            }
            // print_r($sliders);die;

            // Fetch home page config
            $homepagedata = HomePageTwo::select(
                $columnMap['sec1_image_link'], $columnMap['sec1_image'], 'sec1_status',
                $columnMap['sec3_title'], 'sec3_category', 'sec3_status',
                $columnMap['sec4_title'], 'sec4_category', 'sec4_status'
            )->first();

            // Section 1
            $sec1banner = [
                $columnMap['sec1_image_link'] => $homepagedata->{$columnMap['sec1_image_link']},
                $columnMap['sec1_image'] => $homepagedata->{$columnMap['sec1_image']},
                'sec1_status' => $homepagedata->sec1_status,
            ];

            // Section 3 - Categories
            $sectioncategories = [];
            if ($homepagedata->sec3_status == 1 && !empty($homepagedata->sec3_category)) {
                $categoryIds = explode(',', $homepagedata->sec3_category);
                $sectioncategoriesData = Productcategory::select('id', $columnMap['catName'], 'slug', $columnMap['categoryImageField'])
                    ->with($columnMap['categoryImage'])
                    ->whereIn('id', $categoryIds)
                    ->where('status', 1)
                    ->orderByRaw("FIELD(id, $homepagedata->sec3_category)")
                    ->get();

                $sectioncategories = [
                    'categories' => $sectioncategoriesData,
                    'sec3_status' => $homepagedata->sec3_status,
                    $columnMap['sec3_title'] => $homepagedata->{$columnMap['sec3_title']},
                ];
            }

            // Section 4 - Best Sellers
            $BSData = [];
            $BSProcategories = Productcategory::with('bestSellerCategory')
                ->where('best_seller', 1)
                ->limit(10)
                ->get(['id', 'best_seller', $columnMap['catName'], 'slug']);

            foreach ($BSProcategories as $cat) {
                $BSData[$cat->id]['category'] = [
                    'id' => $cat->id,
                    $columnMap['catName'] => $cat->{$columnMap['catName']},
                    'slug' => $cat->slug,
                ];

                $BSData[$cat->id]['prodata'] = $cat->bestSellerCategory
                    ? ProductListingHelperNew::productData([
                        'take' => 5,
                        'page' => 1,
                        'lang' => $lang,
                        'cat_id' => $cat->id,
                    ])
                    : [];
            }

            $BSData[$columnMap['sec4_title']] = $homepagedata->{$columnMap['sec4_title']};
            $BSData['sec4_status'] = $homepagedata->sec4_status;

            // Final response
            $response = array_merge(
                ['sec1banner' => $sec1banner],
                $sliders,
                ['sectioncategories' => $sectioncategories],
                ['bsdata' => $BSData]
            );

            // Cache storage
            CacheStores::create([
                'key' => $cacheKey,
                'type' => 'homepagenew'
            ]);
            Cache::put($cacheKey, $response, $seconds);
        }

        // Compress and return response
        $compressed = gzencode(json_encode($response), 9);
        return response($compressed)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($compressed),
            'Content-Encoding' => 'gzip',
        ]);
    }

    // public function homePageTwoSecOne(Request $request)
    // {   
    //     $seconds = 86400;
    //     $lang = $request->lang ?? 'ar';
    //     $deviceType = $request->device_type === 'desktop' ? 'desktop' : 'mobile';
    //     // Language-based columns
    //     $columnName = $lang === 'en' ? 'name' : 'name_ar';
    //     $catName = $lang === 'en' ? 'name' : 'name_arabic';
    //     $column1 = $lang === 'en' ? 'sec1_image_link' : 'sec1_image_link_ar';
    //     $column2 = $lang === 'en' ? 'sec1_image' : 'sec1_image_ar';
    //     $column3 = $lang === 'en' ? 'sec3_category_title' : 'sec3_category_title_ar';
    //     $column4 = $lang === 'en' ? 'sec4_best_seller_title' : 'sec4_best_seller_title_ar';
    //     $column5 = $deviceType === 'desktop' ? 'web_image_media' : 'mobile_image_media';
    //     $deviceColumn = $deviceType === 'desktop' ? 'image_web' : 'image_mobile';
    //     $mediaRelation = $deviceType === 'desktop' ? 'featuredImageWeb:id,image' : 'featuredImageApp:id,image';
    //     $categoryImageRelation = $deviceType === 'desktop' ? 'WebMediaImage:id,image' : 'MobileMediaAppImage:id,image';

    //     if(Cache::has('homepagetwoseconecache_'.$lang.'_'.$deviceType))
    //         $response = Cache::get('homepagetwoseconecache_'.$lang.'_'.$deviceType);
    //     else{

    //         // Helper to get slider by position
    //         $getSlider = function ($position) use ($columnName, $deviceColumn, $mediaRelation) {
    //             return Slider::with($mediaRelation)
    //                 ->where('position', $position)
    //                 ->where('status', 1)
    //                 ->orderBy('sorting', 'asc')
    //                 ->select('id', $columnName, 'slider_type', $deviceColumn, 'custom_link', 'redirection_type')
    //                 ->get();
    //         };

    //         // Section 2 Sliders
    //         $sec2SliderLeft = $getSlider(14);
    //         $sec2SliderRight = $getSlider(15);
    //         $sec2SliderMiddleTop = $getSlider(16);
    //         $sec2SliderMiddleBottomRight = $getSlider(17);
    //         $sec2SliderMiddleBottomLeft = $getSlider(18);

    //         // Home page data
    //         $homepagedata = HomePageTwo::select(
    //             $column1, $column2, 'sec1_status',
    //             $column3, 'sec3_category', 'sec3_status',
    //             $column4, 'sec4_category', 'sec4_status'
    //         )->first();

    //         // Section 1 banner
    //         $sec1banner = [
    //             $column1 => $homepagedata->$column1,
    //             $column2 => $homepagedata->$column2,
    //             'sec1_status' => $homepagedata->sec1_status,
    //         ];

    //         // Section 3 categories
    //         $sectioncategories = [];
    //         if ($homepagedata->sec3_status == 1 && $homepagedata->sec3_category) {
    //             $categoryids = explode(',', $homepagedata->sec3_category);
    //             $sectioncategoriesData = Productcategory::select('id', $catName, 'slug', $column5)
    //                 ->with($categoryImageRelation)
    //                 ->whereIn('id', $categoryids)
    //                 ->where('status', 1)
    //                 ->orderByRaw("FIELD(id, $homepagedata->sec3_category)")
    //                 ->get();

    //             $sectioncategories = [
    //                 'categories' => $sectioncategoriesData,
    //                 'sec3_status' => $homepagedata->sec3_status,
    //                 $column3 => $homepagedata->$column3,
    //             ];
    //         }

    //         // Section 4 Best Seller data
    //         $BSData = [];
    //         $BSProcategories = Productcategory::with('bestSellerCategory')
    //             ->where('best_seller', 1)
    //             ->limit(10)
    //             ->get(['id', 'best_seller', $catName, 'slug']);

    //         foreach ($BSProcategories as $BSProcategory) {
    //             $BSData[$BSProcategory->id]['category'] = [
    //                 'id' => $BSProcategory->id,
    //                 $catName => $BSProcategory->$catName,
    //                 'slug' => $BSProcategory->slug,
    //             ];

    //             if ($BSProcategory->bestSellerCategory) {
    //                 $filters = [
    //                     'take' => 5,
    //                     'page' => 1,
    //                     'lang' => $lang,
    //                     'cat_id' => $BSProcategory->id,
    //                 ];
    //                 $BSData[$BSProcategory->id]['prodata'] = ProductListingHelperNew::productData($filters);
    //             } else {
    //                 $BSData[$BSProcategory->id]['prodata'] = [];
    //             }
    //         }

    //         $BSData[$column4] = $homepagedata->$column4;
    //         $BSData['sec4_status'] = $homepagedata->sec4_status;

    //         // Section 5 Slider
    //         $sec5Slider = $getSlider(19);

    //         // Final response
    //         $response = [
    //             'sec1banner' => $sec1banner,
    //             'sec2SliderLeft' => $sec2SliderLeft,
    //             'sec2SliderRight' => $sec2SliderRight,
    //             'sec2SliderMiddleTop' => $sec2SliderMiddleTop,
    //             'sec2SliderMiddleBottomRight' => $sec2SliderMiddleBottomRight,
    //             'sec2SliderMiddleBottomLeft' => $sec2SliderMiddleBottomLeft,
    //             'sec5Slider' => $sec5Slider,
    //             'sectioncategories' => $sectioncategories,
    //             'bsdata' => $BSData,
    //         ];

    //         CacheStores::create(
    //             [
    //             'key' => 'homepagetwoseconecachenew_'.$lang.'_'.$deviceType,
    //             'type' => 'homepagenew'
    //         ]);
    //         Cache::remember('homepagetwoseconecachenew_'.$lang.'_'.$deviceType, $seconds, function () use ($response) {
    //             return $response;
    //         });
    //     }

    //     $responseJson = json_encode($response);
    //     $compressedData = gzencode($responseJson, 9);

    //     return response($compressedData)->withHeaders([
    //         'Content-Type' => 'application/json; charset=utf-8',
    //         'Content-Length' => strlen($compressedData),
    //         'Content-Encoding' => 'gzip',
    //     ]);
    // }


    // Sec Two
    public function homePageTwoSecTwo(Request $request) {
        $seconds = 86400;
        
        $lang = $request->lang ? $request->lang : 'ar';
        $deviceType = $request->device_type == 'desktop' ? 'desktop' : 'mobile';
        $deviceColumn = $request->device_type == 'desktop' ? 'image_web' : 'image_mobile';
        $columnName = $lang == 'en' ? 'name' : 'name_arabic';
        $column1 = $lang == 'en' ? 'sec6_title' : 'sec6_title_ar';
        $column2 = $lang == 'en' ? 'sec6_description' : 'sec6_description_ar';
        $column3 = $lang == 'en' ? 'sec6_button_title' : 'sec6_button_title_ar';
        $column4 = $lang == 'en' ? 'sec6_button_link' : 'sec6_button_link_ar';
        $column5 = $lang == 'en' ? 'sec7_brand_title' : 'sec7_brand_title_ar';
        $column6 = $lang == 'en' ? 'sec7_show_all_title' : 'sec7_show_all_title_ar';
        $column7 = $lang == 'en' ? 'sec7_show_all_link' : 'sec7_show_all_link_ar';
        // $column8 = $lang == 'en' ? 'sec8_first_vedio' : 'sec8_first_vedio_ar';
        $column8 = $lang == 'en' ? ($deviceType == 'desktop' ? 'sec8_first_vedio' : 'sec8_first_mobile_video') : ($deviceType == 'desktop' ? 'sec8_first_vedio_ar' : 'sec8_first_mobile_video_ar');
        $column9 = $lang == 'en' ? 'sec8_title' : 'sec8_title_ar';
        $column10 = $lang == 'en' ? 'sec8_description' : 'sec8_description_ar';
        $column11 = $lang == 'en' ? 'sec8_button_title' : 'sec8_button_title_ar';
        $column12 = $lang == 'en' ? 'sec8_button_link' : 'sec8_button_link_ar';
        $column13 = $lang == 'en' ? 'sec8_secondary_image_link' : 'sec8_secondary_image_link_ar';
        $column14 = $lang == 'en' ? 'sec8_secondary_image' : 'sec8_secondary_image_ar';
        $column15 = $lang == 'en' ? 'sec9_title' : 'sec9_title_ar';
        $column16 = $lang == 'en' ? 'sec9_show_alltitle' : 'sec9_show_all_title_ar';
        $column17 = $lang == 'en' ? 'sec9_show_all_link' : 'sec9_show_all_link_ar';
        $column18 = $lang == 'en' ? 'sec10_title' : 'sec10_title_ar';
        $column19 = $lang == 'en' ? 'sec10_description' : 'sec10_description_ar';
        $column20 = $lang == 'en' ? 'sec10_button_title' : 'sec10_button_title_ar';
        $column21 = $lang == 'en' ? 'sec10_button_link' : 'sec10_button_link_ar';
        $column22 = $lang == 'en' ? 'sec8_video_link' : 'sec8_video_link_ar';
        
        $column24 = $lang == 'en' ? 'sec8_secondary_mobile_image' : 'sec8_secondary_mobile_image_ar';
        
       
        if(Cache::has('sectwonewcachehomepagetwonewnew_'.$lang.'_'.$deviceType)){
                $response = Cache::get('sectwonewcachehomepagetwonewnew_'.$lang.'_'.$deviceType);
        }
        else{   
            $homepagedata = HomePageTwo::select($column1,$column2,$column3,$column4,'sec6_product_sku','sec6_status',$column5,$column6,$column7,'sec7_brand','sec7_status',$column8,$column9,$column10,$column11,$column12,$column13,$column14,'sec8_status',$column15,$column16,$column17,$column22,$column24,'sec9_product_sku','sec9_status',$column18,$column19,$column20,$column21,'sec10_product_sku','sec10_status')->first();
            
            $productSixData = [];
            if($homepagedata->sec6_status == 1 && isset($homepagedata->sec6_product_sku)){
                $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
                $filters['productbyid'] = explode(',', $homepagedata->sec6_product_sku);
                $productSixData = ProductListingHelperNew::productData($filters);
                $productSixData[$column1] = $homepagedata->$column1;
                $productSixData[$column2] = $homepagedata->$column2;
                $productSixData[$column3] = $homepagedata->$column3;
                $productSixData[$column4] = $homepagedata->$column4;
                $productSixData['sec6_status'] = $homepagedata->sec6_status;
            }
            
            
            if($homepagedata->sec7_status == 1){
                $brandids = explode(',',$homepagedata->sec7_brand);
                $brands = Brand::withCount('productname')->with([
                    
                'BrandMediaImage:id,image,desktop,mobile','BrandMediaAppImage:id,image,desktop,mobile'
                
                ])->whereIn('id',$brandids)->where('status',1)->select(['id','name','name_arabic','slug','brand_image_media','brand_app_image_media','status','sorting'])->orderBy('sorting','ASC')->limit(8)->get();
                foreach ($brands as $brand) {
                    $categories = Productcategory::select("$columnName", 'slug','image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        ->limit(4)
                        ->get();
                    $brand->categories = $categories;
                }
            }else{
                $brands = [];
            }
            $sec7Brands = [];
            $sec7Brands['brands'] = $brands;
            $sec7Brands[$column5] =  $homepagedata->$column5 ;
            $sec7Brands[$column6] =  $homepagedata->$column6 ;
            $sec7Brands[$column7] =  $homepagedata->$column7 ;
            $sec7Brands['sec7_status'] =  $homepagedata->sec7_status ;
            
            $productEightData = [];
            if($homepagedata->sec8_status = 1){
                $productEightData[$column8] = $homepagedata->$column8;
                $productEightData[$column9] = $homepagedata->$column9;
                $productEightData[$column10] = $homepagedata->$column10;
                $productEightData[$column11] = $homepagedata->$column11;
                $productEightData[$column12] = $homepagedata->$column12;
                $productEightData[$column13] = $homepagedata->$column13;
                $productEightData[$column22] = $homepagedata->$column22;
                $deviceType == 'desktop' ? $productEightData[$column14] = $homepagedata->$column14 : $productEightData[$column24] = $homepagedata->$column24;
                $productEightData['sec8_status'] = $homepagedata->sec8_status;
            }
            
            $productNineData = [];
            if($homepagedata->sec9_status == 1 && isset($homepagedata->sec9_product_sku)){
                $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
                $filters['productbyid'] = explode(',', $homepagedata->sec9_product_sku);
                $productNineData = ProductListingHelperNew::productData($filters);
                $productNineData[$column15] = $homepagedata->$column15;
                $productNineData[$column16] = $homepagedata->$column16;
                $productNineData[$column17] = $homepagedata->$column17;
                $productNineData['sec9_status'] = $homepagedata->sec9_status;
            }
            
            $productTenData = [];
            if($homepagedata->sec10_status == 1 && isset($homepagedata->sec10_product_sku)){
                $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
                $filters['productbyid'] = explode(',', $homepagedata->sec10_product_sku);
                $productTenData = ProductListingHelperNew::productData($filters);
                $productTenData[$column18] = $homepagedata->$column18;
                $productTenData[$column19] = $homepagedata->$column19;
                $productTenData[$column20] = $homepagedata->$column20;
                $productTenData[$column21] = $homepagedata->$column21;
                $productTenData['sec10_status'] = $homepagedata->sec10_status;
            }
            
            
            $response = [
                'sec6' => $productSixData,
                'sec7' => $sec7Brands,
                'sec8' => $productEightData,
                'sec9' => $productNineData,
                'sec10' => $productTenData,
            ];

            CacheStores::create([
                'key' => 'sectwonewcachehomepagetwonewnew_'.$lang.'_'.$deviceType,
                'type' => 'homepagenewnew'
            ]);
            Cache::remember('sectwonewcachehomepagetwonewnew_'.$lang.'_'.$deviceType, $seconds, function () use ($response) {
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
    public function homePageTwoSecThree(Request $request) {
        
        $seconds = 86400;
        
      $lang = $request->lang ? $request->lang : 'ar';
      $deviceType = $request->device_type == 'desktop' ? 'desktop' : 'mobile';
      
        $column1 = $lang == 'en' ? 'sec11_title' : 'sec11_title_ar';
        $column2 = $lang == 'en' ? 'sec11_show_alltitle' : 'sec11_show_all_title_ar';
        $column3 = $lang == 'en' ? 'sec11_show_all_link' : 'sec11_show_all_link_ar';
        $column4 = $lang == 'en' ? 'sec12_title' : 'sec12_title_ar';
        $column5 = $lang == 'en' ? 'sec12_button' : 'sec12_button_ar';
        $column6 = $lang == 'en' ? 'sec12_button_link' : 'sec12_button_link_ar';
        $column7 = $lang == 'en' ? 'sec12_mid_image_link' : 'sec12_mid_image_link_ar';
        $column8 = $lang == 'en' ? ($deviceType == 'desktop' ? 'sec12_mid_image' : 'sec12_mid_mobile_image') : ($deviceType == 'desktop' ? 'sec12_mid_image_ar' : 'sec12_mid_mobile_image_ar');
        $column9 = $lang == 'en' ? 'sec12_mid_image2' : 'sec12_mid_image2_ar';
        $column10 = $lang == 'en' ? 'sec12_mid_image3' : 'sec12_mid_image3_ar';
        $column11 = $lang == 'en' ? 'sec12_mid_image4' : 'sec12_mid_image4_ar';
        $column12 = $lang == 'en' ? 'sec13_title' : 'sec13_title_ar';
        $column13 = $lang == 'en' ? 'sec13_show_alltitle' : 'sec13_show_all_title_ar';
        $column14 = $lang == 'en' ? 'sec13_show_all_link' : 'sec13_show_all_link_ar';
        $column15 = $lang == 'en' ? 'sec14_title' : 'sec14_title_ar';
        $column16 = $lang == 'en' ? 'sec14_description' : 'sec14_description_ar';
        $column17 = $lang == 'en' ? 'sec14_button_title' : 'sec14_button_title_ar';
        $column18 = $lang == 'en' ? 'sec14_button_link' : 'sec14_button_link_ar';
        $column19 = $lang == 'en' ? 'sec14_image_link1' : 'sec14_image_link1_ar';
        $column20 = $lang == 'en' ? 'sec14_image1' : 'sec14_image1_ar';
        $column21 = $lang == 'en' ? 'sec14_image_link2' : 'sec14_image_link2_ar';
        $column22 = $lang == 'en' ? 'sec14_image2' : 'sec14_image2_ar';
        $column23 = $lang == 'en' ? 'sec14_image_link3' : 'sec14_image_link3_ar';
        $column24 = $lang == 'en' ? 'sec14_image3' : 'sec14_image3_ar';
        $column25 = $lang == 'en' ? 'sec15_title' : 'sec15_title_ar';
        $column26 = $lang == 'en' ? 'sec15_show_alltitle' : 'sec15_show_all_title_ar';
        $column27 = $lang == 'en' ? 'sec15_show_all_link' : 'sec15_show_all_link_ar';
        $column28 = $lang == 'en' ? 'sec16_title' : 'sec16_title_ar';
        $column29 = $lang == 'en' ? 'sec16_show_alltitle' : 'sec16_show_all_title_ar';
        $column30 = $lang == 'en' ? 'sec16_show_all_link' : 'sec16_show_all_link_ar';
        
        
        if(Cache::has('threecachenewhomepagetwosec_'.$lang.'_'.$deviceType))
            $response = Cache::get('threecachenewhomepagetwosec_'.$lang.'_'.$deviceType);
        else{
        $homepagedata = HomePageTwo::select($column1,$column2,$column3,'sec11_product_sku','sec11_status',$column4,$column5,$column6,$column7,$column8,$column9,$column10,$column11,'sec12_status',$column12,$column13,$column14,'sec13_product_sku','sec13_status',$column15,$column16,$column17,$column18,$column19,$column20,$column21,$column22,$column23,$column24,'sec14_status',$column25,$column26,$column27,'sec15_product_sku','sec15_status',$column28,$column29,$column30,'sec16_product_sku','sec16_status')->first();
        
        $productelevenData = [];
        if($homepagedata->sec11_status == 1 && isset($homepagedata->sec11_product_sku)){
            $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
            $filters['productbyid'] = explode(',', $homepagedata->sec11_product_sku);
            $productelevenData = ProductListingHelperNew::productData($filters);
            $productelevenData[$column1] = $homepagedata->$column1;
            $productelevenData[$column2] = $homepagedata->$column2;
            $productelevenData[$column3] = $homepagedata->$column3;
            $productelevenData['sec11_status'] = $homepagedata->sec11_status;
        }
        
         $productTwelveData = [];
        if($homepagedata->sec12_status = 1){
            $productTwelveData[$column4] = $homepagedata->$column4;
            $productTwelveData[$column5] = $homepagedata->$column5;
            $productTwelveData[$column6] = $homepagedata->$column6;
            $productTwelveData[$column7] = $homepagedata->$column7;
            $productTwelveData[$column8] = $homepagedata->$column8;
            $productTwelveData[$column9] = $homepagedata->$column9;
            $productTwelveData[$column10] = $homepagedata->$column10;
            $productTwelveData[$column11] = $homepagedata->$column11;
            $productTwelveData['sec12_status'] = $homepagedata->sec12_status;
        }
        
         $producthirteenData = [];
        if($homepagedata->sec13_status == 1 && isset($homepagedata->sec13_product_sku)){
            $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
            $filters['productbyid'] = explode(',', $homepagedata->sec13_product_sku);
            $producthirteenData = ProductListingHelperNew::productData($filters);
            $producthirteenData[$column12] = $homepagedata->$column12;
            $producthirteenData[$column13] = $homepagedata->$column13;
            $producthirteenData[$column14] = $homepagedata->$column14;
            $producthirteenData['sec13_status'] = $homepagedata->sec13_status;
        }
        
         $productFourteenData = [];
        if($homepagedata->sec14_status = 1){
            $productFourteenData[$column15] = $homepagedata->$column15;
            $productFourteenData[$column16] = $homepagedata->$column16;
            $productFourteenData[$column17] = $homepagedata->$column17;
            $productFourteenData[$column18] = $homepagedata->$column18;
            $productFourteenData[$column19] = $homepagedata->$column19;
            $productFourteenData[$column20] = $homepagedata->$column20;
            $productFourteenData[$column21] = $homepagedata->$column21;
            $productFourteenData[$column22] = $homepagedata->$column22;
            $productFourteenData[$column23] = $homepagedata->$column23;
            $productFourteenData[$column24] = $homepagedata->$column24;
            $productFourteenData['sec14_status'] = $homepagedata->sec14_status;
        }
        
         $productfifteenData = [];
        if($homepagedata->sec15_status == 1 && isset($homepagedata->sec15_product_sku)){
            $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
            $filters['productbyid'] = explode(',', $homepagedata->sec15_product_sku);
            $productfifteenData = ProductListingHelperNew::productData($filters);
            $productfifteenData[$column25] = $homepagedata->$column25;
            $productfifteenData[$column26] = $homepagedata->$column26;
            $productfifteenData[$column27] = $homepagedata->$column27;
            $productfifteenData['sec15_status'] = $homepagedata->sec15_status;
        }
        
        $productsixteenData = [];
        $filters = ['take' => 8, 'page' => 1,'lang' => $lang];
        $filters['productbyid'] = explode(',', $homepagedata->sec16_product_sku);
        $productsixteenData = ProductListingHelperNew::productData($filters);
        $productsixteenData[$column28] = $homepagedata->$column28;
        $productsixteenData[$column29] = $homepagedata->$column29;
        $productsixteenData[$column30] = $homepagedata->$column30;
        $productsixteenData['sec16_status'] = $homepagedata->sec16_status;
        
        $response = [
            'sec11'=> $productelevenData,
            'sec12' => $productTwelveData,
            'sec13'=> $producthirteenData,
            'sec14' => $productFourteenData,
            'sec15'=> $productfifteenData,
            'sec16' => $productsixteenData,
            ];

            CacheStores::create([
                'key' => 'threecachenewhomepagetwosec_'.$lang.'_'.$deviceType,
                'type' => 'homepagenew'
            ]);
            Cache::remember('threecachenewhomepagetwosec_'.$lang.'_'.$deviceType, $seconds, function () use ($response) {
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

}