<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoyaltyHistory;
use App\Traits\CrudTrait;

class LoyaltyHistoryApiCotroller extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'loyalty_histories';
    protected $relationKey = ' loyalty_histories_id';


    public function model() {
        $data = ['limit' => -1, 'model' => LoyaltyHistory::class, 'sort' => ['id','desc']];
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
        return [];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
    
    public function index(Request $request){
        $data = LoyaltyHistory::with(['UserDetail:id,first_name,last_name,phone_number', 'orderData:id,order_no,status' ,'orderData.ordersummary'=> function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            }])->orderBy('id', 'desc')->get();
            
        foreach($data as $da) {
            $ordata = $da->orderData;
            if($ordata) {
                $or =  $ordata->status;
                // print_r($or);die;
                
                if($or == 5 || $or == 6 || $or == 7) {
                    $da->status = 2;
                    $da->update();
                }
                
                if($or == 4) {
                    $da->status = 1;
                    $da->update();
                }   
            }
        
        }
        
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
}
