<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GiftCardSetting;
use App\Models\GiftCardSettingAmounts;
use App\Models\GiftCardSettingImages;
use App\Traits\CrudTrait;

class GiftCardSettingController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'gift_card_setting';
    protected $relationKey = 'gift_card_setting_id';


    public function model() {
        $data = ['limit' => -1, 'model' => GiftCardSetting::class, 'sort' => ['id','desc']];
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
        return ['giftCardAmonts' => 'giftCardAmounts:id,amount,gift_card_setting_id','giftCardImages' => 'giftCardImages.giftCardImageData:id,image'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['giftCardAmounts' => GiftCardSettingAmounts::get(), 'giftCardImages' => GiftCardSettingImages::get()];
    }
    
    public function store(Request $request) 
    {
        $success = false;
        $message = '';
        $setting = GiftCardSetting::first();
        if($setting) {
            if (isset($request->amounts)) {
                $settingAmounts = GiftCardSettingAmounts::where('gift_card_setting_id', '=',$setting->id)->get();
                $settingAmounts->each->delete();
                
                foreach ($request->amounts as $k => $value) {
                    $data = [
                        'gift_card_setting_id' => $setting->id,
                        'amount' => isset($value['amount']) ? $value['amount'] : null,
                    ];
                    
                    GiftCardSettingAmounts::create($data);
                }
            }
            
            if (isset($request->images)) {
                $settingAmounts = GiftCardSettingImages::where('gift_card_setting_id', '=',$setting->id)->get();
                $settingAmounts->each->delete();
                
                foreach ($request->images as $ke => $val) {
                    $data = [
                        'gift_card_setting_id' => $setting->id,
                        'image' => isset($val['image_id']) ? $val['image_id'] : null,
                    ];
                    
                    GiftCardSettingImages::create($data);
                }
            }
             
            $giftCardSetting = GiftCardSetting::whereId($setting->id)->update([
                'status' => isset($request->status) ? $request->status : 0,
            ]);
            $success = true;
        }   
        else {
            $giftCardSetting = GiftCardSetting::create([
                'status' => isset($request->status) ? $request->status : 0,
            ]);
            
            if (isset($request->amounts)) {
                foreach ($request->amounts as $k => $value) {
                    $data = [
                        'gift_card_setting_id' => $giftCardSetting->id,
                        'amount' => isset($value['amount']) ? $value['amount'] : null,
                    ];
                    
                    GiftCardSettingAmounts::create($data);
                }
            }
            
            if (isset($request->images)) {
                foreach ($request->images as $ek => $val) {
                    $data = [
                        'gift_card_setting_id' => $giftCardSetting->id,
                        'image' => isset($val['image_id']) ? $val['image_id'] : null,
                    ];
                    
                    GiftCardSettingImages::create($data);
                }
            }
            if($giftCardSetting) {
                $success = true;   
            } 
        }
        return response()->json(['success' => $success, 'message' => $setting ? 'Gift Card Setting Has been updated!' : 'Gift Card Setting Has been created!']);
    }
    
    //  public function update(Request $request, $id) {
    //     $success = false;
    //     $message = '';
    //     if (isset($request->amounts)) {
    //         $settingAmounts = GiftCardSettingAmounts::where('gift_card_setting_id', '=',$id)->get();
    //         $settingAmounts->each->delete();
            
    //         foreach ($request->amounts as $k => $value) {
    //             $data = [
    //                 'gift_card_setting_id' => $id,
    //                 'amount' => isset($value['amount']) ? $value['amount'] : null,
    //             ];
                
    //             GiftCardSettingAmounts::create($data);
    //         }
    //     }
        
    //     if (isset($request->images)) {
    //         $settingAmounts = GiftCardSettingImages::where('gift_card_setting_id', '=',$id)->get();
    //         $settingAmounts->each->delete();
            
    //         foreach ($request->images as $ke => $val) {
    //             $data = [
    //                 'gift_card_setting_id' => $id,
    //                 'image' => isset($val['image_id']) ? $val['image_id'] : null,
    //             ];
                
    //             GiftCardSettingImages::create($data);
    //         }
    //     }
         
    //     $giftCardSetting = GiftCardSetting::whereId($id)->update([
    //         'status' => isset($request->status) ? $request->status : 0,
    //     ]);
    //     $success = true;   
    //     return response()->json(['success' => $success, 'message' => 'Gift Card Setting Has been updated!']);
    //  }
     
    public function destroy($id)
    {
        $success = false;
        $giftAmounts = GiftCardSettingAmounts::where('gift_card_setting_id', '=',$id)->get();
        $giftAmounts->each->delete();
        
        $giftImages = GiftCardSettingImages::where('gift_card_setting_id', '=',$id)->get();
        $giftImages->each->delete();
        
        $setting = GiftCardSetting::findorFail($id);
        $setting->delete();
        $success = true;
        return response()->json(['success' => $success, 'message' =>'Gift Card Setting Has been deleted!']);
    }
    
    public function multidelete(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            
            $giftAmounts = GiftCardSettingAmounts::where('gift_card_setting_id', '=',$ids)->get();
            $giftAmounts->each->delete();
            
            $giftImages = GiftCardSettingImages::where('gift_card_setting_id', '=',$ids)->get();
            $giftImages->each->delete();
            
            $setting = GiftCardSetting::whereIn('id',$ids);
            $setting->each->delete();
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Selected Gift Card Settings Has been deleted!']);
            
    }
}
