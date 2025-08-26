<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockAlert;
use Mail;
use App\Mail\PriceAlertEmail;
use App\Traits\CrudTrait;

class StockAlertApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'stock_alert';
    protected $relationKey = 'stock_alert_id';


    public function model() {
        $data = ['limit' => -1, 'model' => StockAlert::class, 'sort' => ['id','desc']];
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
        return ['productdata_id' => 'productData:id,name,sku', 'userdata_id' => 'UserData:id,email,phone_number'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
    
    public function sendEmail(Request $request) {
        $id = $request->id;
        $success = false;
        if($id) {
            $data = StockAlert::where('id', $id)->first();
            $userData = $data->UserData;
            Mail::to($userData->email)->send(new PriceAlertEmail($data));
            $success = true;
            $data->status = 0;
            $data->update();
        }
        return response()->json(['success' => $success, 'message' => 'Email Sended Successfully!!']);
        
    }
    
    
    public function sendMultiEmail(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $data = StockAlert::whereIn('id',$ids)->get();
            foreach($data as $emaildata) {
                $userData = $emaildata->UserData;
                Mail::to($userData->email)->send(new PriceAlertEmail($emaildata));
                $emaildata->status = 0;
                $emaildata->update();
            }
            $success = true;
        }
        
        return response()->json(['success' => $success, 'message' => 'Email Sended Successfully!']);
        
    }
}
