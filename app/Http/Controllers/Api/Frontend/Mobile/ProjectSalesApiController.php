<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectSale;
use App\Models\ContactUs;
use App\Models\MobileSetting;
use App\Models\Newslatter;
use App\Models\States;
use Mail;

class ProjectSalesApiController extends Controller
{
    public function ProjectSaleData(Request $request) {
        $mobdata = MobileSetting::first();
        if($mobdata->project_sale_status == 1) {
            $data = MobileSetting::with('ImageEn:id,image', 'ImageAr:id,image')->select('id', 'project_sale_image', 'project_sale_image_arabic', 'project_sale_status')->first();
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
    
    public function AddProjectSale(Request $request) {
        $data = ProjectSale::create([
            'full_name' => isset($request->full_name) ? $request->full_name : null,
            'email_address' => isset($request->email_address) ? $request->email_address : null,
            'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
            'company_name' => isset($request->company_name) ? $request->company_name : null,
            'comment' => isset($request->comment) ? $request->comment : null,
            'city' => isset($request->city) ? $request->city : null
        ]);
        
        $citydata = States::where('id', $data->city)->first();
        
        $emailContent = "New Project Sale Request:\n";
        $emailContent .= "Full Name: {$data->full_name}\n";
        $emailContent .= "Email: {$data->email_address}\n";
        $emailContent .= "Phone Number: {$data->phone_number}\n";
        $emailContent .= "Company Name: {$data->company_name}\n";
        $emailContent .= "City: {$citydata->name}\n";
        $emailContent .= "Comment: {$data->comment}\n";
        
        // Send the email
        Mail::raw($emailContent, function($message) use ($data) {
            $message->from('sales@tamkeenstores.com.sa')
                    ->to('mohammed.saied@tamkeen-ksa.com')
                    ->subject('New Project Sales Request');
        });
        
        $response = [
            'success' => 'true',
            'message' => 'Project Sale Added Successfully!'
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function AddContactUs(Request $request) {
        $data = ContactUs::create([
            'full_name' => isset($request->full_name) ? $request->full_name : null,
            'email_address' => isset($request->email_address) ? $request->email_address : null,
            'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
            'reason' => isset($request->reason) ? $request->reason : null,
            'complain' => isset($request->complain) ? $request->complain : null,
        ]);
        
        $response = [
            'success' => 'true',
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
