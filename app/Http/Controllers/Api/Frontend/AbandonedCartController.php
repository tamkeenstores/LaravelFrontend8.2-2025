<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbandonedCart;

class AbandonedCartController extends Controller
{
    public function abandonedCartStore(Request $request) {
        $success = false;
        $message = 'Error! Something went wrong.';
        $userid = $request->user_id;
        $data = json_encode($request->cart_data, true);
        if($userid && $userid != null && $data != '') {
            $check = AbandonedCart::where('user_id', $userid)->first();
            if($check) {
                $check->delete();
                $cart = AbandonedCart::create([
                    'user_id' => $userid,
                    'cartdata' => $data
                ]);
            }
            else {
                $cart = AbandonedCart::create([
                    'user_id' => $userid,
                    'cartdata' => $data
                ]);
            }
            $success = true;
            $message = 'Success! Abandoned cart created successfully.';
        }
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    // get abandoned cart
    public function abandonedCartGet($slug) {
        $success = false;
        $message = 'Error! Something went wrong.';
        $cart = AbandonedCart::where('id', $slug)->first();
        if($cart) {
            $success = true;
            $message = 'Success! Abandoned cart get successfully.';
        }

        $response = [
            'cart' => $cart,
            'success' => $success,
            'message' => $message
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
