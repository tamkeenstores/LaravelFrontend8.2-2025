<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Models\States;
use App\Models\Area;
use App\Models\InternalTicket;
use App\Models\InternalTicketHistory;
use App\Models\User;
use App\Models\shippingAddress;
use App\Models\TicketTag;
use App\Models\MaintenanceCenter;
use App\Models\StoreLocator;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function storeTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }
        
        $phone = $request->phone_number;
        $checkphone = User::where('phone_number',$phone)->first();
        if(!$checkphone) {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
            ]);
            if($user) {
                 shippingAddress::create([
                    'country_id' => 171,
                    'customer_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone_number' => $request->phone_number,
                    'address' => $request->address,
                    'state_id' => $request->city,
                    'area_id' => $request->area,
                    'make_default' => 1
                ]);
            }
            $checkphone = $user;   
        }else{
            $shippingAddressupdated = shippingAddress::where('customer_id', $checkphone->id)->where('make_default', 1)->update(['make_default'=> 0]);
            shippingAddress::create([
                'country_id' => 171,
                'customer_id' => $checkphone->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'state_id' => $request->city,
                'area_id' => $request->area,
                'make_default' => 1
            ]);
        }

        $customerCity = $request->city;
        $branchData = MaintenanceCenter::
        whereHas('multiCityData', function ($query) use ($customerCity) {
            return $query->where('city_id', $customerCity);
        })
        ->where('status', 1)
        ->first();

        $success = false;
        $ticket = InternalTicket::create([
            'title' => $request->title != null ? $request->title :  'صياة الشكاوي واتساب',
            'customer_id' => $checkphone->id,
            'assignee'=> 189359,
            'assignee_type' =>2,
            'type' => 25,
            'value'=> 0,
            'status'=> 0,
            'urgency'=> 2,
            'service_no' => $request->invoice_number,
            'details' => $request->complain,
            'input_channel' => 10,
            'department' => 1,
            'ticket_type' => 1,
            'branch' => $branchData ? $branchData->id : null,
            'purchased_from' => $request->purchasing_channel,
            'requester_category' => $request->requester_category,
            'device_category' => $request->device_category,
            'device_model' => isset($request->device_model_status) ? null : $request->device_model,
            'device_model_status' => isset($request->device_model_status) ? 1 : 0,
            'requestor_showroom' => $request->showroom,
            'requestor_warehouse' => $request->warehouse,
        ]);

        if($ticket){
            $success = true;
            $id = $ticket->id;
            $con = (5 - strlen($ticket->id));
            $ordersno = 'SS';
            for ($i=0; $i < $con; $i++) { 
                $ordersno .= '0';
            }
            $ordersno .= $ticket->id;
            $ticket->ticket_no = $ordersno;
            $ticket->save();
        }

        TicketTag::create([
            'ticket_id' => $ticket->id,
            'tag_id' => 8
        ]);

        InternalTicketHistory::create([
            'comment' => 'Ticket Create',
            'comment_arabic' => 'إنشاء التذكة',
            'user_id' => $checkphone->id,
            'ticket_id' => $ticket->id,
            'status' => 0
        ]);
        $customer_name = ($checkphone->first_name ?? '') . ' ' . ($checkphone->last_name ?? '');
        
        $response = [
            'success' => $success,
            'message' => 'Ticket has been created, successfully!',
            'customer_name' => $customer_name
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);

    }
    public function ticketIndex(){
        $ticket = InternalTicket::with('frontTicketProducts','frontTicketCategory','customerData.shippingAddressData.areaData')->where('id',5455)->get();
        return $ticket;
    }
    
    public function ticketUserCheck(Request $request){
        $success = false;
        $brandids = [42,22,23];
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => $success,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }
        $data = false;
        $phone = $request->phone_number;
        $checkphone = User::where('phone_number',$phone)->first(['id','first_name','last_name','email','phone_number']);
        // with('shippingAddressDataDefault.stateData')->
        
        if($request->lang == 'en'){
            $categories = Productcategory::where('status',1)->where('menu',1)->get(['id as value', 'name as label']);
            $products = Product::whereIn('brands',$brandids)->where('status',1)->get(['id as value', 'sku as label','brands as brands']);
            $cities = States::where('country_id','191')->where('status',1)->get(['id as value', 'name as label']);
            $areas = Area::where('status',1)->get(['id as value', 'name as label', 'city as city_id']);
            $showrooms = StoreLocator::where('status',1)->get(['id as value', 'name as label']);
            $warehouse = Warehouse::where('status',1)->get(['id as value', 'name as label',]);
        }else{
            $categories = Productcategory::where('status',1)->where('menu',1)->get(['id as value', 'name_arabic as label']);
            $products = Product::whereIn('brands',$brandids)->where('status',1)->get(['id as value', 'sku as label','brands as brands']);
            $cities = States::where('country_id','191')->where('status',1)->get(['id as value','name_arabic as label']);
            $areas = Area::where('status',1)->get(['id as value', 'name_arabic as label','city as city_id']);
            $showrooms = StoreLocator::where('status',1)->get(['id as value', 'name_arabic as label']);
            $warehouse = Warehouse::where('status',1)->get(['id as value', 'name_arabic as label']);
        }
        
        
        if($checkphone){
            $data = $checkphone;
            $success = true;
        }
            
        $response = ['success'=>$success,'data' => $data,'categories' => $categories,'products' => $products,'cities' => $cities,'areas' => $areas , 'showrooms' => $showrooms , 'warehouses' => $warehouse];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getAllBrands(Request $request){
        $success = false;
        if($request->lang == 'en'){
            $data = Brand::select(['id as value', 'name as label'])->where('status',1)->get();
        }else{
            $data = Brand::select(['id as value', 'name as label'])->where('status',1)->get();
        }
        if($data)
            $success = true;
            
        $response = [
            'success' => $success,
            'data' => $data,
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
