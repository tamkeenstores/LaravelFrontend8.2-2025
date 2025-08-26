<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoyaltySetting;
use App\Models\LoyaltySettingDesktop;
use App\Models\LoyaltySettingMobile;
use App\Traits\CrudTrait;

class LoyaltySettingApiController extends Controller
{
    // use CrudTrait;
    // protected $viewVariable = 'loyalty_setting';
    // protected $relationKey = 'loyalty_setting_id';


    // public function model() {
    //     $data = ['limit' => -1, 'model' => LoyaltySetting::class, 'sort' => ['id','asc']];
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
    //     return ['settingdesktop_id' => 'settingdesktop','settingmobile_id' => 'settingmobile'];
    // }

    // public function arrayData(){
    //     return [];
    //     // data in coulumn is 0, data in json is 1
    // }

    // public function models()
    // {
    //     return [];
    // }
    
    public function index(Request $request) {
        
        $data = LoyaltySetting::with('settingdesktop','settingmobile')->first();
        
        return response()->json(['data' => $data]);
    }
    
    public function store(Request $request) {
        $settingData = LoyaltySetting::with('settingdesktop','settingmobile')->first();
        if($settingData) {
            if (isset($request->settingdesktop)) {
                if(isset($settingData->id)) {
                    $settingdesktop = LoyaltySettingDesktop::where('loyaltysetting_id', '=',$settingData->id)->get();
                    $settingdesktop->each->delete();   
                }
                foreach ($request->settingdesktop as $k => $value) {
                $data = [
                     'loyaltysetting_id' => $settingData->id,
                     'min_order_value' => isset($value['min_order_value']) ? $value['min_order_value'] : null,
                     'max_order_value' => isset($value['max_order_value']) ? $value['max_order_value'] : null,
                     'reward_points' => isset($value['reward_points']) ? $value['reward_points'] : null,
                ];
                LoyaltySettingDesktop::create($data);
                }
            }
            if (isset($request->settingmobile)) {
                if(isset($settingData->id)) {
                    $settingmobile = LoyaltySettingMobile::where('loyaltysetting_id', '=',$settingData->id)->get();
                    $settingmobile->each->delete();   
                }
                foreach ($request->settingmobile as $k => $val) {
                    $mobiledata = [
                         'loyaltysetting_id' => $settingData->id,
                         'min_order_value' => isset($val['min_order_value']) ? $val['min_order_value'] : null,
                         'fixed_amount' => isset($val['fixed_amount']) ? $val['fixed_amount'] : null,
                         'reward_points' => isset($val['reward_points']) ? $val['reward_points'] : null,
                    ];
                    LoyaltySettingMobile::create($mobiledata);
                }
            }
            
            $Setting = LoyaltySetting::whereId($settingData->id)->update([
                'notes_desktop' => isset($request->notes_desktop) ? $request->notes_desktop : null,
                'notes_mobile' => isset($request->notes_mobile) ? $request->notes_mobile : null,
                'extra_reward_newuser' => isset($request->extra_reward_newuser) ? $request->extra_reward_newuser : 0,
                'reward_newuser_amount' => isset($request->reward_newuser_amount) ? $request->reward_newuser_amount : null,
                'extra_reward_freeshipping' => isset($request->extra_reward_freeshipping) ? $request->extra_reward_freeshipping : null,
                'reward_freeshipping_amount' => isset($request->reward_freeshipping_amount) ? $request->reward_freeshipping_amount : null,
                'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
                'min_order_value' => isset($request->min_order_value) ? $request->min_order_value : null,
                'reward_points' => isset($request->reward_points) ? $request->reward_points : null,
                'fixed_amount_mobile' => isset($request->fixed_amount_mobile) ? $request->fixed_amount_mobile : null,
                'fixed_amount_website' => isset($request->fixed_amount_website) ? $request->fixed_amount_website : null,
                'status' => isset($request->status) ? $request->status : 0,
                'expire_days' => isset($request->expire_days) ? $request->expire_days : null,
            ]);
        }
        else {
            $Setting = LoyaltySetting::create([
                'notes_desktop' => isset($request->notes_desktop) ? $request->notes_desktop : null,
                'notes_mobile' => isset($request->notes_mobile) ? $request->notes_mobile : null,
                'extra_reward_newuser' => isset($request->extra_reward_newuser) ? $request->extra_reward_newuser : 0,
                'reward_newuser_amount' => isset($request->reward_newuser_amount) ? $request->reward_newuser_amount : null,
                'extra_reward_freeshipping' => isset($request->extra_reward_freeshipping) ? $request->extra_reward_freeshipping : null,
                'reward_freeshipping_amount' => isset($request->reward_freeshipping_amount) ? $request->reward_freeshipping_amount : null,
                'discount_devices' => isset($request->discount_devices) ? implode(',',$request->discount_devices) : null,
                'min_order_value' => isset($request->min_order_value) ? $request->min_order_value : null,
                'reward_points' => isset($request->reward_points) ? $request->reward_points : null,
                'fixed_amount_mobile' => isset($request->fixed_amount_mobile) ? $request->fixed_amount_mobile : null,
                'fixed_amount_website' => isset($request->fixed_amount_website) ? $request->fixed_amount_website : null,
                'status' => isset($request->status) ? $request->status : 0,
                'expire_days' => isset($request->expire_days) ? $request->expire_days : null,
            ]);
            if (isset($request->settingdesktop)) {
                foreach ($request->settingdesktop as $k => $value) {
                    $data = [
                         'loyaltysetting_id' => $Setting->id,
                         'min_order_value' => isset($value['min_order_value']) ? $value['min_order_value'] : null,
                         'max_order_value' => isset($value['max_order_value']) ? $value['max_order_value'] : null,
                         'reward_points' => isset($value['reward_points']) ? $value['reward_points'] : null,
                    ];
                    LoyaltySettingDesktop::create($data);
                }
            }
            if (isset($request->settingmobile)) {
                foreach ($request->settingmobile as $k => $val) {
                    $mobiledata = [
                         'loyaltysetting_id' => $Setting->id,
                         'min_order_value' => isset($val['min_order_value']) ? $val['min_order_value'] : null,
                         'fixed_amount' => isset($val['fixed_amount']) ? $val['fixed_amount'] : null,
                         'reward_points' => isset($val['reward_points']) ? $val['reward_points'] : null,
                    ];
                    LoyaltySettingMobile::create($mobiledata);
                }
            }
        }
        return response()->json(['success' => true, 'message' => 'Loyalty Setting Has been updated!']);
    }
}
