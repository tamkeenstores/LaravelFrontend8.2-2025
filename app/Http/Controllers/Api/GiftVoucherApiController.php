<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GiftVoucher;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\Rules;
use App\Models\States;
use App\Models\FreeGift;
use App\Models\FrequentlyBoughtTogether;
use App\Models\RulesConditions;
use App\Models\GiftVoucherRestriction;
use App\Models\FlashSale;
use App\Models\Coupon;
use App\Models\GiftVoucherSms;
use App\Traits\CrudTrait;
use App\Helper\ConditionSetup_helper;

class GiftVoucherApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'gift_vouchcer';
    protected $relationKey = 'gift_vouchcer_id';
    
    // store sms
    public function storeGvSms(Request $request) {
        $GiftVoucherSms = GiftVoucherSms::first();
        $success = false;
        $update = false;
        if(!$GiftVoucherSms) {
            $voucher = GiftVoucherSms::create([
                'sms' => $request->get('sms'),
                'sms_arabic' => $request->get('sms_arabic'),
                'status' => $request->get('status')
            ]);
            $success = true;
        }
        else {
            $GiftVoucherSms->delete();
            $voucher = GiftVoucherSms::create([
                'sms' => $request->get('sms'),
                'sms_arabic' => $request->get('sms_arabic'),
                'status' => $request->get('status')
            ]);
            $success = true;
            $update = true;
        }
        return response()->json(['success' => $success, 'update' => $update,'message' => 'Gift Voucher SMS Has been '. $update == true ? 'updated' : 'created'.'!']);
    }


    public function model() {
        $data = ['limit' => -1, 'model' => GiftVoucher::class, 'sort' => ['id','asc']];
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
        return ['restrictions_id' => 'restrictions','conditions_id' => 'conditions'];
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
         'coupon' => Coupon::where('status','=',1)->get(['id as value', 'coupon_code as label']),
         ];
    }
    
    public function index(Request $request){
        $data = GiftVoucher::select('id', 'name', 'name_arabic', 'discount_amount', 'discount_type', 'end_date'
        ,'start_date', 'status')->orderBy('id', 'desc')->get();
        $GiftVoucherSms = GiftVoucherSms::first(['id', 'sms', 'sms_arabic', 'status']);
        
        $response = [
            'data' => $data,
            'sms' => $GiftVoucherSms
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function store(Request $request) 
    {
        // print_r($request->all());die();
        // print_r(implode(',',$request->restriction_sub_tag_id));die();
        $voucher = GiftVoucher::create([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'description' => isset($request->description) ? $request->description : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'discount_type' => isset($request->discount_type['value']) ? $request->discount_type['value'] : null,
            'discount_amount' => isset($request->discount_amount) ? $request->discount_amount : null,
            'max_cap_amount' => $request->discount_type['value'] == 1 || $request->discount_type['value'] == 3 || $request->discount_type['value'] == 4 ? $request->max_cap_amount : null,
            'usage_limit_voucher' => isset($request->usage_limit_voucher) ? $request->usage_limit_voucher : null,
            'usage_limit_user' => isset($request->usage_limit_user) ? $request->usage_limit_user : null,
            'voucher_restriction_type' => isset($request->voucher_restriction_type['value']) ? $request->voucher_restriction_type['value'] : null,
            'restriction_brand_id' => isset($request->restriction_brand_id) ? implode(',',$request->restriction_brand_id) : null,
            'restriction_sub_tag_id' => isset($request->restriction_sub_tag_id) ? implode(',',$request->restriction_sub_tag_id) : null,
            'restriction_product_id' => isset($request->restriction_product_id) ? implode(',',$request->restriction_product_id) : null,
            'restriction_category_id' => isset($request->restriction_category_id) ? implode(',',$request->restriction_category_id) : null,
            'voucher_applied_type' => isset($request->voucher_applied_type['value']) ? $request->voucher_applied_type['value'] : null,
            'applied_brand_id' => isset($request->applied_brand_id) ? implode(',',$request->applied_brand_id) : null,
            'applied_sub_tag_id' => isset($request->applied_sub_tag_id) ? implode(',',$request->applied_sub_tag_id) : null,
            'applied_product_id' => isset($request->applied_product_id) ? implode(',',$request->applied_product_id) : null,
            'applied_category_id' => isset($request->applied_category_id) ? implode(',',$request->applied_category_id) : null,
            'applied_start_date' => isset($request->applied_start_date) ? $request->applied_start_date : null,
            'applied_end_date' => isset($request->applied_end_date) ? $request->applied_end_date : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'voucher_disable_rules' => isset($request->voucher_disable_rules) ? $request->voucher_disable_rules : null,
        ]);
        
        if (isset($request->restrictions_id)) {
             foreach ($request->restrictions_id as $k => $value) {
                //  print_r($value);die();
                //   print_r($value['list']['value']);
                $restrictiondata = [
                    'voucher_id' => $voucher->id,
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
                if($value['coupon'] && $value['type']['value'] == 4){
                    $restrictiondata['coupon_id'] = implode(',', array_column($value['coupon'], 'value'));
                }
                if($value['fbt'] && $value['type']['value'] == 5){
                    $restrictiondata['fbt_id'] = implode(',', array_column($value['fbt'], 'value'));
                }
                 GiftVoucherRestriction::create($restrictiondata);
             }
         }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $voucher->id;
            $moduletype = 2;
            ConditionSetup_helper::CreateConditionsetup($data,$ruleid, $moduletype);
        }
        return response()->json(['success' => true, 'message' => 'Gift Voucher Has been created!']);
    }
    
    public function update(Request $request, $id) {
        if (isset($request->restrictions_id)) {
            $resdata = GiftVoucherRestriction::where('voucher_id', '=',$id)->get();
            $resdata->each->delete();
            
            foreach ($request->restrictions_id as $k => $value) {
                //  print_r($value);die();
                //   print_r($value['list']['value']);
                $restrictiondata = [
                    'voucher_id' => $id,
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
                if($value['coupon'] && $value['type']['value'] == 4){
                    $restrictiondata['coupon_id'] = implode(',', array_column($value['coupon'], 'value'));
                }
                if($value['fbt'] && $value['type']['value'] == 5){
                    $restrictiondata['fbt_id'] = implode(',', array_column($value['fbt'], 'value'));
                }
                 GiftVoucherRestriction::create($restrictiondata);
             }
            
        }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $id;
            $moduletype = 2;
            ConditionSetup_helper::UpdateConditionsetup($data,$ruleid, $moduletype);
        }
        
        $voucher = GiftVoucher::whereId($id)->update([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'description' => isset($request->description) ? $request->description : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'discount_type' => isset($request->discount_type['value']) ? $request->discount_type['value'] : null,
            'discount_amount' => isset($request->discount_amount) ? $request->discount_amount : null,
            'max_cap_amount' => $request->discount_type['value'] == 1 || $request->discount_type['value'] == 3 || $request->discount_type['value'] == 4 ? $request->max_cap_amount : null,
            'usage_limit_voucher' => isset($request->usage_limit_voucher) ? $request->usage_limit_voucher : null,
            'usage_limit_user' => isset($request->usage_limit_user) ? $request->usage_limit_user : null,
            'voucher_restriction_type' => isset($request->voucher_restriction_type['value']) ? $request->voucher_restriction_type['value'] : null,
            'restriction_brand_id' => isset($request->restriction_brand_id) ? implode(',',$request->restriction_brand_id) : null,
            'restriction_sub_tag_id' => isset($request->restriction_sub_tag_id) ? implode(',',$request->restriction_sub_tag_id) : null,
            'restriction_product_id' => isset($request->restriction_product_id) ? implode(',',$request->restriction_product_id) : null,
            'restriction_category_id' => isset($request->restriction_category_id) ? implode(',',$request->restriction_category_id) : null,
            'voucher_applied_type' => isset($request->voucher_applied_type['value']) ? $request->voucher_applied_type['value'] : null,
            'applied_brand_id' => isset($request->applied_brand_id) ? implode(',',$request->applied_brand_id) : null,
            'applied_sub_tag_id' => isset($request->applied_sub_tag_id) ? implode(',',$request->applied_sub_tag_id) : null,
            'applied_product_id' => isset($request->applied_product_id) ? implode(',',$request->applied_product_id) : null,
            'applied_category_id' => isset($request->applied_category_id) ? implode(',',$request->applied_category_id) : null,
            'applied_start_date' => isset($request->applied_start_date) ? $request->applied_start_date : null,
            'applied_end_date' => isset($request->applied_end_date) ? $request->applied_end_date : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'voucher_disable_rules' => isset($request->voucher_disable_rules) ? $request->voucher_disable_rules : null,    
        ]);
        
        return response()->json(['success' => true, 'message' => 'Gift Voucher Has been updated!']);
    }
}
