<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\Rules;
use App\Models\States;
use App\Models\FreeGift;
use App\Models\FrequentlyBoughtTogether;
use App\Models\CouponBrand;
use App\Models\CouponSubTag;
use App\Models\CouponProduct;
use App\Models\CouponCategory;
use App\Models\CouponRestriction;
use App\Models\RulesConditions;
use App\Models\FlashSale;
use App\Models\GiftVoucher;
use App\Models\LoyaltyProgram;
use App\Models\CouponSms;
use App\Traits\CrudTrait;
use App\Helper\ConditionSetup_helper;
use Carbon\Carbon;

class CouponApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'coupon';
    protected $relationKey = 'coupon_id';
    
    // Coupon Sms
    public function storeCouponSms(Request $request) {
        $CouponSms = CouponSms::first();
        $success = false;
        $update = false;
        if(!$CouponSms) {
            $voucher = CouponSms::create([
                'sms' => $request->get('sms'),
                'sms_arabic' => $request->get('sms_arabic'),
                'status' => $request->get('status')
            ]);
            $success = true;
        }
        else {
            $CouponSms->delete();
            $voucher = CouponSms::create([
                'sms' => $request->get('sms'),
                'sms_arabic' => $request->get('sms_arabic'),
                'status' => $request->get('status')
            ]);
            $success = true;
            $update = true;
        }
        return response()->json(['success' => $success, 'update' => $update,'message' => 'Coupon SMS Has been '. $update == true ? 'updated' : 'created'.'!']);
    }


    public function model() {
        $data = ['limit' => -1, 'model' => Coupon::class, 'sort' => ['id','desc']];
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
        return ['restrictions_id' => 'restrictions','product_id' => 'products:id,name,name_arabic,sku','category_id' => 'category:id,name,name_arabic',
        'brand_id' => 'brands:id,name,name_arabic','sub_tag_id' => 'subtags:id,name,name_arabic','conditions_id' => 'conditions'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         'cities' => States::where('country_id','191')->get(['id as value', 'name as label']),
         'rules' => Rules::where('status','=',1)->get(['id as value', 'name as label']),
         'freegift' => FreeGift::where('status','=',1)->get(['id as value', 'name as label']),
         'flash_sale' => FlashSale::where('status','=',1)->get(['id as value', 'name as label']),
         'frequently_bought' => FrequentlyBoughtTogether::where('status','=',1)->get(['id as value', 'name as label']),
         'gift_voucher' => GiftVoucher::where('status','=',1)->get(['id as value', 'name as label']),
         'loyalty' => LoyaltyProgram::where('status','=',1)->get(['id as value', 'name as label']),
         ];
    }
    
    public function index(Request $request){
        $data = Coupon::select('id', 'coupon_creation' , 'coupon_code', 'discount_type', 'discount_amount', 'coupon_restriction_type', 'description','usage_limit_coupon',
        'voucher_order_number'
        ,'end_date', 'status')->with('products:id,name,sku', 'category:id,name', 'brands:id,name', 'subtags:id,name', 'orders:id,order_id,amount_id', 'orders.orderData:id,order_no')
        ->withCount('orders')->orderBy('id', 'desc')
        ->get();
        
        $coupon = Coupon::where('status', 1)->where('end_date', '<=', Carbon::today()->toDateString())->get(['id', 'status', 'end_date']);
        
        foreach($coupon as $key => $value) {
            $value->status = 0;
            $value->update();
        }
        
        $couponSms = CouponSms::first(['id', 'sms', 'sms_arabic', 'status']);
        // print_r($coupon);die();
        
        $response = [
            'data' => $data,
            'sms' => $couponSms,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function couponcode() {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = "";
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars)-1)];
        }
        return response()->json(['data' => $code]);
    }
    
    public function store(Request $request) 
    {
        // print_r(implode(',', $request->brand_id));die();
        // print_r($request->coupon_restriction_type['value']);die();
        $code = $request->coupon_code;
        // $exists = Coupon::where('coupon_code', $code)->count();
        // if($exists >= 1){
        //     $success = false;
        //     $coupon = true;
        //     return response()->json(['success' => $success, 'coupon' => $coupon, 'message' => 'Coupon Code is already used!']);
        // }
        $coupon = false;
        
        $Coupon = Coupon::create([
            'coupon_code' => isset($code) ? $code : null,
            'description' => isset($request->description) ? $request->description : null,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'discount_type' => isset($request->discount_type['value']) ? $request->discount_type['value'] : null,
            'discount_amount' => isset($request->discount_amount) ? $request->discount_amount : null,
            'max_cap_amount' => isset($request->max_cap_amount) ? $request->max_cap_amount : null,
            'usage_limit_coupon' => isset($request->usage_limit_coupon) ? $request->usage_limit_coupon : null,
            'usage_limit_user' => isset($request->usage_limit_user) ? $request->usage_limit_user : null,
            'coupon_restriction_type' => isset($request->coupon_restriction_type['value']) ? $request->coupon_restriction_type['value'] : null,
            'restriction_auto_apply' => isset($request->restriction_auto_apply) ? $request->restriction_auto_apply : null,
            'disable_rule_coupon' => isset($request->disable_rule_coupon) ? $request->disable_rule_coupon : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'connect_with_arabyads' => ($request->connect_with_arabyads == null) ? '0': '1',
            'show_on_commission' => ($request->show_on_commission == null) ? '0': '1',
            'coupon_creation' =>  isset($request->coupon_creation) ? $request->coupon_creation : 0,
            'voucher_order_number' => isset($request->voucher_order_number) ? $request->voucher_order_number : null,
        ]);
        
        if (isset($request->coupon_restriction_type)) {
                $data = [
                     'coupon_id' => $Coupon->id,
                ];
                // print_r($value);die();
                if($request->brand_id && $request->coupon_restriction_type['value'] == 1){
                    // print_r($request->brand_id);die();
                    foreach ($request->brand_id as $k => $value) {
                        // $data['brand_id'] = isset($value) ? $value : null;
                        $Couponbrand = CouponBrand::create([
                            'coupon_id' => $Coupon->id,
                            'brand_id' => isset($value) ? $value : null,
                        ]);
                    }
                    //  CouponBrand::create($data);
                }
                
                
                if($request->sub_tag_id && $request->coupon_restriction_type['value'] == 2){
                    foreach ($request->sub_tag_id as $k => $value)  {
                        // $data['sub_tag_id'] = isset($value) ? $value : null;
                        $CouponTag = CouponSubTag::create([
                            'coupon_id' => $Coupon->id,
                            'sub_tag_id' => isset($value) ? $value : null,
                        ]);
                    }
                    // CouponSubTag::create($data);
                }
                
                if($request->product_id && $request->coupon_restriction_type['value'] == 3){
                    foreach ($request->product_id as $k => $value)  {
                        // $data['product_id'] = isset($value) ? $value : null;
                        $CouponProduct = CouponProduct::create([
                            'coupon_id' => $Coupon->id,
                            'product_id' => isset($value) ? $value : null,
                        ]);
                    }
                    // CouponProduct::create($data);
                }
                
                if($request->category_id && $request->coupon_restriction_type['value'] == 4){
                    foreach ($request->category_id as $k => $value)  {
                        // $data['category_id'] = isset($value) ? $value : null;
                        $CouponCategory = CouponCategory::create([
                            'coupon_id' => $Coupon->id,
                            'category_id' => isset($value) ? $value : null,
                        ]);
                    }
                    // CouponCategory::create($data);
                }
        }
        
        if (isset($request->restrictions_id)) {
             foreach ($request->restrictions_id as $k => $value) {
                //  print_r($value);die();
                //   print_r($value['list']['value']);
                $restrictiondata = [
                    'coupon_id' => $Coupon->id,
                    'disabled_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'select_include_exclude' => isset($value['list']['value']) ? $value['list']['value'] : 0,
                ];
        
                if($value['rules'] && $value['type']['value'] == 1){
                    $restrictiondata['rules_id'] = implode(',', array_column($value['rules'], 'value'));
                }
                if($value['free_gift'] && $value['type']['value'] == 2){
                    $restrictiondata['free_gifts_id'] = implode(',', array_column($value['free_gift'], 'value'));
                }
                if($value['special_offer'] && $value['type']['value'] == 3){
                    $restrictiondata['special_offers_id'] = implode(',', array_column($value['special_offer'], 'value'));
                }
                if($value['gift_voucher'] && $value['type']['value'] == 4){
                    $restrictiondata['discount_coupon_id'] = implode(',', array_column($value['gift_voucher'], 'value'));
                }
                if($value['fbt'] && $value['type']['value'] == 5){
                    $restrictiondata['fbt_id'] = implode(',', array_column($value['fbt'], 'value'));
                }
                if($value['loyalty'] && $value['type']['value'] == 6){
                    $restrictiondata['loyalty_id'] = implode(',', array_column($value['loyalty'], 'value'));
                }
                 CouponRestriction::create($restrictiondata);
             }
         }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $Coupon->id;
            $moduletype = 1;
            ConditionSetup_helper::CreateConditionsetup($data,$ruleid, $moduletype);
        }

        // SMS send on coupon creation
        $CouponSms = CouponSms::where('status', 1)->first();
        if($CouponSms && $request->status == 1) {
            $data = [];
            $result = array();
            foreach ($request->conditiondata as $key => $val) {
                if($val['type']['value'] == 13) {
                    $data[] = $val['phone'];
                }
            }
            if(count($data) >= 1) {
                foreach ($data as $value) {
                    $parts = explode(',', $value);
                    $result = array_merge($result, $parts);
                }
                foreach ($result as $key => $num) {
                    ConditionSetup_helper::sms('+966'.$num, $CouponSms->sms);
                }
            }
        }
        return response()->json(['success' => true, 'coupon' => $coupon, 'message' => 'Coupon Has been created!']);
    }
    
    public function update(Request $request, $id) {
        $code = $request->coupon_code;
        // $exists = Coupon::where('id', '!=', $id)->where('coupon_code', $code)->count();
        // if($exists >= 1){
        //     $success = false;
        //     $coupon = true;
        //     return response()->json(['success' => $success, 'coupon' => $coupon, 'message' => 'Coupon Code is already used!']);
        // }
        $coupon = false;
        
        if (isset($request->coupon_restriction_type)) {
            
            $coupon_branddata = CouponBrand::where('coupon_id', '=',$id)->get();
            $coupon_branddata->each->delete();
            
            $coupon_subtagdata = CouponSubTag::where('coupon_id', '=',$id)->get();
            $coupon_subtagdata->each->delete();
            
            $coupon_productdata = CouponProduct::where('coupon_id', '=',$id)->get();
            $coupon_productdata->each->delete();
            
            $coupon_categorydata = CouponCategory::where('coupon_id', '=',$id)->get();
            $coupon_categorydata->each->delete();
            
            $data = [
                     'coupon_id' => $id,
                ];
                // print_r($value);die();
                if($request->brand_id && $request->coupon_restriction_type['value'] == 1){
                    // print_r($request->brand_id);die();
                    foreach ($request->brand_id as $k => $value) {
                        // $data['brand_id'] = isset($value) ? $value : null;
                        $Couponbrand = CouponBrand::create([
                            'coupon_id' => $id,
                            'brand_id' => isset($value) ? $value : null,
                        ]);
                    }
                    //  CouponBrand::create($data);
                }
                
                
                if($request->sub_tag_id && $request->coupon_restriction_type['value'] == 2){
                    foreach ($request->sub_tag_id as $k => $value)  {
                        // $data['sub_tag_id'] = isset($value) ? $value : null;
                        $CouponTag = CouponSubTag::create([
                            'coupon_id' => $id,
                            'sub_tag_id' => isset($value) ? $value : null,
                        ]);
                    }
                    // CouponSubTag::create($data);
                }
                
                if($request->product_id && $request->coupon_restriction_type['value'] == 3){
                    foreach ($request->product_id as $k => $value)  {
                        // $data['product_id'] = isset($value) ? $value : null;
                        $CouponProduct = CouponProduct::create([
                            'coupon_id' => $id,
                            'product_id' => isset($value) ? $value : null,
                        ]);
                    }
                    // CouponProduct::create($data);
                }
                
                if($request->category_id && $request->coupon_restriction_type['value'] == 4){
                    foreach ($request->category_id as $k => $value)  {
                        // $data['category_id'] = isset($value) ? $value : null;
                        $CouponCategory = CouponCategory::create([
                            'coupon_id' => $id,
                            'category_id' => isset($value) ? $value : null,
                        ]);
                    }
                    // CouponCategory::create($data);
                }
        }    
        
        if (isset($request->restrictions_id)) {
            $resdata = CouponRestriction::where('coupon_id', '=',$id)->get();
            $resdata->each->delete();
            
            foreach ($request->restrictions_id as $k => $value) {
                //  print_r($value);die();
                //   print_r($value['list']['value']);
                $restrictiondata = [
                    'coupon_id' => $id,
                    'disabled_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'select_include_exclude' => isset($value['list']['value']) ? $value['list']['value'] : 0,
                ];
        
                if($value['rules'] && $value['type']['value'] == 1){
                    $restrictiondata['rules_id'] = implode(',', array_column($value['rules'], 'value'));
                }
                if($value['free_gift'] && $value['type']['value'] == 2){
                    $restrictiondata['free_gifts_id'] = implode(',', array_column($value['free_gift'], 'value'));
                }
                if($value['special_offer'] && $value['type']['value'] == 3){
                    $restrictiondata['special_offers_id'] = implode(',', array_column($value['special_offer'], 'value'));
                }
                if($value['gift_voucher'] && $value['type']['value'] == 4){
                    $restrictiondata['discount_coupon_id'] = implode(',', array_column($value['gift_voucher'], 'value'));
                }
                if($value['fbt'] && $value['type']['value'] == 5){
                    $restrictiondata['fbt_id'] = implode(',', array_column($value['fbt'], 'value'));
                }
                if($value['loyalty'] && $value['type']['value'] == 6){
                    $restrictiondata['loyalty_id'] = implode(',', array_column($value['loyalty'], 'value'));
                }
                 CouponRestriction::create($restrictiondata);
             }
        }    
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $id;
            $moduletype = 1;
            ConditionSetup_helper::UpdateConditionsetup($data,$ruleid, $moduletype);
        }
        // print_r($request->discount_type['value']);die();
        
        $Coupon = Coupon::whereId($id)->update([
            'coupon_code' => isset($code) ? $code : null,
            'description' => isset($request->description) ? $request->description : null,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'discount_type' => isset($request->discount_type['value']) ? $request->discount_type['value'] : null,
            'discount_amount' => isset($request->discount_amount) ? $request->discount_amount : null,
            'max_cap_amount' => isset($request->max_cap_amount) ? $request->max_cap_amount : null,
            'usage_limit_coupon' => isset($request->usage_limit_coupon) ? $request->usage_limit_coupon : null,
            'usage_limit_user' => isset($request->usage_limit_user) ? $request->usage_limit_user : null,
            'coupon_restriction_type' => isset($request->coupon_restriction_type['value']) ? $request->coupon_restriction_type['value'] : null,
            'restriction_auto_apply' => isset($request->restriction_auto_apply) ? $request->restriction_auto_apply : null,
            'disable_rule_coupon' => isset($request->disable_rule_coupon) ? $request->disable_rule_coupon : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,    
            'connect_with_arabyads' => isset($request->connect_with_arabyads) ? $request->connect_with_arabyads : '0',
            'show_on_commission' => isset($request->show_on_commission) ? $request->show_on_commission : '0',
            'coupon_creation' =>  isset($request->coupon_creation) ? $request->coupon_creation : 0,
            'voucher_order_number' => isset($request->voucher_order_number) ? $request->voucher_order_number : null,
        ]);
        
        return response()->json(['success' => true, 'coupon' => $coupon, 'message' => 'Coupon Has been updated!']);
    }
    
    public function CountsData() {
        $coupon = Coupon::count();
        
        $expiredcoupon = Coupon::where('status', 0)->count();
        // print_r($expiredcoupon);die();
        return response()->json(['count' => $coupon, 'expiredcount' => $expiredcoupon]);
        
    }
}
