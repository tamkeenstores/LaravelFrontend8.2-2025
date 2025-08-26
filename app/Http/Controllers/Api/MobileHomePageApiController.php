<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileHomePage;
use App\Models\MobileHomeImages;
use App\Models\MobileHomeServices;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\FlashSale;
use App\Traits\CrudTrait;

class MobileHomePageApiController extends Controller
{
    public function index(Request $request) {
        
        $data = MobileHomePage::with('images', 'services')->first();
        return response()->json(['data' => $data]);
    }
    
    public function create() {
        $mobilehomepageData = MobileHomePage::with('images', 'images.FeaturedImage:id,image','images.FeaturedImageArabic:id,image','services', 'services.FeaturedImage:id,image',
        'services.FeaturedImageArabic:id,image')->first();
        $category = Productcategory::where('status','=',1)->get(['id as value', 'name as label']);
        $brands = Brand::where('status','=',1)->get(['id as value', 'name as label']);
        $products = Product::where('status','=',1)->get(['id as value', 'sku as label']);
        $flashsales = FlashSale::where('status','=',1)->get(['id as value', 'name as label']);
        return response()->json(['data'=>$mobilehomepageData,'category' => $category,'brands' => $brands, 'products' => $products, 'flashsales' => $flashsales]);
    }
    
