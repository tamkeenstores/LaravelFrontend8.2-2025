<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\InternalTicket;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use App\Exports\ProductCsvEmail;

use Mail;
use App\Models\GeneralEmailJobs;
use App\Exports\OrderInvoiceExcelEmail;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
ini_set('max_execution_time', '300');
ini_set("pcre.backtrack_limit", "5000000");
use App\Helper\NotificationHelper;
use App\Helper\ConditionSetup_helper;
use SimpleXMLElement; 
use DB;
use Illuminate\Support\Facades\Cache;


class ExtraApisController extends Controller
{
    public function UpdateLnOrderStatusById($id,$data = false,Request $request){
        $arrayids = explode(',', $id);
        $arraydata = explode(',', $data);
        $success = false;
        $message = 'Please add update value';
        if(sizeof($arraydata)){
            $orders = Order::whereIn('order_no', $arrayids)->get(['id','order_no','status']);
            if(sizeof($orders)){
                $status = false;
                foreach($orders as $key => $order){
                    if(isset($arraydata[$key]) && $arraydata[$key] != null){
                        if($arraydata[$key] == 'cancel'){
                            $status = 5;
                        }elseif($arraydata[$key] == 'refund'){
                            $status = 6;
                        }else{
                            $status = false;
                        }
                        if($status){
                            $order = Order::where('order_no', $order->order_no)->update(['status'=>$status,'erp_fetch_date'=>Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d'),'erp_fetch_time'=>Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('H:i:s')]);
                            if($order){
                                $success = true;
                                $message = 'Order has been Updated!';
                            }
                        }
                    } 
                }
            }else{
                $message = 'order number does not exist!';
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
    
    public function UpdateLninforByIdOnlyDateTime($id,$data = false,Request $request){
        $arrayids = explode(',', $id);
        $arraydata = explode(',', $data);
        $success = false;
        $message = 'Please add update value';
        if(sizeof($arraydata)){
            $orders = Order::whereIn('order_no', $arrayids)->get(['id','order_no','erp_status']);
            if(sizeof($orders)){
                foreach($orders as $key => $order){
                    if(isset($arraydata[$key]) && $arraydata[$key] != null){
                        $order = Order::where('order_no', $order->order_no)->update(['erp_status'=>$arraydata[$key],'erp_fetch_date'=>Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d'),'erp_fetch_time'=>Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('H:i:s')]);
                        if($order){
                            $success = true;
                            $message = 'Order has been Updated!';
                        }
                    }
                    
                }
            }else{
                $message = 'Order Number does not exist!';
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
    
    public function getDataUnifonic(Request $request){
        $data = $request->all();
        $orderStatus = false;
        $ordersStatus = [];
        if(isset($data['order_no']) && $data['order_no']){
            $order = Order::where('order_no',$data['order_no'])->select(['status'])->first();
            if($order){
                if($order->status == 0){
                    $orderStatus = "Order Received";
                }elseif($order->status == 1){
                    $orderStatus = "Order Confirmed";
                }elseif($order->status == 2){
                    $orderStatus = "Processing";
                }elseif($order->status == 3){
                    $orderStatus = "Out for Delivery";
                }elseif($order->status == 4){
                    $orderStatus = "Delivered";
                }elseif($order->status == 5){
                    $orderStatus = "OrderCancel";
                }elseif($order->status == 6){
                    $orderStatus = "Refund";
                }elseif($order->status == 7){
                    $orderStatus = "Failed";
                }else{
                    $orderStatus = "Pending Payment";
                }
            }
        }
        
        if(isset($data['number']) && $data['number']){
            $phoneNumber = substr($data['number'], 3, strlen($data['number']) - 3);
            

            $user = User::where('phone_number',$phoneNumber)->first();
            
            if($user){
                $orders = Order::where('customer_id',$user->id)->get(['order_no','status']);
                // print_r($orders);die;
                foreach($orders as $k => $order){
                    if($order->status == 0){
                        $ordersStatus[$order->order_no] = "Order Received";
                    }elseif($order->status == 1){
                        $ordersStatus[$order->order_no] = "Order Confirmed";
                    }elseif($order->status == 2){
                        $ordersStatus[$order->order_no] = "Processing";
                    }elseif($order->status == 3){
                        $ordersStatus[$order->order_no] = "Out for Delivery";
                    }elseif($order->status == 4){
                        $ordersStatus[$order->order_no] = "Delivered";
                    }elseif($order->status == 5){
                        $ordersStatus[$order->order_no] = "OrderCancel";
                    }elseif($order->status == 6){
                        $ordersStatus[$order->order_no] = "Refund";
                    }elseif($order->status == 7){
                        $ordersStatus[$order->order_no] = "Failed";
                    }else{
                        $ordersStatus[$order->order_no] = "Pending Payment";
                    }
                }
            }
        }
        if($orderStatus){
            $response = [
                'orderStatus' => $orderStatus
            ];
        }elseif($ordersStatus){
            $response = [
                'ordersStatus' => $ordersStatus
            ];
        }
        else{
            return response()->json(['Error'])->setStatusCode(404);
        }
        
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    public function dailySkuUpdate(Request $request)
    {   
        $products = Product::get();
        
        // these are the headers for the csv file.
        $headers = array(
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition' => 'attachment; filename=download.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        );

            //creating the download file
            $export = new ProductCsvEmail($products);

            // $fileName = "'Stock-Update_' . date('Y-m-d') . '.csv'";
            $fileName = 'Stock-Update_' . date('Y-m-d') . '.csv';
            // print_r($fileName);die; 

            Excel::store($export, $fileName);
        
            Mail::send('email.stockskuupdate', ['product' => $products], function ($message) use ($fileName) {
                $message->to('mubashirasif1@gmail.com')->cc('sameeriqbal1200@gmail.com')->subject('Product Stock Update')->attach(storage_path('app/' . $fileName));
            });
    
            unlink(storage_path('app/' . $fileName));
        
    }
    public function UpdateProductQtyBySku($id,$data = false,Request $request){
        
        $arrayids = explode(',', $id);
        $arraydata = explode(',', $data);
        $success = false;
        $message = 'Please add update qty!';
        
        if(sizeof($arrayids)){
            $products = Product::whereIn('sku', $arrayids)->get(['id','sku','quantity','price','sale_price']);
            if(sizeof($products)){
                foreach($products as $key => $product){
                    if(isset($arraydata[$key]) && $arraydata[$key] != null){
                        // print_r($product->quantity);die;
                            $product->quantity = $product->quantity + $arraydata[$key];
                            $product->save();
                            $success = true;
                            $message = 'Product Qty has been updated.';
                        
                    }
                    
                }
            }else{
                $message = 'Product SKU does not exist!';
            }
        }
        
        return response()->json(['success' => $success,'message' => $message]);
    }
    
    public function getERPOrders(Request $request)
    {   
        $success = false;
        $setdata = $request->all(); 
        $orders = [];
        if(isset($setdata['credentials']) && $setdata['credentials'] == 'TamkeenStores2@'){
        $date_created = date('Y-m-d', strtotime($request->created_at));
        $orders = Order::with('details:id,order_id,product_id,product_name,unit_price,quantity,total','details.productSku','ordersummary:id,name,type,price,order_id,amount_id','Address:id,customer_id,first_name,last_name,phone_number,state_id,address,address_label','Address.stateData:id,name','UserDetail:id,first_name,last_name,phone_number,email')->where('erp_status', 0)->whereNotIn('status',['5','7','8'])->whereDate('created_at', $date_created)->select(['id','order_no','erp_status','customer_id','shipping_id','status','paymentmethod','paymentid','created_at'])->get();
        
        if($orders->toArray()){
            $success = true;
        }

        $orderArrayData = $orders->toArray();
        foreach($orderArrayData as $key => $orderDataa){
                
        //     // print_r($orderDataa['order_detail_product']);
            
        //     $feespriceupdated = 0;
        //     $discountpriceupdated = 0;
        //     $jsonData = json_decode($orderDataa['discount_rule_data'],true);
        //     if($orderDataa['discount_rule_data'] && sizeof($jsonData)){
        //         // print_r($jsonData);
        //         $discountpriceupdated = array_sum(array_column($jsonData, 'price'));
        //     }
            
            
        //     // if($orderDataa['subtotal'] > 0){
        //         // $orderDataasubtotal = $orderDataa['subtotal'] - ($orderDataa['subtotal']/115*100);
        //         // $orderArrayData[$key]['subtotal'] = number_format($orderDataa['subtotal'] - $orderDataasubtotal, 2, '.', '');
        //     // }
        //     if($orderDataa['discount'] > 0){
        //         $orderDataaDiscount = $orderDataa['discount'] - ($orderDataa['discount']/115*100);
        //         $orderArrayData[$key]['discount'] = number_format($orderDataa['discount'] - $orderDataaDiscount, 2, '.', '');
        //     }
        //     if($orderDataa['door_step_amount'] > 0){
        //         $orderDataaDiscount = $orderDataa['door_step_amount'] - ($orderDataa['door_step_amount']/115*100);
        //         $orderArrayData[$key]['door_step_amount'] = number_format($orderDataa['door_step_amount'] - $orderDataaDiscount, 2, '.', '');
        //     }
        //     if($orderDataa['cod_additional_charges'] > 0){
        //         $orderDataaDiscount = $orderDataa['cod_additional_charges'] - ($orderDataa['cod_additional_charges']/115*100);
        //         $orderArrayData[$key]['cod_additional_charges'] = number_format($orderDataa['cod_additional_charges'] - $orderDataaDiscount, 2, '.', '');
        //     }
        //     if($orderDataa['shipping'] > 0){
        //         // print_r($orderDataa['order_no']);
        //         // echo "--";
        //         // print_r($orderDataa['shipping']);
        //         // echo "--";
        //         // print_r($orderDataa['shipping']/115*100);
        //         if($orderDataa['order_no'] == 'TKS174280'){
        //             $orderArrayData[$key]['shipping'] = "37.42";
        //         }else{
        //             $orderDataaDiscount = $orderDataa['shipping'] - ($orderDataa['shipping']/115*100);
        //             $orderArrayData[$key]['shipping'] = number_format($orderDataa['shipping'] - $orderDataaDiscount, 2, '.', '');
        //         }
        //     }
            
        //     $jsonDataa = json_decode($orderDataa['fees'],true);
        //     if($orderDataa['fees'] && sizeof($jsonDataa)){
        //         // $feespriceupdated = $jsonDataa['amount'];
        //     }
        //     $orderDataaDiscountvat = $feespriceupdated - ($feespriceupdated/115*100);
        //     $orderArrayData[$key]['fees'] = number_format($feespriceupdated - $orderDataaDiscountvat, 2, '.', '');
            
        //     $orderDataaDiscountvat = $discountpriceupdated - ($discountpriceupdated/115*100);
        //     $orderArrayData[$key]['discount_rule_data'] = number_format($discountpriceupdated - $orderDataaDiscountvat, 2, '.', '');
                
                
        //     if($orderDataa['total'] > 0){
        //         $orderDataasubtotal = $orderDataa['total'] - ($orderDataa['total']/115*100);
        //         $orderArrayData[$key]['total'] = number_format($orderDataa['total'] - $orderDataasubtotal, 2, '.', '');
        //     }
            
            foreach($orderDataa['details'] as $k => $productData){
                $proData = Product::where('id', $productData['product_id'])->first();
                        if($proData->ln_sku === null){
                          $orderArrayData[$key]['details'][$k]['product_sku']['sku'] = $proData->sku;
                        }else{
                          $orderArrayData[$key]['details'][$k]['product_sku']['sku'] = $proData->ln_sku;
                        }
                if($orderDataa['details'][$k]['product_sku']['is_bundle'] == 1){
                        // $orderArrayData[$key]['order_detail_product'][$k]['product_sku']['sku'];
                        $explodeproducts = explode("+",$orderDataa['details'][$k]['product_sku']['sku']);
                        $bundleSkuupdate = '';
                        foreach($explodeproducts as $proKey => $explodeproduct){
                            $bundleProData = Product::where('sku', $explodeproduct)->first();
                            // print_r($bundleProData);die();
                            // $bundleSkuupdate = '';
                            if($bundleProData->ln_sku === null){
                              $bundleSkuupdate = $bundleProData->sku;
                            }else{
                              $bundleSkuupdate = $bundleProData->ln_sku;
                            }
                            if($proKey > 0){
                                $ArrayData = array(
                                    'id'=>$orderDataa['details'][$k]['id'],
                                    'order_id'=>$orderDataa['details'][$k]['order_id'],
                                    'product_id'=>$orderDataa['details'][$k]['product_id'],
                                    'product_name'=>$orderDataa['details'][$k]['product_name'],
                                    'unit_price'=>0,
                                    'quantity'=>$orderDataa['details'][$k]['quantity'],
                                    'total'=>0,
                                    'product_sku'=>array(
                                        'id'=>$orderDataa['details'][$k]['product_id'],
                                        'sku'=>$bundleSkuupdate,
                                        'is_bundle'=>1
                                        ),
                                    );
                                $orderArrayData[$key]['details'][] = $ArrayData;
                                // print_r($orderDataa['order_detail_product'][$k]['id']);
                                
                                // die();
                            }
                        }
                        // die();
                        $orderArrayData[$key]['details'][$k]['product_sku']['sku'] = $explodeproducts[0];
                        // print_r(explode("+",$orderDataa['order_detail_product'][$k]['product_sku']['sku']));die();
                    }
                // print_r($orderDataa['order_detail_product'][$k]['total']);die();
                $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                $orderArrayData[$key]['details'][$k]['unit_price'] = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                
                $productDatasubtotal = $productData['total'] - ($productData['total']/115*100);
                $orderArrayData[$key]['details'][$k]['total'] = number_format($productData['total'] - $productDatasubtotal, 2, '.', '');
            }
            
            $orderArrayData[$key]['created_at'] = $orders[$key]->created_at->format('Y-m-d H:i:s');
        //     // $orderArrayData[$key]['discount_rule_data'] = ($discountpriceupdated - $discountpriceupdated/115*100);
        }
            if($orders->toArray()){
                $success = true;
            }
           
        }
        
        $response = ['success' => $success,'data' => $orderArrayData];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }



    public function InvoiceEmail() {
        $emaildata = GeneralEmailJobs::where('type', 'order-invoice-email')->where('status', 1)->with('emailtimes')->first();
        if($emaildata) {
            $days = isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->days : 1;
            $currentDate = date('Y-m-d');
            $priorDate = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));
            // print_r($priorDate);die;
            
            
            $today = new DateTime("today " . isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->end_time . ':00' : '08:00:00');
            $todaydate = $today->format('Y-m-d H:i:s');
            // print_r($todaydate);die;
            
            // Yesterday
            $yesterday = isset($emaildata->emailtimes[0]) ? $priorDate . ' ' .  $emaildata->emailtimes[0]->start_time . ':00' :  new Datetime('yesterday 08:00:00');
            $yesterdaydate = isset($emaildata->emailtimes[0]) ? $yesterday : $yesterday->format('Y-m-d H:i:s');
            
    
            $orders = Order::whereIn('status', [0, 2])->whereBetween('created_at', [$yesterdaydate, $todaydate])->pluck('id')->toArray();
            // print_r($orders);die;
    
            $thankyous = Order::whereIn('id', $orders)->get();
            $html = (string)view('pdf-backend', compact('thankyous'));
            $pdf = PDF::loadView('pdf-backend', ['thankyous' => $thankyous], [], ['format' => [210, 297],'margin_top' => 4, 'margin_footer' => 0,'margin_bottom' => 4, 'margin_left' => 4, 'margin_right' => 4, 'mode' =>'utf-8']);
    
    
            // Today
            $emailtoday = new DateTime("today " . isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->end_time . ':00' : '08:00:00');
            $emailtodaydate = $emailtoday->format('d M, Y');
            
    
            // Yesterday
            $emailyesterday = isset($emaildata->emailtimes[0]) ? new Datetime($priorDate . ' ' .  $emaildata->emailtimes[0]->start_time . ':00') :  new Datetime('yesterday 08:00:00');
            $emailyesterdaydate = $emailyesterday->format('d M, Y');
            
    
            // Today
            $filenametoday = new DateTime("today " . isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->end_time . ':00' : '08:00:00');
            $filenametodaydate = $filenametoday->format('dM_');
            
    
            // Yesterday
            $filenameyesterday = isset($emaildata->emailtimes[0]) ? new Datetime($priorDate . ' ' .  $emaildata->emailtimes[0]->start_time . ':00') :  new Datetime('yesterday 08:00:00');
            $filenameyesterdaydate = $filenameyesterday->format('dM_Y');
            $bccemails = ['mohammed.saied@tamkeen-ksa.com', 'fawad@tamkeen-ksa.com', 'usman@tamkeen-ksa.com', 'ali.hassan@tamkeen-ksa.com', 'g.elzahaby@tamkeen-ksa.com', 'sameeriqbal1200@gmail.com'];
            $todata = isset($emaildata->to) ? explode(',' ,$emaildata->to) : ['usman@tamkeen-ksa.com'];
            $ccdata = isset($emaildata->cc) ? explode(',' ,$emaildata->cc) : ['usman@tamkeen-ksa.com'];
            //  $todata = ['sameeriqbal1200@gmail.com'];
            //  $ccdata = ['sameeriqbal1200@gmail.com'];
            // $bccdata = isset($emaildata->bcc) ? explode(',' ,$emaildata->bcc) : [];
            $fromdata = isset($emaildata->from) ? $emaildata->from : ['sales@tamkeenstores.com.sa'];
                        
            try {
                // die('yes');
                $success = true;
                $message = 'email send';
                Mail::send('email.invoices', $orders, function($message) use ($pdf, $emailyesterdaydate, $emailtodaydate, $filenameyesterdaydate, $filenametodaydate, $fromdata, $bccemails, $todata, $ccdata){
                    $message->to($todata);
                    $message->cc($ccdata);
                    $message->from($fromdata);
                    $message->subject('Tamkeen Stores Invoices ' . $emailyesterdaydate . ' to ' . $emailtodaydate . '.');
                    $message->attachData($pdf->output(),'Invoices_'.$filenameyesterdaydate.$filenametodaydate.'.pdf');
                });
                return response()->json(['success' => $success, 'message' => $message]);
            } catch (Exception $e) {
                // die('no');
                $success = false;
                $message = 'email not send';
                return response()->json(['success' => $success, 'message' => $message]);
            }
        }
    }
    
    public function getTesting() {
        $otp = '1234';
        $params = [
            [
               "type" => "text", 
               "text" => $otp
            ]
        ];
        $lang = 'en';
        $sms = ConditionSetup_helper::sms('+966563057534',''.$otp.' is your verification code. For your security, do not share this code.');
        // $msg = NotificationHelper::whatsappmessage("+966563057534",'test',$lang,$params,false,$otp);
        $msg = false;
        return response()->json(['message' => $msg,'sms' => $sms]);
    }
    
    public function productDataXml(Request $request){
        $data = [];
        $setdata = $request->all();
        $lang = isset($setdata['lang']) && $setdata['lang'] == 'en' ? true : false;
        if(isset($setdata['credentials']) && $setdata['credentials'] == 'TamkeenStores2@'){
            $products = Product::with('brand:id,name,name_arabic','featuredImage:id,image','productcategory:id,name,name_arabic')
            ->where('products.price','>',0)
            ->where('products.status',1)
            ->has('brand')
            ->join('livestock', function ($join) {
                $join->on('livestock.sku', '=', 'products.sku')
                     ->where('livestock.qty', '>', 0);
            })
            ->join('warehouse', function ($join) {
                $join->on('warehouse.ln_code', '=', 'livestock.city')
                     ->whereIn('warehouse.ln_code', ['OLN1', 'KUW101']);
            })
            ->get(['products.id','products.name','products.name_arabic','products.slug','products.description','products.description_arabic','products.price','products.sale_price','products.brands','products.feature_image','products.promotional_price']);
                // print_r(sizeof($products));die();
                foreach($products as $key => $productData){
                 $categoryNames = $lang ? $productData->productcategory->pluck('name')->implode(' > ') : $productData->productcategory->pluck('name_arabic')->implode(' > ');
                    $item = [
                        'g:id' => $productData->id,
                        'title' => $lang ? $productData->name : $productData->name_arabic,
                        'link' => 'https://www.tamkeenstores.com.sa/ar/product/'.$productData->slug,
                        'description' => $lang ? $productData->description : $productData->description_arabic,
                        'g:image_link' => 'https://images.tamkeenstores.com.sa/assets/new-media/'.$productData->featuredImage->image,
                        'g:price' => $productData->price.' SAR',
                        'g:sale_price' => $productData->promotional_price > 0 ? $productData->sale_price - $productData->promotional_price.' SAR' : $productData->sale_price.' SAR',
                        'g:availability' => $lang ? 'In stock' : 'في لأورق المالية',
                        'g:brand' => $lang ? $productData->brand->name : $productData->brand->name_arabic ,
                        'g:item_group_id' => $productData->id,
                        'g:condition' => $lang ? 'New' : 'جد',
                        // 'g:color' => 'Silver',
                        // 'g:gender' => 'female',
                        'g:category_path' => $categoryNames,
                        // 'g:size' => 'S',
                        // 'g:size_system' => 'EU',
                    ];
                    $data[] = $item;
            }
        }
        
        if (empty($data)) {
            return response()->json(['message' => 'Access Denied'], 404);
        }
        
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><context_node></context_node>');
        $this->arrayToXml($data, $xmlData);
        return response($xmlData->asXML(), 200)->header('Content-Type', 'application/xml');
    }
    
    private function arrayToXml($data, &$xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = is_numeric($key) ? $xmlData->addChild("item") : $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }
    
    public function apiTesting() {
        
        $tickets = InternalTicket::with('userData')->get();
        return response()->json(['data' => $tickets]);
    }

    //products data
    public function getProductsData(Request $request)
    {
        $cacheKey = 'products_data_' . md5(json_encode($request->all()));
        $cacheDuration = 86400; // 24 hours in seconds

        // Return cached response if exists (with proper headers)
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            $cachedResponse->headers->set('X-Cache', 'HIT');
            return $cachedResponse;
        }

        // Initialize response
        $response = [
            'success' => false,
            'data' => []
        ];

        // Validate credentials
        if (!($request->input('Username') === 'TamkeenStores' && 
              $request->input('Password') === 'TamkeenStoresKey')) {
                return response()->json($response);
        }

        // Database queries
        $liveStockSums = DB::table('livestock')
            ->select('sku', DB::raw('SUM(qty) as total_qty'))
            ->whereIn('city', ['ONL1', 'KUW101'])
            ->groupBy('sku');

        $products = Product::with([
                'featuredImage:id,image',
                'productcategory' => function($query) {
                    $query->where('productcategories.status', 1)
                          ->where('menu', 1)
                          ->select('productcategories.id', 'name_arabic', 'slug')
                          ->orderByDesc('productcategories.created_at');
                }
            ])
            ->joinSub($liveStockSums, 'stock', function ($join) {
                $join->on('products.sku', '=', 'stock.sku')
                     ->where('stock.total_qty', '>', 0);
            })
            ->where('products.price', '>=', 50)
            ->where('products.sale_price', '>=', 50)
            ->where('products.status', 1)
            ->select(['products.id', 'name_arabic', 'slug', 'price', 'sale_price', 'status', 'feature_image'])
            ->get();

        // Prepare URLs
        $imageBaseUrl = 'https://images.tamkeenstores.com.sa/public/assets/new-media/';
        $proBaseUrl = 'https://tamkeenstores.com.sa/ar/product/';

        // Transform products
        $productArray = $products->map(function ($product) use ($imageBaseUrl, $proBaseUrl) {
            $category = $product->productcategory->first(); 
            return [
                'id' => $product->id,
                'name_arabic' => $product->name_arabic,
                'slug' => $product->slug,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'status' => $product->status,
                'feature_image' => $product->featuredImage ? $imageBaseUrl . $product->featuredImage->image : null,
                'product_url' => $proBaseUrl . $product->slug,
                'category' => $category ? [
                    'name_arabic' => $category->name_arabic,
                    'slug' => $category->slug
                ] : null
            ];
        });

        $response = [
            'success' => true,
            'data' => $productArray
        ];
        
        $responsejson = json_encode($response);
        $data = gzencode($responsejson, 9);

        // Create and cache the response
        $cachedResponse = response($data)
            ->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Length' => strlen($data),
                'Content-Encoding' => 'gzip',
                'X-Cache' => 'MISS'
            ]);

        Cache::put($cacheKey, $cachedResponse, $cacheDuration);

        return $cachedResponse;
    }
    
}