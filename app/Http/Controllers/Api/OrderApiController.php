<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderComments;
use App\Models\OrderStatusTimeLine;
use App\Models\OrderSummary;
use App\Models\States;
use App\Models\shippingAddress;
use App\Models\User;
use App\Models\LoyaltyHistory;
use App\Models\EmailTemplate;
use DB;
use Carbon\Carbon;
use PDF;
use Mail;
use App\Models\Notification;
use App\Models\NotificationToken;
use App\Helper\NotificationHelper;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\AbandonedCart;
use App\Models\Brand;
use App\Exports\ExportErpOrder;
use App\Models\GeneralEmailJobs;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;
use DateTimeZone;
use App\Exports\OrderInvoiceExcelEmail;
use App\Exports\ExportShippingCalculations;
use App\Exports\ProductCsvEmail;
// use Maatwebsite\Excel\Facades\Excel;
ini_set('max_execution_time', '500');
ini_set("pcre.backtrack_limit", "50000000");

class OrderApiController extends Controller
{
    public function getShippingCalculation(Request $request) {
        // print_r($request->all());die;
        return Excel::download(new ExportShippingCalculations($request->all()), 'shipping-calculation.csv');
    }
    
    public function sendSmsBulk(Request $request) {
        // print_r($request->all());die;
        $success = false;
        $response = '';
        foreach($request->numbers as $data) {
            $lang = $data['value'] != null ? $data['value'] : 'en';
            if(count($request->template) >= 1) {
                if($request->template['value'] == 'pending_orders') {
                    $response = NotificationHelper::whatsappmessageImage('+966' . str_replace('+966', '', $data['label']),'pending_orders',$lang, 'https://partners.tamkeenstores.com.sa/public/assets/new-media/ee2c8ac8195fe080ee33a29ddb77a2cc1715249183.jpeg');
                }
                else {
                    $response = NotificationHelper::whatsappmessageContent('+966' . str_replace('+966', '', $data['label']), $request->template['value'],$lang);   
                }
            }
            // number sms
            if($request->sms_content != '') {
                $response = NotificationHelper::sms('+966' . str_replace('+966', '', $data['label']), $request->sms_content);    
            }
            $success = true;   
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
    
    public function orderSend(Request $request) {
        $success = false;
        $response = '';
        $data = Order::with('UserDetail', 'Address')->where('id', $request->id)->first();
        if($request->type == 1) {
            if($data->UserDetail->email != null) {
                Mail::send('email.pending-order-email-template', ['order' => $data], function ($message) use ($data) {
                    $message->to($data->UserDetail->email)
                    ->subject('Pending Order');
                });   
            }
            $data->pending_email_count = $data->pending_email_count + 1;
            $data->update();
            $success = true;
        }
        else if($request->type == 2) {
            // $lang = $data->lang != null ? $data->lang : 'en';
            $lang = 'ar';
            $response = NotificationHelper::whatsappmessageImage('+966' . str_replace('+966', '', $data->Address->phone_number),'pending_orders',$lang, 'https://partners.tamkeenstores.com.sa/public/assets/new-media/ee2c8ac8195fe080ee33a29ddb77a2cc1715249183.jpeg');
            $data->pending_whatsapp_count = $data->pending_whatsapp_count + 1;
            $data->update();
            $success = true;
        }

        $responsee = [
            'success' => $success,
            'response' => $response,
            'number' => '+966' . str_replace('+966', '', $data->Address->phone_number),
        ];
        $responsejson=json_encode($responsee);
        $dataa=gzencode($responsejson,9);
        return response($dataa)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($dataa),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function index(Request $request){
        $search = $request['search'];
        $order = $request['sort'];
        $take = isset($request['page_size']) ? $request['page_size'] : 100;
        $pageNumber = isset($request['page']) ? $request['page'] : 1;
        $status = $request['status']; 
        $date = $request['date'];
        $paymentmethod = $request['paymentmethod'];
        $city = $request['city'];
        $shippingdetail = $request['shippingdetail'];
        
        
        $data = Order::with(['orderloyaltypoints:order_id,status,points','UserDetail:id,first_name,last_name,phone_number,user_device', 'Address:id,address,phone_number', 'ordersummary' 
        => function ($que) {
            return $que->where('type', 'discount');   
        },
        'ordersummary.couponData:id,coupon_code'])
        ->select(['order.id', 'order.customer_id', 'order.lang', 'order_no', 'shipping_id', 'order.status', 'pending_email_count', 'pending_whatsapp_count','paymentmethod', 'paymentid', 
        'erp_status','erp_status', 'erp_fetch_time','userDevice', 'order.created_at', 'city.name as city'])
        ->addSelect([
            'subtotal' => OrderSummary::selectRaw(DB::raw('price as subtotal'))
            ->whereColumn('order.id', 'order_summary.order_id')
            ->where('order_summary.type', 'subtotal')
        ,'total' => OrderSummary::selectRaw(DB::raw('price as total'))
            ->whereColumn('order.id', 'order_summary.order_id')
            ->where('order_summary.type', 'total')
        ,
        'shipping' => OrderSummary::selectRaw(DB::raw('price as shipping'))
            ->whereColumn('order.id', 'order_summary.order_id')
            ->where('order_summary.type', 'shipping')
        ])
        ->leftJoin('shipping_address as shippingaddress', function($join) {
            $join->on('order.shipping_id', '=', 'shippingaddress.id');
        })
        ->leftJoin('states as city', function($join) {
            $join->on('shippingaddress.state_id', '=', 'city.id');
        })
         ->withCount('details')
         ->withSum(['orderloyaltypoints' => function($query) {
                    $query->where('status', '0');
                }],
                'points')
        // ->when($order, function ($q) use ($order) {
        //     return $q->orderBy($order[0], $order[1]);
        // })
        ->when($search, function ($q) use ($search) {
            return $q->where(function($query) use($search){
                return $query->where("order_no","LIKE","%{$search}%")
                    ->orWhere("city.name","LIKE","%{$search}%")
                    ->orWhere("paymentmethod","LIKE","%{$search}%");
                    // ->orWhere("order.customer_id","LIKE","%{$search}%");
            });
            return $q->whereHas('UserDetail', function($query) use($search){
                 return $query->where("first_name", "LIKE", "%{$search}%")
                ->orWhere("last_name", "LIKE", "%{$search}%")
                ->orWhere("phone_number", "LIKE", "%{$search}%");
            });
        })
        // ->when($search, function ($q) use ($search) {
        //     return $q->whereHas('UserDetail', function($query) use($search){
        //         // print_r($q->UserDetail);die();
        //         return $query->where("first_name", "LIKE", "%{$search}%")
        //         ->orWhere("last_name", "LIKE", "%{$search}%")
        //         ->orWhere("phone_number", "LIKE", "%{$search}%");
        //     });
        // })
        
        // ->whereHas('UserDetail', function($query) use($search){
        //     return $query->where("first_name", "LIKE", "%{$search}%")
        //     ->orWhere("last_name", "LIKE", "%{$search}%")
        //     ->orWhere("phone_number", "LIKE", "%{$search}%");
        // })
        
        ->when($date, function ($q) use ($date) {
            return $q->whereDate('order.created_at', $date);
        })
        ->when($status && $status != 'yes', function ($q) use ($status) {
        // ->when($status, function ($q) use ($status) {
            return $q->whereIn('order.status', $status);
        })
        ->when($paymentmethod, function ($q) use ($paymentmethod) {
            return $q->whereIn('paymentmethod', $paymentmethod);
        })
        ->when($city, function ($q) use ($city) {
            return $q->whereIn('shippingaddress.state_id', $city);
        })
        ->when($shippingdetail, function ($q) use ($shippingdetail) {
            return $q->whereHas('UserDetail', function ($query) use ($shippingdetail) {
                $query->where('first_name', $shippingdetail)
                ->orWhere('last_name', $shippingdetail)
                ->orWhere('phone_number', $shippingdetail);
            });
            // return $q->whereIn('UserDetail.first_name', $shippingdetail);
        })
        
        
        ->orderBy('id', 'desc')
        ->paginate($take, ['*'], 'page', $pageNumber);
        
        // print_r($data);die();
        //->limit(500)
        //->get();
        // return response()->json(['data' => $data]);
        
        
        $response = [
            'data' => $data,
            'status' => $status
            // 'cities' => $cities,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function edit($id)
    {
        $order = Order::where('id', $id)->with(['details.productData.featuredImage', 'statustimeline', 'ordersummary:id,order_id,name,price',
        'comments.UserDetail:id,first_name,last_name', 'UserDetail', 'Address.stateData' , 'orderloyaltypoints' => function ($query) {
            $query->where('calculate_type', '1');
        }, 'usercommission' => function ($query) {
            $query->where('calculate_type', '1');
        }])
        ->withSum('orderloyaltypoints','points')
        ->withSum('usercommission','value')
        ->where('id', $id)
        // ->leftJoin('shipping_address as shippingaddress', function($join) {
        //     $join->on('order.shipping_id', '=', 'shippingaddress.id');
        // })
        // ->leftJoin('states as city', function($join) {
        //     $join->on('shippingaddress.state_id', '=', 'city.id');
        // })
        ->first();
        $cities = States::where('country_id','191')->get(['id as value', 'name as label']);
        $addresses = shippingAddress::where('customer_id', $order->customer_id)->get(['id as value', 'address as label']);
        
        // return response()->json(['data' => $order]);
        
        $response = [
            'data' => $order,
            'cities' => $cities,
            'addresses' => $addresses,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function show($id)
    {
        $order = Order::withTrashed()->with(['details.productData.featuredImage', 'statustimeline', 'ordersummary:id,order_id,name,price',
        'comments.UserDetail:id,first_name,last_name', 'UserDetail', 'Address', 'Address.stateData', 'orderloyaltypoints' => function ($query) {
            $query->where('calculate_type', '1');
        }, 'usercommission' => function ($query) {
            $query->where('calculate_type', '1');
        }])
        ->withSum('orderloyaltypoints','points')
        ->withSum('usercommission','value')
        ->where('id', $id)
        ->first();
        
        // $usdata = $order->UserDetail;
        // $loyaltyPoints = $usdata->loyaltypoints;
        // print_r($loyaltyPoints);die;
        // // $totalPoints = 0;
        // // foreach ($loyaltyPoints as $loyaltyPoint) {
        // //     $totalPoints += $loyaltyPoint['points'];
        // // }
        // $totalPoints = collect($loyaltyPoints)->sum('points');
        
        // return response()->json(['data' => $order]);
        
        $response = [
            'data' => $order,
            // 'loyaltypoints' => $totalPoints,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function update(Request $request, $id) {
        
        $orderdata = Order::where('id', $id)->first();
        if($orderdata->status == 0 && $request->status == 4)
        {
            OrderStatusTimeLine::create([
                'order_id' => $id,
                'status' => 1,
                'craeted_at' => Carbon::now(),
            ]);
            OrderStatusTimeLine::create([
                'order_id' => $id,
                'status' => 2,
                'craeted_at' => Carbon::now(),
            ]);
            OrderStatusTimeLine::create([
                'order_id' => $id,
                'status' => 3,
                'craeted_at' => Carbon::now(),
            ]);
            OrderStatusTimeLine::create([
                'order_id' => $id,
                'status' => isset($request->status) ? $request->status : null,
                'craeted_at' => Carbon::now(),
            ]);
        }
        else {
            $check = OrderStatusTimeLine::where('order_id', $id)->where('status', $request->status)->count();
            if($check >= 1){
                $timeline = false;
                // return response()->json(['success' => true, 'message' => 'Order Has been updated!!']);
            }
            else {
                OrderStatusTimeLine::create([
                    'order_id' => $id,
                    'status' => isset($request->status) ? $request->status : null,
                    'craeted_at' => Carbon::now(),
                ]);
            }
        }
        
        // pending time
        if($orderdata->status == 8 && $request->status <= 4) {
            $order = Order::whereId($id)->update([
                'pending_order_date' => $orderdata->created_at,
                'created_at' => Carbon::now(),
            ]);
        }
        
        $order = Order::whereId($id)->update([
            'shipping_id' => isset($request->shipping_id) ? $request->shipping_id : null,
            'status' => isset($request->status) ? $request->status : null,
        ]);
        
        if($orderdata->token)
            $data = NotificationHelper::global_notification([$orderdata->token], 'order status change notification', 'order status change notification', 'https://react.tamkeenstores.com.sa/assets/new-media/2ab592fe27552e8a493b9c9037ddcc9b1708257241.webp',$orderdata->userDevice);
        // print_r($order);die;
        
        if($request->paymentmethod == 'cod' && ($request->status == 6 || $request->status == 5)) {
            $user = User::whereId($request->userid)->update([
                'blacklist' => 1,
            ]);
        }
        else {
            $user = User::whereId($request->userid)->update([
                'blacklist' => 0,
            ]);
        }
        
        $addressupdate = shippingAddress::whereId($request->shipping_id)->update([
            'shippinginstractions' => isset($request->shippinginstractions) ? $request->shippinginstractions : null,
        ]);
        
        
        return response()->json(['success' => true, 'message' => 'Order Has been updated!']);
    }
    
    public function destroy($id)
    {
        $order = Order::find($id);
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Order Has been deleted!']);
    }
    
    public function multidelete(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $deletetags = Order::whereIn('id',$ids)->get();
            $deletetags->each->delete();
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Selected Orders Has been deleted!']);
            
    }
    
    public function trashOrder() {
        $take = isset($request['page_size']) ? $request['page_size'] : 100;
        $pageNumber = isset($request['page']) ? $request['page'] : 1;
        $trashOrder = Order::onlyTrashed()->with('UserDetail:id,first_name,last_name,phone_number')
        ->select(['order.id', 'order.customer_id', 'order_no', 'shipping_id', 'order.status','paymentmethod', 'userDevice', 'order.created_at', 'city.name as city'])
        ->addSelect(['subtotal' => OrderSummary::selectRaw(DB::raw('group_concat(price) as subtotal'))
            ->whereColumn('order.id', 'order_summary.order_id')
            ->where('order_summary.type', 'subtotal')
        ,'total' => OrderSummary::selectRaw(DB::raw('group_concat(price) as total'))
            ->whereColumn('order.id', 'order_summary.order_id')
            ->where('order_summary.type', 'total')
        ])
        ->leftJoin('shipping_address as shippingaddress', function($join) {
            $join->on('order.shipping_id', '=', 'shippingaddress.id');
        })
        ->leftJoin('states as city', function($join) {
            $join->on('shippingaddress.state_id', '=', 'city.id');
        })
         ->withCount('details')
        //  ->paginate(100, ['*'], 'page', 1);
        // ->limit(152)
        ->get();
         
        return response()->json(['data' => $trashOrder]);
    }

    public function restoreOrder($id) {
        $restoreOrder = Order::onlyTrashed()->findOrFail($id);
        $restoreOrder->restore();
        
        return response()->json(['success' => true, 'message' => 'Order Has been restored!']);
    }
    
    public function boxadditionaldata(Request $request) {
        
        $cities = States::where('country_id','191')->get(['id as value', 'name as label']);
        $date = $request['date'];
        $ordercount = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })    
        ->count();
        $ordertotal = OrderSummary::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })    
        ->where('type', 'total')->sum('price');
        
        $processing = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })      
        ->whereIn('status', [0,1,2])->count();
        
        $process = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })      
        ->whereIn('status', [0,1,2])->pluck('id')->toArray();
        // print_r($process);die();
        
        if($processing > 0) {
            $processtotal = OrderSummary::whereIn('order_id', $process)->where('type', 'total')->sum('price');
        }
        else {
            $processtotal = null;
        }
        
        
        $refundcount = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })      
        ->where('status', 6)->count();
        
        $refund = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })      
        ->where('status', 6)->pluck('id')->toArray();
        // print_r($refund);die();
        
        if($refundcount > 0) {
            $refundtotal = OrderSummary::whereIn('order_id', $refund)->where('type', 'total')->sum('price');
        }
        else {
            $refundtotal = null;
        }
        
        $pendingcount = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })      
        ->where('status', 8)->count();
        $pending = Order::
        when($date, function ($q) use ($date) {
            return $q->whereDate('created_at', $date);
        })    
        ->where('status', 8)->pluck('id')->toArray();
        
        if($pendingcount > 0) {
            $pendingtotal = OrderSummary::whereIn('order_id', $pending)->where('type', 'total')->sum('price');
        }
        else {
            $pendingtotal = null;
        }
        return response()->json(['data' => $cities, 'ordercount' => $ordercount, 'ordertotal' => $ordertotal, 'processingcount' => $processing,
        'processtotal' => $processtotal,
        'refundcount' => $refundcount, 'refundtotal' => $refundtotal, 'pendingcount' => $pendingcount, 'pendingtotal' => $pendingtotal]);
    }
    
    public function AddComment(Request $request) 
    {
        $comments = OrderComments::create([
            'order_id' => isset($request->order_id) ? $request->order_id : null,
            'customer_id' => isset($request->customer_id) ? $request->customer_id : null,
            'comments' => isset($request->comments) ? $request->comments : null,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Comment Has been created!']);
    }
    
    public function UpdateMadac(Request $request) {
        $id = $request->id;
        $success = false;
        if($id) {
            $check = Order::where('madac_id', $request->madac_id)->count();
            if($check >= 1){
                $success == false;
                $message = "Madac ID Already Exists";
            }
            else {
                
            $data = Order::whereId($id)->update([
                'madac_id' => isset($request->madac_id) ? $request->madac_id : null,
            ]);
            $success = true;
            $message = "Madac ID Successfully Updated!";
            }
        }
        return response()->json(['success' => $success, 'message' => $message]);
    }
    
    public function UpdatePending(Request $request, $id) {
        $pendingupdate = array(
            'pending_order_date' => $request->created_date ? $request->created_date : null,
            'created_at'    =>  Carbon::now(),
            'status' => $request->status,
        );
        Order::whereId($id)->update($pendingupdate);
            
        return response()->json(['success' => 'true', 'message' => 'status Updated Successfully']);
    }
    
    public function getAddressCity(Request $request) {
        $address = shippingAddress::where('id', $request->id)->select('id', 'state_id')->with('stateData:id,name')->first();
        
        $response = [
            'data' => $address
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    // public function downloadPDF($id) {
    //     $thankyou = Order::find($id);

    //     $html = (string)view('pdf', compact('thankyou'));
    //     $pdf = PDF::loadHtml($html);
    //     return $pdf->download($thankyou->order_no .'.pdf');
    // }
    
    public function downloadPDF($id) {
        $thankyou = Order::find($id);
        $totalSummary = $thankyou->ordersummary->where('type', 'total')->first();
        $total = (float)$totalSummary->price;
        $vatRate = 0.15; // 15%
        $netAmount = $total / (1 + $vatRate);
        // Calculate the VAT
        $vat = $total - $netAmount;
        $VATAmount = $total - $netAmount;
        $rawTimestamp = $thankyou->created_at;
        $sellerName = "Tamkeen International"; // Length: 21
        $vatRegistrationNumber = "310180376600003"; // Length: 15
        $timeStamp = date("Y-m-d\TH:i:s\Z", strtotime($rawTimestamp)); // "2024-09-21T10:26:27Z"
        $invoiceTotal = number_format($total, 2, '.', ''); // Ensures "1000.00"
        $vatTotal = number_format($VATAmount, 2, '.', ''); // Ensures "150.00"
        function createTLV($tag, $value) {
            $tagByte = chr($tag);
            $lengthByte = chr(strlen($value));
            return $tagByte . $lengthByte . $value;
        }

        $tlv1 = createTLV(1, $sellerName); // Seller's name
        $tlv2 = createTLV(2, $vatRegistrationNumber); // VAT registration number
        $tlv3 = createTLV(3, $timeStamp); // Time stamp of the invoice
        $tlv4 = createTLV(4, $invoiceTotal); // Invoice total
        $tlv5 = createTLV(5, $vatTotal); // VAT total

        // Concatenate all TLVs into one string
        $fullTLV = $tlv1 . $tlv2 . $tlv3 . $tlv4 . $tlv5;

        // Convert each character to hex and concatenate
        $hexString = '';
        for ($i = 0; $i < strlen($fullTLV); $i++) {
            $hexString .= sprintf("%02x", ord($fullTLV[$i]));
        }

        // Encode the hex string in base64
        $barcode = base64_encode(hex2bin($hexString));

        // Output the base64-encoded result
        // echo $barcode;die;

        // Optional: To verify, decode the barcode
        $decoded = base64_decode($barcode);
        // echo "\nDecoded output:\n";
        // echo bin2hex($decoded); // Show the hex representation of the decoded value

        $html = (string)view('pdf', compact('thankyou','barcode','total'));
        $pdf = PDF::loadView('pdf', ['thankyou' => $thankyou, 'barcode' => $barcode, 'total' => $total], [], ['format' => [210, 297],'margin_top' => 4, 'margin_footer' => 0,'margin_bottom' => 4, 'margin_left' => 4, 'margin_right' => 4, 'mode' =>'utf-8']);
        return $pdf->download($thankyou->order_no .'.pdf');
    }
    
    public function MultidownloadPDF(Request $request) {
        // print_r($request->all());die;
        $fromdate = isset($request['fromdate']) ? $request['fromdate'] : null;
        $todate = isset($request['todate']) ? $request['todate'] : null;
        $statusfilter = isset($request['filterstatus']) ? $request['filterstatus'] : null;
        $paymentfilter = isset($request['filterpayment']) ? $request['filterpayment'] : null;
        $orders = isset($request['selectedData']) ? $request['selectedData'] : null;
        
         
        $thankyous = Order::
        when($fromdate != $todate, function ($q) use ($fromdate,$todate) {
            return $q->whereBetween('order.created_at', [$fromdate, $todate]);
        })
        ->when($fromdate == $todate, function ($q) use ($fromdate,$todate) {
            return $q->whereDate('order.created_at', $fromdate);
        })
        ->when($statusfilter, function ($q) use ($statusfilter) {
            return $q->whereIn('order.status', $statusfilter);
        })
        ->when($orders, function ($q) use ($orders) {
            return $q->whereIn('order.order_no', $orders);
        })
        ->when($paymentfilter, function ($q) use ($paymentfilter) {
            return $q->whereIn('order.paymentmethod', $paymentfilter);
        })
        ->get();
        if($thankyous->isEmpty()){
            return response()->json(['message'=>'order not found'],404);
        }
        
        $pdf = PDF::loadView('pdf-backend', ['thankyous' => $thankyous], [], ['format' => [210, 297],'margin_top' => 4, 'margin_footer' => 0,'margin_bottom' => 4, 'margin_left' => 4, 'margin_right' => 4, 'mode' =>'utf-8']);
        return $pdf->download('download-invoices.pdf');
    }
    
    public function ProcessEmail() {
        // print_r('timedate'.date('Y-m-d H:i'));die();
        $notifications = Notification::where('date', date('Y-m-d H:i'))->where('for_app', 1)->where('for_web', 1)->get();
        // print_r('notidate'.$notifications);die;
        
        $totalblogs = NotificationToken::count();
        $totalxml = $totalblogs / 1000;
        for ($i=0; $i < $totalxml; $i++) {
            $products = NotificationToken::select('token')->orderBy('id', 'asc')->paginate('1000', ['*'], 'page', ($i+1));
            $tokenData = $products->pluck('token')->toArray();
            foreach($notifications as $notification){
                $product = '';
                $brand = '';
                $cat = '';
                if($notification->type == 1) {
                    $type = 'Products';
                    $product = Product::where('id', $notification->product_id)->first();
                }
                elseif($notification->type == 2) {
                    $type = 'Brands';
                    $brand = Brand::where('id', $notification->brand_id)->first();
                }
                elseif($notification->type == 3) {
                    $type = 'Product Categories';
                    $cat = Productcategory::where('id', $notification->category_id)->first();
                }
                else{
                    $type = '';
                }
                
                if($notification->type == 1) {
                    $data = NotificationHelper::global_notification($tokenData, $notification->title, $notification->message, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type, isset($product->slug) ? $product->slug : null);
                }
                elseif($notification->type == 2) {
                    $data = NotificationHelper::global_notification($tokenData, $notification->title, $notification->message, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type, isset($brand->slug) ? $brand->slug : null);
                }
                elseif($notification->type == 3) {
                    $data = NotificationHelper::global_notification($tokenData, $notification->title, $notification->message, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type, isset($cat->slug) ? $cat->slug : null);
                }
                else {
                    $data = NotificationHelper::global_notification($tokenData, $notification->title, $notification->message, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type);
                }
            }
        }
    }
    
    public function ProscsdcessEmail() {
        
        $emaildata = GeneralEmailJobs::where('type', 'erp-email-status')->with('emailtimes')->where('status', 1)->first();
        if($emaildata) {
            if(isset($emaildata->emailtimes)) {
                foreach ($emaildata->emailtimes as $key => $value) {
                    $endtime = $value->end_time;
                    if($endtime == date('H:i')) {
                        $days = $value->days ? $value->days : 0;
                        $currentDate = date('Y-m-d');
                        $minuDays = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));

                        $from = $minuDays . ' ' . $value->start_time . ':00';
                        $to = $minuDays . ' ' . $value->end_time . ':00';
                        $currenttime = Carbon::now();

                        $order = Order::whereNotIn('status',['5','7','8'])
                        ->whereDate('created_at', $minuDays)
                        ->whereTime('created_at', '>=', $value->start_time)
                        ->whereTime('created_at', '<=', $value->end_time)
                        ->orderBy('created_at', 'ASC')
                        ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
                        ->get();

                        // $orderfetchcount = Order::whereNotIn('status',['5','7','8'])
                        // ->whereDate('created_at', $minuDays)
                        // ->whereTime('created_at', '>=', $value->start_time)
                        // ->whereTime('created_at', '<=', $value->end_time)
                        // ->where('erp_status', 1)
                        // ->orderBy('created_at', 'ASC')
                        // ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
                        // ->count();
                        
                        $orderfetchcount = $order->where('erp_status', 1)->count();
                        // print_r($order);die;

                        $orderscount = $order->count();
            
                        $todata = isset($emaildata->to) ? explode(',' ,$emaildata->to) : ['mubashirasif1@gmail.com'];
                        $ccdata = isset($emaildata->cc) ? explode(',' ,$emaildata->cc) : [];
                        $bccdata = isset($emaildata->bcc) ? explode(',' ,$emaildata->bcc) : [];
                        $fromdata = isset($emaildata->from) ? $emaildata->from : ['adminpanel@tamkeenstores.com.sa'];

                        if ($order->count() > 0) {
                            $export = new ExportErpOrder(['fromdate' => $from,'todate' => $to]);
                            $fileName = 'erp_orders.csv';
                            Excel::store($export, $fileName);
                            Mail::send('email.erpfetchorder', ['order' => $order, 'currenttime' => $currenttime, 'orderscount' => $orderscount, 'orderfetchcount' => $orderfetchcount], function ($message) use ($fileName, $todata, $ccdata, $bccdata, $fromdata) {
                                $message->to($todata)
                                    ->cc($ccdata)
                                    ->bcc($bccdata)
                                    ->from($fromdata)->subject('ERP Fetch Order Email')->attach(storage_path('app/' . $fileName));
                            });
                            // Delete the file after sending email
                            unlink(storage_path('app/' . $fileName));
                        }
                    }
                }
            }
        }
        // $emaildata = GeneralEmailJobs::where('type', 'erp-email-status')->with('emailtimes')->where('status', 1)->first();
        // if($emaildata) {
        //     if(isset($emaildata->emailtimes)) {
        //         foreach ($emaildata->emailtimes as $key => $value) {
        //             $endtime = $value->end_time;
        //             if($endtime == date('H:i')) {
        //                 $days = $value->days ? $value->days : 0;
        //                 $currentDate = date('Y-m-d');
        //                 $minuDays = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));

        //                 $from = $minuDays . ' ' . $value->start_time . ':00';
        //                 $to = $minuDays . ' ' . $value->end_time . ':00';
        //                 $currenttime = Carbon::now();

        //                 $order = Order::whereNotIn('status',['5','7','8'])
        //                 ->whereDate('created_at', $minuDays)
        //                 ->whereTime('created_at', '>=', $value->start_time)
        //                 ->whereTime('created_at', '<=', $value->end_time)
        //                 ->orderBy('created_at', 'ASC')
        //                 ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
        //                 ->get();

        //                 $orderfetchcount = Order::whereNotIn('status',['5','7','8'])
        //                 ->whereDate('created_at', $minuDays)
        //                 ->whereTime('created_at', '>=', $value->start_time)
        //                 ->whereTime('created_at', '<=', $value->end_time)
        //                 ->where('erp_status', 1)
        //                 ->orderBy('created_at', 'ASC')
        //                 ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
        //                 ->count();

        //                 $orderscount = $order->count();
            
        //                 $todata = explode(',' ,$emaildata->to);
        //                 $ccdata = explode(',' ,$emaildata->cc);
        //                 $bccdata = explode(',' ,$emaildata->bcc);
        //                 $fromdata = $emaildata->from;

        //                 if ($order->count() > 0) {
        //                     $export = new ExportErpOrder(['fromdate' => $from,'todate' => $to]);
        //                     $fileName = 'erp_orders.csv';
        //                     Excel::store($export, $fileName);
        //                     Mail::send('email.erpfetchorder', ['order' => $order, 'currenttime' => $currenttime, 'orderscount' => $orderscount, 'orderfetchcount' => $orderfetchcount], function ($message) use ($fileName, $todata, $ccdata, $bccdata, $fromdata) {
        //                         $message->to($todata)
        //                             ->cc($ccdata)
        //                             ->bcc($bccdata)
        //                             ->from($fromdata)->subject('ERP Fetch Order Email')->attach(storage_path('app/' . $fileName));
        //                     });
        //                     // Delete the file after sending email
        //                     unlink(storage_path('app/' . $fileName));
        //                 }
        //             }
        //         }
        //     }
        // }
    }
}
