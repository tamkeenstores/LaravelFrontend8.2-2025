<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserComissions;
use App\Traits\CrudTrait;

class UserCommisionApiCotroller extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'user_comissions';
    protected $relationKey = ' user_comissions_id';


    public function model() {
        $data = ['limit' => -1, 'model' => UserComissions::class, 'sort' => ['id','desc']];
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
        $data = UserComissions::with(['UserDetail:id,first_name,last_name,phone_number','RulesData:id,name', 'CouponData:id,coupon_code',
        'AffiliationData:id', 'AffiliationData.restrictions:id,affiliation_id,restriction_type', 'orderData:id,order_no,status' ,'orderData.ordersummary'=> function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            }])->orderBy('id', 'desc')->get();
        foreach($data as $da) {
            $ordata = $da->orderData;
            if($ordata) {
                $or =  $ordata->status;
            }
            else {
                $or = '';
            }
            if($or == 5 || $or == 6 || $or == 7) {
                $da->status = 2;
                $da->update();
            }
            
            if($or == 4) {
                $da->status = 1;
                $da->update();
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
