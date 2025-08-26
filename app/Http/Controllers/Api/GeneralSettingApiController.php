<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\GeneralSettingPayment;
use App\Models\GeneralSettingProduct;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Traits\CrudTrait;

class GeneralSettingApiController extends Controller
{
    // use CrudTrait;
    // protected $viewVariable = 'general_setting';
    // protected $relationKey = 'general_setting_id';


    // public function model() {
    //     $data = ['limit' => -1, 'model' => GeneralSetting::class, 'sort' => ['id','asc']];
    //     return $data;
    // }
    // public function validationRules($resource_id = 0)
    // {
    //     return [];
    // }

    // public function files(){
    //     return [];
    // }

    // public function relations(){
    //     return ['productsetting_id' => 'productsetting', 'paymentsetting_id' => 'paymentsetting'];
    // }

    // public function arrayData(){
    //     return [];
    //     // data in coulumn is 0, data in json is 1
    // }

    // public function models()
    // {
    //      return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
    //      'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
    //      'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
    //      'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
    //      'states' => States::where('country_id','191')->get(['id as value', 'name as label']),
    //      ];
    // }
    
    public function index(Request $request) {
        
        $data = GeneralSetting::with('productsetting','paymentsetting', 'faviconimage:id,image', 'logowebimage:id,image', 'logomobileimage:id,image')->first();
        $category = Productcategory::where('status','=',1)->get(['id as value', 'name as label']);
        $tags = SubTags::where('status','=',1)->get(['id as value', 'name as label']);
        $brands = Brand::where('status','=',1)->get(['id as value', 'name as label']);
        $products = Product::where('status','=',1)->get(['id as value', 'sku as label']);
        $states = States::where('country_id','191')->get(['id as value', 'name as label']);
        
        
        return response()->json(['data' => $data,'category' => $category, 'tags' => $tags, 'brands' => $brands, 'products' => $products, 'states' => $states]);
    }
    
    public function create() {
        
        $category = Productcategory::where('status','=',1)->get(['id as value', 'name as label']);
        $tags = SubTags::where('status','=',1)->get(['id as value', 'name as label']);
        $brands = Brand::where('status','=',1)->get(['id as value', 'name as label']);
        $products = Product::where('status','=',1)->get(['id as value', 'sku as label']);
        $states = States::where('country_id','191')->get(['id as value', 'name as label']);
        
        return response()->json(['category' => $category, 'tags' => $tags, 'brands' => $brands, 'products' => $products, 'states' => $states]);
    }
    
