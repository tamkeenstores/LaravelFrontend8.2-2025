<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbandonedCart;
use App\Traits\CrudTrait;
use Mail;
use App\Helper\NotificationHelper;

class AbandonedCartApiController extends Controller
{

    public function abandonedCartSend(Request $request) {
        $success = false;
        $ids = $request->id;
        $response = '';
        $data = AbandonedCart::with('userData')->whereIn('id', $ids)->get();
        foreach($data as $cart) {
            if($request->type == 1) {
                Mail::send('email.abandoned-template', ['cartdata' => $cart], function ($message) use ($cart) {
                    $message->to($cart->userData->email)
                    ->subject('Cart Abandoned');
                });
                
                $cart->firstemail = $cart->firstemail + 1;
                $cart->update();

                $success = true;
            }
            else if($request->type == 2) {
                $lang = $cart->userData->lang != null ? $cart->userData->lang : 'en';
                $response = NotificationHelper::whatsappmessageContent('+966' . $cart->userData->phone_number,'cart_abandonment',$lang);
                $success = true;
            }
        }

        $responsee = [
            'success' => $success,
            'response' => $response
        ];
        $responsejson=json_encode($responsee);
        $dataa=gzencode($responsejson,9);
        return response($dataa)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($dataa),
            'Content-Encoding' => 'gzip'
        ]);
    }


    use CrudTrait;
    protected $viewVariable = 'shoppingcart_nextjs';
    protected $relationKey = 'shoppingcart_nextjs_id';


    public function model() {
        $data = ['limit' => -1, 'model' => AbandonedCart::class, 'sort' => ['id','desc']];
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
        return ['user_id' => 'userData', 'addressdata' => 'userData.shippingAddressDataDefault.stateData'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return [];
    }
    
}
