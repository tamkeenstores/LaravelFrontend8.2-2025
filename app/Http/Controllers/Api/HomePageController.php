<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomePage;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Traits\CrudTrait;

class HomePageController extends Controller
{

    public function index(Request $request) {
        
        $data = HomePage::first();
        return response()->json(['data' => $data]);
    }
    
    public function create() {
        $homepageData = HomePage::first();
        $category = Productcategory::where('status','=',1)->get(['id as value', 'name as label']);
        $brands = Brand::where('status','=',1)->get(['id as value', 'name as label']);
        $products = Product::where('status','=',1)->get(['id as value', 'sku as label']);
        return response()->json(['data'=>$homepageData,'category' => $category,'brands' => $brands, 'products' => $products]);
    }
    
    public function store(Request $request) 
    {
        // print_r(implode(',', $request->hyperpay_brand_id));die();
        
        // return response()->json(['success' => implode(",", $request->brands)]);
        $data = HomePage::first();
        if($data) {
            $homepagedataDelete = HomePage::where('id',$data->id)->first();
            $homepagedataDelete->delete();
            $homepagedata = [
                'meta_title_en' => $request->meta_title_en,
                'meta_title_ar' => $request->meta_title_ar,
                'meta_description_en' => $request->meta_description_en,
                'meta_description_ar' => $request->meta_description_ar,
                'meta_keyword_en' => $request->meta_keyword_en,
                'meta_keyword_ar' => $request->meta_keyword_ar,
                'categories_top_status' => isset($request->categories_top_status) ? $request->categories_top_status : 0,
                'categories_top'=> isset($request->categories) ? implode(",", $request->categories) : null,
                'brands_middle_status' => isset($request->brands_middle_status) ? $request->brands_middle_status : 0,
                'brands_middle'=> isset($request->brands) ? implode(",", $request->brands) : null,
                'products_first_status' => isset($request->products_first_status) ? $request->products_first_status : 0,
                'products_first'=> isset($request->products_first) ? implode(",", $request->products_first) : null,
                'products_second_status' => isset($request->products_second_status) ? $request->products_second_status : 0,
                'products_second'=> isset($request->products_second) ? implode(",", $request->products_second) : null,
                'products_third_status' => isset($request->products_third_status) ? $request->products_third_status : 0,
                'products_third'=> isset($request->products_third) ? implode(",", $request->products_third) : null,
                'products_fourth_status' => isset($request->products_fourth_status) ? $request->products_fourth_status : 0,
                'products_fourth'=> isset($request->products_fourth) ? implode(",", $request->products_fourth) : null,
                'products_fifth_status' => isset($request->products_fifth_status) ? $request->products_fifth_status : 0,
                'products_fifth'=> isset($request->products_fifth) ? implode(",", $request->products_fifth) : null,
                'cat_view_all'=> isset($request->cat_view_all) ? $request->cat_view_all : null,
                'brand_view_all'=> isset($request->brand_view_all) ? $request->brand_view_all : null,
                'pro_first_view_all'=> isset($request->pro_first_view_all) ? $request->pro_first_view_all : null,
                'pro_first_heading'=> isset($request->pro_first_heading) ? $request->pro_first_heading : null,
                'pro_first_heading_arabic'=> isset($request->pro_first_heading_arabic) ? $request->pro_first_heading_arabic : null,
                'pro_second_view_all'=> isset($request->pro_second_view_all) ? $request->pro_second_view_all : null,
                'pro_second_heading'=> isset($request->pro_second_heading) ? $request->pro_second_heading : null,
                'pro_second_heading_arabic'=> isset($request->pro_second_heading_arabic) ? $request->pro_second_heading_arabic : null,
                'pro_third_view_all'=> isset($request->pro_third_view_all) ? $request->pro_third_view_all : null,
                'pro_third_heading'=> isset($request->pro_third_heading) ? $request->pro_third_heading : null,
                'pro_third_heading_arabic'=> isset($request->pro_third_heading_arabic) ? $request->pro_third_heading_arabic : null,
                'pro_fourth_view_all'=> isset($request->pro_fourth_view_all) ? $request->pro_fourth_view_all : null,
                'pro_fourth_heading'=> isset($request->pro_fourth_heading) ? $request->pro_fourth_heading : null,
                'pro_fourth_heading_arabic'=> isset($request->pro_fourth_heading_arabic) ? $request->pro_fourth_heading_arabic : null,
                'pro_fifth_view_all'=> isset($request->pro_fifth_view_all) ? $request->pro_fifth_view_all : null,
                'pro_fifth_heading'=> isset($request->pro_fifth_heading) ? $request->pro_fifth_heading : null,
                'pro_fifth_heading_arabic'=> isset($request->pro_fifth_heading_arabic) ? $request->pro_fifth_heading_arabic : null,
                'cat_heading'=> isset($request->cat_heading) ? $request->cat_heading : null,
                'cat_heading_arabic'=> isset($request->cat_heading_arabic) ? $request->cat_heading_arabic : null,

                'products_sixth_status' => isset($request->products_sixth_status) ? $request->products_sixth_status : 0,
                'products_sixth'=> isset($request->products_sixth) ? implode(",", $request->products_sixth) : null,
                'pro_sixth_view_all'=> isset($request->pro_sixth_view_all) ? $request->pro_sixth_view_all : null,
                'pro_sixth_heading'=> isset($request->pro_sixth_heading) ? $request->pro_sixth_heading : null,
                'pro_sixth_heading_arabic'=> isset($request->pro_sixth_heading_arabic) ? $request->pro_sixth_heading_arabic : null,

                'products_seventh_status' => isset($request->products_seventh_status) ? $request->products_seventh_status : 0,
                'products_seventh'=> isset($request->products_seventh) ? implode(",", $request->products_seventh) : null,
                'pro_seventh_view_all'=> isset($request->pro_seventh_view_all) ? $request->pro_seventh_view_all : null,
                'pro_seventh_heading'=> isset($request->pro_seventh_heading) ? $request->pro_seventh_heading : null,
                'pro_seventh_heading_arabic'=> isset($request->pro_seventh_heading_arabic) ? $request->pro_seventh_heading_arabic : null,

                'products_eigth_status' => isset($request->products_eigth_status) ? $request->products_eigth_status : 0,
                'products_eigth'=> isset($request->products_eigth) ? implode(",", $request->products_eigth) : null,
                'pro_eigth_view_all'=> isset($request->pro_eigth_view_all) ? $request->pro_eigth_view_all : null,
                'pro_eigth_heading'=> isset($request->pro_eigth_heading) ? $request->pro_eigth_heading : null,
                'pro_eigth_heading_arabic'=> isset($request->pro_eigth_heading_arabic) ? $request->pro_eigth_heading_arabic : null,

                'products_nineth_status' => isset($request->products_nineth_status) ? $request->products_nineth_status : 0,
                'products_nineth'=> isset($request->products_nineth) ? implode(",", $request->products_nineth) : null,
                'pro_nineth_view_all'=> isset($request->pro_nineth_view_all) ? $request->pro_nineth_view_all : null,
                'pro_nineth_heading'=> isset($request->pro_nineth_heading) ? $request->pro_nineth_heading : null,
                'pro_nineth_heading_arabic'=> isset($request->pro_nineth_heading_arabic) ? $request->pro_nineth_heading_arabic : null,

                'products_tenth_status' => isset($request->products_tenth_status) ? $request->products_tenth_status : 0,
                'products_tenth'=> isset($request->products_tenth) ? implode(",", $request->products_tenth) : null,
                'pro_tenth_view_all'=> isset($request->pro_tenth_view_all) ? $request->pro_tenth_view_all : null,
                'pro_tenth_heading'=> isset($request->pro_tenth_heading) ? $request->pro_tenth_heading : null,
                'pro_tenth_heading_arabic'=> isset($request->pro_tenth_heading_arabic) ? $request->pro_tenth_heading_arabic : null,

                'products_eleventh_status' => isset($request->products_eleventh_status) ? $request->products_eleventh_status : 0,
                'products_eleventh'=> isset($request->products_eleventh) ? implode(",", $request->products_eleventh) : null,
                'pro_eleventh_view_all'=> isset($request->pro_eleventh_view_all) ? $request->pro_eleventh_view_all : null,
                'pro_eleventh_heading'=> isset($request->pro_eleventh_heading) ? $request->pro_eleventh_heading : null,
                'pro_eleventh_heading_arabic'=> isset($request->pro_eleventh_heading_arabic) ? $request->pro_eleventh_heading_arabic : null,
                
                'products_twelveth_status' => isset($request->products_twelveth_status) ? $request->products_twelveth_status : 0,
                'products_twelveth'=> isset($request->products_twelveth) ? implode(",", $request->products_twelveth) : null,
                'pro_twelveth_view_all'=> isset($request->pro_twelveth_view_all) ? $request->pro_twelveth_view_all : null,
                'pro_twelveth_heading'=> isset($request->pro_twelveth_heading) ? $request->pro_twelveth_heading : null,
                'pro_twelveth_heading_arabic'=> isset($request->pro_twelveth_heading_arabic) ? $request->pro_twelveth_heading_arabic : null,
                
                'banner_image1'=> isset($request->banner_image1) ? $request->banner_image1 : null,
                'banner_image2'=> isset($request->banner_image2) ? $request->banner_image2 : null,
                'banner_image3'=> isset($request->banner_image3) ? $request->banner_image3 : null,
                'banner_image4'=> isset($request->banner_image4) ? $request->banner_image4 : null,
                'banner_image1_link'=> isset($request->banner_image1_link) ? $request->banner_image1_link : null,
                'banner_image2_link'=> isset($request->banner_image2_link) ? $request->banner_image2_link : null,
                'banner_image3_link'=> isset($request->banner_image3_link) ? $request->banner_image3_link : null,
                'banner_image4_link'=> isset($request->banner_image4_link) ? $request->banner_image4_link : null,
                'banner_first_status' => isset($request->banner_first_status) ? $request->banner_first_status : 0,
                'banner_second_status' => isset($request->banner_second_status) ? $request->banner_second_status : 0,
                
                'banner_first_heading'=> isset($request->banner_first_heading) ? $request->banner_first_heading : null,
                'banner_first_heading_arabic'=> isset($request->banner_first_heading_arabic) ? $request->banner_first_heading_arabic : null,
                'banner_second_heading'=> isset($request->banner_second_heading) ? $request->banner_second_heading : null,
                'banner_second_heading_arabic'=> isset($request->banner_second_heading_arabic) ? $request->banner_second_heading_arabic : null,
                
            ];
            HomePage::create($homepagedata);
        }
        else {
            $general = HomePage::create([
                'meta_title_en' => $request->meta_title_en,
                'meta_title_ar' => $request->meta_title_ar,
                'meta_description_en' => $request->meta_description_en,
                'meta_description_ar' => $request->meta_description_ar,
                'meta_keyword_en' => $request->meta_keyword_en,
                'meta_keyword_ar' => $request->meta_keyword_ar,
                'categories_top_status' => isset($request->categories_top_status) ? $request->categories_top_status : 0,
                'categories_top'=> isset($request->categories) ? implode(",", $request->categories) : null,
                'brands_middle_status' => isset($request->brands_middle_status) ? $request->brands_middle_status : 0,
                'brands_middle'=> isset($request->brands) ? implode(",", $request->brands) : null,
                'products_first_status' => isset($request->products_first_status) ? $request->products_first_status : 0,
                'products_first'=> isset($request->products_first) ? implode(",", $request->products_first) : null,
                'products_second_status' => isset($request->products_second_status) ? $request->products_second_status : 0,
                'products_second'=> isset($request->products_second) ? implode(",", $request->products_second) : null,
                'products_third_status' => isset($request->products_third_status) ? $request->products_third_status : 0,
                'products_third'=> isset($request->products_third) ? implode(",", $request->products_third) : null,
                'products_fourth_status' => isset($request->products_fourth_status) ? $request->products_fourth_status : 0,
                'products_fourth'=> isset($request->products_fourth) ? implode(",", $request->products_fourth) : null,
                'products_fifth_status' => isset($request->products_fifth_status) ? $request->products_fifth_status : 0,
                'products_fifth'=> isset($request->products_fifth) ? implode(",", $request->products_fifth) : null,
                'cat_view_all'=> isset($request->cat_view_all) ? $request->cat_view_all : null,
                'brand_view_all'=> isset($request->brand_view_all) ? $request->brand_view_all : null,
                'pro_first_view_all'=> isset($request->pro_first_view_all) ? $request->pro_first_view_all : null,
                'pro_first_heading'=> isset($request->pro_first_heading) ? $request->pro_first_heading : null,
                'pro_first_heading_arabic'=> isset($request->pro_first_heading_arabic) ? $request->pro_first_heading_arabic : null,
                'pro_second_view_all'=> isset($request->pro_second_view_all) ? $request->pro_second_view_all : null,
                'pro_second_heading'=> isset($request->pro_second_heading) ? $request->pro_second_heading : null,
                'pro_second_heading_arabic'=> isset($request->pro_second_heading_arabic) ? $request->pro_second_heading_arabic : null,
                'pro_third_view_all'=> isset($request->pro_third_view_all) ? $request->pro_third_view_all : null,
                'pro_third_heading'=> isset($request->pro_third_heading) ? $request->pro_third_heading : null,
                'pro_third_heading_arabic'=> isset($request->pro_third_heading_arabic) ? $request->pro_third_heading_arabic : null,
                'pro_fourth_view_all'=> isset($request->pro_fourth_view_all) ? $request->pro_fourth_view_all : null,
                'pro_fourth_heading'=> isset($request->pro_fourth_heading) ? $request->pro_fourth_heading : null,
                'pro_fourth_heading_arabic'=> isset($request->pro_fourth_heading_arabic) ? $request->pro_fourth_heading_arabic : null,
                'pro_fifth_view_all'=> isset($request->pro_fifth_view_all) ? $request->pro_fifth_view_all : null,
                'pro_fifth_heading'=> isset($request->pro_fifth_heading) ? $request->pro_fifth_heading : null,
                'pro_fifth_heading_arabic'=> isset($request->pro_fifth_heading_arabic) ? $request->pro_fifth_heading_arabic : null,
                'cat_heading'=> isset($request->cat_heading) ? $request->cat_heading : null,
                'cat_heading_arabic'=> isset($request->cat_heading_arabic) ? $request->cat_heading_arabic : null,

                'products_sixth_status' => isset($request->products_sixth_status) ? $request->products_sixth_status : 0,
                'products_sixth'=> isset($request->products_sixth) ? implode(",", $request->products_sixth) : null,
                'pro_sixth_view_all'=> isset($request->pro_sixth_view_all) ? $request->pro_sixth_view_all : null,
                'pro_sixth_heading'=> isset($request->pro_sixth_heading) ? $request->pro_sixth_heading : null,
                'pro_sixth_heading_arabic'=> isset($request->pro_sixth_heading_arabic) ? $request->pro_sixth_heading_arabic : null,

                'products_seventh_status' => isset($request->products_seventh_status) ? $request->products_seventh_status : 0,
                'products_seventh'=> isset($request->products_seventh) ? implode(",", $request->products_seventh) : null,
                'pro_seventh_view_all'=> isset($request->pro_seventh_view_all) ? $request->pro_seventh_view_all : null,
                'pro_seventh_heading'=> isset($request->pro_seventh_heading) ? $request->pro_seventh_heading : null,
                'pro_seventh_heading_arabic'=> isset($request->pro_seventh_heading_arabic) ? $request->pro_seventh_heading_arabic : null,

                'products_eigth_status' => isset($request->products_eigth_status) ? $request->products_eigth_status : 0,
                'products_eigth'=> isset($request->products_eigth) ? implode(",", $request->products_eigth) : null,
                'pro_eigth_view_all'=> isset($request->pro_eigth_view_all) ? $request->pro_eigth_view_all : null,
                'pro_eigth_heading'=> isset($request->pro_eigth_heading) ? $request->pro_eigth_heading : null,
                'pro_eigth_heading_arabic'=> isset($request->pro_eigth_heading_arabic) ? $request->pro_eigth_heading_arabic : null,

                'products_nineth_status' => isset($request->products_nineth_status) ? $request->products_nineth_status : 0,
                'products_nineth'=> isset($request->products_nineth) ? implode(",", $request->products_nineth) : null,
                'pro_nineth_view_all'=> isset($request->pro_nineth_view_all) ? $request->pro_nineth_view_all : null,
                'pro_nineth_heading'=> isset($request->pro_nineth_heading) ? $request->pro_nineth_heading : null,
                'pro_nineth_heading_arabic'=> isset($request->pro_nineth_heading_arabic) ? $request->pro_nineth_heading_arabic : null,

                'products_tenth_status' => isset($request->products_tenth_status) ? $request->products_tenth_status : 0,
                'products_tenth'=> isset($request->products_tenth) ? implode(",", $request->products_tenth) : null,
                'pro_tenth_view_all'=> isset($request->pro_tenth_view_all) ? $request->pro_tenth_view_all : null,
                'pro_tenth_heading'=> isset($request->pro_tenth_heading) ? $request->pro_tenth_heading : null,
                'pro_tenth_heading_arabic'=> isset($request->pro_tenth_heading_arabic) ? $request->pro_tenth_heading_arabic : null,

                'products_eleventh_status' => isset($request->products_eleventh_status) ? $request->products_eleventh_status : 0,
                'products_eleventh'=> isset($request->products_eleventh) ? implode(",", $request->products_eleventh) : null,
                'pro_eleventh_view_all'=> isset($request->pro_eleventh_view_all) ? $request->pro_eleventh_view_all : null,
                'pro_eleventh_heading'=> isset($request->pro_eleventh_heading) ? $request->pro_eleventh_heading : null,
                'pro_eleventh_heading_arabic'=> isset($request->pro_eleventh_heading_arabic) ? $request->pro_eleventh_heading_arabic : null,
                
                'products_twelveth_status' => isset($request->products_twelveth_status) ? $request->products_twelveth_status : 0,
                'products_twelveth'=> isset($request->products_twelveth) ? implode(",", $request->products_twelveth) : null,
                'pro_twelveth_view_all'=> isset($request->pro_twelveth_view_all) ? $request->pro_twelveth_view_all : null,
                'pro_twelveth_heading'=> isset($request->pro_twelveth_heading) ? $request->pro_twelveth_heading : null,
                'pro_twelveth_heading_arabic'=> isset($request->pro_twelveth_heading_arabic) ? $request->pro_twelveth_heading_arabic : null,
                
                'banner_image1'=> isset($request->banner_image1) ? $request->banner_image1 : null,
                'banner_image2'=> isset($request->banner_image2) ? $request->banner_image2 : null,
                'banner_image3'=> isset($request->banner_image3) ? $request->banner_image3 : null,
                'banner_image4'=> isset($request->banner_image4) ? $request->banner_image4 : null,
                
                'banner_image1_link'=> isset($request->banner_image1_link) ? $request->banner_image1_link : null,
                'banner_image2_link'=> isset($request->banner_image2_link) ? $request->banner_image2_link : null,
                'banner_image3_link'=> isset($request->banner_image3_link) ? $request->banner_image3_link : null,
                'banner_image4_link'=> isset($request->banner_image4_link) ? $request->banner_image4_link : null,
                'banner_first_status' => isset($request->banner_first_status) ? $request->banner_first_status : 0,
                'banner_second_status' => isset($request->banner_second_status) ? $request->banner_second_status : 0,
                
                'banner_first_heading'=> isset($request->banner_first_heading) ? $request->banner_first_heading : null,
                'banner_first_heading_arabic'=> isset($request->banner_first_heading_arabic) ? $request->banner_first_heading_arabic : null,
                'banner_second_heading'=> isset($request->banner_second_heading) ? $request->banner_second_heading : null,
                'banner_second_heading_arabic'=> isset($request->banner_second_heading_arabic) ? $request->banner_second_heading_arabic : null,
            ]);
        }
        return response()->json(['success' => true, 'message' => 'Home Page Data have been updated, successfully!']);
    }
}