    public function store(Request $request) 
    {
        // print_r(implode(',', $request->hyperpay_brand_id));die();
        $data = GeneralSetting::with('productsetting','paymentsetting')->first();
        if($data) {
            $settingproduct = GeneralSettingProduct::where('generalsetting_id', '=',$data->id)->get();
            $settingproduct->each->delete();
            
            $productsettingdata = [
                'generalsetting_id' => $data->id,
                'catalog_badge_status' => isset($request->catalog_badge_status) ? $request->catalog_badge_status : 0,
                'product_badge_status' => isset($request->product_badge_status) ? $request->product_badge_status : 0,
                'discount_type' => isset($request->discount_type) ? $request->discount_type : null,
                'hot' => isset($request->hot) ? $request->hot : 0,
                'new' => isset($request->new) ? $request->new : 0,
                'sales' => isset($request->sales) ? $request->sales : 0,
                'out_of_stock' => isset($request->out_of_stock) ? $request->out_of_stock : 0,
                'low_in_stock' => isset($request->low_in_stock) ? $request->low_in_stock : 0,
                'selling_out_fast' => isset($request->selling_out_fast) ? $request->selling_out_fast : 0,
                'hot_badge' => isset($request->hot_badge) ? $request->hot_badge : null,
                'hot_badge_arabic' => isset($request->hot_badge_arabic) ? $request->hot_badge_arabic : null,
                'hot_badge_colour' => isset($request->hot_badge_colour) ? $request->hot_badge_colour : null,
                'new_badge' => isset($request->new_badge) ? $request->new_badge : null,
                'new_badge_arabic' => isset($request->new_badge_arabic) ? $request->new_badge_arabic : null,
                'new_badge_days' => isset($request->new_badge_days) ? $request->new_badge_days : null,
                'new_badge_colour' => isset($request->new_badge_colour) ? $request->new_badge_colour : null,
                'low_in_stock_badge' => isset($request->low_in_stock_badge) ? $request->low_in_stock_badge : null,
                'low_in_stock_badge_arabic' => isset($request->low_in_stock_badge_arabic) ? $request->low_in_stock_badge_arabic : null,
                'low_in_stock_badge_colour' => isset($request->low_in_stock_badge_colour) ? $request->low_in_stock_badge_colour : null,
                'selling_out_fast_badge' => isset($request->selling_out_fast_badge) ? $request->selling_out_fast_badge : null,
                'selling_out_fast_badge_arabic' => isset($request->selling_out_fast_badge_arabic) ? $request->selling_out_fast_badge_arabic : null,
                'selling_out_fast_badge_colour' => isset($request->selling_out_fast_badge_colour) ? $request->selling_out_fast_badge_colour : null,
                'out_of_stock_badge' => isset($request->out_of_stock_badge) ? $request->out_of_stock_badge : null,
                'out_of_stock_badge_arabic' => isset($request->out_of_stock_badge_arabic) ? $request->out_of_stock_badge_arabic : null,
                'out_of_stock_badge_colour' => isset($request->out_of_stock_badge_colour) ? $request->out_of_stock_badge_colour : null,
                'low_stock_status' => isset($request->low_stock_status) ? $request->low_stock_status : 0,
                'low_stock_quantity' => isset($request->low_stock_quantity) ? $request->low_stock_quantity : null,
                'low_stock_email' => isset($request->low_stock_email) ? $request->low_stock_email : null,
                'low_stock_category_id' => isset($request->low_stock_category_id) ? implode(',', $request->low_stock_category_id) : null,
            ];
            
             GeneralSettingProduct::create($productsettingdata);
             
            $settingpayment = GeneralSettingPayment::where('generalsetting_id', '=',$data->id)->get();
            $settingpayment->each->delete();
            
            $paymentsettingdata = [
                'generalsetting_id' => $data->id,
                'hyperpay_status' => isset($request->hyperpay_status) ? $request->hyperpay_status : 0,
                'hyperpay_exclude_type' => isset($request->hyperpay_exclude_type) ? $request->hyperpay_exclude_type : null,
                'hyperpay_brand_id' => isset($request->hyperpay_brand_id) ? implode(',', $request->hyperpay_brand_id) : null,
                'hyperpay_product_id' => isset($request->hyperpay_product_id) ? implode(',', $request->hyperpay_product_id) : null,
                'hyperpay_sub_tag_id' => isset($request->hyperpay_sub_tag_id) ? implode(',', $request->hyperpay_sub_tag_id) : null,
                'hyperpay_category_id' => isset($request->hyperpay_category_id) ? implode(',', $request->hyperpay_category_id) : null,
                'hyperpay_min_value' => isset($request->hyperpay_min_value) ? $request->hyperpay_min_value : null,
                'hyperpay_max_value' => isset($request->hyperpay_max_value) ? $request->hyperpay_max_value : null,
                'applepay_status' => isset($request->applepay_status) ? $request->applepay_status : 0,
                'applepay_exclude_type' => isset($request->applepay_exclude_type) ? $request->applepay_exclude_type : null,
                'applepay_brand_id' => isset($request->applepay_brand_id) ? implode(',', $request->applepay_brand_id) : null,
                'applepay_product_id' => isset($request->applepay_product_id) ? implode(',', $request->applepay_product_id) : null,
                'applepay_sub_tag_id' => isset($request->applepay_sub_tag_id) ? implode(',', $request->applepay_sub_tag_id) : null,
                'applepay_category_id' => isset($request->applepay_category_id) ? implode(',', $request->applepay_category_id) : null,
                'applepay_min_value' => isset($request->applepay_min_value) ? $request->applepay_min_value : null,
                'applepay_max_value' => isset($request->applepay_max_value) ? $request->applepay_max_value : null,
                'tasheel_status' => isset($request->tasheel_status) ? $request->tasheel_status : 0,
                'tasheel_exclude_type' => isset($request->tasheel_exclude_type) ? $request->tasheel_exclude_type : null,
                'tasheel_brand_id' => isset($request->tasheel_brand_id) ? implode(',', $request->tasheel_brand_id) : null,
                'tasheel_product_id' => isset($request->tasheel_product_id) ? implode(',', $request->tasheel_product_id) : null,
                'tasheel_sub_tag_id' => isset($request->tasheel_sub_tag_id) ? implode(',', $request->tasheel_sub_tag_id) : null,
                'tasheel_category_id' => isset($request->tasheel_category_id) ? implode(',', $request->tasheel_category_id) : null,
                'tasheel_min_value' => isset($request->tasheel_min_value) ? $request->tasheel_min_value : null,
                'tasheel_max_value' => isset($request->tasheel_max_value) ? $request->tasheel_max_value : null,
                'tabby_status' => isset($request->tabby_status) ? $request->tabby_status : 0,
                'tabby_exclude_type' => isset($request->tabby_exclude_type) ? $request->tabby_exclude_type : null,
                'tabby_brand_id' => isset($request->tabby_brand_id) ? implode(',', $request->tabby_brand_id) : null,
                'tabby_product_id' => isset($request->tabby_product_id) ? implode(',', $request->tabby_product_id) : null,
                'tabby_sub_tag_id' => isset($request->tabby_sub_tag_id) ? implode(',', $request->tabby_sub_tag_id) : null,
                'tabby_category_id' => isset($request->tabby_category_id) ? implode(',', $request->tabby_category_id) : null,
                'tabby_min_value' => isset($request->tabby_min_value) ? $request->tabby_min_value : null,
                'tabby_max_value' => isset($request->tabby_max_value) ? $request->tabby_max_value : null,
                'tamara_status' => isset($request->tamara_status) ? $request->tamara_status : 0,
                'tamara_exclude_type' => isset($request->tamara_exclude_type) ? $request->tamara_exclude_type : null,
                'tamara_brand_id' => isset($request->tamara_brand_id) ? implode(',', $request->tamara_brand_id) : null,
                'tamara_product_id' => isset($request->tamara_product_id) ? implode(',', $request->tamara_product_id) : null,
                'tamara_sub_tag_id' => isset($request->tamara_sub_tag_id) ? implode(',', $request->tamara_sub_tag_id) : null,
                'tamara_category_id' => isset($request->tamara_category_id) ? implode(',', $request->tamara_category_id) : null,
                'tamara_min_value' => isset($request->tamara_min_value) ? $request->tamara_min_value : null,
                'tamara_max_value' => isset($request->tamara_max_value) ? $request->tamara_max_value : null,
                'cod_status' => isset($request->cod_status) ? $request->cod_status : 0,
                'cod_exclude_type' => isset($request->cod_exclude_type) ? $request->cod_exclude_type : null,
                'cod_brand_id' => isset($request->cod_brand_id) ? implode(',', $request->cod_brand_id) : null,
                'cod_product_id' => isset($request->cod_product_id) ? implode(',', $request->cod_product_id) : null,
                'cod_sub_tag_id' => isset($request->cod_sub_tag_id) ? implode(',', $request->cod_sub_tag_id) : null,
                'cod_category_id' => isset($request->cod_category_id) ? implode(',', $request->cod_category_id) : null,
                'cod_min_value' => isset($request->cod_min_value) ? $request->cod_min_value : null,
                'cod_max_value' => isset($request->cod_max_value) ? $request->cod_max_value : null,
                'cod_city_id' => isset($request->cod_city_id) ? implode(',', $request->cod_city_id) : null,
            ];
            
             GeneralSettingPayment::create($paymentsettingdata);
             
             $general = GeneralSetting::whereId($data->id)->update([
                'title' => isset($request->title) ? $request->title : null,
                'title_arabic' => isset($request->title_arabic) ? $request->title_arabic : null,
                'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
                'email' => isset($request->email) ? $request->email : null,
                'address' => isset($request->address) ? $request->address : null,
                'address_arabic' => isset($request->address_arabic) ? $request->address_arabic : null,
                'primary_colour' => isset($request->primary_colour) ? $request->primary_colour : null,
                'secondary_colour' => isset($request->secondary_colour) ? $request->secondary_colour : null,
                'favicon_image' => isset($request->favicon_image) ? $request->favicon_image : null,
                'logo_web_image' => isset($request->logo_web_image) ? $request->logo_web_image : null,
                'logo_mob_image' => isset($request->logo_mob_image) ? $request->logo_mob_image : null,
                'saudi_business_status' => isset($request->saudi_business_status) ? $request->saudi_business_status : 0,
                'saudi_business_link' => isset($request->saudi_business_link) ? $request->saudi_business_link : null,
                'ministry_commerce_status' => isset($request->ministry_commerce_status) ? $request->ministry_commerce_status : 0,
                'ministry_commerce_link' => isset($request->ministry_commerce_link) ? $request->ministry_commerce_link : null,
                'maroof_status' => isset($request->maroof_status) ? $request->maroof_status : 0,
                'maroof_link' => isset($request->maroof_link) ? $request->maroof_link : null,
                'ministry_zakat_status' => isset($request->ministry_zakat_status) ? $request->ministry_zakat_status : 0,
                'ministry_zakat_link' => isset($request->ministry_zakat_link) ? $request->ministry_zakat_link : null,
                'promotion_category' => isset($request->promotion_category) ? $request->promotion_category : null,
            ]);
        }
        else {
            $general = GeneralSetting::create([
                'title' => isset($request->title) ? $request->title : null,
                'title_arabic' => isset($request->title_arabic) ? $request->title_arabic : null,
                'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
                'email' => isset($request->email) ? $request->email : null,
                'address' => isset($request->address) ? $request->address : null,
                'address_arabic' => isset($request->address_arabic) ? $request->address_arabic : null,
                'primary_colour' => isset($request->primary_colour) ? $request->primary_colour : null,
                'secondary_colour' => isset($request->secondary_colour) ? $request->secondary_colour : null,
                'favicon_image' => isset($request->favicon_image) ? $request->favicon_image : null,
                'logo_web_image' => isset($request->logo_web_image) ? $request->logo_web_image : null,
                'logo_mob_image' => isset($request->logo_mob_image) ? $request->logo_mob_image : null,
                'saudi_business_status' => isset($request->saudi_business_status) ? $request->saudi_business_status : 0,
                'saudi_business_link' => isset($request->saudi_business_link) ? $request->saudi_business_link : null,
                'ministry_commerce_status' => isset($request->ministry_commerce_status) ? $request->ministry_commerce_status : 0,
                'ministry_commerce_link' => isset($request->ministry_commerce_link) ? $request->ministry_commerce_link : null,
                'maroof_status' => isset($request->maroof_status) ? $request->maroof_status : 0,
                'maroof_link' => isset($request->maroof_link) ? $request->maroof_link : null,
                'ministry_zakat_status' => isset($request->ministry_zakat_status) ? $request->ministry_zakat_status : 0,
                'ministry_zakat_link' => isset($request->ministry_zakat_link) ? $request->ministry_zakat_link : null,
                'promotion_category' => isset($request->promotion_category) ? $request->promotion_category : null,
            ]);
            
            $productsettingdata = [
                'generalsetting_id' => $general->id,
                'catalog_badge_status' => isset($request->catalog_badge_status) ? $request->catalog_badge_status : 0,
                'product_badge_status' => isset($request->product_badge_status) ? $request->product_badge_status : 0,
                'discount_type' => isset($request->discount_type) ? $request->discount_type : null,
                'hot' => isset($request->hot) ? $request->hot : 0,
                'new' => isset($request->new) ? $request->new : 0,
                'sales' => isset($request->sales) ? $request->sales : 0,
                'out_of_stock' => isset($request->out_of_stock) ? $request->out_of_stock : 0,
                'low_in_stock' => isset($request->low_in_stock) ? $request->low_in_stock : 0,
                'selling_out_fast' => isset($request->selling_out_fast) ? $request->selling_out_fast : 0,
                'hot_badge' => isset($request->hot_badge) ? $request->hot_badge : null,
                'hot_badge_arabic' => isset($request->hot_badge_arabic) ? $request->hot_badge_arabic : null,
                'hot_badge_colour' => isset($request->hot_badge_colour) ? $request->hot_badge_colour : null,
                'new_badge' => isset($request->new_badge) ? $request->new_badge : null,
                'new_badge_arabic' => isset($request->new_badge_arabic) ? $request->new_badge_arabic : null,
                'new_badge_days' => isset($request->new_badge_days) ? $request->new_badge_days : null,
                'new_badge_colour' => isset($request->new_badge_colour) ? $request->new_badge_colour : null,
                'low_in_stock_badge' => isset($request->low_in_stock_badge) ? $request->low_in_stock_badge : null,
                'low_in_stock_badge_arabic' => isset($request->low_in_stock_badge_arabic) ? $request->low_in_stock_badge_arabic : null,
                'low_in_stock_badge_colour' => isset($request->low_in_stock_badge_colour) ? $request->low_in_stock_badge_colour : null,
                'selling_out_fast_badge' => isset($request->selling_out_fast_badge) ? $request->selling_out_fast_badge : null,
                'selling_out_fast_badge_arabic' => isset($request->selling_out_fast_badge_arabic) ? $request->selling_out_fast_badge_arabic : null,
                'selling_out_fast_badge_colour' => isset($request->selling_out_fast_badge_colour) ? $request->selling_out_fast_badge_colour : null,
                'out_of_stock_badge' => isset($request->out_of_stock_badge) ? $request->out_of_stock_badge : null,
                'out_of_stock_badge_arabic' => isset($request->out_of_stock_badge_arabic) ? $request->out_of_stock_badge_arabic : null,
                'out_of_stock_badge_colour' => isset($request->out_of_stock_badge_colour) ? $request->out_of_stock_badge_colour : null,
                'low_stock_status' => isset($request->low_stock_status) ? $request->low_stock_status : 0,
                'low_stock_quantity' => isset($request->low_stock_quantity) ? $request->low_stock_quantity : null,
                'low_stock_email' => isset($request->low_stock_email) ? $request->low_stock_email : null,
                'low_stock_category_id' => isset($request->low_stock_category_id) ? implode(',', $request->low_stock_category_id) : null,
            ];
            
             GeneralSettingProduct::create($productsettingdata);
            
            $paymentsettingdata = [
                'generalsetting_id' => $general->id,
                'hyperpay_status' => isset($request->hyperpay_status) ? $request->hyperpay_status : 0,
                'hyperpay_exclude_type' => isset($request->hyperpay_exclude_type) ? $request->hyperpay_exclude_type : null,
                'hyperpay_brand_id' => isset($request->hyperpay_brand_id) ? implode(',', $request->hyperpay_brand_id) : null,
                'hyperpay_product_id' => isset($request->hyperpay_product_id) ? implode(',', $request->hyperpay_product_id) : null,
                'hyperpay_sub_tag_id' => isset($request->hyperpay_sub_tag_id) ? implode(',', $request->hyperpay_sub_tag_id) : null,
                'hyperpay_category_id' => isset($request->hyperpay_category_id) ? implode(',', $request->hyperpay_category_id) : null,
                'hyperpay_min_value' => isset($request->hyperpay_min_value) ? $request->hyperpay_min_value : null,
                'hyperpay_max_value' => isset($request->hyperpay_max_value) ? $request->hyperpay_max_value : null,
                'applepay_status' => isset($request->applepay_status) ? $request->applepay_status : 0,
                'applepay_exclude_type' => isset($request->applepay_exclude_type) ? $request->applepay_exclude_type : null,
                'applepay_brand_id' => isset($request->applepay_brand_id) ? implode(',', $request->applepay_brand_id) : null,
                'applepay_product_id' => isset($request->applepay_product_id) ? implode(',', $request->applepay_product_id) : null,
                'applepay_sub_tag_id' => isset($request->applepay_sub_tag_id) ? implode(',', $request->applepay_sub_tag_id) : null,
                'applepay_category_id' => isset($request->applepay_category_id) ? implode(',', $request->applepay_category_id) : null,
                'applepay_min_value' => isset($request->applepay_min_value) ? $request->applepay_min_value : null,
                'applepay_max_value' => isset($request->applepay_max_value) ? $request->applepay_max_value : null,
                'tasheel_status' => isset($request->tasheel_status) ? $request->tasheel_status : 0,
                'tasheel_exclude_type' => isset($request->applepay_exclude_type) ? $request->applepay_exclude_type : null,
                'tasheel_brand_id' => isset($request->tasheel_brand_id) ? implode(',', $request->tasheel_brand_id) : null,
                'tasheel_product_id' => isset($request->tasheel_product_id) ? implode(',', $request->tasheel_product_id) : null,
                'tasheel_sub_tag_id' => isset($request->tasheel_sub_tag_id) ? implode(',', $request->tasheel_sub_tag_id) : null,
                'tasheel_category_id' => isset($request->tasheel_category_id) ? implode(',', $request->tasheel_category_id) : null,
                'tasheel_min_value' => isset($request->tasheel_min_value) ? $request->tasheel_min_value : null,
                'tasheel_max_value' => isset($request->tasheel_max_value) ? $request->tasheel_max_value : null,
                'tabby_status' => isset($request->tabby_status) ? $request->tabby_status : 0,
                'tabby_exclude_type' => isset($request->tabby_exclude_type) ? $request->tabby_exclude_type : null,
                'tabby_brand_id' => isset($request->tabby_brand_id) ? implode(',', $request->tabby_brand_id) : null,
                'tabby_product_id' => isset($request->tabby_product_id) ? implode(',', $request->tabby_product_id) : null,
                'tabby_sub_tag_id' => isset($request->tabby_sub_tag_id) ? implode(',', $request->tabby_sub_tag_id) : null,
                'tabby_category_id' => isset($request->tabby_category_id) ? implode(',', $request->tabby_category_id) : null,
                'tabby_min_value' => isset($request->tabby_min_value) ? $request->tabby_min_value : null,
                'tabby_max_value' => isset($request->tabby_max_value) ? $request->tabby_max_value : null,
                'tamara_status' => isset($request->tamara_status) ? $request->tamara_status : 0,
                'tamara_exclude_type' => isset($request->tamara_exclude_type) ? $request->tamara_exclude_type : null,
                'tamara_brand_id' => isset($request->tamara_brand_id) ? implode(',', $request->tamara_brand_id) : null,
                'tamara_product_id' => isset($request->tamara_product_id) ? implode(',', $request->tamara_product_id) : null,
                'tamara_sub_tag_id' => isset($request->tamara_sub_tag_id) ? implode(',', $request->tamara_sub_tag_id) : null,
                'tamara_category_id' => isset($request->tamara_category_id) ? implode(',', $request->tamara_category_id) : null,
                'tamara_min_value' => isset($request->tamara_min_value) ? $request->tamara_min_value : null,
                'tamara_max_value' => isset($request->tamara_max_value) ? $request->tamara_max_value : null,
                'cod_status' => isset($request->cod_status) ? $request->cod_status : 0,
                'cod_exclude_type' => isset($request->cod_exclude_type) ? $request->cod_exclude_type : null,
                'cod_brand_id' => isset($request->cod_brand_id) ? implode(',', $request->cod_brand_id) : null,
                'cod_product_id' => isset($request->cod_product_id) ? implode(',', $request->cod_product_id) : null,
                'cod_sub_tag_id' => isset($request->cod_sub_tag_id) ? implode(',', $request->cod_sub_tag_id) : null,
                'cod_category_id' => isset($request->cod_category_id) ? implode(',', $request->cod_category_id) : null,
                'cod_min_value' => isset($request->cod_min_value) ? $request->cod_min_value : null,
                'cod_max_value' => isset($request->cod_max_value) ? $request->cod_max_value : null,
                'cod_city_id' => isset($request->cod_city_id) ? implode(',', $request->cod_city_id) : null,
            ];
            
             GeneralSettingPayment::create($paymentsettingdata);
        }
        return response()->json(['success' => true, 'message' => 'General Setting Has been updated!']);
    }
}
