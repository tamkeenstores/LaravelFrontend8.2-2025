<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletSetting;
use App\Traits\CrudTrait;

class WalletSettingApiController extends Controller
{
    public function index(Request $request) {
        
        $data = WalletSetting::first();
        return response()->json(['data' => $data]);
    }
    
    public function store(Request $request) {
        $success = false;
        $settingData = WalletSetting::first();
        if($settingData) {
            $Setting = WalletSetting::whereId($settingData->id)->update([
                'new_user' => isset($request->new_user) ? $request->new_user : 0,
                'new_user_amount' => isset($request->new_user_amount) ? $request->new_user_amount : null,
                'new_user_device' => count($request->new_user_device) >= 1 ? implode(',', $request->new_user_device) : null,
                
                'all_user' => isset($request->all_user) ? $request->all_user : 0,
                'all_user_amount' => isset($request->all_user_amount) ? $request->all_user_amount : null,
                'all_user_device' => count($request->all_user_device) >= 1 ? implode(',', $request->all_user_device) : null,
                'status' => isset($request->status) ? $request->status : 0,
            ]);
            $success = true;
        }
        else {
            $Setting = WalletSetting::create([
                'new_user' => isset($request->new_user) ? $request->new_user : 0,
                'new_user_amount' => isset($request->new_user_amount) ? $request->new_user_amount : null,
                'new_user_device' => count($request->new_user_device) >= 1 ? implode(',', $request->new_user_device) : null,
                
                'all_user' => isset($request->all_user) ? $request->all_user : 0,
                'all_user_amount' => isset($request->all_user_amount) ? $request->all_user_amount : null,
                'all_user_device' => count($request->all_user_device) >= 1 ? implode(',', $request->all_user_device) : null,
                'status' => isset($request->status) ? $request->status : 0,
            ]);
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Wallet Setting Has been updated!']);
    }
}
