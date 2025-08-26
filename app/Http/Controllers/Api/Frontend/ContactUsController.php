<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;

class ContactUsController extends Controller
{
    public function StoreContactUs (Request $request) {
        $success = false;
        $data = ContactUs::create([
            'full_name' => isset($request->full_name) ? $request->full_name : null,
            'email_address' => isset($request->email) ? $request->email : null,
            'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'reason' => isset($request->reason) ? $request->reason : null,
            'complain' => isset($request->complain) ? $request->complain : null,
        ]);
        $success = true;
        
        $response = [
            'success' => $success,
            'message' => 'Contact Us Added Successfully!'
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
