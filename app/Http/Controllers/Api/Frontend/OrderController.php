<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderSummary;
use App\Models\OrderStatusTimeLine;
use App\Models\OrderBogo;
use App\Models\OrderFBT;
use App\Models\OrderFreeGift;
use App\Models\AbandonedCart;
use App\Models\GiftCards;
use App\Models\Product;
use App\Models\User;
use App\Models\WalletHistory;
use App\Models\WalletSetting;
use App\Models\LoyaltyPoints;
use App\Models\OrderDetailRegionalQty;
use App\Models\LiveStock;
use App\Models\Warehouse;
use App\Models\OrdersRegion;
use App\Jobs\LoyaltyPointsViewJob;
use App\Jobs\GiftVoucherViewJob;
use App\Jobs\AffiliationViewJob;
use App\Jobs\WalletViewJob;
use Webewox\Hyperpay\Hyperpay;
use Webewox\Hyperpay\Madapay;
use Webewox\Hyperpay\Applepay;
use DB;
use App\Helper\NotificationHelper;
use PDF;
use App\Mail\OrderReceived;
use Mail;
use Log;

class OrderController extends Controller
{
    
    public function testdetails(){
        //print_r($_SERVER);die;
        $order = order::where('id', '200765')->first();
        $paramsdata = [];
        $params = [
            [
               "type" => "text", 
               "text" => $order->Address->first_name
            ],
            [
               "type" => "text", 
               "text" => $order->Address->last_name
            ],
            [
               "type" => "text", 
               "text" => $order->order_no
            ],
            [
               "type" => "text", 
               "text" => $order->ordersummary()->where('type','total')->first()->price
            ],
        ];
        $header = [
            "type" => "header", 
            "parameters" => [
                [
                   "type" => "file", 
                   "url" => "https://react.tamkeenstores.com.sa/api/frontend/downloadPDF/".$order->id, 
                   "fileName" => $order->order_no .'.pdf', 
                   "text" => "Invoice" 
                ] 
            ] 
        ];
        // $response = NotificationHelper::whatsappmessage("+966506663513",'ordercreation','ar',$params,$order->id);
        $response = NotificationHelper::whatsappmessageContentImage('+966506663513','year_end_2024_for_showroom','ar',$paramsdata,'https://scontent.whatsapp.net/v/t61.29466-34/471733638_517241134667518_7157708994570939627_n.jpg?ccb=1-7&_nc_sid=8b1bef&_nc_ohc=D_7bmKgfGcoQ7kNvgHJT2Xi&_nc_zt=3&_nc_ht=scontent.whatsapp.net&edm=AH51TzQEAAAA&_nc_gid=AdCO0yHpY6EI3wj64nGF1ym&oh=01_Q5AaINdYlF4JcMd4Zb3v9dGWHZF838ckqHHCez7urM3XX2bB&oe=679493D8');
        
        print_r($response);
    }
    
