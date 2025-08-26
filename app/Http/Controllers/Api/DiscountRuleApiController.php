<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rules;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Models\RulesRestriction;
use App\Models\BogoDiscount;
use App\Models\BulkDiscount;
use App\Models\RulesConditions;
use App\Traits\CrudTrait;
use App\Helper\ConditionSetup_helper;

class DiscountRuleApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'discount_rules';
    protected $relationKey = 'discount_rules_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Rules::class, 'sort' => ['id','asc']];
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
        return ['restrictions_id' => 'restrictions', 'conditions_id' => 'conditions', 'bogodiscount_id' => 'bogodiscount', 'bulkdiscount_id' => 'bulkdiscount'];
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
         ];
    }
    
    public function index(Request $request){
        $data = Rules::select('id', 'name', 'discount_type', 'start_date', 'end_date', 'status'
        ,'notes')->orderBy('id', 'desc')->get();
        
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
        // print_r($request->discountRuleData['value']);die();
        //  print_r($request->all());die();
        // print_r($request->cartdiscountType['value']);die();
        
        $DiscountRules = Rules::create([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'notes' => isset($request->note) ? $request->note : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'usage_limit' => isset($request->usage_limit) ? $request->usage_limit : null,
            'discount_type' => isset($request->discountRuleData['value']) ? $request->discountRuleData['value'] : null,
            'bogo_discount_type' => isset($request->bogodiscountType['value']) ? $request->bogodiscountType['value'] : null,
            'bogo_status' => isset($request->bogoadd) ? $request->bogoadd : null,
            'cart_discount_depend' => isset($request->cartdiscountType['value']) ? $request->cartdiscountType['value'] : null,
            'cart_fixed_amount' => isset($request->discount) ? $request->discount : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : 0,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'cart_maxcap_amount' => isset($request->cart_capamount) ? $request->cart_capamount : null,
        ]);
        
         if (isset($request->filterdiscountdata)) {
             foreach ($request->filterdiscountdata as $k => $value) {
                //   print_r($value['list']['value']);
                $data = [
                     'rule_id' => $DiscountRules->id,
                    'restriction_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'select_include_exclude' => isset($value['list']['value']) ? $value['list']['value'] : 0,
                ];
        
                if($value['brand'] && $value['type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand'], 'value'));
                }
                if($value['tag'] && $value['type']['value'] == 2){
                    $data['sub_tag_id'] = implode(',', array_column($value['tag'], 'value'));
                }
                if($value['product'] && $value['type']['value'] == 3){
                    $data['product_id'] = implode(',', array_column($value['product'], 'value'));
                }
                if($value['category'] && $value['type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category'], 'value'));
                }
                RulesRestriction::create($data);
             }
         }
        
        if (isset($request->discountRuleData) && $request->discountRuleData['value'] == 1) {
             if(isset($request->bogodiscountType)) {
                  foreach ($request->bogodata as $key => $val) {
                      
                      $discountbogodata = [
                               'rule_id' => $DiscountRules->id,
                               'min_quantity' => isset($val['min']) ? $val['min'] : null,
                               'max_quantity' => isset($val['max']) ? $val['max'] : null,
                               'quantity' => isset($val['qty']) ? $val['qty'] : null,
                               'max_cap_amount' => isset($val['bogo_capamount']) ? $val['bogo_capamount'] : null,
                    ];
                    
                    if($val['product']){
                        $discountbogodata['products_id'] = implode(',', array_column($val['product'], 'value'));
                    }
                    if(isset($val['type']) && $val['type']['value'] == 1){
                        $discountbogodata['discount_depend'] = 1;
                        $discountbogodata['recursive'] = $val['recursive'] == true ? 1 : 0;
                    }
                    if(isset($val['type']) && $val['type']['value'] == 2){
                        $discountbogodata['discount_depend'] = 2;
                        $discountbogodata['fixed_amount'] = isset($val['discount']) ? $val['discount'] : null;
                        $discountbogodata['recursive'] = $val['recursive'] == true ? 1 : 0;
                    }
                    if(isset($val['type']) && $val['type']['value'] == 3){
                        $discountbogodata['discount_depend'] = 3;
                        $discountbogodata['fixed_amount'] = isset($val['discount']) ? $val['discount'] : null;
                        $discountbogodata['recursive'] = $val['recursive'] == true ? 1 : 0;
                    }
                    BogoDiscount::create($discountbogodata);
                 };
            }
        }
        
        // if (isset($request->discountRuleData) && $request->discountRuleData['value'] == 2) {
        //     if(isset($request->cartdiscountType) && $request->cartdiscountType['value'] == 1) {
        //         $discountcartdata = [
        //             'rule_id' => $DiscountRules->id,
        //             'discount_type' => 2,
        //             'discount_depend' => 1,
        //             'fixed_amount' => isset($request->discount) ? $request->discount : null,
        //         ];
        //     }
            
        //     if(isset($request->cartdiscountType) && $request->cartdiscountType['value'] == 2) {
        //         $discountcartdata = [
        //             'rule_id' => $DiscountRules->id,
        //             'discount_type' => 2,
        //             'discount_depend' => 2,
        //             'fixed_amount' => isset($request->discount) ? $request->discount : null,
        //         ];
        //     }
        //     RulesDiscount::create($discountcartdata);
        // }
        
        if (isset($request->discountRuleData) && $request->discountRuleData['value'] == 4) {
            if (isset($request->bundledata)) {
              foreach ($request->bundledata as $ky => $valu) {
                
                $bulkdata = [
                        'rule_id' => $DiscountRules->id,
                    ];
                
                if(isset($valu['type']['value']) && $valu['type']['value'] == 1){
                         $bulkdata['min_quantity'] = isset($valu['min']) ? $valu['min'] : null;
                         $bulkdata['max_quantity'] = isset($valu['max']) ? $valu['max'] : null;
                         $bulkdata['label'] = isset($valu['label']) ? $valu['label'] : null;
                         $bulkdata['discount_amount'] = isset($valu['discount']) ? $valu['discount'] : null;
                         $bulkdata['bulk_discount_type'] = 1;
                }
        
                 if(isset($valu['type']['value']) && $valu['type']['value'] == 2){
                         $bulkdata['min_quantity'] = isset($valu['min']) ? $valu['min'] : null;
                         $bulkdata['max_quantity'] = isset($valu['max']) ? $valu['max'] : null;
                         $bulkdata['label'] = isset($valu['label']) ? $valu['label'] : null;
                         $bulkdata['discount_amount'] = isset($valu['discount']) ? $valu['discount'] : null;
                         $bulkdata['max_cap_amount'] = isset($valu['bulk_capamount']) ? $valu['bulk_capamount'] : null;
                         $bulkdata['bulk_discount_type'] = 2;
                }
                    
                  if(isset($valu['type']['value']) && $valu['type']['value'] == 3){
                         $bulkdata['min_quantity'] = isset($valu['min']) ? $valu['min'] : null;
                         $bulkdata['max_quantity'] = isset($valu['max']) ? $valu['max'] : null;
                         $bulkdata['label'] = isset($valu['label']) ? $valu['label'] : null;
                         $bulkdata['discount_amount'] = isset($valu['discount']) ? $valu['discount'] : null;
                         $bulkdata['bulk_discount_type'] = 3;
                }
              BulkDiscount::create($bulkdata);
              }
            }
        }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $DiscountRules->id;
            $moduletype = 0;
            ConditionSetup_helper::CreateConditionsetup($data,$ruleid, $moduletype);
        }
        return response()->json(['success' => true, 'message' => 'Discount Rule Has been created!']);
    }
    
    public function update(Request $request, $id) {
        
        if (isset($request->filterdiscountdata)) {
            $restriction_data = RulesRestriction::where('rule_id', '=',$id)->get();
            $restriction_data->each->delete();
            
            foreach ($request->filterdiscountdata as $k => $value) {
                $data = [
                    'rule_id' => $id,
                    'restriction_type' => isset($value['type']['value']) ? $value['type']['value'] : 0,
                    'select_include_exclude' => isset($value['list']['value']) ? $value['list']['value'] : 0,
                ];
        
                if($value['brand'] && $value['type']['value'] == 1){
                    $data['brand_id'] = implode(',', array_column($value['brand'], 'value'));
                }
                if($value['tag'] && $value['type']['value'] == 2){
                    $data['sub_tag_id'] = implode(',', array_column($value['tag'], 'value'));
                }
                if($value['product'] && $value['type']['value'] == 3){
                    $data['product_id'] = implode(',', array_column($value['product'], 'value'));
                }
                if($value['category'] && $value['type']['value'] == 4){
                    $data['category_id'] = implode(',', array_column($value['category'], 'value'));
                }
                RulesRestriction::create($data);
            }
        }
        
        if (isset($request->discountRuleData)) {
            $bogo_disc_data = BogoDiscount::where('rule_id', '=',$id)->get();
            $bogo_disc_data->each->delete();
            $bulk_disc_data = BulkDiscount::where('rule_id', '=',$id)->get();
            $bulk_disc_data->each->delete();
            
            if($request->discountRuleData['value'] == 1){
                if(isset($request->bogodiscountType)) {
                  foreach ($request->bogodata as $key => $val) {
                      
                      $discountbogodata = [
                               'rule_id' => $id,
                               'min_quantity' => isset($val['min']) ? $val['min'] : null,
                               'max_quantity' => isset($val['max']) ? $val['max'] : null,
                               'quantity' => isset($val['qty']) ? $val['qty'] : null,
                               'max_cap_amount' => isset($val['bogo_capamount']) ? $val['bogo_capamount'] : null,
                    ];
                    
                    if($val['product']){
                        $discountbogodata['products_id'] = implode(',', array_column($val['product'], 'value'));
                    }
                    if(isset($val['type']) && $val['type']['value'] == 1){
                        $discountbogodata['discount_depend'] = 1;
                        $discountbogodata['recursive'] = $val['recursive'] == true ? 1 : 0;
                    }
                    if(isset($val['type']) && $val['type']['value'] == 2){
                        $discountbogodata['discount_depend'] = 2;
                        $discountbogodata['fixed_amount'] = isset($val['discount']) ? $val['discount'] : null;
                        $discountbogodata['recursive'] = $val['recursive'] == true ? 1 : 0;
                    }
                    if(isset($val['type']) && $val['type']['value'] == 3){
                        $discountbogodata['discount_depend'] = 3;
                        $discountbogodata['fixed_amount'] = isset($val['discount']) ? $val['discount'] : null;
                        $discountbogodata['recursive'] = $val['recursive'] == true ? 1 : 0;
                    }
                    BogoDiscount::create($discountbogodata);
                 };
              }
                
            }
            
            
            if($request->discountRuleData['value'] == 4) {
                if (isset($request->bundledata)) {
                  foreach ($request->bundledata as $ky => $valu) {
                
                $bulkdata = [
                        'rule_id' => $id,
                    ];
                
                if(isset($valu['type']['value']) && $valu['type']['value'] == 1){
                         $bulkdata['min_quantity'] = isset($valu['min']) ? $valu['min'] : null;
                         $bulkdata['max_quantity'] = isset($valu['max']) ? $valu['max'] : null;
                         $bulkdata['label'] = isset($valu['label']) ? $valu['label'] : null;
                         $bulkdata['discount_amount'] = isset($valu['discount']) ? $valu['discount'] : null;
                         $bulkdata['bulk_discount_type'] = 1;
                }
        
                 if(isset($valu['type']['value']) && $valu['type']['value'] == 2){
                         $bulkdata['min_quantity'] = isset($valu['min']) ? $valu['min'] : null;
                         $bulkdata['max_quantity'] = isset($valu['max']) ? $valu['max'] : null;
                         $bulkdata['label'] = isset($valu['label']) ? $valu['label'] : null;
                         $bulkdata['discount_amount'] = isset($valu['discount']) ? $valu['discount'] : null;
                         $bulkdata['max_cap_amount'] = isset($valu['bulk_capamount']) ? $valu['bulk_capamount'] : null;
                         $bulkdata['bulk_discount_type'] = 2;
                }
                    
                  if(isset($valu['type']['value']) && $valu['type']['value'] == 3){
                         $bulkdata['min_quantity'] = isset($valu['min']) ? $valu['min'] : null;
                         $bulkdata['max_quantity'] = isset($valu['max']) ? $valu['max'] : null;
                         $bulkdata['label'] = isset($valu['label']) ? $valu['label'] : null;
                         $bulkdata['discount_amount'] = isset($valu['discount']) ? $valu['discount'] : null;
                         $bulkdata['bulk_discount_type'] = 3;
                }
              BulkDiscount::create($bulkdata);
              }
               }
            }
        }
        
        if (isset($request->conditiondata)) {
            $data = $request->all();
            $ruleid = $id;
            $moduletype = 0;
            ConditionSetup_helper::UpdateConditionsetup($data,$ruleid, $moduletype);
        }
        
        
        $DiscountRules = Rules::whereId($id)->update([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'notes' => isset($request->note) ? $request->note : null,
            'start_date' => isset($request->start_date) ? $request->start_date : null,
            'end_date' => isset($request->end_date) ? $request->end_date : null,
            'status' => isset($request->status) ? $request->status : 0,
            'usage_limit' => isset($request->usage_limit) ? $request->usage_limit : null,
            'discount_type' => isset($request->discountRuleData['value']) ? $request->discountRuleData['value'] : null,
            'bogo_discount_type' => isset($request->bogodiscountType['value']) ? $request->bogodiscountType['value'] : null,
            'bogo_status' => isset($request->bogoadd) ? $request->bogoadd : null,
            'cart_discount_depend' => isset($request->cartdiscountType['value']) ? $request->cartdiscountType['value'] : null,
            'cart_fixed_amount' => isset($request->discount) ? $request->discount : null,
            'condition_match_status' => isset($request->conditiontype) ? $request->conditiontype : 0,
            'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
            'cart_maxcap_amount' => isset($request->cart_capamount) ? $request->cart_capamount : null,
            
        ]);
        
        return response()->json(['success' => true, 'message' => 'Discount Rule Has been updated!']);
    }
}
