<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Affiliation;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\Rules;
use App\Models\States;
use App\Models\FreeGift;
use App\Models\User;
use App\Models\RulesConditions;
use App\Models\AffiliationRestriction;
use App\Models\LoyaltyProgram;
use App\Models\Coupon;
use App\Models\GiftVoucher;
use App\Traits\CrudTrait;
use App\Helper\ConditionSetup_helper;

class AffiliationApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'affiliation';
    protected $relationKey = 'affiliation_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Affiliation::class, 'sort' => ['id','desc']];
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
        return ['restrictions_id' => 'restrictions','conditions_id' => 'conditions', 'redirect_cat' => 'RedirectCat:id,name,slug', 'redirect_pro' => 'RedirectPro:id,sku,name,slug', 'redirect_brand' => 'RedirectBrand:id,name,slug', 'redirect_tag' => 'RedirectTag:id,name'];
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
         'coupon' => Coupon::where('status','=',1)->get(['id as value', 'coupon_code as label']),
         'loyalty' => LoyaltyProgram::where('status','=',1)->get(['id as value', 'name as label']),
         'gift_voucher' => GiftVoucher::where('status','=',1)->get(['id as value', 'name as label']),
        //  'users' => User::where('status','=',1)->get(['id as value', 'first_name as label']),
         ];
    }
    
    public function index(Request $request){
        $data = Affiliation::select('id','name','name_arabic' ,'specific_users_id', 'redirect_type', 'slug_code', 'notes', 'status', 'pages_id', 'product_id', 'brand_id', 'sub_tag_id'
        ,'category_id', 'custom_link')
        ->with('RedirectCat:id,name', 'RedirectBrand:id,name', 'RedirectPro:id,sku', 'RedirectTag:id,name')
        ->orderBy('id', 'desc')
        ->get();
        
        $response = [
            'data' => $data
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
        $name = $request->name;
        if($name) {
            $exists = Affiliation::where('name', $name)->count();
        }
        if($exists >= 1){
            $success = false;
            return response()->json(['success' => $success, 'message' => 'Name is already used!']);
        }
        
        $namearabic = $request->name_arabic;
        if($namearabic) {
            $existsarabic = Affiliation::where('name', $namearabic)->count();
        }
        if($existsarabic >= 1){
            $success = false;
            return response()->json(['success' => $success, 'message' => 'Name Arabic is already used!']);
        }
        
        // print_r($request->redirect_type);die;
        $affiliation = Affiliation::create([
            'rules_type' => isset($request->rules_type) ? $request->rules_type : null,
            'rules_id' => isset($request->rules_id) ? implode(',',$request->rules_id) : null,
            'coupon_id' => isset($request->coupon_id) ? implode(',',$request->coupon_id) : null,
            'free_gifts_id' => isset($request->free_gifts_id) ? implode(',',$request->free_gifts_id) : null,
            'gift_voucher_id' => isset($request->gift_voucher_id) ? implode(',',$request->gift_voucher_id) : null,
            'loyalty_id' => isset($request->loyalty_id) ? implode(',',$request->loyalty_id) : null,
            'disable_rules' => isset($request->disable_rules) ? $request->disable_rules : null,
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'slug_code' => isset($request->slug_code) ? $request->slug_code : null,
            'redirect_type' => isset($request->redirect_type) ? $request->redirect_type : null,
            'pages_id' => isset($request->pages_id) ? $request->pages_id['value'] : null,
            'brand_id' => !empty($request->brand_id) ? $request->brand_id['value'] : null,
            'product_id' => !empty($request->product_id) ? $request->product_id['value'] : null,
            'sub_tag_id' => !empty($request->sub_tag_id) ? $request->sub_tag_id['value'] : null,
            'category_id' => !empty($request->category_id) ? $request->category_id['value'] : null,
            'custom_link' => isset($request->custom_link) ? $request->custom_link : null,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'usage_limit' => isset($request->usage_limit) ? $request->usage_limit : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'specific_users_id' => isset($request->specific_users_id) ? $request->specific_users_id : null,
            'status' => isset($request->status) ? $request->status : 0,
            'disable_conditions' => isset($request->disable_conditions) ? $request->disable_conditions : null,
        ]);
        
        if (isset($request->restrictions)) {
             foreach ($request->restrictions as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                    'affiliation_id' => $affiliation->id,
                    'restriction_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'restriction_discount_type' => !empty($value['discount_type']) ? $value['discount_type']['value'] : null,
                ];
        
                if($value['brand_id'] && $value['type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand_id'], 'value'));
                }
                if($value['sub_tag_id'] && $value['type']['value'] == 3){
                    $data['sub_tag_id'] = implode(',', array_column($value['sub_tag_id'], 'value'));
                }
                if($value['product_id'] && $value['type']['value'] == 2){
                    $data['product_id'] = implode(',', array_column($value['product_id'], 'value'));
                }
                if($value['category_id'] && $value['type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category_id'], 'value'));
                }
                
                if($value['discount_type'] && $value['discount_type']['value'] == 0){
                    $data['discount_amount'] = isset($value['discount_percentage']) ? $value['discount_percentage'] : null;
                    $data['max_cap_amount'] = isset($value['max_cap_amount']) ? $value['max_cap_amount'] : null;
                }
                
                if($value['discount_type'] && $value['discount_type']['value'] == 1){
                    $data['discount_amount'] = isset($value['fixed_amount']) ? $value['fixed_amount'] : null;
                }
                
                AffiliationRestriction::create($data);
             }
         }
         
         if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $affiliation->id;
            $moduletype = 4;
            ConditionSetup_helper::CreateConditionsetup($data,$ruleid, $moduletype);
        }
        return response()->json(['success' => true, 'message' => 'Affiliation Has been created!']);
    }
    
    public function update(Request $request, $id) {
        $name = $request->name;
        if($name) {
            $exists = Affiliation::where('name', $name)->count();
        }
        if($exists >= 1){
            $success = false;
            return response()->json(['success' => $success, 'message' => 'Name is already used!']);
        }
        
        $namearabic = $request->name_arabic;
        if($namearabic) {
            $existsarabic = Affiliation::where('name', $namearabic)->count();
        }
        if($existsarabic >= 1){
            $success = false;
            return response()->json(['success' => $success, 'message' => 'Name Arabic is already used!']);
        }
        
        if (isset($request->restrictions)) {
            $restriction_data = AffiliationRestriction::where('affiliation_id', '=',$id)->get();
            $restriction_data->each->delete();
            
            foreach ($request->restrictions as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                     'affiliation_id' => $id,
                    'restriction_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'restriction_discount_type' => !empty($value['discount_type']) ? $value['discount_type']['value'] : null,
                ];
                
                if($value['brand_id'] && $value['type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand_id'], 'value'));
                }
                if($value['sub_tag_id'] && $value['type']['value'] == 3){
                    $data['sub_tag_id'] = implode(',', array_column($value['sub_tag_id'], 'value'));
                }
                if($value['product_id'] && $value['type']['value'] == 2){
                    $data['product_id'] = implode(',', array_column($value['product_id'], 'value'));
                }
                if($value['category_id'] && $value['type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category_id'], 'value'));
                }
                
                if($value['discount_type'] && $value['discount_type']['value'] == 0){
                    $data['discount_amount'] = isset($value['discount_percentage']) ? $value['discount_percentage'] : null;
                    $data['max_cap_amount'] = isset($value['max_cap_amount']) ? $value['max_cap_amount'] : null;
                }
                
                if($value['discount_type'] && $value['discount_type']['value'] == 1){
                    $data['discount_amount'] = isset($value['fixed_amount']) ? $value['fixed_amount'] : null;
                }
                
                AffiliationRestriction::create($data);
             }
        }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $id;
            $moduletype = 4;
            ConditionSetup_helper::UpdateConditionsetup($data,$ruleid, $moduletype);
        }
        
        $affiliation = Affiliation::whereId($id)->update([
            'rules_type' => isset($request->rules_type) ? $request->rules_type : null,
            'rules_id' => isset($request->rules_id) ? implode(',',$request->rules_id) : null,
            'coupon_id' => isset($request->coupon_id) ? implode(',',$request->coupon_id) : null,
            'free_gifts_id' => isset($request->free_gifts_id) ? implode(',',$request->free_gifts_id) : null,
            'gift_voucher_id' => isset($request->gift_voucher_id) ? implode(',',$request->gift_voucher_id) : null,
            'loyalty_id' => isset($request->loyalty_id) ? implode(',',$request->loyalty_id) : null,
            'disable_rules' => isset($request->disable_rules) ? $request->disable_rules : null,
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'slug_code' => isset($request->slug_code) ? $request->slug_code : null,
            'redirect_type' => isset($request->redirect_type) ? $request->redirect_type : null,
            'pages_id' => isset($request->pages_id) ? $request->pages_id['value'] : null,
            'brand_id' => !empty($request->brand_id) ? $request->brand_id['value'] : null,
            'product_id' => !empty($request->product_id) ? $request->product_id['value'] : null,
            'sub_tag_id' => !empty($request->sub_tag_id) ? $request->sub_tag_id['value'] : null,
            'category_id' => !empty($request->category_id) ? $request->category_id['value'] : null,
            'custom_link' => isset($request->custom_link) ? $request->custom_link : null,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'usage_limit' => isset($request->usage_limit) ? $request->usage_limit : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'specific_users_id' => isset($request->specific_users_id) ? $request->specific_users_id : null,
            'status' => isset($request->status) ? $request->status : 0,
            'disable_conditions' => isset($request->disable_conditions) ? $request->disable_conditions : null, 
        ]);
        
        return response()->json(['success' => true, 'message' => 'Affiliation Has been updated!']);
    }
}