    public function store(Request $request) 
    {
        // print_r($request->all());die;
        $data = MobileHomePage::first();
        if($data) {
            
            $homepagedata = MobileHomePage::whereId($data->id)->update([
                'cat_sec_heading' => isset($request->cat_sec_heading) ? $request->cat_sec_heading : null,
                'cat_sec_heading_arabic' => isset($request->cat_sec_heading_arabic) ? $request->cat_sec_heading_arabic : null,
                'cats_first_line' => isset($request->cats_first_line) ? implode(",", $request->cats_first_line) : null,
                'cats_second_line' => isset($request->cats_second_line) ? implode(",", $request->cats_second_line) : null,
                'cats_view_all_link' => isset($request->cats_view_all_link) ? $request->cats_view_all_link : null,
                'cats_view_all_link_arabic' => isset($request->cats_view_all_link_arabic) ? $request->cats_view_all_link_arabic : null,
                'cat_viewall_link' => isset($request->cat_viewall_link) ? $request->cat_viewall_link : null,
                'cat_sec_status' => isset($request->cat_sec_status) ? $request->cat_sec_status : 0,
                'first_pro_heading' => isset($request->first_pro_heading) ? $request->first_pro_heading : null,
                'first_pro_heading_arabic' => isset($request->first_pro_heading_arabic) ? $request->first_pro_heading_arabic : null,
                'first_products' => isset($request->first_products) ? implode(",", $request->first_products) : null,
                'first_pro_view_all_link' => isset($request->first_pro_view_all_link) ? $request->first_pro_view_all_link : null,
                'first_pro_view_all_link_arabic' => isset($request->first_pro_view_all_link_arabic) ? $request->first_pro_view_all_link_arabic : null,
                'first_pro_link_viewall' => isset($request->first_pro_link_viewall) ? $request->first_pro_link_viewall : null,
                'first_pro_status' => isset($request->first_pro_status) ? $request->first_pro_status : 0,
                'brands_heading' => isset($request->brands_heading) ? $request->brands_heading : null,
                'brands_heading_arabic' => isset($request->brands_heading_arabic) ? $request->brands_heading_arabic : null,
                'brands' => isset($request->brands) ? implode(",", $request->brands) : null,
                'brands_view_all_link' => isset($request->brands_view_all_link) ? $request->brands_view_all_link : null,
                'brands_view_all_link_arabic' => isset($request->brands_view_all_link_arabic) ? $request->brands_view_all_link_arabic : null,
                'brand_link_viewall' => isset($request->brand_link_viewall) ? $request->brand_link_viewall : null,
                'brands_status' => isset($request->brands_status) ? $request->brands_status : 0,
                'second_pro_heading' => isset($request->second_pro_heading) ? $request->second_pro_heading : null,
                'second_pro_heading_arabic' => isset($request->second_pro_heading_arabic) ? $request->second_pro_heading_arabic : null,
                'second_products' => isset($request->second_products) ? implode(",", $request->second_products) : null,
                'second_pro_view_all_link' => isset($request->second_pro_view_all_link) ? $request->second_pro_view_all_link : null,
                'second_pro_view_all_link_arabic' => isset($request->second_pro_view_all_link_arabic) ? $request->second_pro_view_all_link_arabic : null,
                'second_pro_link_viewall' => isset($request->second_pro_link_viewall) ? $request->second_pro_link_viewall : null,
                'second_pro_status' => isset($request->second_pro_status) ? $request->second_pro_status : 0,
                'flash_sale_heading' => isset($request->flash_sale_heading) ? $request->flash_sale_heading : null,
                'flash_sale_heading_arabic' => isset($request->flash_sale_heading_arabic) ? $request->flash_sale_heading_arabic : null,
                'first_flash_sale' => isset($request->first_flash_sale) ? implode(",", $request->first_flash_sale) : null,
                'second_flash_sale' => isset($request->second_flash_sale) ? implode(",", $request->second_flash_sale) : null,
                'flash_sale_view_all' => isset($request->flash_sale_view_all) ? $request->flash_sale_view_all : null,
                'flash_sale_view_all_arabic' => isset($request->flash_sale_view_all_arabic) ? $request->flash_sale_view_all_arabic : null,
                'flash_sale_link_viewall' => isset($request->flash_sale_link_viewall) ? $request->flash_sale_link_viewall : null,
                'flash_sale_status' => isset($request->flash_sale_status) ? $request->flash_sale_status : 0,
                'flash_sale_sec_status' => isset($request->flash_sale_sec_status) ? $request->flash_sale_sec_status : 0,
                'images_heading' => isset($request->images_heading) ? $request->images_heading : null,
                'images_heading_arabic' => isset($request->images_heading_arabic) ? $request->images_heading_arabic : null,
                'images_status' => isset($request->images_status) ? $request->images_status : 0,
                'image_view_all' => isset($request->image_view_all) ? $request->image_view_all : null,
                'image_view_all_arabic' => isset($request->image_view_all_arabic) ? $request->image_view_all_arabic : null,
                'image_link_viewall' => isset($request->image_link_viewall) ? $request->image_link_viewall : null,
                'first_text_editor_data' => isset($request->first_text_editor_data) ? $request->first_text_editor_data : null,
                'first_text_editor_data_arabic' => isset($request->first_text_editor_data_arabic) ? $request->first_text_editor_data_arabic : null,
                'first_text_editor_status' => isset($request->first_text_editor_status) ? $request->first_text_editor_status : 0,
                'third_pro_heading' => isset($request->third_pro_heading) ? $request->third_pro_heading : null,
                'third_pro_heading_arabic' => isset($request->third_pro_heading_arabic) ? $request->third_pro_heading_arabic : null,
                'third_products' => isset($request->third_products) ? implode(",", $request->third_products) : null,
                'third_pro_view_all_link' => isset($request->third_pro_view_all_link) ? $request->third_pro_view_all_link : null,
                'third_pro_view_all_link_arabic' => isset($request->third_pro_view_all_link_arabic) ? $request->third_pro_view_all_link_arabic : null,
                'third_pro_link_viewall' => isset($request->third_pro_link_viewall) ? $request->third_pro_link_viewall : null,
                'third_pro_status' => isset($request->third_pro_status) ? $request->third_pro_status : 0,
                'fourth_pro_heading' => isset($request->fourth_pro_heading) ? $request->fourth_pro_heading : null,
                'fourth_pro_heading_arabic' => isset($request->fourth_pro_heading_arabic) ? $request->fourth_pro_heading_arabic : null,
                'fourth_products' => isset($request->fourth_products) ? implode(",", $request->fourth_products) : null,
                'fourth_pro_view_all_link' => isset($request->fourth_pro_view_all_link) ? $request->fourth_pro_view_all_link : null,
                'fourth_pro_view_all_link_arabic' => isset($request->fourth_pro_view_all_link_arabic) ? $request->fourth_pro_view_all_link_arabic : null,
                'fourth_pro_link_viewall' => isset($request->fourth_pro_link_viewall) ? $request->fourth_pro_link_viewall : null,
                'fourth_pro_status' => isset($request->fourth_pro_status) ? $request->fourth_pro_status : 0,
                'fifth_pro_heading' => isset($request->fifth_pro_heading) ? $request->fifth_pro_heading : null,
                'fifth_pro_heading_arabic' => isset($request->fifth_pro_heading_arabic) ? $request->fifth_pro_heading_arabic : null,
                'fifth_products' => isset($request->fifth_products) ? implode(",", $request->fifth_products) : null,
                'fifth_pro_view_all_link' => isset($request->fifth_pro_view_all_link) ? $request->fifth_pro_view_all_link : null,
                'fifth_pro_view_all_link_arabic' => isset($request->fifth_pro_view_all_link_arabic) ? $request->fifth_pro_view_all_link_arabic : null,
                'fifth_pro_link_viewall' => isset($request->fifth_pro_link_viewall) ? $request->fifth_pro_link_viewall : null,
                'fifth_pro_status' => isset($request->fifth_pro_status) ? $request->fifth_pro_status : 0,
                'second_text_editor_data' => isset($request->second_text_editor_data) ? $request->second_text_editor_data : null,
                'second_text_editor_data_arabic' => isset($request->second_text_editor_data_arabic) ? $request->second_text_editor_data_arabic : null,
                'second_text_editor_status' => isset($request->second_text_editor_status) ? $request->second_text_editor_status : 0,
                'services_heading' => isset($request->services_heading) ? $request->services_heading : null,
                'services_heading_arabic' => isset($request->services_heading_arabic) ? $request->services_heading_arabic : null,
                'services_status' => isset($request->services_status) ? $request->services_status : 0,
                'services_view_all' => isset($request->services_view_all) ? $request->services_view_all : null,
                'services_view_all_arabic' => isset($request->services_view_all_arabic) ? $request->services_view_all_arabic : null,
                'services_link_viewall' => isset($request->services_link_viewall) ? $request->services_link_viewall : null,
                'third_text_editor_data' => isset($request->third_text_editor_data) ? $request->third_text_editor_data : null,
                'third_text_editor_data_arabic' => isset($request->third_text_editor_data_arabic) ? $request->third_text_editor_data_arabic : null,
                'third_text_editor_status' => isset($request->third_text_editor_status) ? $request->third_text_editor_status : 0,
            ]);
            
            if (isset($request->imagesdata)) {
                // print_r($data->id);die;
                $oldimagedata = MobileHomeImages::where('mobile_home_page_id', $data->id)->get();
                // print_r($oldimagedata);die;
                $oldimagedata->each->delete();
                foreach ($request->imagesdata as $k => $value) {
                    // print_r($value);die;
                    $dataimages = [
                        'mobile_home_page_id' => $data->id,
                        'image' => isset($value['ImageId']) ? $value['ImageId'] : null,
                        'image_arabic' => isset($value['ImageArabicId']) ? $value['ImageArabicId'] : null,
                        'link' => isset($value['link']) ? $value['link'] : null,
                    ];
                    MobileHomeImages::create($dataimages);
                }
            }
            
            if (isset($request->servicesdata)) {
                $oldservicedata = MobileHomeServices::where('mobile_home_page_id', $data->id)->get();
                $oldservicedata->each->delete();
                foreach ($request->servicesdata as $k => $value) {
                    $dataservices = [
                        'mobile_home_page_id' => $data->id,
                        'image' => isset($value['ImageId']) ? $value['ImageId'] : null,
                        'image_arabic' => isset($value['ImageArabicId']) ? $value['ImageArabicId'] : null,
                        'link' => isset($value['link']) ? $value['link'] : null,
                    ];
                    MobileHomeServices::create($dataservices);
                }
            }
        }
        else {
            // print_r('on-create');die;
            $general = MobileHomePage::create([
                'cat_sec_heading' => isset($request->cat_sec_heading) ? $request->cat_sec_heading : null,
                'cat_sec_heading_arabic' => isset($request->cat_sec_heading_arabic) ? $request->cat_sec_heading_arabic : null,
                'cats_first_line' => isset($request->cats_first_line) ? implode(",", $request->cats_first_line) : null,
                'cats_second_line' => isset($request->cats_second_line) ? implode(",", $request->cats_second_line) : null,
                'cats_view_all_link' => isset($request->cats_view_all_link) ? $request->cats_view_all_link : null,
                'cats_view_all_link_arabic' => isset($request->cats_view_all_link_arabic) ? $request->cats_view_all_link_arabic : null,
                'cat_viewall_link' => isset($request->cat_viewall_link) ? $request->cat_viewall_link : null,
                'cat_sec_status' => isset($request->cat_sec_status) ? $request->cat_sec_status : 0,
                'first_pro_heading' => isset($request->first_pro_heading) ? $request->first_pro_heading : null,
                'first_pro_heading_arabic' => isset($request->first_pro_heading_arabic) ? $request->first_pro_heading_arabic : null,
                'first_products' => isset($request->first_products) ? implode(",", $request->first_products) : null,
                'first_pro_view_all_link' => isset($request->first_pro_view_all_link) ? $request->first_pro_view_all_link : null,
                'first_pro_view_all_link_arabic' => isset($request->first_pro_view_all_link_arabic) ? $request->first_pro_view_all_link_arabic : null,
                'first_pro_link_viewall' => isset($request->first_pro_link_viewall) ? $request->first_pro_link_viewall : null,
                'first_pro_status' => isset($request->first_pro_status) ? $request->first_pro_status : 0,
                'brands_heading' => isset($request->brands_heading) ? $request->brands_heading : null,
                'brands_heading_arabic' => isset($request->brands_heading_arabic) ? $request->brands_heading_arabic : null,
                'brands' => isset($request->brands) ? implode(",", $request->brands) : null,
                'brands_view_all_link' => isset($request->brands_view_all_link) ? $request->brands_view_all_link : null,
                'brands_view_all_link_arabic' => isset($request->brands_view_all_link_arabic) ? $request->brands_view_all_link_arabic : null,
                'brand_link_viewall' => isset($request->brand_link_viewall) ? $request->brand_link_viewall : null,
                'brands_status' => isset($request->brands_status) ? $request->brands_status : 0,
                'second_pro_heading' => isset($request->second_pro_heading) ? $request->second_pro_heading : null,
                'second_pro_heading_arabic' => isset($request->second_pro_heading_arabic) ? $request->second_pro_heading_arabic : null,
                'second_products' => isset($request->second_products) ? implode(",", $request->second_products) : null,
                'second_pro_view_all_link' => isset($request->second_pro_view_all_link) ? $request->second_pro_view_all_link : null,
                'second_pro_view_all_link_arabic' => isset($request->second_pro_view_all_link_arabic) ? $request->second_pro_view_all_link_arabic : null,
                'second_pro_link_viewall' => isset($request->second_pro_link_viewall) ? $request->second_pro_link_viewall : null,
                'second_pro_status' => isset($request->second_pro_status) ? $request->second_pro_status : 0,
                'flash_sale_heading' => isset($request->flash_sale_heading) ? $request->flash_sale_heading : null,
                'flash_sale_heading_arabic' => isset($request->flash_sale_heading_arabic) ? $request->flash_sale_heading_arabic : null,
                'first_flash_sale' => isset($request->first_flash_sale) ? implode(",", $request->first_flash_sale) : null,
                'second_flash_sale' => isset($request->second_flash_sale) ? implode(",", $request->second_flash_sale) : null,
                'flash_sale_view_all' => isset($request->flash_sale_view_all) ? $request->flash_sale_view_all : null,
                'flash_sale_view_all_arabic' => isset($request->flash_sale_view_all_arabic) ? $request->flash_sale_view_all_arabic : null,
                'flash_sale_link_viewall' => isset($request->flash_sale_link_viewall) ? $request->flash_sale_link_viewall : null,
                'flash_sale_status' => isset($request->flash_sale_status) ? $request->flash_sale_status : 0,
                'flash_sale_sec_status' => isset($request->flash_sale_sec_status) ? $request->flash_sale_sec_status : 0,
                'images_heading' => isset($request->images_heading) ? $request->images_heading : null,
                'images_heading_arabic' => isset($request->images_heading_arabic) ? $request->images_heading_arabic : null,
                'images_status' => isset($request->images_status) ? $request->images_status : 0,
                'image_view_all' => isset($request->image_view_all) ? $request->image_view_all : null,
                'image_view_all_arabic' => isset($request->image_view_all_arabic) ? $request->image_view_all_arabic : null,
                'image_link_viewall' => isset($request->image_link_viewall) ? $request->image_link_viewall : null,
                'first_text_editor_data' => isset($request->first_text_editor_data) ? $request->first_text_editor_data : null,
                'first_text_editor_data_arabic' => isset($request->first_text_editor_data_arabic) ? $request->first_text_editor_data_arabic : null,
                'first_text_editor_status' => isset($request->first_text_editor_status) ? $request->first_text_editor_status : 0,
                'third_pro_heading' => isset($request->third_pro_heading) ? $request->third_pro_heading : null,
                'third_pro_heading_arabic' => isset($request->third_pro_heading_arabic) ? $request->third_pro_heading_arabic : null,
                'third_products' => isset($request->third_products) ? implode(",", $request->third_products) : null,
                'third_pro_view_all_link' => isset($request->third_pro_view_all_link) ? $request->third_pro_view_all_link : null,
                'third_pro_view_all_link_arabic' => isset($request->third_pro_view_all_link_arabic) ? $request->third_pro_view_all_link_arabic : null,
                'third_pro_link_viewall' => isset($request->third_pro_link_viewall) ? $request->third_pro_link_viewall : null,
                'third_pro_status' => isset($request->third_pro_status) ? $request->third_pro_status : 0,
                'fourth_pro_heading' => isset($request->fourth_pro_heading) ? $request->fourth_pro_heading : null,
                'fourth_pro_heading_arabic' => isset($request->fourth_pro_heading_arabic) ? $request->fourth_pro_heading_arabic : null,
                'fourth_products' => isset($request->fourth_products) ? implode(",", $request->fourth_products) : null,
                'fourth_pro_view_all_link' => isset($request->fourth_pro_view_all_link) ? $request->fourth_pro_view_all_link : null,
                'fourth_pro_view_all_link_arabic' => isset($request->fourth_pro_view_all_link_arabic) ? $request->fourth_pro_view_all_link_arabic : null,
                'fourth_pro_link_viewall' => isset($request->fourth_pro_link_viewall) ? $request->fourth_pro_link_viewall : null,
                'fourth_pro_status' => isset($request->fourth_pro_status) ? $request->fourth_pro_status : 0,
                'fifth_pro_heading' => isset($request->fifth_pro_heading) ? $request->fifth_pro_heading : null,
                'fifth_pro_heading_arabic' => isset($request->fifth_pro_heading_arabic) ? $request->fifth_pro_heading_arabic : null,
                'fifth_products' => isset($request->fifth_products) ? implode(",", $request->fifth_products) : null,
                'fifth_pro_view_all_link' => isset($request->fifth_pro_view_all_link) ? $request->fifth_pro_view_all_link : null,
                'fifth_pro_view_all_link_arabic' => isset($request->fifth_pro_view_all_link_arabic) ? $request->fifth_pro_view_all_link_arabic : null,
                'fifth_pro_link_viewall' => isset($request->fifth_pro_link_viewall) ? $request->fifth_pro_link_viewall : null,
                'fifth_pro_status' => isset($request->fifth_pro_status) ? $request->fifth_pro_status : 0,
                'second_text_editor_data' => isset($request->second_text_editor_data) ? $request->second_text_editor_data : null,
                'second_text_editor_data_arabic' => isset($request->second_text_editor_data_arabic) ? $request->second_text_editor_data_arabic : null,
                'second_text_editor_status' => isset($request->second_text_editor_status) ? $request->second_text_editor_status : 0,
                'services_heading' => isset($request->services_heading) ? $request->services_heading : null,
                'services_heading_arabic' => isset($request->services_heading_arabic) ? $request->services_heading_arabic : null,
                'services_status' => isset($request->services_status) ? $request->services_status : 0,
                'services_view_all' => isset($request->services_view_all) ? $request->services_view_all : null,
                'services_view_all_arabic' => isset($request->services_view_all_arabic) ? $request->services_view_all_arabic : null,
                'services_link_viewall' => isset($request->services_link_viewall) ? $request->services_link_viewall : null,
                'third_text_editor_data' => isset($request->third_text_editor_data) ? $request->third_text_editor_data : null,
                'third_text_editor_data_arabic' => isset($request->third_text_editor_data_arabic) ? $request->third_text_editor_data_arabic : null,
                'third_text_editor_status' => isset($request->third_text_editor_status) ? $request->third_text_editor_status : 0,
            ]);
            
            if (isset($request->imagesdata)) {
                foreach ($request->imagesdata as $k => $value) {
                    $dataimages = [
                        'mobile_home_page_id' => $general->id,
                        'image' => isset($value['ImageId']) ? $value['ImageId'] : null,
                        'image_arabic' => isset($value['ImageArabicId']) ? $value['ImageArabicId'] : null,
                        'link' => isset($value['link']) ? $value['link'] : null,
                    ];
                    MobileHomeImages::create($dataimages);
                }
            }
            
            if (isset($request->servicesdata)) {
                foreach ($request->servicesdata as $k => $value) {
                    $dataservices = [
                        'mobile_home_page_id' => $general->id,
                        'image' => isset($value['ImageId']) ? $value['ImageId'] : null,
                        'image_arabic' => isset($value['ImageArabicId']) ? $value['ImageArabicId'] : null,
                        'link' => isset($value['link']) ? $value['link'] : null,
                    ];
                    MobileHomeServices::create($dataservices);
                }
            }
        }
        return response()->json(['success' => true, 'message' => 'Mobile Home Page Data have been updated, successfully!']);
    }
}
