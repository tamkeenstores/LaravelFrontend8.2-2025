<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotifyProduct;
use Mail;
use App\Mail\NotifyproductEmail;
use App\Traits\CrudTrait;

class NotifyProductApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'notify_product';
    protected $relationKey = 'notify_product_id';


    public function model() {
        $data = ['limit' => -1, 'model' => NotifyProduct::class, 'sort' => ['id','desc']];
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
        return ['productdata_id' => 'productData:id,name,sku'];
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
            $data = NotifyProduct::where('id', $id)->first();
            // print_r($data->email);die();
            Mail::to($data->email)->send(new NotifyproductEmail($data));
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Email Sended Successfully!!']);
        
    }
    
    
    public function sendMultiEmail(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $data = NotifyProduct::whereIn('id',$ids)->get();
            foreach($data as $emaildata) {
                Mail::to($emaildata->email)->send(new NotifyproductEmail($emaildata));   
            }
            $success = true;
        }
        
        return response()->json(['success' => $success, 'message' => 'Email Sended Successfully!']);
        
    }
}