    public function giftdetail($orderid){
        $order = GiftCards::where('id', $orderid)->first();
        $response = [
            'giftdata' => $order,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function hyperpaygift($orderid,$lang){
        $token = config('tamara.token');
        $rurl = config('webconfig.link').$lang.'/';
        $order = GiftCards::where('id', $orderid)->first();
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        $data = [
            'id' => $order->giftcard_no,
            'firstName' => $order->name,
            'lastname' => '',
            'address' => '',
            'zip' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'email' => $order->email,
            'amount' => str_replace(',','', $order->amount)
        ];
        $type = $order->paymentmethod;
        if($type == 'hyperpay')
            $hyperpay = new Hyperpay($rurl.'paymentstatusgift/hyperpay-response',$lang);
        elseif($type == 'applepay')
            $hyperpay = new Applepay($rurl.'paymentstatusgift/hyperpay-response',$lang);
        elseif($type == 'madapay')
            $hyperpay = new Madapay($rurl.'paymentstatusgift/hyperpay-response',$lang);
        $token = $hyperpay->token($data);
        //print_r($type);die;
        ob_start();
        $hyperpay->renderPaymentForm($order->order_no, $token);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    
    public function giftCardStoreData(Request $request) {
        $success = false;
        $data = $request->all();
        
        $chars = "0123456789";
        $giftcard_no = "GC";
        for ($i = 0; $i < 5; $i++) {
            $giftcard_no .= $chars[mt_rand(0, strlen($chars)-1)];
        }
        
        $giftcard = GiftCards::create([
            'userid' => isset($data['userid']) ? $data['userid'] : null,
            'giftcard_no' => $giftcard_no,
            'name' => isset($data['name']) ? $data['name'] : null,
            'email' => isset($data['email']) ? $data['email'] : null,
            'phonenumber' => isset($data['phonenumber']) ? $data['phonenumber'] : null,
            'myself' => $data['myself'],
            'amount' => isset($data['amount']) ? $data['amount'] : null,
            'paymentmethod' => isset($data['paymentmethod']) ? $data['paymentmethod'] : null,
            'paymentid' => null,
            'status' => 0,
        ]);
        
        if($giftcard){
            $success = true;
        }
        
        $responsee = [
            'success' => $success,
            'giftid' => $giftcard->id
        ];
        $responsejson=json_encode($responsee);
        $dataa=gzencode($responsejson,9);
        return response($dataa)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($dataa),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function giftupdatepayment($orderid,$paymentid){
        $paymentupdate = array(
            'paymentid' => $paymentid ? $paymentid : null,
            'status' => 1,
        );
        GiftCards::whereId($orderid)->update($paymentupdate);
        return response()->json(['success' => 'true', 'message' => 'Order Updated Successfully', 'id' => $orderid]);
    }
    
    public function submitOrder(Request $request) {
        $otpCode = random_int(100000, 999999);
        $walletsData  = WalletSetting::where('status',1)->first();
        $data = $request->all();
        $data['cartdata']['paymentMethod'] = $data['cartdata']['paymentMethod'] == 'tasheel' ? 'madapay' : $data['cartdata']['paymentMethod'];
        //print_r($data['cartdata']);die;
        if(!is_array($data['cartdata']))
        //echo gettype($data['cartdata']);
        $data['cartdata'] = json_decode($data['cartdata'], true);
        //delivery date
        // $newdate = isset($data['cartdata']['products'][0]['express']) && $data['cartdata']['products'][0]['express'] ? date('Y-m-d', strtotime('+1 days')) : date('Y-m-d', strtotime('+7 days'));
        // $deliveryDate = isset($data['cartdata']['deliveryDate']) ? $data['cartdata']['deliveryDate'] : $newdate;
        $defaultDate = !empty($data['cartdata']['products'][0]['express']) ? date('Y-m-d', strtotime('+1 days')) : date('Y-m-d', strtotime('+7 days'));
        $deliveryDate = !empty(trim($data['cartdata']['deliveryDate'] ?? '')) ? $data['cartdata']['deliveryDate'] : $defaultDate;
        
        // print_r($data['cartdata']);die;
        $orderData = [
            'customer_id' => $data['userid'],
            'shipping_id' => $data['cartdata']['shippingAddress'],
            'status' => $data['cartdata']['paymentMethod'] == 'cod' ? 0 : 8,
            'paymentmethod' => $data['cartdata']['paymentMethod'],
            'shippingMethod' => isset($data['cartdata']['fees']['shipping']['title']) ? $data['cartdata']['fees']['shipping']['title'] : '',
            'lang' => $data['lang'],
            'userDevice' => $data['userDevice'],
            'affiliationcode' => isset($data['affiliationCode']) ? $data['affiliationCode'] : null,
            'token' => isset($data['token']) ? $data['token'] : null,
            'mobileapp' => isset($data['mobileapp']) ? 1 : 0,
            'order_type' => isset($data['cartdata']['storeType']) ? $data['cartdata']['storeType'] : 0,
            'otp_code' => (isset($data['cartdata']['storeType']) && $data['cartdata']['storeType'] == 1) ? $otpCode : null,
            'loyalty_shipping' => isset($data['cartdata']['loyalty_shipping']) ? $data['cartdata']['loyalty_shipping'] : 0,
            'store_id' => isset($data['cartdata']['storeId']) ? $data['cartdata']['storeId'] : 0,
            //delivery_date
            'delivery_date' => $deliveryDate,
        ];
        if(isset($data['cartdata']['orderId']) && $data['cartdata']['orderId']){
            $order = Order::findOrFail($data['cartdata']['orderId']);
            if($order->status != 8){
                unset($data['cartdata']['orderId']);
            }
        }
        if(isset($data['cartdata']['orderId']) && $data['cartdata']['orderId']){
            Order::whereId($data['cartdata']['orderId'])->update($orderData);
            $order = Order::findOrFail($data['cartdata']['orderId']);
            $order->statustimeline()->delete();
            $order->details()->delete();
            $order->ordersummary()->delete();
            $order->freegifts()->detach();
            $order->fbt()->detach();
        }
        else{
            $order = Order::create($orderData);
            $order->main_date = date('Y-m-d H:i:s');
            $order->order_no = 'TKS'.$order->id;
            $order->save();
        }
        
        if($order->paymentmethod == 'madfu'){
            $number = hexdec(uniqid());
            $varray = str_split($number);
            $len = sizeof($varray);
            $code = array_slice($varray, $len-6, $len);
            $code = implode(",", $code);
            $code = str_replace(',', '', $code);
            $order->madfu_preference  = $order->order_no.'-'.$code;
            $order->save();
        }
        
        $order->statustimeline()->create([
            'status' => $order->status    
        ]);
        
        $gifts = [];
        $fbt = [];
        foreach($data['cartdata']['products'] as $key => $pro){
            $order->details()->create([
                'product_id' => $pro['id'],
                'product_name' => $pro['name'],
                'product_image' => $pro['image'],
                'unit_price' => isset($pro['bogo']) ? $pro['discounted_amount'] : $pro['price'],
                'quantity' => $pro['quantity'],
                'pre_order' => isset($pro['pre_order']) ? $pro['pre_order'] : 0,
                'expressproduct' => isset($pro['express']) && $pro['express'] ? $pro['express'] : 0,
                'express_qty' => isset($pro['express_qty']) && isset($pro['express']) && $pro['express'] ? $pro['express_qty'] : 0,
                'total' => isset($pro['bogo']) ? $pro['discounted_amount'] * $pro['quantity'] : $pro['price'] * $pro['quantity']
            ]);
            if($order->paymentmethod == 'cod'){
                $product = Product::whereId($pro['id'])->first();
                $product->quantity = $product->quantity - $pro['quantity'];
                $product->save();
            }
            if(isset($pro['gift']) && sizeof($pro['gift'])){
                foreach($pro['gift'] as $gkey => $gift){
                    $order->details()->create([
                        'product_id' => $gift['id'],
                        'product_name' => $gift['name'],
                        'product_image' => $gift['image'],
                        'unit_price' => $gift['discounted_amount'],
                        'quantity' => $gift['quantity'],
                        'total' => $gift['discounted_amount'] * $pro['quantity'],
                        'pre_order' => isset($gift['pre_order']) ? $gift['pre_order'] : 0,
                        'gift_id' => isset($gift['gift_id']) ? $gift['gift_id'] : 0,
                        
                        'expressproduct' => isset($gift['express']) && $gift['express'] ? $gift['express'] : 0,
                        'express_qty' => isset($gift['express_qty']) && isset($gift['express']) && $gift['express'] ? $gift['express_qty'] : 0,
                
                    ]);
                    if($order->paymentmethod == 'cod'){
                        $product = Product::whereId($gift['id'])->first();
                        $product->quantity = $product->quantity - $gift['quantity'];
                        $product->save();
                    }
                    $gifts[] = isset($gift['gift_id']) ? $gift['gift_id'] : 0;
                }
            }
            
            if(isset($pro['fbt']) && sizeof($pro['fbt'])){
                foreach($pro['fbt'] as $gkey => $fbt){
                    $order->details()->create([
                        'product_id' => $fbt['id'],
                        'product_name' => $fbt['name'],
                        'product_image' => $fbt['image'],
                        'unit_price' => $fbt['discounted_amount'],
                        'quantity' => $fbt['quantity'],
                        'total' => $fbt['discounted_amount'] * $pro['quantity'],
                        'fbt_id' => $fbt['fbt_id'],
                        'pre_order' => isset($fbt['pre_order']) ? $fbt['pre_order'] : 0,
                        
                        'expressproduct' => isset($fbt['express']) && $fbt['express'] ? $fbt['express'] : 0,
                        'express_qty' => isset($fbt['express_qty']) && isset($fbt['express']) && $fbt['express'] ? $fbt['express_qty'] : 0,
                
                    ]);
                    if($order->paymentmethod == 'cod'){
                        $product = Product::whereId($fbt['id'])->first();
                        $product->quantity = $product->quantity - $fbt['quantity'];
                        $product->save();
                    }
                    $fbt[] = $fbt['fbt_id'];
                }
            }
        }
        
        if($gifts){
            $order->freegifts()->attach(array_unique($gifts));
        }
        // if($fbt){
        //     $order->fbt()->attach(array_unique($fbt));
        // }
        
        $order->ordersummary()->create([
            'name' => 'subtotal',
            'name_arabic' => 'المجموع الفرعي',
            'type' => 'subtotal',
            'calculation_type' => 1,
            'price' => isset($data['subtotal']) ? $data['subtotal'] : null,
        ]);
        if(isset($data['saveamounttotal'])){
        $order->ordersummary()->create([
            'name' => 'Total Save Amount',
            'name_arabic' => 'إجمالي مبلغ التوفير',
            'type' => 'saveamounttotal',
            'calculation_type' => 0,
            'price' => isset($data['saveamounttotal']) ? $data['saveamounttotal'] : null,
        ]);
        }
        
        if(isset($data['cartdata']['fees']['shipping']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['shipping']['title'],
                'name_arabic' => $data['cartdata']['fees']['shipping']['title_arabic'],
                'type' => 'shipping',
                'calculation_type' => 1,
                'amount_id' => $data['cartdata']['fees']['shipping']['id'],
                'price' => $data['cartdata']['fees']['shipping']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['express']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['express']['title'],
                'name_arabic' => $data['cartdata']['fees']['express']['title_arabic'],
                'type' => 'express',
                'calculation_type' => 1,
                'amount_id' => $data['cartdata']['fees']['express']['id'],
                'price' => $data['cartdata']['fees']['express']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['doorstep']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['doorstep']['title'],
                'name_arabic' => $data['cartdata']['fees']['doorstep']['title_arabic'],
                'type' => 'doorstep',
                'calculation_type' => 1,
                'amount_id' => $data['cartdata']['fees']['doorstep']['id'],
                'price' => $data['cartdata']['fees']['doorstep']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['fee'])){
            foreach($data['cartdata']['fees']['fee'] as $key => $fee){
                $order->ordersummary()->create([
                    'name' => $fee['title'],
                    'name_arabic' => $fee['title_arabic'],
                    'type' => 'fee',
                    'calculation_type' => 1,
                    'amount_id' => $fee['id'],
                    'price' => $fee['amount'],
                ]);
            }
        }
        
        if(isset($data['cartdata']['fees']['wrapper']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['wrapper']['title'],
                'name_arabic' => $data['cartdata']['fees']['wrapper']['title_arabic'],
                'type' => 'wrapper',
                'calculation_type' => 1,
                'price' => $data['cartdata']['fees']['wrapper']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['installation']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['installation']['title'],
                'name_arabic' => $data['cartdata']['fees']['installation']['title_arabic'],
                'type' => 'installation',
                'calculation_type' => 1,
                'price' => $data['cartdata']['fees']['installation']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['loyalty']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['loyalty']['title'],
                'name_arabic' => $data['cartdata']['loyalty']['title_arabic'],
                'type' => 'loyalty',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['loyalty']['id'],
                'price' => $data['cartdata']['loyalty']['amount'],
            ]);
        }


        if(isset($data['cartdata']['discounts']['coupon']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['discounts']['coupon']['title'],
                'name_arabic' => $data['cartdata']['discounts']['coupon']['title_arabic'],
                'type' => 'discount',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['discounts']['coupon']['id'],
                'price' => $data['cartdata']['discounts']['coupon']['amount'],
            ]);
        }
        if(isset($data['cartdata']['discounts']['additionalDiscount']) && sizeof($data['cartdata']['discounts']['additionalDiscount']) >= 1){
            $order->ordersummary()->create([
                'name' => isset($data['cartdata']['discounts']['additionalDiscount']['title']) ?  $data['cartdata']['discounts']['additionalDiscount']['title'] : null,
                'name_arabic' => isset($data['cartdata']['discounts']['additionalDiscount']['title_arabic']) ? $data['cartdata']['discounts']['additionalDiscount']['title_arabic'] : null,
                'type' => 'discount_rule',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['discounts']['additionalDiscount']['id'],
                'price' => $data['cartdata']['discounts']['additionalDiscount']['amount'],
            ]);
        }
        if(sizeof($data['cartdata']['discounts']['discuountRules'])){
            foreach($data['cartdata']['discounts']['discuountRules'] as $key => $discount){
                $order->ordersummary()->create([
                    'name' => $discount['title'],
                    'name_arabic' => $discount['title_arabic'],
                    'type' => 'discount_rule',
                    'calculation_type' => 0,
                    'amount_id' => $discount['id'],
                    'price' => $discount['amount'],
                ]);
                
                if($data['cartdata']['paymentMethod'] == 'cod' && $walletsData){
                    $userPlus = User::where('id', $data['userid'])->first();
                    $currentAmountPlus = $userPlus->amount + str_replace(",","",$discount['amount']);
                    $userPlus->amount = $currentAmountPlus;
                    $userPlus->save();
                    
                    if($userPlus){
                        $walletHistoryPlus = WalletHistory::create([
                            'user_id' => $userPlus->id,
                            'order_id' => $discount['id'],
                            'type' => 1,
                            'amount' => $discount['amount'],
                            'description' => $discount['title_arabic'],
                            'description_arabic' => $discount['title_arabic'],
                            'wallet_type' => 'discount_rule',
                            'title' => $discount['title'],
                            'title_arabic' => $discount['title_arabic'],
                            'current_amount' => $currentAmountPlus,
                            'status' => 0,
                        ]);
                    }
                    
                    
                    $userMinus = User::where('id', $data['userid'])->first();
                    $currentAmountMinus = $userMinus->amount - str_replace(",","",$discount['amount']);
                    $userMinus->amount = $currentAmountMinus;
                    $userMinus->save();
                    
                    if($userMinus){
                        $walletHistoryMinus = WalletHistory::create([
                            'user_id' => $userMinus->id,
                            'order_id' => $discount['id'],
                            'type' => 0,
                            'amount' => $discount['amount'],
                            'description' => $discount['title_arabic'],
                            'description_arabic' => $discount['title_arabic'],
                            'wallet_type' => 'discount_rule',
                            'title' => $discount['title'],
                            'title_arabic' => $discount['title_arabic'],
                            'current_amount' => $currentAmountMinus,
                            'status' => 0,
                        ]);
                    }
                    
                }
            }
        }
        
        
        $order->ordersummary()->create([
            'name' => 'total',
            'name_arabic' => 'المجموع',
            'type' => 'total',
            'calculation_type' => 1,
            'price' => isset($data['total']) ? $data['total'] : null,
        ]);
        //pick up from store msg
        // if($order->order_type == 1){
        //     $paramsPickUpFromStore = [
        //         [
        //             "type" => "text", 
        //             "text" => optional($order->Address)->first_name . " " . optional($order->Address)->last_name
        //         ],
        //         [
        //             "type" => "text", 
        //             "text" => $order->order_no ?? ''
        //         ],
        //         [
        //             "type" => "text", 
        //             "text" => optional($order->ordersummary()->where('type','total')->first())->price ?? ''
        //         ],
        //         [
        //             "type" => "text", 
        //             "text" => $order->lang == 'ar' 
        //                 ? optional(optional($order->warehouse)->showroomData)->name_arabic 
        //                 : optional(optional($order->warehouse)->showroomData)->name
        //         ],
        //         [
        //             "type" => "text", 
        //             "text" => optional(optional($order->warehouse)->showroomData)->direction_button ?? ''
        //         ],
        //     ];
        //     $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'pickup_from_store',$order->lang,$paramsPickUpFromStore,$order->id);
        //     $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
        //     $phone = str_replace("_","",$phone);
        //     $responsesms = false;
        //     // pickup from store
        //     $customer_name = optional($order->Address)->first_name . ' ' . optional($order->Address)->last_name;
        //     $order_number = $order->order_no ?? '';
        //     $amount_of_order = optional($order->ordersummary()->where('type', 'total')->first())->price ?? '';
        //     $otp_code = $order->otp_code ?? ''; // Ensure this is defined earlier
        //     $showroom_name = $order->lang == 'ar' ? optional(optional($order->warehouse)->showroomData)->name_arabic : optional(optional($order->warehouse)->showroomData)->name;
        //     $showroom_location = optional(optional($order->warehouse)->showroomData)->direction_button ?? '';
        //     if ($order->lang == 'en') {
        //         $message = "Dear {$customer_name},\n\n";
        //         $message .= "Thank you for choosing Tamkeen Stores.\n";
        //         $message .= "Your order {$order_number}, with a total amount of {$amount_of_order}, has been successfully confirmed. ";
        //         $message .= "You will be notified once it is ready for pickup.\n\n";
        //         // $message .= "When collecting your order, please provide the following OTP code for verification: {$otp_code}\n\n";
        //         $message .= "Pickup Location:\n{$showroom_name}\n{$showroom_location}\n\n";
        //         $message .= "Thank you,\nTamkeen Stores";

        //         $responsesms = NotificationHelper::sms($phone, $message);

        //     } else {
        //         $message1 = "عزيز/عزيزتي {$customer_name},\n\n";
        //         $message2 = "شكرًا لاختياك متاجر تين.\n";
        //         $message3 = "تم أي طلبك قم {$order_number} بمبلغ إجملي {$amount_of_order}. سيتم إشرك عند جاز الطلب للسم.\n\n";
        //         // $message4 = "ند استلام اطل، يُرجى تدي رمز التق التالي: {$otp_code}\n\n";
        //         $message5 = "وع الاستلام:\n{$showroom_name}\n{$showroom_location}\n\n";
        //         $message6 = "شا لك،\nمتاجر ك";

        //         // Combine messages
        //         $message = $message1 . $message2 . $message3 . $message4 . $message5 . $message6;
        //         $responsesms = NotificationHelper::sms($phone, $message);
        //     }
        // }
        
        
        if($data['cartdata']['paymentMethod'] == 'cod' && $walletsData){
            $userPlus = User::where('id', $data['userid'])->first();
            $currentAmountPlus = $userPlus->amount + str_replace(",","",$data['total']);
            $userPlus->amount = $currentAmountPlus;
            $userPlus->save();
            
            if($userPlus){
                $walletHistoryPlus = WalletHistory::create([
                    'user_id' => $userPlus->id,
                    'order_id' => $order->id,
                    'type' => 1,
                    'amount' => $data['total'],
                    'description' => $order->order_no,
                    'description_arabic' => $order->order_no,
                    'wallet_type' => 'orderfee',
                    'title' => $order->order_no,
                    'title_arabic' => $order->order_no,
                    'current_amount' => $currentAmountPlus,
                    'status' => 0,
                ]);
            }
            
            
            $userMinus = User::where('id', $data['userid'])->first();
            $currentAmountMinus = $userMinus->amount - str_replace(",","",$data['total']);
            $userMinus->amount = $currentAmountMinus;
            $userMinus->save();
            
            if($userMinus){
                $walletHistoryMinus = WalletHistory::create([
                    'user_id' => $userMinus->id,
                    'order_id' => $order->id,
                    'type' => 0,
                    'amount' => $data['total'],
                    'description' => $order->order_no,
                    'description_arabic' => $order->order_no,
                    'wallet_type' => 'orderfee',
                    'title' => $order->order_no,
                    'title_arabic' => $order->order_no,
                    'current_amount' => $currentAmountMinus,
                    'status' => 0,
                ]);
            }
            
            WalletViewJob::dispatch($order->id);
        }
        
        
        $redirection = [];
        
        if($data['cartdata']['paymentMethod'] == 'cod'){
            $params = [
                [
                  "type" => "text", 
                  "text" => $order->Address->first_name
                ],
                [
                  "type" => "text", 
                  "text" => $order->Address->last_name
                ],
                [
                  "type" => "text", 
                  "text" => $order->order_no
                ],
                [
                  "type" => "text", 
                  "text" => $order->ordersummary()->where('type','total')->first()->price
                ],
            ];
            $header = [
                "type" => "header", 
                "parameters" => [
                    [
                       "type" => "file", 
                       "url" => "https://react.tamkeenstores.com.sa/api/frontend/downloadPDF/".$order->id, 
                       "fileName" => $order->order_no .'.pdf', 
                       "text" => "Invoice" 
                    ] 
                ] 
            ];
            $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'ordercreation',$order->lang,$params,$order->id);
            $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
            $phone = str_replace("_","",$phone);
            
            if ($order->lang == 'en') {
                 $response = NotificationHelper::sms($phone,'Dear '.$order->Address->first_name.' '.$order->Address->last_name.'

Congratulation! Your order '.$order->order_no.' has been processed successfully.

You have chosen cash on delivery the total amount is SR '.$order->ordersummary()->where('type','total')->first()->price. ', which you have to pay at the time of delivery to our representative.

Thanks for shopping with TamkeenStores.');
            }
//             else{
//                  $response = NotificationHelper::sms($phone, 'ز '.$order->Address->first_name.' '.$order->Address->last_name.'

// هن أن  م تا معل ل '.$order->order_no.' با.

// ق اخرت ع عند س؛ البلغ لج هو '.$order->ordersummary()->where('type','total')->first()->price. ' يال ود، والذ ي عه ن الستلام  منوبا.

// شرًا ت مع ك او');
//             }
            else {
                if($order->type == 0){
                    $fullName = $order->Address->first_name . ' ' . $order->Address->last_name;
                    $orderNo = $order->order_no;
                    $amount = $order->ordersummary()->where('type', 'total')->first()->price;
                
                    // Arabic message parts
                    $greeting = "عزي $fullName ،";
                    $thankYou = "شرا تسوقك ر مة كي الإلترنة.";
                    $orderInfo = "م اتلام لب رق: $orderNo  بمل إالي قره: $amount ال عدي.";
                    $contact = "لمزد ن لمعومات رجى لتوص ا ر رم الحد: 8002444.";
                    
                    // Combine message
                    $message = "$greeting\n\n$thankYou\n$orderInfo\n\n$contact";

                
                    $response = NotificationHelper::sms($phone, $message);
                } else {
                    $paramsPickUpFromStore = [
                        [
                            "type" => "text", 
                            "text" => optional($order->Address)->first_name . " " . optional($order->Address)->last_name
                        ],
                        [
                            "type" => "text", 
                            "text" => $order->order_no ?? ''
                        ],
                        [
                            "type" => "text", 
                            "text" => optional($order->ordersummary()->where('type','total')->first())->price ?? ''
                        ],
                        [
                            "type" => "text", 
                            "text" => $order->lang == 'ar' 
                                ? optional(optional($order->warehouse)->showroomData)->name_arabic 
                                : optional(optional($order->warehouse)->showroomData)->name
                        ],
                        [
                            "type" => "text", 
                            "text" => optional(optional($order->warehouse)->showroomData)->direction_button ?? ''
                        ],
                    ];
                    $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'pickup_from_store',$order->lang,$paramsPickUpFromStore,$order->id);
                    $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
                    $phone = str_replace("_","",$phone);
                    $responsesms = false;
                    // pickup from store
                    $customer_name = optional($order->Address)->first_name . ' ' . optional($order->Address)->last_name;
                    $order_number = $order->order_no ?? '';
                    $amount_of_order = optional($order->ordersummary()->where('type', 'total')->first())->price ?? '';
                    $otp_code = $order->otp_code ?? ''; // Ensure this is defined earlier
                    $showroom_name = $order->lang == 'ar' ? optional(optional($order->warehouse)->showroomData)->name_arabic : optional(optional($order->warehouse)->showroomData)->name;
                    $showroom_location = optional(optional($order->warehouse)->showroomData)->direction_button ?? '';
                    if ($order->lang == 'en') {
                        $message = "Dear {$customer_name},\n\n";
                        $message .= "Thank you for choosing Tamkeen Stores.\n";
                        $message .= "Your order {$order_number}, with a total amount of {$amount_of_order}, has been successfully confirmed. ";
                        $message .= "You will be notified once it is ready for pickup.\n\n";
                        $message .= "When collecting your order, please provide the following OTP code for verification: {$otp_code}\n\n";
                        $message .= "Pickup Location:\n{$showroom_name}\n{$showroom_location}\n\n";
                        $message .= "Thank you,\nTamkeen Stores";

                        $responsesms = NotificationHelper::sms($phone, $message);

                    } else {
                        $message1 = "عززي/عزيزتي {$customer_name},\n\n";
                        $message2 = "ا لاختيارك ار تمكين.\n";
                        $message3 = "تم أكد طلبك رقم {$order_number} بمبلغ إجال {$amount_of_order}. سيتم شعر عند از الطلب لللام.\n\n";
                        $message4 = "عن استلام لب، يُرجى قدي رمز التح التالي: {$otp_code}\n\n";
                        $message5 = "موقع اتلا:\n{$showroom_name}\n{$showroom_location}\n\n";
                        $message6 = "شرً لك،\nمتاجر من";

                        // Combine messages
                        $message = $message1 . $message2 . $message3 . $message4 . $message5 . $message6;
                        $responsesms = NotificationHelper::sms($phone, $message);
                    }
                }   
            }

            
            // Mail::to($order->UserDetail->email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
            if($order->token){
                // if ($order->lang == 'en') {
                //     $datanotifi = NotificationHelper::global_notification([$order->token], 'Congratulations ', 'Your order has been placed its under review.', '',$order->userDevice);
                // }
                // else{
                    $datanotifi = NotificationHelper::global_notification([$order->token], 'برو ', ' م ض ل و ق لماج.', '',$order->userDevice);
                // }
            }
            $orderid = $order->id;
            LoyaltyPointsViewJob::dispatch($orderid);
            GiftVoucherViewJob::dispatch($orderid);
            AffiliationViewJob::dispatch($orderid);
            // WalletViewJob::dispatch($orderid);
            Mail::to($order->UserDetail->email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
            //print_r('testing');die;
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/congratulations/'.$order->id, 'cod' => true];
        }
        if($data['cartdata']['paymentMethod'] == 'hyperpay'){
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/card/'.$order->id, 'id' => $order->id];
        }
        if($data['cartdata']['paymentMethod'] == 'madapay'){
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/card/'.$order->id, 'id' => $order->id];
        }
        if($data['cartdata']['paymentMethod'] == 'applepay'){
            $link = 'https://partners.tamkeenstores.com.sa/api/frontend/hyperpay/'.$order->id.'/'.$data['lang'];
            $redirection = ['type'=> 'external', 'link' => $link];
            // $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/card/'.$order->id, 'id' => $order->id];
        }
        if($data['cartdata']['paymentMethod'] == 'tasheel'){
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/tasheel/'.$order->id, 'id' => $order->id];
        }
        
        if($data['cartdata']['paymentMethod'] == 'tabby'){
            $link = $this->tabby($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        
        if($data['cartdata']['paymentMethod'] == 'madfu'){
            $link = $this->madfu($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
            // $redirection = $link;
        }
        
        if($data['cartdata']['paymentMethod'] == 'mispay'){
            $link = $this->mispay($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        
        if($data['cartdata']['paymentMethod'] == 'clickpay' || $data['cartdata']['paymentMethod'] == 'clickpay_applepay'){
            $link = $this->clickpay($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        
        if($data['cartdata']['paymentMethod'] == 'tamara'){
            $link = $this->tamara($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        if($data['cartdata']['paymentMethod'] == 'loyalty'){
            $this->updatepayment($order->id, 'loyalty');
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/congratulations/'.$order->id, 'cod' => true];
        }
        //print_r($redirection);die;
        // abandoned cart remove
        $check = AbandonedCart::where('user_id', $data['userid'])->first();
        if($check) {
            $check->delete();
        }
        // abandoned cart remove
        
        $responsee = [
            //'data' => $data,
            'id' => $order->id,
            'order_id' => $order->id,
            'redirection' => $redirection
        ];
        $responsejson=json_encode($responsee);
        $dataa=gzencode($responsejson,9);
        return response($dataa)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($dataa),
            'Content-Encoding' => 'gzip'
        ]);
    }


    // new duplicate order api for local
    public function submitOrderDuplicate(Request $request) {
        $otpCode = random_int(100000, 999999);
        $walletsData  = WalletSetting::where('status',1)->first();
        $data = $request->all();
        $data['cartdata']['paymentMethod'] = $data['cartdata']['paymentMethod'] == 'tasheel' ? 'madapay' : $data['cartdata']['paymentMethod'];
        //print_r($data['cartdata']);die;
        if(!is_array($data['cartdata']))
        //echo gettype($data['cartdata']);
        $data['cartdata'] = json_decode($data['cartdata'], true);
        //delivery date
        // $newdate = isset($data['cartdata']['products'][0]['express']) && $data['cartdata']['products'][0]['express'] ? date('Y-m-d', strtotime('+1 days')) : date('Y-m-d', strtotime('+7 days'));
        // $deliveryDate = isset($data['cartdata']['deliveryDate']) ? $data['cartdata']['deliveryDate'] : $newdate;
        $defaultDate = !empty($data['cartdata']['products'][0]['express']) ? date('Y-m-d', strtotime('+1 days')) : date('Y-m-d', strtotime('+7 days'));
        $deliveryDate = !empty(trim($data['cartdata']['deliveryDate'] ?? '')) ? $data['cartdata']['deliveryDate'] : $defaultDate;
        
        // print_r($data['cartdata']);die;
        $orderData = [
            'customer_id' => $data['userid'],
            'shipping_id' => $data['cartdata']['shippingAddress'],
            'status' => $data['cartdata']['paymentMethod'] == 'cod' ? 0 : 8,
            'paymentmethod' => $data['cartdata']['paymentMethod'],
            'shippingMethod' => isset($data['cartdata']['fees']['shipping']['title']) ? $data['cartdata']['fees']['shipping']['title'] : '',
            'lang' => $data['lang'],
            'userDevice' => $data['userDevice'],
            'affiliationcode' => isset($data['affiliationCode']) ? $data['affiliationCode'] : null,
            'token' => isset($data['token']) ? $data['token'] : null,
            'mobileapp' => isset($data['mobileapp']) ? 1 : 0,
            'order_type' => isset($data['cartdata']['storeType']) ? $data['cartdata']['storeType'] : 0,
            'otp_code' => (isset($data['cartdata']['storeType']) && $data['cartdata']['storeType'] == 1) ? $otpCode : null,
            'loyalty_shipping' => isset($data['cartdata']['loyalty_shipping']) ? $data['cartdata']['loyalty_shipping'] : 0,
            'store_id' => isset($data['cartdata']['storeId']) ? $data['cartdata']['storeId'] : 0,
            //delivery_date
            'delivery_date' => $deliveryDate,
            
        ];
        if(isset($data['cartdata']['orderId']) && $data['cartdata']['orderId']){
            $order = Order::findOrFail($data['cartdata']['orderId']);
            if($order->status != 8){
                unset($data['cartdata']['orderId']);
            }
        }
        if(isset($data['cartdata']['orderId']) && $data['cartdata']['orderId']){
            Order::whereId($data['cartdata']['orderId'])->update($orderData);
            $order = Order::findOrFail($data['cartdata']['orderId']);
            $order->statustimeline()->delete();
            $order->details()->delete();
            $order->ordersummary()->delete();
            $order->freegifts()->detach();
            $order->fbt()->detach();
        }
        else{
            $order = Order::create($orderData);
            $order->main_date = date('Y-m-d H:i:s');
            $order->order_no = 'TKS'.$order->id;
            $order->save();
        }
        
        if($order->paymentmethod == 'madfu'){
            $number = hexdec(uniqid());
            $varray = str_split($number);
            $len = sizeof($varray);
            $code = array_slice($varray, $len-6, $len);
            $code = implode(",", $code);
            $code = str_replace(',', '', $code);
            $order->madfu_preference  = $order->order_no.'-'.$code;
            $order->save();
        }
        
        $order->statustimeline()->create([
            'status' => $order->status    
        ]);
        
        $gifts = [];
        $fbt = [];
        $directCashbacks = [];
        foreach($data['cartdata']['products'] as $key => $pro){
            $order->details()->create([
                'product_id' => $pro['id'],
                'product_name' => $pro['name'],
                'product_image' => $pro['image'],
                'unit_price' => isset($pro['bogo']) ? $pro['discounted_amount'] : $pro['price'],
                'quantity' => $pro['quantity'],
                'pre_order' => isset($pro['pre_order']) ? $pro['pre_order'] : 0,
                'expressproduct' => isset($pro['express']) && $pro['express'] ? $pro['express'] : 0,
                'express_qty' => isset($pro['express_qty']) && isset($pro['express']) && $pro['express'] ? $pro['express_qty'] : 0,
                'total' => isset($pro['bogo']) ? $pro['discounted_amount'] * $pro['quantity'] : $pro['price'] * $pro['quantity']
            ]);

            // Store direct cashback data for later
            if(isset($pro['directcashback']) && $pro['directcashback'] >= 1) {
                $directCashbacks[] = [
                    'product_id' => $pro['id'],
                    'title' => $pro['directcashback_title'],
                    'title_arabic' => $pro['directcashback_title_arabic'],
                    'amount' => $pro['quantity'] * $pro['directcashback']
                ];
            }


            if($order->paymentmethod == 'cod'){
                $product = Product::whereId($pro['id'])->first();
                $product->quantity = $product->quantity - $pro['quantity'];
                $product->save();
            }
            if(isset($pro['gift']) && sizeof($pro['gift'])){
                foreach($pro['gift'] as $gkey => $gift){
                    $order->details()->create([
                        'product_id' => $gift['id'],
                        'product_name' => $gift['name'],
                        'product_image' => $gift['image'],
                        'unit_price' => $gift['discounted_amount'],
                        'quantity' => $gift['quantity'],
                        'total' => $gift['discounted_amount'] * $pro['quantity'],
                        'pre_order' => isset($gift['pre_order']) ? $gift['pre_order'] : 0,
                        'gift_id' => isset($gift['gift_id']) ? $gift['gift_id'] : (0 . $gift['id']),
                        
                        'expressproduct' => isset($gift['express']) && $gift['express'] ? $gift['express'] : 0,
                        'express_qty' => isset($gift['express_qty']) && isset($gift['express']) && $gift['express'] ? $gift['express_qty'] : 0,
                
                    ]);
                    if($order->paymentmethod == 'cod'){
                        $product = Product::whereId($gift['id'])->first();
                        $product->quantity = $product->quantity - $gift['quantity'];
                        $product->save();
                    }
                    $gifts[] = isset($gift['gift_id']) ? $gift['gift_id'] : (0 . $gift['id']);
                }
            }
            
            if(isset($pro['fbt']) && sizeof($pro['fbt'])){
                foreach($pro['fbt'] as $gkey => $fbt){
                    $order->details()->create([
                        'product_id' => $fbt['id'],
                        'product_name' => $fbt['name'],
                        'product_image' => $fbt['image'],
                        'unit_price' => $fbt['discounted_amount'],
                        'quantity' => $fbt['quantity'],
                        'total' => $fbt['discounted_amount'] * $pro['quantity'],
                        'fbt_id' => $fbt['fbt_id'],
                        'pre_order' => isset($fbt['pre_order']) ? $fbt['pre_order'] : 0,
                        
                        'expressproduct' => isset($fbt['express']) && $fbt['express'] ? $fbt['express'] : 0,
                        'express_qty' => isset($fbt['express_qty']) && isset($fbt['express']) && $fbt['express'] ? $fbt['express_qty'] : 0,
                
                    ]);
                    if($order->paymentmethod == 'cod'){
                        $product = Product::whereId($fbt['id'])->first();
                        $product->quantity = $product->quantity - $fbt['quantity'];
                        $product->save();
                    }
                    $fbt[] = $fbt['fbt_id'];
                }
            }
        }
        
        if($gifts){
            $order->freegifts()->attach(array_unique($gifts));
        }
        // if($fbt){
        //     $order->fbt()->attach(array_unique($fbt));
        // }
        
        $order->ordersummary()->create([
            'name' => 'subtotal',
            'name_arabic' => 'المجموع الفرعي',
            'type' => 'subtotal',
            'calculation_type' => 1,
            'price' => isset($data['subtotal']) ? $data['subtotal'] : null,
        ]);
        if(isset($data['saveamounttotal'])){
        $order->ordersummary()->create([
            'name' => 'Total Save Amount',
            'name_arabic' => 'إجمالي مبلغ التوفير',
            'type' => 'saveamounttotal',
            'calculation_type' => 0,
            'price' => isset($data['saveamounttotal']) ? $data['saveamounttotal'] : null,
        ]);
        }
        
        if(isset($data['cartdata']['fees']['shipping']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['shipping']['title'],
                'name_arabic' => $data['cartdata']['fees']['shipping']['title_arabic'],
                'type' => 'shipping',
                'calculation_type' => 1,
                'amount_id' => $data['cartdata']['fees']['shipping']['id'],
                'price' => $data['cartdata']['fees']['shipping']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['express']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['express']['title'],
                'name_arabic' => $data['cartdata']['fees']['express']['title_arabic'],
                'type' => 'express',
                'calculation_type' => 1,
                'amount_id' => $data['cartdata']['fees']['express']['id'],
                'price' => $data['cartdata']['fees']['express']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['doorstep']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['doorstep']['title'],
                'name_arabic' => $data['cartdata']['fees']['doorstep']['title_arabic'],
                'type' => 'doorstep',
                'calculation_type' => 1,
                'amount_id' => $data['cartdata']['fees']['doorstep']['id'],
                'price' => $data['cartdata']['fees']['doorstep']['amount'],
            ]);
        }

        if(isset($data['cartdata']['loyalty']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['loyalty']['title'],
                'name_arabic' => $data['cartdata']['loyalty']['title_arabic'],
                'type' => 'loyalty',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['loyalty']['id'],
                'price' => $data['cartdata']['loyalty']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['fee'])){
            foreach($data['cartdata']['fees']['fee'] as $key => $fee){
                $order->ordersummary()->create([
                    'name' => $fee['title'],
                    'name_arabic' => $fee['title_arabic'],
                    'type' => 'fee',
                    'calculation_type' => 1,
                    'amount_id' => $fee['id'],
                    'price' => $fee['amount'],
                ]);
            }
        }
        
        if(isset($data['cartdata']['fees']['wrapper']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['wrapper']['title'],
                'name_arabic' => $data['cartdata']['fees']['wrapper']['title_arabic'],
                'type' => 'wrapper',
                'calculation_type' => 1,
                'price' => $data['cartdata']['fees']['wrapper']['amount'],
            ]);
        }
        
        if(isset($data['cartdata']['fees']['installation']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['fees']['installation']['title'],
                'name_arabic' => $data['cartdata']['fees']['installation']['title_arabic'],
                'type' => 'installation',
                'calculation_type' => 1,
                'price' => $data['cartdata']['fees']['installation']['amount'],
            ]);
        }
        
        
        if(isset($data['cartdata']['discounts']['coupon']['title'])){
            $order->ordersummary()->create([
                'name' => $data['cartdata']['discounts']['coupon']['title'],
                'name_arabic' => $data['cartdata']['discounts']['coupon']['title_arabic'],
                'type' => 'discount',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['discounts']['coupon']['id'],
                'price' => $data['cartdata']['discounts']['coupon']['amount'],
            ]);
        }

        if(isset($data['cartdata']['discounts']['additionalDiscount']) && sizeof($data['cartdata']['discounts']['additionalDiscount']) >= 1){
            $order->ordersummary()->create([
                'name' => isset($data['cartdata']['discounts']['additionalDiscount']['title']) ?  $data['cartdata']['discounts']['additionalDiscount']['title'] : null,
                'name_arabic' => isset($data['cartdata']['discounts']['additionalDiscount']['title_arabic']) ? $data['cartdata']['discounts']['additionalDiscount']['title_arabic'] : null,
                'type' => 'discount_rule',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['discounts']['additionalDiscount']['id'],
                'price' => $data['cartdata']['discounts']['additionalDiscount']['amount'],
            ]);
        }
        if(isset($data['cartdata']['discounts']['additionalDiscount']) && sizeof($data['cartdata']['discounts']['additionalDiscount']) >= 1){
            $order->ordersummary()->create([
                'name' => isset($data['cartdata']['discounts']['additionalDiscount']['title']) ?  $data['cartdata']['discounts']['additionalDiscount']['title'] : null,
                'name_arabic' => isset($data['cartdata']['discounts']['additionalDiscount']['title_arabic']) ? $data['cartdata']['discounts']['additionalDiscount']['title_arabic'] : null,
                'type' => 'discount_rule',
                'calculation_type' => 0,
                'amount_id' => $data['cartdata']['discounts']['additionalDiscount']['id'],
                'price' => $data['cartdata']['discounts']['additionalDiscount']['amount'],
            ]);
        }


        // Now create individual direct discount entries after subtotal
        if(count($directCashbacks) >= 1) {
            foreach ($directCashbacks as $cashback) {
                $order->ordersummary()->create([
                    'name' => $cashback['title'],
                    'name_arabic' => $cashback['title_arabic'],
                    'type' => 'direct_discount',
                    'calculation_type' => 0,
                    'amount_id' => $cashback['product_id'],
                    'price' => $cashback['amount']
                ]);
            }
        }

        if(sizeof($data['cartdata']['discounts']['discuountRules'])){
            foreach($data['cartdata']['discounts']['discuountRules'] as $key => $discount){
                $order->ordersummary()->create([
                    'name' => $discount['title'],
                    'name_arabic' => $discount['title_arabic'],
                    'type' => 'discount_rule',
                    'calculation_type' => 0,
                    'amount_id' => $discount['id'],
                    'price' => $discount['amount'],
                ]);
                
                if($data['cartdata']['paymentMethod'] == 'cod' && $walletsData){
                    $userPlus = User::where('id', $data['userid'])->first();
                    $currentAmountPlus = $userPlus->amount + str_replace(",","",$discount['amount']);
                    $userPlus->amount = $currentAmountPlus;
                    $userPlus->save();
                    
                    if($userPlus){
                        $walletHistoryPlus = WalletHistory::create([
                            'user_id' => $userPlus->id,
                            'order_id' => $discount['id'],
                            'type' => 1,
                            'amount' => $discount['amount'],
                            'description' => $discount['title_arabic'],
                            'description_arabic' => $discount['title_arabic'],
                            'wallet_type' => 'discount_rule',
                            'title' => $discount['title'],
                            'title_arabic' => $discount['title_arabic'],
                            'current_amount' => $currentAmountPlus,
                            'status' => 0,
                        ]);
                    }
                    
                    
                    $userMinus = User::where('id', $data['userid'])->first();
                    $currentAmountMinus = $userMinus->amount - str_replace(",","",$discount['amount']);
                    $userMinus->amount = $currentAmountMinus;
                    $userMinus->save();
                    
                    if($userMinus){
                        $walletHistoryMinus = WalletHistory::create([
                            'user_id' => $userMinus->id,
                            'order_id' => $discount['id'],
                            'type' => 0,
                            'amount' => $discount['amount'],
                            'description' => $discount['title_arabic'],
                            'description_arabic' => $discount['title_arabic'],
                            'wallet_type' => 'discount_rule',
                            'title' => $discount['title'],
                            'title_arabic' => $discount['title_arabic'],
                            'current_amount' => $currentAmountMinus,
                            'status' => 0,
                        ]);
                    }
                    
                }
            }
        }
        
        
        $order->ordersummary()->create([
            'name' => 'total',
            'name_arabic' => 'المجموع',
            'type' => 'total',
            'calculation_type' => 1,
            'price' => isset($data['total']) ? $data['total'] : null,
        ]);

        if($order->order_type == 1){
            $paramsPickUpFromStore = [
                    [
                        "type" => "text", 
                        "text" => optional($order->Address)->first_name . " " . optional($order->Address)->last_name
                    ],
                    [
                        "type" => "text", 
                        "text" => $order->order_no ?? ''
                    ],
                    [
                        "type" => "text", 
                        "text" => optional($order->ordersummary()->where('type','total')->first())->price ?? ''
                    ],
                    [
                        "type" => "text", 
                        "text" => $order->lang == 'ar' 
                            ? optional(optional($order->warehouse)->showroomData)->name_arabic 
                            : optional(optional($order->warehouse)->showroomData)->name
                    ],
                    [
                        "type" => "text", 
                        "text" => optional(optional($order->warehouse)->showroomData)->direction_button ?? ''
                    ],
                ];
                $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'pickup_from_store',$order->lang,$paramsPickUpFromStore,$order->id);
                $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
                $phone = str_replace("_","",$phone);
                $responsesms = false;
                // pickup from store
                $customer_name = optional($order->Address)->first_name . ' ' . optional($order->Address)->last_name;
                $order_number = $order->order_no ?? '';
                $amount_of_order = optional($order->ordersummary()->where('type', 'total')->first())->price ?? '';
                $otp_code = $order->otp_code ?? ''; // Ensure this is defined earlier
                $showroom_name = $order->lang == 'ar' ? optional(optional($order->warehouse)->showroomData)->name_arabic : optional(optional($order->warehouse)->showroomData)->name;
                $showroom_location = optional(optional($order->warehouse)->showroomData)->direction_button ?? '';
                if ($order->lang == 'en') {
                    $message = "Dear {$customer_name},\n\n";
                    $message .= "Thank you for choosing Tamkeen Stores.\n";
                    $message .= "Your order {$order_number}, with a total amount of {$amount_of_order}, has been successfully confirmed. ";
                    $message .= "You will be notified once it is ready for pickup.\n\n";
                    // $message .= "When collecting your order, please provide the following OTP code for verification: {$otp_code}\n\n";
                    $message .= "Pickup Location:\n{$showroom_name}\n{$showroom_location}\n\n";
                    $message .= "Thank you,\nTamkeen Stores";

                    $responsesms = NotificationHelper::sms($phone, $message);

                } else {
                    $message1 = "عزيزي/عت {$customer_name},\n\n";
                    $message2 = "شكرًا لاختر متاجر تمين.\n";
                    $message3 = " تأكيد طلك رقم {$order_number} لغ إجمالي {$amount_of_order}. سيم إشعارك ع جاهزية لطب للاستلام.\n\n";
                    // $message4 = "ع استلام للب، يُرجى قم رمز التحق التالي: {$otp_code}\n\n";
                    $message5 = "موقع اسلام:\n{$showroom_name}\n{$showroom_location}\n\n";
                    $message6 = "ا لك،\nمتاجر تك";

                    // Combine messages
                    $message = $message1 . $message2 . $message3 . $message4 . $message5 . $message6;
                    $responsesms = NotificationHelper::sms($phone, $message);
            }
        }
        
        
        if($data['cartdata']['paymentMethod'] == 'cod' && $walletsData){
            $userPlus = User::where('id', $data['userid'])->first();
            $currentAmountPlus = $userPlus->amount + str_replace(",","",$data['total']);
            $userPlus->amount = $currentAmountPlus;
            $userPlus->save();
            
            if($userPlus){
                $walletHistoryPlus = WalletHistory::create([
                    'user_id' => $userPlus->id,
                    'order_id' => $order->id,
                    'type' => 1,
                    'amount' => $data['total'],
                    'description' => $order->order_no,
                    'description_arabic' => $order->order_no,
                    'wallet_type' => 'orderfee',
                    'title' => $order->order_no,
                    'title_arabic' => $order->order_no,
                    'current_amount' => $currentAmountPlus,
                    'status' => 0,
                ]);
            }
            
            
            $userMinus = User::where('id', $data['userid'])->first();
            $currentAmountMinus = $userMinus->amount - str_replace(",","",$data['total']);
            $userMinus->amount = $currentAmountMinus;
            $userMinus->save();
            
            if($userMinus){
                $walletHistoryMinus = WalletHistory::create([
                    'user_id' => $userMinus->id,
                    'order_id' => $order->id,
                    'type' => 0,
                    'amount' => $data['total'],
                    'description' => $order->order_no,
                    'description_arabic' => $order->order_no,
                    'wallet_type' => 'orderfee',
                    'title' => $order->order_no,
                    'title_arabic' => $order->order_no,
                    'current_amount' => $currentAmountMinus,
                    'status' => 0,
                ]);
            }
            
            WalletViewJob::dispatch($order->id);
        }
        
        
        $redirection = [];
        
        if($data['cartdata']['paymentMethod'] == 'cod'){
            $params = [
                [
                  "type" => "text", 
                  "text" => $order->Address->first_name
                ],
                [
                  "type" => "text", 
                  "text" => $order->Address->last_name
                ],
                [
                  "type" => "text", 
                  "text" => $order->order_no
                ],
                [
                  "type" => "text", 
                  "text" => $order->ordersummary()->where('type','total')->first()->price
                ],
            ];
            $header = [
                "type" => "header", 
                "parameters" => [
                    [
                       "type" => "file", 
                       "url" => "https://react.tamkeenstores.com.sa/api/frontend/downloadPDF/".$order->id, 
                       "fileName" => $order->order_no .'.pdf', 
                       "text" => "Invoice" 
                    ] 
                ] 
            ];
            $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'ordercreation',$order->lang,$params,$order->id);
            $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
            $phone = str_replace("_","",$phone);
            
            if ($order->lang == 'en') {
                 $response = NotificationHelper::sms($phone,'Dear '.$order->Address->first_name.' '.$order->Address->last_name.'

Congratulation! Your order '.$order->order_no.' has been processed successfully.

You have chosen cash on delivery the total amount is SR '.$order->ordersummary()->where('type','total')->first()->price. ', which you have to pay at the time of delivery to our representative.

Thanks for shopping with TamkeenStores.');
            }
            else{
                 $response = NotificationHelper::sms($phone, 'عي '.$order->Address->first_name.' '.$order->Address->last_name.'

ن بأن  تم ت معل  '.$order->order_no.' نا.

لق اخ لفع ن استلم؛ لغ لجال هو '.$order->ordersummary()->where('type','total')->first()->price. ' رل سد، وا لي ع عن اسلم  .

شرًا تس ع من او');
            }
            
            // Mail::to($order->UserDetail->email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
            if($order->token){
                // if ($order->lang == 'en') {
                //     $datanotifi = NotificationHelper::global_notification([$order->token], 'Congratulations ', 'Your order has been placed it’s under review.', '',$order->userDevice);
                // }
                // else{
                    $datanotifi = NotificationHelper::global_notification([$order->token], 'مبك ', 'ل  ض لب وهو قي لمرجع.', '',$order->userDevice);
                // }
            }
            $orderid = $order->id;
            LoyaltyPointsViewJob::dispatch($orderid);
            GiftVoucherViewJob::dispatch($orderid);
            AffiliationViewJob::dispatch($orderid);
            // WalletViewJob::dispatch($orderid);
            Mail::to($order->UserDetail->email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
            //print_r('testing');die;
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/congratulations/'.$order->id, 'cod' => true];
        }
        if($data['cartdata']['paymentMethod'] == 'hyperpay'){
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/card/'.$order->id, 'id' => $order->id];
        }
        if($data['cartdata']['paymentMethod'] == 'madapay'){
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/card/'.$order->id, 'id' => $order->id];
        }
        if($data['cartdata']['paymentMethod'] == 'applepay'){
            $link = 'https://partners.tamkeenstores.com.sa/api/frontend/hyperpay/'.$order->id.'/'.$data['lang'];
            $redirection = ['type'=> 'external', 'link' => $link];
            // $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/card/'.$order->id, 'id' => $order->id];
        }
        if($data['cartdata']['paymentMethod'] == 'tasheel'){
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/tasheel/'.$order->id, 'id' => $order->id];
        }
        
        if($data['cartdata']['paymentMethod'] == 'tabby'){
            $link = $this->tabby($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        
        if($data['cartdata']['paymentMethod'] == 'madfu'){
            $link = $this->madfu($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
            // $redirection = $link;
        }
        
        if($data['cartdata']['paymentMethod'] == 'mispay'){
            $link = $this->mispay($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        
        if($data['cartdata']['paymentMethod'] == 'clickpay' || $data['cartdata']['paymentMethod'] == 'clickpay_applepay'){
            $link = $this->clickpay($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }
        
        if($data['cartdata']['paymentMethod'] == 'tamara'){
            $link = $this->tamara($order->id,$data['lang']);
            $redirection = ['type'=> 'external', 'link' => $link];
        }

        if($data['cartdata']['paymentMethod'] == 'loyalty'){
            $this->updatepayment($order->id, 'loyalty');
            $redirection = ['type'=> 'internal', 'link' => '/'.$data['lang'].'/checkout/congratulations/'.$order->id, 'cod' => true];
        }
        
        //print_r($redirection);die;
        // abandoned cart remove
        $check = AbandonedCart::where('user_id', $data['userid'])->first();
        if($check) {
            $check->delete();
        }
        // abandoned cart remove
        
        $responsee = [
            //'data' => $data,
            'id' => $order->id,
            'order_id' => $order->id,
            'redirection' => $redirection
        ];
        $responsejson=json_encode($responsee);
        $dataa=gzencode($responsejson,9);
        return response($dataa)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($dataa),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function tamara($orderId,$lang){
        $token = config('tamara.token');
        $rurl = config('webconfig.link').$lang.'/';
        $order = Order::where('id', $orderId)->first();
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        $items = [];
        foreach ($order->details as $row){
            $items[] = array(
                "reference_id"=> $row->productData->id,
                "sku"=> $row->productData->sku,
                'type'=> 'Electronic',
                "name"=> $row->product_name,
                "quantity"=> (int)$row->quantity,
                'unit_price'=> [
                    'amount'=> str_replace(',','',number_format($row->unit_price,2)),
                    'currency'=> 'SAR',
                ],
                'discount_amount'=> [
                    'amount'=> '0.00',
                    'currency'=> 'SAR',
                ],
                'tax_amount'=> [
                    'amount'=> '0.00',
                    'currency'=> 'SAR',
                ],
                'total_amount'=> [
                    'amount'=> str_replace(',','',number_format($row->unit_price * $row->quantity,2)),
                    'currency'=> 'SAR',
                ],
            );
        }
        $shipping = 0;
        $discount = 0;
        $summary = $order->ordersummary()->where('type','!=','total')->where('type','!=','subtotal')->get();
        foreach($summary as $key => $value){
            if($value->calculation_type == 0)
                $discount += $value->price;
            else
                $shipping += $value->price;
        }
        
        $tamaradata = [
            'order_reference_id'=> $order->order_no,
            'total_amount'=> [
                'amount'=> str_replace(',','',number_format($order->ordersummary()->where('type','total')->first()->price,2)),
                'currency'=> 'SAR',
            ],
            'description'=> 'Some order description',
            'country_code'=> 'SA',
            'payment_type'=> 'PAY_BY_INSTALMENTS',
            'locale'=> 'en_US',
            'items'=> $items,
        
            'consumer'=> [
                'first_name'=> $order->UserDetail->first_name,
                'last_name'=> $order->UserDetail->last_name,
                'phone_number'=> "+966".$order->UserDetail->phone_number,
                'email'=> $order->UserDetail->email,
            ],
            'tax_amount'=> [
                'amount'=> '0.00',
                'currency'=> 'SAR',
            ],
            'shipping_amount'=> [
                'amount'=> str_replace(',','',number_format($shipping,2)),
                'currency'=> 'SAR',
            ],
            'discount'=> [
                'name'=> 'Coupon',
                'amount'=> [
                    'amount'=> str_replace(',','',number_format($discount,2)),
                    'currency'=> 'SAR',
                ],
            ],
            'merchant_url'=> [
                'success'=> $rurl.config('tamara.success'),
                'failure'=> $rurl.config('tamara.failure'),
                'cancel'=> $rurl.config('tamara.cancel'),
                'notification'=> config('tamara.notification'),
            ],
            'shipping_address'=> [
                'first_name'=> $order->Address->first_name,
                'last_name'=> $order->Address->last_name,
                'line1'=> $order->Address->address,
                'line2'=> $order->Address->address,
                'region'=> '',
                'city'=> $order->Address->stateData->name,
                'country_code'=> 'SA',
                'phone_number'=> $order->Address->phone_number,
            ],
            
            'risk_assessment'=> [
                'is_premium_customer'=> true,
                'is_existing_customer' => true,
                'account_creation_date' => date('d-m-Y'),
                'date_of_first_transaction' => date('d-m-Y'),
                'is_card_on_file' => false,
                'has_delivered_order' => false,
                'is_phone_verified' => true,
                'is_email_verified' => true,
                'is_fraudulent_customer' => false,
                'total_order_count' => 0,
                'order_amount_last3month'=> '0.00',
                'order_count_last3months' => 0
            ],
        ];
        $ch = curl_init(config('tamara.url').'/checkout'); // Initialise cURL
        $post = json_encode($tamaradata); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        
        $result = json_decode($result, true);
        //print_r($result);
        if (isset($result['checkout_url'])) {
            return $result['checkout_url'];
        }
        else{
            return false;
        }
    }
    
    
    public function tabby($orderId,$lang){
        $rurl = config('webconfig.link').$lang.'/';
        $token = config('tabby.token');
        $order = Order::where('id', $orderId)->first();
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        $items = array();
        foreach ($order->details as $row){
            $items[] = array(
                "reference_id"=> $row->productData->sku,
                "title"=> $row->product_name,
                "unit_price"=> $row->unit_price,
                "quantity"=> (int)$row->quantity
            );
        }
        $data = array(
            'payment' => array(
                "amount"=> str_replace(',','', $order->ordersummary()->where('type','total')->first()->price),
                //"merchant_code"=> 'tamkeen_app',
                "currency"=> "SAR",
                'buyer' => array(
                    "phone" => "+966".$order->UserDetail->phone_number,
                    "email" => $order->UserDetail->email,
                    "name" => $order->UserDetail->first_name.' '.$order->UserDetail->last_name
                ),
                'order' => array(
                    'reference_id' => $order->order_no,
                    'items' => $items
                )
            ),
            "lang"=> "en",
            "merchant_code"=> "tamkeen_app",
            "merchant_urls"=> array(
                "success"=> $rurl.config('tabby.success'),
                "cancel"=> $rurl.config('tabby.cancel'),
                "failure"=> $rurl.config('tabby.failure')
            )
        );
        //print_r($data);die;
        $ch = curl_init(config('tabby.apilink')); // Initialise cURL
        $post = json_encode($data); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        
        $result = json_decode($result, true);
        
        if (isset($result['id'])) {
            return 'https://checkout.tabby.ai/?sessionId='.$result['id'].'&apiKey='.$token.'&product=installments&merchantCode=default';
        }
        else{
            return false;
        }
    }
    
    public function madfu($orderId,$lang){
        
        
        
        $rurl = config('webconfig.link').$lang.'/';
        // $rurl = 'http://localhost:3000/'.$lang.'/';
        // $token = config('madfu.token');
        $order = Order::where('id', $orderId)->first();
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        
        $authorizationParams = array(
            'uuid' => $order->madfu_preference,
            'systemInfo'=> 'web'
        );
        // get initialize token
        $authorizationParamspost = json_encode($authorizationParams); // Encode the data array into a JSON string
        $authorizationch = curl_init();
        curl_setopt($authorizationch, CURLOPT_URL, config('madfu.apilink').'/merchants/token/init');
        $authorizationheaders = array();
        $authorizationheaders[] = 'Apikey: '.config('madfu.apikey');
        $authorizationheaders[] = 'Appcode: '.config('madfu.appcode');
        $authorizationheaders[] = 'Authorization: '.config('madfu.authorization');
        $authorizationheaders[] = 'Platformtypeid: '.config('madfu.platformyypeid');
        $authorizationheaders[] = 'Accept: application/json';
        $authorizationheaders[] = 'Content-Type: application/json';
        curl_setopt($authorizationch, CURLOPT_HTTPHEADER, $authorizationheaders);
        curl_setopt($authorizationch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($authorizationch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($authorizationch, CURLOPT_POSTFIELDS, $authorizationParamspost); // Set the posted fields
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $authorizationresult = curl_exec($authorizationch); // Execute the cURL statement
        curl_close($authorizationch); // Close the cURL connection
        $authorizationToken = json_decode($authorizationresult, true);
        // return $authorizationresult;
        // return $authorizationToken['token'];
        if(!isset($authorizationToken['token'])){
            return false;
        }
        
        $loginParams = array(
            'userName' => 'HQR@madfu.com.sa',
            'password'=> 'Welcome@123'
        );
        // get after login token
        $loginParamspost = json_encode($loginParams); // Encode the data array into a JSON string
        $loginch = curl_init();
        curl_setopt($loginch, CURLOPT_URL, config('madfu.apilink').'/Merchants/sign-in');
        $loginheaders = array();
        $loginheaders[] = 'Token: '.$authorizationToken['token'];
        $loginheaders[] = 'Apikey: '.config('madfu.apikey');
        $loginheaders[] = 'Appcode: '.config('madfu.appcode');
        $loginheaders[] = 'Authorization: '.config('madfu.authorization');
        $loginheaders[] = 'Platformtypeid: '.config('madfu.platformyypeid');
        $loginheaders[] = 'Accept: application/json';
        $loginheaders[] = 'Content-Type: application/json';
        curl_setopt($loginch, CURLOPT_HTTPHEADER, $loginheaders);
        curl_setopt($loginch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($loginch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($loginch, CURLOPT_POSTFIELDS, $loginParamspost); // Set the posted fields
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $loginresult = curl_exec($loginch); // Execute the cURL statement
        curl_close($loginch); // Close the cURL connection
        $loginToken = json_decode($loginresult, true);
        // return ['authorizationToken' => $authorizationToken['token'], 'loginToken' => $loginToken['token']];
        
        if(!isset($loginToken['token'])){
            return false;
        }
        
        
        // Create order
        
        $items = array();
        foreach ($order->details as $row){
            $items[] = array(
                "SKU"=> $row->productData->sku,
                "productName"=> $row->product_name,
                "totalAmount"=> str_replace(',','',number_format((int)$row->quantity * $row->unit_price,2)),
                "count"=> (int)$row->quantity,
                'productImage' => $row->product_image
            );
        }
        $total = $order->ordersummary()->where('type','total')->first()->price;
        $createorderdata = array(
            'Order' => array(
                'Taxes' => '0.00',
                'ActualValue' => str_replace(',','',number_format($total,2)),
                'Amount' => $total & 0xFFFFFFFF,
                'MerchantReference' => $order->madfu_preference,
            ),
            'GuestOrderData' => array(
                "CustomerMobile" => $order->UserDetail->phone_number,
                "CustomerName" => $order->UserDetail->first_name.' '.$order->UserDetail->last_name,
                "Lang" => $lang
            ),
            "MerchantUrls"=> array(
                "Success"=> $rurl.config('madfu.success'),
                "Failure"=> $rurl.config('madfu.cancel'),
                "Cancel"=> $rurl.config('madfu.failure')
            ),
            "OrderDetails" =>$items
        );
        
        
        $createorderch = curl_init(config('madfu.apilink').'Merchants/Checkout/CreateOrder'); // Initialise cURL
        $createorderdata = json_encode($createorderdata); // Encode the data array into a JSON string
        $createorderch = curl_init();

        curl_setopt($createorderch, CURLOPT_URL, config('madfu.apilink').'/Merchants/Checkout/CreateOrder');
        $headers = array();
        $headers[] = 'Token: '.$loginToken['token'];
        $headers[] = 'Apikey: '.config('madfu.apikey');
        $headers[] = 'Appcode: '.config('madfu.appcode');
        $headers[] = 'Authorization: '.config('madfu.authorization');
        $headers[] = 'Platformtypeid: '.config('madfu.platformyypeid');
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($createorderch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($createorderch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($createorderch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($createorderch, CURLOPT_POSTFIELDS, $createorderdata); // Set the posted fields
        // curl_setopt($createorderch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $createorderresult = curl_exec($createorderch); // Execute the cURL statement
        curl_close($createorderch); // Close the cURL connection
        $createorderresultdata = json_decode($createorderresult, true);
        if(!isset($createorderresultdata['checkoutLink'])){
            return false;
        }
        $order->paymentid = $createorderresultdata['orderId'];
        $order->invoice_code = $createorderresultdata['invoiceCode'];
        $order->update();
        return $createorderresultdata['checkoutLink'];
        // if (isset($result['id'])) {
        //     return 'https://checkout.madfu.ai/?sessionId='.$result['id'].'&apiKey='.$token.'&product=installments&merchantCode=default';
        // }
        // else{
        //     return false;
        // }
    }

    function decryptionMispay($code) {
        $input = base64_decode($code);
        $salt = substr($input, 0, 16);
        $nonce = substr($input, 16, 12);
        $ciphertext = substr($input, 28, -16);
        $tag = substr($input, -16);
        $key = hash_pbkdf2("sha256", config('mispay.appkey'), $salt, 40000, 32, true);
        $decryptToken = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, 1, $nonce, $tag);
        $jsonResponse = json_decode($decryptToken, true);
        return $jsonResponse;
    }
    
    
    public function clickpay($orderId,$lang){
        $type = 'Standard';
        $order = Order::where('id', $orderId)->first();
        $appKey = config('clickpay.appkey');
        // $appKey = $order->mobileapp == 1 ? config('clickpay.mobappkey') : config('clickpay.appkey');
        // $rurl = 'https://localhost:3000/'.$lang.'/';
        $rurl = config('webconfig.link').$lang.'/';
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
            $type = 'Mobile';
        }

        $url = config('clickpay.apilink').'payment/request';
        $headers = [
            'Content-Type: application/json',
            'Authorization: '.$appKey,
        ];
        $total = $order->ordersummary()->where('type','total')->first()->price;
        
        $data = [
            'profile_id' => config('clickpay.appid'),
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_id' => $order->order_no,
            'cart_amount' => $total,
            'cart_currency' => 'SAR',
            'cart_description' => 'Description of the items/services',
            'paypage_lang' => $lang,
            'customer_details' => [
                'name' => $order->Address->first_name .' '.$order->Address->last_name,
                'email' => $order->UserDetail->email,
                'phone' => $order->Address->phone_number,
                'street1' => $order->Address->address,
                'city' => $order->Address->stateData->name,
                'state' => $order->Address->stateData->name,
                'country' => 'SA',
                'zip' => '',
            ],
            'shipping_details' => [
                'name' => $order->Address->first_name .' '.$order->Address->last_name,
                'email' => $order->UserDetail->email,
                'phone' => $order->Address->phone_number,
                'street1' => $order->Address->address,
                'city' => $order->Address->stateData->name,
                'state' => $order->Address->stateData->name,
                'country' => 'SA',
                'zip' => '',
            ],
            'payment_method'=> $order->paymentmethod == 'clickpay' ? ['card', 'mada'] : ['applepay'],
            // 'callback' => $rurl . 'paymentstatus/clickpay-result',
            'return' => $rurl . 'paymentstatus/clickpay-result',
            'return_using_get' => true,
        ];
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        
        // if (curl_errno($ch)) {
        //     echo 'Curl error: ' . curl_error($ch);
        // } else {
        //     echo $response;
        // }
        
        curl_close($ch);
        $createorderresultdata = json_decode($response, true);
        if (isset($createorderresultdata['redirect_url'])) {
            $order->paymentid = $createorderresultdata['tran_ref'];
            $order->update();
            return $createorderresultdata['redirect_url'];
        }
        else{
            return false;
        }

    }
    
    public function mispay($orderId,$lang){
        $order = Order::where('id', $orderId)->first();
        // $rurl = 'http://localhost:3000/'.$lang.'/';
        $rurl = config('webconfig.link').$lang.'/';
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        
        // Token
        $authorizationch = curl_init();
        curl_setopt($authorizationch, CURLOPT_URL, config('mispay.apilink') . 'token');
        $authorizationheaders = array();
        $authorizationheaders[] = 'x-app-secret: '.config('mispay.appkey');
        $authorizationheaders[] = 'x-app-id: '.config('mispay.appid');
        $authorizationheaders[] = 'Accept: application/json';
        $authorizationheaders[] = 'Content-Type: application/json';
        curl_setopt($authorizationch, CURLOPT_HTTPHEADER, $authorizationheaders);
        curl_setopt($authorizationch, CURLOPT_RETURNTRANSFER, true);
        $authorizationresult = curl_exec($authorizationch); // Execute the cURL statement
        curl_close($authorizationch); // Close the cURL connection
        $authorizationToken = json_decode($authorizationresult, true);
        if(!isset($authorizationToken['result']['token'])){
            return false;
        }
        $token = $authorizationToken['result']['token'];
        
        $decryptedToken = $this->decryptionMispay($token);

        $token = $decryptedToken['token'];
        // start checkout 
        $total = $order->ordersummary()->where('type','total')->first()->price;
        $createorderdata = array(
            'orderId' => $order->order_no,
            'purchaseAmount' => $total,
            "purchaseCurrency" => "SAR",
            "lang" => $order->lang,
            "version" => "v1.1",
            "callbackUri" => $rurl . 'paymentstatus/mispay-result'
        );
        $createorderdata = json_encode($createorderdata);
        $createorderch = curl_init();

        curl_setopt($createorderch, CURLOPT_URL, config('mispay.apilink').'start-checkout');
        $headers = array();
        $headers[] = 'authorization: Bearer '.$token;
        $headers[] = 'x-app-id: '.config('mispay.appid');
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($createorderch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($createorderch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($createorderch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($createorderch, CURLOPT_POSTFIELDS, $createorderdata); // Set the posted fields
        $createorderresult = curl_exec($createorderch); // Execute the cURL statement
        curl_close($createorderch); // Close the cURL connection
        $createorderresultdata = json_decode($createorderresult, true);
        if (isset($createorderresultdata['result']['url'])) {
            $order->paymentid = $createorderresultdata['result']['trackId'];
            $order->update();
            return $createorderresultdata['result']['url'];
        }
        else{
            return false;
        }
    }

    public function mispaypaymentresponse($orderid,$id)
    {

        if($id) {
            $decrypt = $this->decryptionMispay(base64_decode($id));
            if($decrypt['code'] == 'MP00') {
                // Token
                $authorizationch = curl_init();
                curl_setopt($authorizationch, CURLOPT_URL, config('mispay.apilink') . 'token');
                $authorizationheaders = array();
                $authorizationheaders[] = 'x-app-secret: '.config('mispay.appkey');
                $authorizationheaders[] = 'x-app-id: '.config('mispay.appid');
                $authorizationheaders[] = 'Accept: application/json';
                $authorizationheaders[] = 'Content-Type: application/json';
                curl_setopt($authorizationch, CURLOPT_HTTPHEADER, $authorizationheaders);
                curl_setopt($authorizationch, CURLOPT_RETURNTRANSFER, true);
                $authorizationresult = curl_exec($authorizationch); // Execute the cURL statement
                curl_close($authorizationch); // Close the cURL connection
                $authorizationToken = json_decode($authorizationresult, true);
                if(!isset($authorizationToken['result']['token'])){
                    return false;
                }
                $token = $authorizationToken['result']['token'];
                
                $decryptedToken = $this->decryptionMispay($token);

                $token = $decryptedToken['token'];


                // End Checkout Step
                $createorderch = curl_init();

                curl_setopt($createorderch, CURLOPT_URL, config('mispay.apilink').'checkout/' . $decrypt['checkoutId'] . '/end');
                $headers = array();
                $headers[] = 'authorization: Bearer '.$token;
                $headers[] = 'x-app-id: '.config('mispay.appid');
                $headers[] = 'Accept: application/json';
                $headers[] = 'Content-Type: application/json';
                curl_setopt($createorderch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($createorderch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($createorderch, CURLOPT_CUSTOMREQUEST, "PUT"); // Specify the request method as POST
                // curl_setopt($createorderch, CURLOPT_POSTFIELDS, $createorderdata); // Set the posted fields
                // curl_setopt($createorderch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
                $createorderresult = curl_exec($createorderch); // Execute the cURL statement
                curl_close($createorderch); // Close the cURL connection
                $createorderresultdata = json_decode($createorderresult, true);

                return json_encode(['status' =>true, 'id' => $decrypt['checkoutId'], 'response' => $createorderresultdata]);
            }
            else {
                return json_encode(['status' =>false]);   
            }
            // return json_encode([$id, $decrypt]);
        }
        return json_encode(['status' =>false]);
    }

    public function hyperpaynewMobile($orderid,$lang){
        $token = config('tamara.token');
        $rurl = config('webconfig.link').$lang.'/';
        $order = Order::where('id', $orderid)->first();
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        $data = [
            'id' => $order->order_no,
            'firstName' => $order->Address->first_name ?? '',
            'lastname' => $order->Address->last_name ?? '',
            'address' => $order->Address->address ?? '',
            'zip' => $order->Address->zip ?? '',
            'city' => $order->Address->stateData->name ?? '',
            'state' => $order->Address->stateData->name ?? '',
            'country' => $order->Address->countryData->sortname ?? '',
            'email' => $order->UserDetail->email,
            'amount' => str_replace(',','', $order->ordersummary()->where('type','total')->first()->price)
        ];
        $type = $order->paymentmethod;
        if($type == 'hyperpay')
            $hyperpay = new Hyperpay($rurl.'paymentstatus/hyperpay-response',$lang);
        elseif($type == 'applepay')
            $hyperpay = new Applepay($rurl.'paymentstatus/hyperpay-response',$lang);
        elseif($type == 'madapay')
            $hyperpay = new Madapay($rurl.'paymentstatus/hyperpay-response',$lang);
        $token = $hyperpay->token($data);
        //print_r($type);die;
        ob_start();
        $hyperpay->renderPaymentForm($order->order_no, $token);
        $contents = ob_get_contents();
        ob_end_clean();
        $contents = '<head><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'.$contents;
        return $contents;
    }
    
    public function hyperpay($orderid,$lang){
        $token = config('tamara.token');
        $rurl = config('webconfig.link').$lang.'/';
        $order = Order::where('id', $orderid)->first();
        if(isset($order->mobileapp) && $order->mobileapp == 1){
            $rurl = config('webconfig.moblink').$lang.'/';
        }
        $data = [
            'id' => $order->order_no,
            'firstName' => $order->Address->first_name ?? '',
            'lastname' => $order->Address->last_name ?? '',
            'address' => $order->Address->address ?? '',
            'zip' => $order->Address->zip ?? '',
            'city' => $order->Address->stateData->name ?? '',
            'state' => $order->Address->stateData->name ?? '',
            'country' => $order->Address->countryData->sortname ?? '',
            'email' => $order->UserDetail->email,
            'amount' => str_replace(',','', $order->ordersummary()->where('type','total')->first()->price)
        ];
        $type = $order->paymentmethod;
        if($type == 'hyperpay')
            $hyperpay = new Hyperpay($rurl.'paymentstatus/hyperpay-response',$lang);
        elseif($type == 'applepay')
            $hyperpay = new Applepay($rurl.'paymentstatus/hyperpay-response',$lang);
        elseif($type == 'madapay')
            $hyperpay = new Madapay($rurl.'paymentstatus/hyperpay-response',$lang);
        $token = $hyperpay->token($data);
        //print_r($type);die;
        ob_start();
        $hyperpay->renderPaymentForm($order->order_no, $token);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    
    public function hyperpaypaymentresponse($orderid,$id)
    {
        $order = Order::where('id', $orderid)->first();
        $type = $order->paymentmethod;
        if($type == 'hyperpay')
            $hyperpay = new Hyperpay();
        elseif($type == 'applepay')
            $hyperpay = new Applepay();
        elseif($type == 'madapay')
            $hyperpay = new Madapay();
        else
            $hyperpay = new Hyperpay();
        $status = $hyperpay->checkStatus($id);
        if($status['success']){
            return json_encode(['status' =>true]);
        }
        else{
            return json_encode(['status' =>false, 'error' => $status['msg']]);
        }
        
    }

    // public function updatepaymentTestNewNext($orderid) {
    //     $order = Order::where('id', $orderid)->first();
    //     if($order) {
    //         // print_r($order->details);die;
    //         foreach ($order->details as $row){
    //             $regional_stockarray = [];
    //             $customerCity = $order->Address->state_id;
    //             $product = Product::whereId($row['product_id'])->first();
    //             $warehouses = false;
    //             $totalqty = $row['quantity'];
    //             $otherWarehouses = ['OLN1', 'KUW101'];
    //             $removeQty = 3;
    //             // print_r($row);die;
    //             // if($row['expressproduct']) {
    //                 $warehouses = Warehouse::with('livestockData')->whereHas('cityData', function ($query) use ($customerCity) {
    //                     return $query->where('city_id', $customerCity);
    //                 })
    //                 ->where('status', 1)
    //                 ->where('show_in_stock', 1)
    //                 ->orderBy('sort', 'desc')
    //                 ->get();
        
    //                 if(count($warehouses) >= 1) {
    //                     foreach ($warehouses as $key => $warehouse) {
    //                         $checkLiveStock = $warehouse->livestockData->contains('ln_sku', $product->ln_sku);
    //                         if($checkLiveStock) {
    //                             $getQty = $warehouse->livestockData->where('ln_sku', $product->ln_sku)->first();
    //                             $liveQty = (int)$getQty->qty;
    //                             // print_r($warehouse);die;
    //                             $updateLiveStock = LiveStock::where('ln_sku', $product->ln_sku)->where('city', $warehouse->ln_code)->first();
    //                             if($warehouse->ln_code != 'OLN1' && $warehouse->ln_code != 'KUW101') {
    //                                 if($liveQty > $removeQty) {
    //                                     $liveQty = $liveQty - $removeQty;
    //                                     $removeQty = 0;
    //                                 }
    //                                 else {
    //                                     $removeQty = $removeQty - $liveQty;
    //                                     $liveQty = 0;
    //                                 }
    //                             }
    //                             // print_r($liveQty);die;
    //                             if($liveQty > 0) {
    //                                 if($row['quantity'] > $liveQty) {
    //                                     $regional_qty_history = OrderDetailRegionalQty::create([
    //                                         'order_detail_id' => $row['id'],
    //                                         'warehouse_code' => $warehouse->ln_code,
    //                                         'qty' => $liveQty
    //                                     ]);
    //                                     $regional_stockarray[] = array('id' => $warehouse->id, 'qty' => $liveQty);
    //                                     $totalqty = $totalqty - $liveQty;
    //                                     $updateLiveStock->qty = 0;
    //                                     $updateLiveStock->save();
    //                                 }
    //                                 else {
    //                                     $regional_qty_history = OrderDetailRegionalQty::create([
    //                                         'order_detail_id' => $row['id'],
    //                                         'warehouse_code' => $warehouse->ln_code,
    //                                         'qty' => $totalqty
    //                                     ]);
    //                                     $regional_stockarray[] = array('id' => $warehouse->id, 'qty' => $totalqty);
    //                                     if($updateLiveStock) {
    //                                         $updateLiveStock->qty = $updateLiveStock->qty - $row['quantity'];
    //                                         $updateLiveStock->update();
    //                                     }
    //                                     $totalqty = 0;
    //                                 }
    //                             }
    //                         }
    //                         if($totalqty == 0){
    //                             break;
    //                         }
    //                     }
    //                 }
    //             // }
    
    //             if($totalqty > 0){
    //                 $regionalmodules = Warehouse::when($warehouses, function ($q) use ($warehouses) {
    //                     return $q->whereNotIn('id', $warehouses->pluck('id')->toArray());
    //                 })
    //                 ->where('status', 1)
    //                 ->where('show_in_stock', 1)
    //                 ->orderBy('sort', 'asc')
    //                 // ->whereIn('ln_code', $otherWarehouses)
    //                 ->get();
    //                 // print_r($regionalmodules);die;
    
    //                 foreach($regionalmodules as $regionalmodule){
    //                     $regionalproduct = LiveStock::where('ln_sku', $product->ln_sku)->where('city', $regionalmodule->ln_code)->first();
                        
    //                     if($regionalproduct && $regionalproduct->qty > 0){
    //                         if($totalqty > $regionalproduct->qty){
    //                             $regional_qty_history = OrderDetailRegionalQty::create([
    //                                 'order_detail_id' => $row['id'],
    //                                 'warehouse_code' => $regionalmodule->ln_code,
    //                                 'qty' => $regionalproduct->qty
    //                             ]);
    //                             $regional_stockarray[] = array('id' => $regionalmodule->id, 'qty' => $regionalproduct->qty);
    //                             $totalqty = $totalqty - $regionalproduct->qty;
    //                             $regionalproduct->qty = 0;
    //                             $regionalproduct->save();
    //                         }
    //                         else{
    //                             $regional_qty_history = OrderDetailRegionalQty::create([
    //                                 'order_detail_id' => $row['id'],
    //                                 'warehouse_code' => $regionalmodule->ln_code,
    //                                 'qty' => $totalqty
    //                             ]);
    //                             $regional_stockarray[] = array('id' => $regionalmodule->id, 'qty' => $totalqty);
    //                             $regionalproduct->qty = $regionalproduct->qty - $totalqty;
    //                             $regionalproduct->save();
    //                             $totalqty = 0;
    //                         }
    //                     }
    //                     if($totalqty == 0){
    //                         break;
    //                     }
    //                 }
    //             }
    //         }    
    //     }
    //     else {
    //         return 'No Order Found...';
    //     }
    // }

    public function updatepaymentTestNew($orderid) {
        $order = Order::where('id', $orderid)->first();
        if($order) {
            // print_r($order->details);die;
            foreach ($order->details as $row){
                $regional_stockarray = [];
                $customerCity = $order->Address->state_id;
                $product = Product::whereId($row['product_id'])->first();
                $warehouses = false;
                $totalqty = $row['quantity'];
                // $otherWarehouses = ['OLN1', 'KUW101'];
                $otherWarehouses = ['OLN1'];
                $regionIds = [];
                $removeQty = 3;
                // print_r($row);die;
                // if($row['expressproduct']) {
                    $sort = $customerCity == 15 ? 'asc' : 'desc';
                    $warehouses = Warehouse::with('livestockData')->whereHas('cityData', function ($query) use ($customerCity) {
                        return $query->where('city_id', $customerCity);
                    })
                    ->where('status', 1)
                    ->where('show_in_stock', 1)
                    ->orderBy('sort', $sort)
                    ->get();
                    
                    if(count($warehouses) >= 1) {
                        foreach ($warehouses as $key => $warehouse) {
                            $checkLiveStock = $warehouse->livestockData->contains('ln_sku', $product->ln_sku);
                            if($checkLiveStock) {
                                $getQty = $warehouse->livestockData->where('ln_sku', $product->ln_sku)->first();
                                $liveQty = (int)$getQty->qty;
                                $updateLiveStock = LiveStock::where('ln_sku', $product->ln_sku)->where('city', $warehouse->ln_code)->first();
                                // if($warehouse->ln_code != 'OLN1' && $warehouse->ln_code != 'KUW101') {
                                if($warehouse->ln_code != 'OLN1') {
                                    if($liveQty > $removeQty) {
                                        $liveQty = $liveQty - $removeQty;
                                        $removeQty = 0;
                                    }
                                    else {
                                        $removeQty = $removeQty - $liveQty;
                                        $liveQty = 0;
                                    }
                                }
                                if($liveQty > 0 && $row['quantity'] > 0) {
                                    if($row['quantity'] > $liveQty) {
                                        $regional_qty_history = OrderDetailRegionalQty::create([
                                            'order_detail_id' => $row['id'],
                                            'warehouse_code' => $warehouse->ln_code,
                                            'qty' => $liveQty
                                        ]);
                                        $regional_stockarray[] = array('id' => $warehouse->id, 'qty' => $liveQty);
                                        $totalqty = $totalqty - $liveQty;
                                        $updateLiveStock->qty = 0;
                                        $updateLiveStock->save();
                                    }
                                    else {
                                        $regional_qty_history = OrderDetailRegionalQty::create([
                                            'order_detail_id' => $row['id'],
                                            'warehouse_code' => $warehouse->ln_code,
                                            'qty' => $totalqty
                                        ]);
                                        $regional_stockarray[] = array('id' => $warehouse->id, 'qty' => $totalqty);
                                        if($updateLiveStock) {
                                            $updateLiveStock->qty = $updateLiveStock->qty - $row['quantity'];
                                            $updateLiveStock->update();
                                        }
                                        $totalqty = 0;
                                    }
                                }
                            }
                            if($totalqty == 0){
                                break;
                            }
                            //order regions
                                    // dd($warehouse);
                            // if ($warehouse && $warehouse->warehouseRegions) {
                            //     $regionIds[] = $warehouse->warehouseRegions->id;
                            // }
                        }
                        // $uniqueRegionIds = array_unique($regionIds);
                        //insert order region
                        // foreach ($uniqueRegionIds as $regionId) {
                        //     OrdersRegion::create([
                        //         'order_id' => $order->id,
                        //         'region_id' => $regionId,
                        //     ]);
                        // }
                    }
                    $slotWarehouse = Warehouse::with('livestockData')->whereHas('cityData', function ($query) use ($customerCity) {
                        return $query->where('city_id', $customerCity);
                    })
                    ->where('status', 1)
                    ->where('show_in_stock', 1)
                    ->first();
                    
                    if($slotWarehouse && isset($slotWarehouse->warehouseRegions)){
                         OrdersRegion::create([
                            'order_id' => $order->id,
                            'region_id' => $slotWarehouse->warehouseRegions->id,
                        ]);
                    }
                // }
    
                if($totalqty > 0){
                    $regionalmodules = Warehouse::when($warehouses, function ($q) use ($warehouses) {
                        return $q->whereNotIn('id', $warehouses->pluck('id')->toArray());
                    })
                    ->where('status', 1)
                    ->where('show_in_stock', 1)
                    ->orderBy('sort', 'asc')
                    // ->whereIn('ln_code', $otherWarehouses)
                    ->get();
                    // print_r($regionalmodules);die;
    
                    foreach($regionalmodules as $regionalmodule){
                        $regionalproduct = LiveStock::where('ln_sku', $product->ln_sku)->where('city', $regionalmodule->ln_code)->first();
                        
                        if($regionalproduct && $regionalproduct->qty > 0){
                            if($totalqty > $regionalproduct->qty){
                                $regional_qty_history = OrderDetailRegionalQty::create([
                                    'order_detail_id' => $row['id'],
                                    'warehouse_code' => $regionalmodule->ln_code,
                                    'qty' => $regionalproduct->qty
                                ]);
                                $regional_stockarray[] = array('id' => $regionalmodule->id, 'qty' => $regionalproduct->qty);
                                $totalqty = $totalqty - $regionalproduct->qty;
                                $regionalproduct->qty = 0;
                                $regionalproduct->save();
                            }
                            else{
                                $regional_qty_history = OrderDetailRegionalQty::create([
                                    'order_detail_id' => $row['id'],
                                    'warehouse_code' => $regionalmodule->ln_code,
                                    'qty' => $totalqty
                                ]);
                                $regional_stockarray[] = array('id' => $regionalmodule->id, 'qty' => $totalqty);
                                $regionalproduct->qty = $regionalproduct->qty - $totalqty;
                                $regionalproduct->save();
                                $totalqty = 0;
                            }
                        }
                        if($totalqty == 0){
                            break;
                        }
                    }
                }
            }    
        }
        else {
            return 'No Order Found...';
        }
    }

    public function updatepaymentTest($orderid) {
        $order = Order::where('id', $orderid)->first();
        if($order) {
            foreach ($order->details as $row){
                $regional_stockarray = [];
                $customerCity = $order->Address->state_id;
                $product = Product::whereId($row['product_id'])->first();
                $warehouse = false;
                $totalqty = $row['quantity'];
                $otherWarehouses = ['OLN1', 'KUW101'];
                // print_r($row);die;
                if($row['expressproduct']) {
                    $warehouse = Warehouse::with('livestockData')->whereHas('cityData', function ($query) use ($customerCity) {
                        return $query->where('city_id', $customerCity);
                    })
                    ->where('status', 1)
                    ->where('show_in_stock', 1)
                    ->orderBy('sort', 'asc')
                    ->first();
                    // print_r($warehouse);die;
        
                    if($warehouse) {
                        
                        $checkLiveStock = $warehouse->livestockData->contains('ln_sku', $product->ln_sku);
        
                        if($checkLiveStock) {
                            $getQty = $warehouse->livestockData->where('ln_sku', $product->ln_sku)->first();
                            $updateLiveStock = LiveStock::where('ln_sku', $product->ln_sku)->where('city', $warehouse->ln_code)->first();
        
                            if($row['quantity'] > $getQty->qty) {
                                $regional_qty_history = OrderDetailRegionalQty::create([
                                    'order_detail_id' => $row['id'],
                                    'warehouse_code' => $warehouse->ln_code,
                                    'qty' => $updateLiveStock->qty
                                ]);
                                $regional_stockarray[] = array('id' => $warehouse->id, 'qty' => $updateLiveStock->qty);
                                $totalqty = $totalqty - $updateLiveStock->qty;
                                $updateLiveStock->qty = 0;
                                $updateLiveStock->save();
                            }
                            else {
                                $regional_qty_history = OrderDetailRegionalQty::create([
                                    'order_detail_id' => $row['id'],
                                    'warehouse_code' => $warehouse->ln_code,
                                    'qty' => $row['quantity']
                                ]);
                                $regional_stockarray[] = array('id' => $warehouse->id, 'qty' => $totalqty);
                                if($updateLiveStock) {
                                    $updateLiveStock->qty = $updateLiveStock->qty - $row['quantity'];
                                    $updateLiveStock->update();
                                }
                                $totalqty = 0;
                            }
                        }
        
                        
                    }
                }
               
                
                if($totalqty > 0){
                    $regionalmodules = Warehouse::when($warehouse, function ($q) use ($warehouse) {
                        return $q->where('id', '!=', $warehouse->id);
                    })
                    ->where('status', 1)
                    ->where('show_in_stock', 1)
                    // ->whereIn('ln_code', $otherWarehouses)
                    ->orderBy('sort', 'asc')
                    ->get();
                    // print_r($regionalmodules);die;
    
                    foreach($regionalmodules as $regionalmodule){
                        $regionalproduct = LiveStock::where('ln_sku', $product->ln_sku)->where('city', $regionalmodule->ln_code)->first();
                        if($regionalproduct){
                            if($totalqty > $regionalproduct->qty){
                                $regional_qty_history = OrderDetailRegionalQty::create([
                                    'order_detail_id' => $row['id'],
                                    'warehouse_code' => $regionalmodule->ln_code,
                                    'qty' => $regionalproduct->qty
                                ]);
                                $regional_stockarray[] = array('id' => $regionalmodule->id, 'qty' => $regionalproduct->qty);
                                $totalqty = $totalqty - $regionalproduct->qty;
                                $regionalproduct->qty = 0;
                                $regionalproduct->save();
                            }
                            else{
                                $regional_qty_history = OrderDetailRegionalQty::create([
                                    'order_detail_id' => $row['id'],
                                    'warehouse_code' => $regionalmodule->ln_code,
                                    'qty' => $totalqty
                                ]);
                                $regional_stockarray[] = array('id' => $regionalmodule->id, 'qty' => $totalqty);
                                $regionalproduct->qty = $regionalproduct->qty - $totalqty;
                                $regionalproduct->save();
                                $totalqty = 0;
                            }
                        }
                        if($totalqty == 0){
                            break;
                        }
                    }
                }
            }
        }
        else {
            return 'No Order Found...';
        }
    }

    public function updatePaymentFromSingleWarehouse($orderId, $warehouseCode)
    {
        $order = Order::where('id', $orderId)->first();

        if (!$order) {
            return 'No Order Found...';
        }

        foreach ($order->details as $row) {
            $product = Product::whereId($row['product_id'])->first();
            $totalQty = $row['quantity'];

            // Get the warehouse by its code
            $warehouse = Warehouse::with('livestockData')
                ->where('ln_code', $warehouseCode)
                ->where('status', 1)
                ->where('show_in_stock', 1)
                ->first();
            if (!$warehouse) {
                continue; // No such warehouse or not active
            }

            // Check if product exists in this warehouse's live stock
            $liveStockItem = $warehouse->livestockData->where('ln_sku', $product->ln_sku)->first();
            // if($order->customer_id == 171) {
            //     print_r($liveStockItem);die;
            // }
            if ($liveStockItem && $liveStockItem->qty > 0) {
                $availableQty = $liveStockItem->qty;

                if ($totalQty > $availableQty) {
                    // Consume all available stock
                    OrderDetailRegionalQty::create([
                        'order_detail_id' => $row['id'],
                        'warehouse_code' => $warehouse->ln_code,
                        'qty' => $availableQty
                    ]);

                    $liveStockItem->qty = 0;
                    $liveStockItem->save();
                    $totalQty = $totalQty - $availableQty;
                } else {
                    // Consume only required qty
                    OrderDetailRegionalQty::create([
                        'order_detail_id' => $row['id'],
                        'warehouse_code' => $warehouse->ln_code,
                        'qty' => $totalQty
                    ]);

                    $liveStockItem->qty = $availableQty - $totalQty;
                    $liveStockItem->save();
                    $totalQty = 0;
                }
            }

            if ($totalQty > 0) {
                return false;
                // Not enough stock in this single warehouse
                // return "Insufficient stock in warehouse {$warehouseCode} for product {$product->ln_sku}. Needed: {$row['quantity']}, Available: {$row['quantity'] - $totalQty}";
            }
        }

        return 'Stock updated successfully from single warehouse.';
    }


    public function updatepayment($orderid,$paymentid)
    {   
        $walletsData  = WalletSetting::where('status',1)->first();
        $orderSummary = Order::with('ordersummary', 'UserDetail')->where('id', $orderid)->first();
        if($walletsData){
            $orderTotal = $orderSummary->ordersummary->where('type','total')->toArray();
            if($orderTotal){
                foreach($orderTotal as $key => $orderTotalData){
                $userPlus = User::where('id', $orderSummary->customer_id)->first();
                $currentAmountPlus = $userPlus->amount + str_replace(",","",$orderTotalData['price']);
                $userPlus->amount = $currentAmountPlus;
                $userPlus->save();
                if($userPlus){
                    $walletHistoryPlus = WalletHistory::create([
                        'user_id' => $orderSummary->customer_id,
                        'order_id' => $orderTotalData['order_id'],
                        'type' => 1,
                        'amount' => $orderTotalData['price'],
                        'description' => $orderSummary->order_no,
                        'description_arabic' => $orderSummary->order_no,
                        'wallet_type' => 'orderfee',
                        'title' => $orderSummary->order_no,
                        'title_arabic' => $orderSummary->order_no,
                        'current_amount' => $currentAmountPlus,
                        'status' => 0,
                    ]);
                }
                
                $userMinus = User::where('id', $orderSummary->customer_id)->first();
                $currentAmountMinus = $userMinus->amount - str_replace(",","",$orderTotalData['price']);
                $userMinus->amount = $currentAmountMinus;
                $userMinus->save();
                if($userMinus){
                    $walletHistoryMinus = WalletHistory::create([
                        'user_id' => $orderSummary->customer_id,
                        'order_id' => $orderTotalData['order_id'],
                        'type' => 0,
                        'amount' => $orderTotalData['price'],
                        'description' => $orderSummary->order_no,
                        'description_arabic' => $orderSummary->order_no,
                        'wallet_type' => 'orderfee',
                        'title' => $orderSummary->order_no,
                        'title_arabic' => $orderSummary->order_no,
                        'current_amount' => $currentAmountMinus,
                        'status' => 0,
                    ]);
                }
                }
                    
                }
            
            $discountRules = $orderSummary->ordersummary->where('type','discount_rule')->toArray();
            if($discountRules){
                foreach($discountRules as $key => $discountrule){
                    
                $userPlus = User::where('id', $orderSummary->customer_id)->first();
                $currentAmountPlus = $userPlus->amount + str_replace(",","",$discountrule['price']);
                $userPlus->amount = $currentAmountPlus;
                $userPlus->save();
                if($userPlus){
                    $walletHistoryPlus = WalletHistory::create([
                        'user_id' => $orderSummary->customer_id,
                        'order_id' => $discountrule['order_id'],
                        'type' => 1,
                        'amount' => $discountrule['price'],
                        'description' => $discountrule['name'],
                        'description_arabic' => $discountrule['name_arabic'],
                        'wallet_type' => 'discount_rule',
                        'title' => $discountrule['name'],
                        'title_arabic' => $discountrule['name_arabic'],
                        'current_amount' => $currentAmountPlus,
                        'status' => 0,
                    ]);
                }
                
                $userMinus = User::where('id', $orderSummary->customer_id)->first();
                $currentAmountMinus = $userMinus->amount - str_replace(",","",$discountrule['price']);
                $userMinus->amount = $currentAmountMinus;
                $userMinus->save();
                if($userMinus){
                    $walletHistoryMinus = WalletHistory::create([
                        'user_id' => $orderSummary->customer_id,
                        'order_id' => $discountrule['order_id'],
                        'type' => 0,
                        'amount' => $discountrule['price'],
                        'description' => $discountrule['name'],
                        'description_arabic' => $discountrule['name_arabic'],
                        'wallet_type' => 'discount_rule',
                        'title' => $discountrule['name'],
                        'title_arabic' => $discountrule['name_arabic'],
                        'current_amount' => $currentAmountMinus,
                        'status' => 0,
                    ]);
                }
                }
            }
        }
        if($paymentid == 'madfu' || $paymentid == 'loyalty'){
            $paymentupdate = array(
                'status' => 0,
                'created_at' =>date('Y-m-d H:i:s')
            );
        }
        else{
            $paymentupdate = array(
                'paymentid' => $paymentid ? $paymentid : null,
                'status' => 0,
                'created_at' =>date('Y-m-d H:i:s')
            );
        }
        
        Order::whereId($orderid)->update($paymentupdate);
        $order = Order::where('id', $orderid)->first();
        $order->statustimeline()->create([
            'status' => $order->status    
        ]);

        if($order->order_type == 0){
            $this->updatepaymentTestNew($order->id);
        }
        else{
            // if($order->customer_id == 171) {
            //     print_r($order->warehouse);die;
            // }
            $this->updatePaymentFromSingleWarehouse($order->id, $order->warehouse->ln_code);
        }

        foreach ($order->details as $row){
            $product = Product::whereId($row['product_id'])->first();
            $product->quantity = $product->quantity - $row['quantity'];
            $product->save();
        }
        
        
        if($order->paymentmethod == 'tabby'){
            //$token = 'sk_test_777ea5f5-cc6b-46f3-b3b1-57679bbd8214';
            $token = config('tabby.token2');
            //$this->calculateCart();
            $datatabby = array(
                "amount"=> str_replace(',','', $order->ordersummary()->where('type','total')->first()->price),
            );
            $ch = curl_init('https://api.tabby.ai/api/v1/payments/'.$paymentid.'/captures'); // Initialise cURL
            $post = json_encode($datatabby); // Encode the data array into a JSON string
            $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
            $result = curl_exec($ch); // Execute the cURL statement
            curl_close($ch); // Close the cURL connection
            // print_r($result);die;
            $result = json_decode($result, true);
        }
        
        if($order->paymentmethod == 'tamara'){
            //$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhY2NvdW50SWQiOiI0NTg0ZTk3Zi1jYTU1LTQ4Y2ItOTQ5Yi1hN2Y5ZTZmMjA0N2EiLCJ0eXBlIjoibWVyY2hhbnQiLCJzYWx0IjoiODdiNDYyODg4MmU2ZjE5ZTY4Y2U1OTAxZmE4M2I0MGUiLCJpYXQiOjE2Mjc4MTkyNzYsImlzcyI6IlRhbWFyYSJ9.aqAsFBQO8AhryXJlEe-scVDOejo5dhBWhGcXerABfJuGkNgaFao6wDQherojrKfXZLSirRGvdWzhZhFwuSSb0sHomyktPnyhQmiwWgWFWk-P2JvoWPu0jgXsMyY4xaPHgQHsD-qa_tYGYYlufR8Eker8Ppkt_Ke4qBp-RB6KAnAdrgGaJFnMqO3XznvuKhIUW0R3qxDqPR4uQjh8H8fOitUcaUmZBiaPEaD3LuccGrIQCniEdTgBa_8x0eha03Gh6vtAszCM9Vc2wxuM8ihFv8Pnrlq7qExw62j4xvifiyBKhYpEFGBfI62c644v7MubWdOgZ9XxTSJd5a9Qsi68aw';
            $token = config('tamara.token');
            //$this->calculateCart();
            $datatamara = array(
                "order_id"=> $paymentid,
                'total_amount' => array(
                   'amount' => number_format($order->ordersummary()->where('type','total')->first()->price, 2),
                   'currency' => 'SAR'
                ),
                'shipping_info' => array(
                    'shipping_company' => 'NAQEEL',
                    'shipped_at' => date("Y-m-d").'T'.date('H:i:s').'Z',
                    //'shipped_at' =>new DateTimeImmutable()
                )
            );
            $ch = curl_init(config('tamara.url').'/payments/capture'); // Initialise cURL
            $post = json_encode($datatamara); // Encode the data array into a JSON string
            $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
            $result = curl_exec($ch); // Execute the cURL statement
            curl_close($ch); // Close the cURL connection
            // print_r($datatamara);
            // print_r($result);
            $result = json_decode($result, true);
        }
        
        $params = [
            [
               "type" => "text", 
               "text" => $order->Address->first_name
            ],
            [
               "type" => "text", 
               "text" => $order->Address->last_name
            ],
            [
               "type" => "text", 
               "text" => $order->order_no
            ],
            [
               "type" => "text", 
               "text" => $order->ordersummary()->where('type','total')->first()->price
            ],
        ];
        $header = [
            "type" => "header", 
            "parameters" => [
                [
                   "type" => "file", 
                   "url" => "https://react.tamkeenstores.com.sa/api/frontend/downloadPDF/".$order->id, 
                   "fileName" => $order->order_no .'.pdf', 
                   "text" => "Invoice" 
                ] 
            ] 
        ];
        if($order->order_type == 0){
            $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'ordercreation',$order->lang,$params,$order->id);
        }else {
            $paramsPickUpFromStore = [
                [
                    "type" => "text", 
                    "text" => $order->Address->first_name . " " . $order->Address->last_name
                ],
                [
                    "type" => "text", 
                    "text" => $order->order_no ?? ''
                ],
                [
                    "type" => "text", 
                    "text" => $order->ordersummary()->where('type','total')->first()->price ?? ''
                ],
                [
                    "type" => "text", 
                    "text" => $order->lang == 'ar' 
                        ? $order->warehouse->showroomData->name_arabic 
                        : $order->warehouse->showroomData->name
                ],
                [
                    "type" => "text", 
                    "text" => $order->warehouse->showroomData->direction_button ?? ''
                ],
            ];
            Log::info('paramsPickUpFromStore: ' . json_encode($paramsPickUpFromStore));
            // $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'pickupfrom_store_2025',$order->lang,$paramsPickUpFromStore,$order->id);
            $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'pickupfromstore_2025_final',$order->lang,$paramsPickUpFromStore,$order->id);
            Log::info('WhatsApp response: ' . json_encode($response));
        }
        $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
        $phone = str_replace("_","",$phone);
        $responsesms = false;
        // pickup from store
        if($order->order_type == 1) {
            if(isset($order->warehouse->showroomData->showroom_supervisor_phone_number) && $order->warehouse->showroomData->showroom_supervisor_phone_number != null) {
                $response = NotificationHelper::whatsappmessage("+966".$order->warehouse->showroomData->showroom_supervisor_phone_number,'ordercreation',$order->lang,$params,$order->id);
            }
            if(isset($order->warehouse->showroomData->showroom_supervisor_email) && $order->warehouse->showroomData->showroom_supervisor_email != null) {
                Mail::to($order->warehouse->showroomData->showroom_supervisor_email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
            }
            
            if(isset($order->warehouse->showroom_email) && $order->warehouse->showroom_email != null) {
                Mail::to($order->warehouse->showroom_email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
            }
            $customer_name = $order->Address->first_name . ' ' . $order->Address->last_name;
            $order_number = $order->order_no ?? '';
            $amount_of_order = 'SAR ' . $order->ordersummary()->where('type', 'total')->first()->price ?? '';
            $otp_code = $order->otp_code ?? ''; // Ensure this is defined earlier
            $showroom_name = $order->lang == 'ar' ? $order->warehouse->showroom_arabic : $order->warehouse->showroom;
            $showroom_location = $order->warehouse->direction_button ?? '';
            if ($order->lang == 'en') {
                $message = "Dear {$customer_name},\n\n";
                $message .= "Thank you for choosing Tamkeen Stores.\n";
                $message .= "Your order {$order_number}, with a total amount of {$amount_of_order}, has been successfully confirmed. ";
                $message .= "You will be notified once it is ready for pickup.\n\n";
                // $message .= "When collecting your order, please provide the following OTP code for verification: {$otp_code}\n\n";
                $message .= "Pickup Location:\n{$showroom_name}\n{$showroom_location}\n\n";
                $message .= "Thank you,\nTamkeen Stores";

                $responsesms = NotificationHelper::sms($phone, $message);

            } else {
                $message1 = "زي/عزيزتي {$customer_name},\n\n";
                $message2 = "شكرًا لاختارك متاجر تمكين.\n";
                $message3 = "تم تأكيد طلبك ق {$order_number} بنجاح، لغ مالي قدره {$amount_of_order}. سيتم إشر فر جاهيته للاستلم.\n\n";
                $message5 = "مان الستل:\n{$showroom_name}\n{$showroom_location}\n\n";
                $message6 = "شرا لك،\nعارض تمكي";
                
                $message = $message1 . $message2 . $message3 . $message5 . $message6;
                $responsesms = NotificationHelper::sms($phone, $message);

            }

        }
        // else {
        //     if ($order->lang == 'en') {
        //         $responsesms = NotificationHelper::sms($phone,'Dear '.$order->Address->first_name.' '.$order->Address->last_name.'
        //         Thank you for your order.
        //         Order '.$order->order_no.' of SAR '.$order->ordersummary()->where('type','total')->first()->price. ' has been recieved.
                
        //         For More Information Contact Us: 8002444464');
        //     }else{
        //         $responsesms = NotificationHelper::sms($phone, 'زي '.$order->Address->first_name.' '.$order->Address->last_name.'
    
        //         كرا ل عر مو تكن إري..
        //         ق ط  '.$order->order_no.' ال بلغ  '.$order->ordersummary()->where('type','total')->first()->price. '  تلام ال ا.
                
        //         لزد م الم نرج ن اوص  عبر ل لو:: 8002444');
        //     }   
        // }
        else {
            $firstName = $order->Address ? $order->Address->first_name : "";
            $lastName = $order->Address ? $order->Address->last_name : "";
            $orderNumber = $order->order_no;
            $amount = $order->ordersummary()->where('type', 'total')->first()->price ?? 0;
        
            // English Message
            $englishGreeting = "Dear $firstName $lastName ,";
            $englishBody = "Thank you for your order.\nOrder $orderNumber of SAR $amount has been received.\n\nFor more information, contact us: 8002444464";
        
            // Arabic Message
            $arabicGreeting = "عزيزي $firstName $lastName ،";
            $arabicBody = "شرا لتسوقك بر وقع تمكين الالكتوني.\nتم استلام طلبك رقم $orderNumber بمبغ $amount ريال.\n\nللميد ن المعلومات نرجو منك التوا معنا عر الرقم لمحد: 8002444464";
        
            if ($order->lang == 'en') {
                $message = $englishGreeting . "\n\n" . $englishBody;
            } else {
                $message = $arabicGreeting . "\n\n" . $arabicBody;
            }
        
            // Send the SMS
            $responsesms = NotificationHelper::sms($phone, $message);
        }

        //Mail::to($order->UserDetail->email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));
        // if($order->token)
        // $data = NotificationHelper::global_notification([$order->token], 'Congratulations ', 'Your order has been placed its under review.', '',$order->userDevice);
         if($order->token){
                // if ($order->lang == 'en') {
                //     $datanotifi = NotificationHelper::global_notification([$order->token], 'Congratulations ', 'Your order has been placed its under review.', '','','','',$order->userDevice);
                // }
                // else{
                    $datanotifi = NotificationHelper::global_notification([$order->token], ' ', 'ق تم و طلك هو قد را', '','','','',$order->userDevice);
                    // $datanotifi = NotificationHelper::global_notification([$order->token], 'بو ', 'ل م وضع بك و  امرج.', '',$order->userDevice);
                // }
            }
        
        $orderid = $order->id;
        LoyaltyPointsViewJob::dispatch($orderid);
        GiftVoucherViewJob::dispatch($orderid);
        AffiliationViewJob::dispatch($orderid);
        if($walletsData){
            WalletViewJob::dispatch($orderid);
        }
        Mail::to($order->UserDetail->email)->bcc('inv@tamkeenstores.com.sa')->send(new OrderReceived($order));


        // Get loyalty order summary
        $loyaltySummary = $orderSummary->ordersummary->where('type', 'loyalty')->first();

        if ($loyaltySummary) {
            $loyaltyPrice = $loyaltySummary->price;
            $loyaltyPoint = $loyaltyPrice * 100;
            $phoneNumber = $orderSummary->UserDetail->phone_number;
            $usage = $orderSummary->mobileapp == 1 ? 1 : 2;

            $getLoyaltyData = LoyaltyPoints::where('mobile_number', $phoneNumber)->latest()->first();

            // Validate loyalty points balance
            // if (!$getLoyaltyData) {
            //     throw new \Exception("No loyalty account found for this user");
            // }

            // if ($getLoyaltyData->t_loyaltypoints < $loyaltyPoint) {
            //     throw new \Exception("Insufficient loyalty points. Available: {$getLoyaltyData->t_loyaltypoints}, Required: {$loyaltyPoint}");
            // }

            if($getLoyaltyData) {
                $remainingLoyaltyPoints = $getLoyaltyData->t_loyaltypoints - $loyaltyPoint;

                // Ensure points don't go negative (double validation)
                $remainingLoyaltyPoints = max(0, $remainingLoyaltyPoints);

                DB::beginTransaction();
                try {
                    // Insert into loyalty_transactions
                    DB::connection('second_db')->table('loyalty_transactions')->insert([
                        'mobile_number' => $phoneNumber,
                        'type' => 0, // Redemption
                        'usage' => $usage,
                        'earning_against' => $orderSummary->order_no,
                        'loyalty_points' => $loyaltyPoint,
                        't_loyaltypoints' => $remainingLoyaltyPoints,
                        'total_amount' => $loyaltyPrice,
                        'date' => now()->toDateString(),
                        'order_number' => $orderSummary->order_no,
                        'update_from' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Update loyalty_points
                    DB::connection('second_db')->table('loyalty_points')
                        ->where('mobile_number', $phoneNumber)
                        ->update([
                            't_loyaltypoints' => $remainingLoyaltyPoints,
                            'updated_at' => now(),
                        ]);

                    DB::commit();
                } 
                catch (\Exception $e) {
                    DB::rollBack();
                    // throw $e;
                }
            }
        }

        return response()->json(['success' => 'true', 'message' => 'Order Updated Successfully', 'id' => $orderid,'whatsapp' => $response, 'sms' => $responsesms]);
        
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
}