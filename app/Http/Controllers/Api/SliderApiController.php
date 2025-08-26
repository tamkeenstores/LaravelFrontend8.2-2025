<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\SliderSetting;
use App\Traits\CrudTrait;

class SliderApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'sliders';
    protected $relationKey = 'sliders_id';

    // Coupon Sms
    public function storeSettingFields(Request $request) {
        $CouponSms = SliderSetting::first();
        $success = false;
        $update = false;
        if(!$CouponSms) {
            $voucher = SliderSetting::create([
                'no_of_columns' => $request->get('no_of_columns'),
                'space_between' => $request->get('space_between')
            ]);
            $success = true;
        }
        else {
            $CouponSms->delete();
            $voucher = SliderSetting::create([
                'no_of_columns' => $request->get('no_of_columns'),
                'space_between' => $request->get('space_between')
            ]);
            $success = true;
            $update = true;
        }
        return response()->json(['success' => $success, 'update' => $update,'message' => 'Slider Setting Has been '. $update == true ? 'updated' : 'created'.'!']);
    }

    public function model() {
        $data = ['limit' => -1, 'model' => Slider::class, 'sort' => ['id','desc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        // return [];
        return ['cat_id' => 'cat:id,name,slug', 'pro_id' => 'pro:id,sku,slug',
        'brand_id' => 'brand:id,name,slug', 'subtag_id' => 'subtag:id,name',
        'image_web' => 'featuredImageWeb:id,image', 'image_app' => 'featuredImageApp:id,image'];
    }

    public function arrayData(){
        return ['slider_devices' => 0];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
        'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
        'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
        'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),];
    }
    
    public function settingdata()
    {
        $setting = SliderSetting::first();
        return response()->json(['data' => $setting]);
    }
}
