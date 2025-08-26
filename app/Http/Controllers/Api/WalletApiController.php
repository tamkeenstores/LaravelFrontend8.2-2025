<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\Rules;
use App\Models\States;
use App\Models\FreeGift;
use App\Models\FrequentlyBoughtTogether;
use App\Models\RulesConditions;
use App\Models\WalletRestriction;
use App\Models\FlashSale;
use App\Models\Coupon;
// use App\Models\GiftwalletSms;
use App\Traits\CrudTrait;
use App\Helper\ConditionSetup_helper;

class WalletApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'wallet';
    protected $relationKey = 'wallet_id';
    
    // store sms
    public function storeWalletSms(Request $request) {
        // $GiftwalletSms = GiftwalletSms::first();
        // $success = false;
        // $update = false;
        // if(!$GiftwalletSms) {
        //     $wallet = GiftwalletSms::create([
        //         'sms' => $request->get('sms'),
        //         'sms_arabic' => $request->get('sms_arabic'),
        //         'status' => $request->get('status')
        //     ]);
        //     $success = true;
        // }
        // else {
        //     $GiftwalletSms->delete();
        //     $wallet = GiftwalletSms::create([
        //         'sms' => $request->get('sms'),
        //         'sms_arabic' => $request->get('sms_arabic'),
        //         'status' => $request->get('status')
        //     ]);
        //     $success = true;
        //     $update = true;
        // }
        // return response()->json(['success' => $success, 'update' => $update,'message' => 'Gift wallet SMS Has been '. $update == true ? 'updated' : 'created'.'!']);
    }


    public function model() {
        $data = ['limit' => -1, 'model' => Wallet::class, 'sort' => ['id','asc']];
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
        $data = Wallet::select('id', 'name', 'name_arabic', 'discount_amount', 'discount_type', 'end_date'
        ,'start_date', 'status')->orderBy('id', 'desc')->get();
        
        $response = [
            'data' => $data,
            // 'sms' => $GiftwalletSms
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
        $wallet = Wallet::create([
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
            'usage_limit_wallet' => isset($request->usage_limit_wallet) ? $request->usage_limit_wallet : null,
            'usage_limit_user' => isset($request->usage_limit_user) ? $request->usage_limit_user : null,
            'wallet_restriction_type' => isset($request->wallet_restriction_type['value']) ? $request->wallet_restriction_type['value'] : null,
            'restriction_brand_id' => isset($request->restriction_brand_id) ? implode(',',$request->restriction_brand_id) : null,
            'restriction_sub_tag_id' => isset($request->restriction_sub_tag_id) ? implode(',',$request->restriction_sub_tag_id) : null,
            'restriction_product_id' => isset($request->restriction_product_id) ? implode(',',$request->restriction_product_id) : null,
            'restriction_category_id' => isset($request->restriction_category_id) ? implode(',',$request->restriction_category_id) : null,
            'wallet_applied_type' => isset($request->wallet_applied_type['value']) ? $request->wallet_applied_type['value'] : null,
            'applied_brand_id' => isset($request->applied_brand_id) ? implode(',',$request->applied_brand_id) : null,
            'applied_sub_tag_id' => isset($request->applied_sub_tag_id) ? implode(',',$request->applied_sub_tag_id) : null,
            'applied_product_id' => isset($request->applied_product_id) ? implode(',',$request->applied_product_id) : null,
            'applied_category_id' => isset($request->applied_category_id) ? implode(',',$request->applied_category_id) : null,
            // 'applied_start_date' => isset($request->applied_start_date) ? $request->applied_start_date : null,
            // 'applied_end_date' => isset($request->applied_end_date) ? $request->applied_end_date : null,
            // 'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'wallet_disable_rules' => isset($request->wallet_disable_rules) ? $request->wallet_disable_rules : null,
        ]);
        
        if (isset($request->restrictions_id)) {
             foreach ($request->restrictions_id as $k => $value) {
                //  print_r($value);die();
                //   print_r($value['list']['value']);
                $restrictiondata = [
                    'wallet_id' => $wallet->id,
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
                 WalletRestriction::create($restrictiondata);
             }
         }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $wallet->id;
            $moduletype = 5;
            ConditionSetup_helper::CreateConditionsetup($data,$ruleid, $moduletype);
        }
        return response()->json(['success' => true, 'message' => 'Wallet Has been created!']);
    }
    
    public function update(Request $request, $id) {
        if (isset($request->restrictions_id)) {
            $resdata = WalletRestriction::where('wallet_id', '=',$id)->get();
            $resdata->each->delete();
            
            foreach ($request->restrictions_id as $k => $value) {
                //  print_r($value);die();
                //   print_r($value['list']['value']);
                $restrictiondata = [
                    'wallet_id' => $id,
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
                 WalletRestriction::create($restrictiondata);
             }
            
        }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $id;
            $moduletype = 5;
            ConditionSetup_helper::UpdateConditionsetup($data,$ruleid, $moduletype);
        }
        
        $wallet = Wallet::whereId($id)->update([
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
            'usage_limit_wallet' => isset($request->usage_limit_wallet) ? $request->usage_limit_wallet : null,
            'usage_limit_user' => isset($request->usage_limit_user) ? $request->usage_limit_user : null,
            'wallet_restriction_type' => isset($request->wallet_restriction_type['value']) ? $request->wallet_restriction_type['value'] : null,
            'restriction_brand_id' => isset($request->restriction_brand_id) ? implode(',',$request->restriction_brand_id) : null,
            'restriction_sub_tag_id' => isset($request->restriction_sub_tag_id) ? implode(',',$request->restriction_sub_tag_id) : null,
            'restriction_product_id' => isset($request->restriction_product_id) ? implode(',',$request->restriction_product_id) : null,
            'restriction_category_id' => isset($request->restriction_category_id) ? implode(',',$request->restriction_category_id) : null,
            'wallet_applied_type' => isset($request->wallet_applied_type['value']) ? $request->wallet_applied_type['value'] : null,
            'applied_brand_id' => isset($request->applied_brand_id) ? implode(',',$request->applied_brand_id) : null,
            'applied_sub_tag_id' => isset($request->applied_sub_tag_id) ? implode(',',$request->applied_sub_tag_id) : null,
            'applied_product_id' => isset($request->applied_product_id) ? implode(',',$request->applied_product_id) : null,
            'applied_category_id' => isset($request->applied_category_id) ? implode(',',$request->applied_category_id) : null,
            // 'applied_start_date' => isset($request->applied_start_date) ? $request->applied_start_date : null,
            // 'applied_end_date' => isset($request->applied_end_date) ? $request->applied_end_date : null,
            // 'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : null,
            'wallet_disable_rules' => isset($request->wallet_disable_rules) ? $request->wallet_disable_rules : null,    
        ]);
        
        return response()->json(['success' => true, 'message' => 'Wallet Has been updated!']);
    }
}
