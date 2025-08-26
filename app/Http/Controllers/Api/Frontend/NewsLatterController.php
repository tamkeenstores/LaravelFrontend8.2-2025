<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Newslatter;
use Mail;

class NewsLatterController extends Controller
{
    public function submitNewslatter(Request $request) {
        $success = false;
        $message = '';
        if($request->email != null) {
            $newslater = Newslatter::where('email', $request->email)->first();
            if(!$newslater) {
                $data = Newslatter::create([
                    'email' => $request->email,
                    'status' => 0
                ]);
                
                Mail::send('email.newslatter-template', ['newslatter' => $data], function ($message) use ($data, $request) {
                    $message->to($request->email)
                    ->subject('Newslatter activation');
                });
                
                $success = true;
            }
            else {
                $success = false;
                $message = "Error! You're already subscribed";
            }
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
    
    public function updateNewslatter($id) {
        $success = false;
        if($id) {
            $newslater = Newslatter::where('id', $id)->first();
            if($newslater) {
                $newslater->status = 1;
                $newslater->update();
                
                $success = true;
            }
        }
        
        $response = [
            'success' => $success
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
