<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Order;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\InternalOrder;
use App\Models\InternalOrderDetails;
use App\Models\InternalTicket;
use App\Models\InternalTicketComments;
use App\Models\StoreLocator;
use App\Models\States;
use App\Models\InternalTicketHistory;
use App\Models\TicketTag;
use App\Models\InternalTicketOrderDetails;
use App\Models\Warehouse;
use App\Models\MaintenanceCenter;
use App\Models\Section;
use App\Models\SLA;
use App\Models\shippingAddress;
use App\Models\InternalTicketMedia;
use App\Traits\CrudTrait;
use Illuminate\Support\Facades\DB;

use App\Models\InputChannel;
use App\Models\companyTags;
use App\Models\companyTypes;

class InternalTicketApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'internal_ticket';
    protected $relationKey = 'internal_ticket_id';
    
    public function model() {
        $data = ['limit' => -1, 'model' => InternalTicket::class, 'sort' => ['id','desc']];
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
        return [
            'department_data' => 'departmentData',
            'section_data' => 'sectionData',
            'customer_data' => 'customerData',
            'order_data' => 'orderData',
            'internal_order_data' => 'internalOrderData.internalOrderDetail',
            'internal_ticket_order_details' => 'internalTicketOrderDetailData.ticketOrderDetailsData',
            'internal_ticket_comments' => 'internalTicketCommentsData.userData',
            'internal_ticket_history' => 'internalTicketHistoryData.userData',
            'media_data' => 'ticketMediaData'
        ];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [
            // 'users' => User::where('role_id', 2)->limit(50)->get(['id as value', DB::raw("CONCAT(phone_number, ' - ', first_name, ' ', last_name) AS label")]),
            // 'sections' => Section::where('status', 1)->get(['id as value', 'name as label']),
            'departments' => Department::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'products' => Product::where('status', 1)->where('quantity', '>=', 1)->get(['id as value', DB::raw("CONCAT(sku, ' - ', name) AS label"), DB::raw("CONCAT(sku, ' - ', name_arabic) AS label_extra")]),
            'categories' => Productcategory::where('status', 1)->get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'showrooms' => StoreLocator::where('status', 1)->get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'cities' => States::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'warehouse' => Warehouse::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'maintenance_center' => MaintenanceCenter::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'department_details' => Department::with('teamsData', 'sectionData', 'ManagerData', 'SupervisorData')->get(),
            
            'input' => InputChannel::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'tags' => companyTags::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'type' => companyTypes::get(['id as value', 'name as label', 'name_arabic as label_extra']),
            'sla' => SLA::where('status', 1)->get(['id as value', 'name as label', 'name_arabic as label_extra']),
        ];
    }
    
    public function fetchUser($number) {
        $success = false;
        $user = User::where('phone_number', $number)->with('shippingAddressDataDefault.stateData', 'ConfirmedOrdersData.details.productData.featuredImage', 'ConfirmedOrdersData.Address')->first();
        if($user) {
            $success = true;
        }
        
        $response = [
            'user' => $user,
            'success' => $success,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function createUser(Request $request){
        $data = $request->all();
        $userData = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'status' => 1
        ]);
        $success = false;
        $user = false;
        if($userData){
            shippingAddress::create([
                'country_id' => 171,
                'customer_id' => $userData->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'shippinginstractions' => $request->instructions,
                'state_id' => $request->city,
                'make_default' => 1
            ]);
            $user = User::where('id', $userData->id)->with('shippingAddressDataDefault.stateData','ConfirmedOrdersData.details.productData')->first();
            $success = true;
        }
        
        $response = [
            'user' => $user,
            'success' => $success,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        
    }
    
    public function createOrder(Request $request){
        $data = $request->all();
        $orderData = InternalOrder::create([
            'order_no' => $request->order_no,
            'order_date' => $request->order_date,
            'status' => $request->status,
            'address' => $request->address,
            'order_type' => $request->order_type,
            'date' => $request->order_date
        ]);
        $success = false;
        $order = false;
        if($orderData){
            $pro = false;
            if($request->product_id)
            $pro = Product::where('id', $request->product_id)->first(['id', 'name','sku']);
            InternalOrderDetails::create([
                'order_id' => $orderData->id,
                'quantity' => $request->quantity,
                'product_id' => $request->product_id,
                'category' => $request->product_id ? null : $request->category,
                'product_sku' => $request->product_id ? $pro->sku : $request->sku,
                'product_name' => $request->product_id ? $pro->name : $request->product_name
            ]);
            $order = InternalOrder::where('id', $orderData->id)->with('internalOrderDetail.productData.featuredImage')->first();
            $success = true;
        }
        
        $response = [
            'order' => $order,
            'success' => $success,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function createTicket(Request $request){
        $data = $request->all();
        $success = false;
        $id = false;
        $ticket = InternalTicket::create([
            'title' => $request->title,
            'emergency' => $request->emergency,
            'follow_up' => $request->followUp,
            'status' => $request->status,
            'subject' => $request->subject,
            'details' => $request->details,
            'input_channel' => $request->input_channel,
            'type' => $request->type,
            'department' => $request->department,
            'section' => $request->section,
            'branch' => $request->branch,
            'branch_type' => $request->branch_type,
            'assignee' => $request->assignee,
            'assignee_type' => $request->assignee_type,
            'purchased_from' => $request->purchased_from,
            'urgency' => $request->urgency,
            'impact' => $request->impact,
            'customer_id' => $request->customer_id,
            'order_id' => $request->order_id,
            'user_id' => $request->user_id,
            'order_type' => $request->order_type,
            // 'image' => $request->image,
            'service_no' => $request->service_no,
            'value' => $request->value,
            'sla' => $request->sla
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
            if($request->order_detail_id){
                foreach($request->order_detail_id as $key => $value){
                    InternalTicketOrderDetails::create([
                        'ticket_id' => $ticket->id,
                        'order_detail_id' => $value
                    ]);
                }
            }
            if($request->tags){
                foreach($request->tags as $key => $value){
                    TicketTag::create([
                        'ticket_id' => $ticket->id,
                        'tag_id' => $value['tag_id']
                    ]);
                }
            }
            if($request->image) {
                foreach($request->image as $ky => $val){
                    InternalTicketMedia::create([
                        'ticket_id' => $ticket->id,
                        'file' => $val
                    ]);
                }
            }
            InternalTicketHistory::create([
                'comment' => 'Ticket Create',
                'user_id' => $request->user_id,
                'ticket_id' => $ticket->id,
                'status' => 0
            ]);
        }
        $response = [
            'id' => $id,
            'success' => $success,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function fetchTickets($id){
        $tickets = InternalTicket::
        select('id','ticket_no','title','status', 'customer_id', 'department', 'user_id', 'created_at', 'urgency', 'impact', 'input_channel', 'type', 'assignee', 'service_no')
        ->where(function ($queryBuilder) use ($id) {
            $queryBuilder
            ->where('assignee', $id)
            ->orWhere('user_id', $id)
            ->orWhereHas('departmentData', function ($query) use ($id) {
                return $query->where('manager', $id);
            });
        })
        ->with('customerData:id,first_name,last_name,phone_number', 'userData:id,first_name,last_name,phone_number', 'departmentData:id,name,name_arabic,manager', 'channelData:id,name,name_arabic', 'typeData:id,name,name_arabic', 'tagsData:id,name,name_arabic')
        ->orderBy('id', 'DESC')
        ->get();
        $response = [
            'tickets' => $tickets,
            'success' => true,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function fetchTicket($userid,$id){
        $tickets = InternalTicket::
        select('id','ticket_no','title','status', 'customer_id', 'department', 'user_id', 'created_at', 'urgency', 'impact', 'input_channel', 'type', 'assignee', 'order_type', 'order_id', 'purchased_from', 'subject', 'details', 'sla')
        ->where(function ($queryBuilder) use ($userid) {
            $queryBuilder
            ->where('assignee', $userid)
            ->orWhere('user_id', $userid)
            ->orWhereHas('departmentData', function ($query) use ($userid) {
                return $query->where('manager', $userid);
            });
        })
        ->with(
            'customerData:id,first_name,last_name,phone_number,email', 
            'customerData.shippingAddressDataDefault.stateData', 
            'userData:id,first_name,last_name,phone_number,email', 
            'assigneeData:id,first_name,last_name,phone_number', 
            'departmentData:id,name,name_arabic,manager', 
            'channelData:id,name,name_arabic', 
            'SLAData:id,name,name_arabic', 
            'typeData:id,name,name_arabic', 
            'tagsData:id,name,name_arabic', 
            'orderData:id,order_no', 
            'internalOrderData:id,order_no', 
            'ticketMediaData:id,ticket_id,file',
            'internalTicketHistoryData:id,ticket_id,user_id,status,comment,created_at',
            'internalTicketHistoryData.userData:id,first_name,last_name'
        )
        ->where('id', $id)
        ->first();
        
        $department_details = Department::where('id', $tickets->department)->with('teamsData', 'sectionData', 'ManagerData', 'SupervisorData')->first();
        $response = [
            'tickets' => $tickets,
            'department_details' => $department_details,
            'success' => true,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function updateAssignee($userid, $assignee, $id) {
        $success = false;
        $ticket = InternalTicket::where('id', $id)->first();
        if($ticket) {
            $ticket->assignee = $assignee;
            $ticket->update();
            $success = true;
        }
        
        InternalTicketHistory::create([
            'comment' => 'Assignee Update',
            'user_id' => $userid,
            'ticket_id' => $id,
            'status' => 1
        ]);
        
        $response = [
            'success' => $success,
        ];
        $responsejson=json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function InternalTicketMediaImageUpload(Request $request) {
        $success = false;
        $fileName = [];
        $imageName = [];
        if ($request->file('file')!=null) {
            foreach (request()->File('file') as $fileData) {
                $file = $fileData;
                // $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                // $imageName[] = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                
                $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                $imageName[] = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                $path = $file->move(public_path('/assets/internal-ticket'), $fileName);
                $success = true;
            }
        }
        return json_encode(['success' => $success, 'id' => $imageName]);
    }
}
