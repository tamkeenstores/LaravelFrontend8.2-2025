<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\GeneralSetting;
use App\Models\FlashSale;
use App\Helper\ProductListingHelper;
use App\Helper\GiftVoucherHelper;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\CacheStores;
use App\Models\Notification;
use App\Models\User;
use App\Models\OrderShipment;
use App\Models\ShipmentDetail;
use App\Models\OrderDetail;
use App\Models\ShipmentActivity;
use App\Models\OrderStatusTimeLine;
use App\Models\ShipmentStatusTimeline;
use App\Mail\PendingOrderEmails;
use App\Models\LiveStock;
use App\Models\RulesConditions;
use App\Models\OrderDetailRegionalQty;
use App\Jobs\CacheViewJob;
use Illuminate\Support\Facades\Cache;
use Mail;
use Carbon\Carbon;
use DateTimeZone;
use DB;
use App\Helper\NotificationHelper;
use SimpleXMLElement; 
use Log;

class GlobalController extends Controller
{
    
    public function testingNoti() {
        $datanotifi = NotificationHelper::global_notification(['ev7hynd_L-mkYfSbiyeWTx:APA91bFf6S6vHKJRWr2QB9_Jw_yeftPreusJq1oNzECWcVudOO6RObXFB0YFsabVxX4JZ_fvonJLXbmLH7QwY47DgPiwreyItkOMDO-ab3h7k8DvR0tJmkA'], 'test', '','');
        print_r($datanotifi);
    }
    public function getCouponAmounts($userid) {
        $succes = false;
        $check = User::where('id', $userid)->first();
        $coupon = Coupon::with('conditionsOnlyVoucher')
            ->whereNotNull('voucher_order_number')
            ->where('status', 1)
            ->where('usage_limit_user', 1)
            ->get();
        
        $totalAmount = 0;
        
        foreach ($coupon as $value) {
            if (count($value->conditionsOnlyVoucher) >= 1) {
                // Corrected the comparison operator
                if ($value->conditionsOnlyVoucher[0]->phone_number == $check->phone_number) {
                    $totalAmount += $value->discount_amount;
                }   
            }
        }

        
        if($totalAmount > 0) {
            $succes = true;
        }
        
        $response = [
            'succes' => $succes,
            'totalAmount' => $totalAmount
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        
    }
    
    public function Menu() {
        
        $seconds = 86400;
        if(Cache::has('menucache'))
            $response = Cache::get('menucache');
        else{
            $promosetting = GeneralSetting::first(['id', 'promotion_category']);
            $promotion = Productcategory::where('status', 1)->where('id', $promosetting->promotion_category)->first(['id', 'name', 'name_arabic', 'slug','image_link_app']);
            
            $menu = Productcategory::with(['child' => function ($q) {
                // 'icon',
                $q->where('status', '1')->where('menu', '1')->orderBy('sort', 'ASC')->select('id', 'parent_id','name', 'name_arabic', 'status', 'menu','slug',  'image_link_app');
            },'child.child' => function ($q) {
                //  'icon',
                $q->where('status', '1')->where('menu', '1')->select('id', 'parent_id','name', 'name_arabic', 'status', 'menu','slug', 'image_link_app');
                //  'icon',
            }])->where('status', 1)->where('menu', 1)->whereNull('parent_id')->orderBy('sort', 'ASC')->get(['id', 'name', 'name_arabic', 'slug', 'parent_id','sort', 'image_link_app']);
            
            $additional_data = Menu::with('ImageData:id,image')->where('status', 1)->orderBy('sort', 'ASC')->get(['id', 'name', 'name_arabic', 'slug', 'status','sort','image']);
            
            $response = [
                'menu' => $menu,
                'promotion' => $promotion,
                'additional_data' => $additional_data
            ];
            // CacheStores::create([
            //     'key' => 'menucache',
            //     'type' => 'menucache'
            // ]);
            Cache::remember('menucache', $seconds, function () use ($response) {
                return $response;
            });
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getFreeGift($id) {
        
        $FreeGiftData = ProductListingHelper::productFreeGifts($id,'jeddah');
        $response = [
            'freegiftdata' => $FreeGiftData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    // Free Gift Cart 
    public function getFreeGiftCart(Request $request) {
        $city = $request->city;
        $productIds = $request->product_ids;
        $qtys = $request->qtys;
        $salePrice = $request->sale_prices;
        $subtotal = $request->subtotal;
        $FreeGiftData = [];
        $productId = '';
        
        if(count($productIds) >= 1) {
            foreach($productIds as $key => $id){
                $FreeGiftData = ProductListingHelper::productFreeGiftsCart($id, $city, $subtotal);   
                $productId = $id;
                if($FreeGiftData !== null) {
                    break;
                }
            }
        }
        
        $response = [
            'freegiftdata' => $FreeGiftData,
            'product_id' => $productId
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getFBT($id) {
        
        $fbtData = ProductListingHelper::productFBT($id,'jeddah');
        $response = [
            'fbtdata' => $fbtData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getExpressDelivery($id) {
        
        $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,'jeddah');
        $response = [
            'expressdeliveryData' => $expressdeliveryData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    public function getBadge($id) {
        
        $badgeData = ProductListingHelper::productBadge($id,'jeddah');
        $response = [
            'badgeData' => $badgeData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getFlashSale($id) {
        
        $FlashSale = FlashSale::where("status",1)->first();
        $response = [
            'flashsale' => $FlashSale
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getERPOrders(Request $request){
        // die();
        $success = false;
        $setdata = $request->all(); 
        $orders = [];
        
        if(isset($setdata['credentials']) && $setdata['credentials'] == 'TamkeenStores2@'){
            $date_created = date('Y-m-d', strtotime($request->created_at));
            // with('orderDetailProduct:id,order_id,product_id,product_name,unit_price,quantity,total','orderDetailProduct.productSku','userShippingData:id,customer_id,first_name,last_name,phone_number,state_id,address,address_label','userShippingData.stateData:id,name,ln_city_code','UserDetail:id,firstname,lastname,phone,email')->
            // select(['id','order_no','erp_status','customer_id','shipping_id','billing_id','coupon_id','coupon_code','subtotal','shipping','discount','total','status','paymentmethod','paymentid','shippingMethod','include_tax','discount_rule_data','repalcedata','fees','discount_rule','discountallowed','giftvoucherallowed','discount_rule_id','discount_rule_bulk','discount_rule_bulk_id','vat_discount','vat_discount_amount','express_option_price','is_delivered','door_step_amount','cod_additional_charges','delivered_date','waybill','shipping_company','created_at'])->/**/
            $orders = Order::with('details.productSku','ordersummary','Address.stateData','UserDetail')->where('erp_status', 0)->whereNotIn('status',['5','7','8'])->whereDate('created_at', $date_created)->select(['id','order_no','erp_status','customer_id','shipping_id','status','paymentmethod','paymentid','shippingMethod','created_at', 'order_type', 'otp_code'])->get();
            
        
            if($orders->toArray()){
                $success = true;
            }
            
            $orderArray = $orders->toArray();
            $orderArrayData = array();
            $previousCode = 'OLN1';
            foreach($orderArray as $key => $orderDataa){
                
                    // print_r($orders[$key]->Address);die();
                    // print_r($orders[$key]->ordersummary->where('type','fee')->pluck('price')->sum());die();
                $orderArrayData[$key]['id'] = $orderDataa['id'];
                $orderArrayData[$key]['order_no'] = $orderDataa['order_no'];
                // $orderArrayData[$key]['erp_status'] = $orderDataa['erp_status'];
                $orderArrayData[$key]['customer_id'] = $orderDataa['customer_id'];
                $orderArrayData[$key]['shipping_id'] = $orderDataa['shipping_id'];
                $orderArrayData[$key]['order_no'] = $orderDataa['order_no'];
                $orderArrayData[$key]['billing_id'] = 0;
                if($orderDataa['order_type'] == 1)
                    $orderArrayData[$key]['pickup_code'] = $orderDataa['otp_code'];
                
                // Order Discount Coupon Data
                $orderArrayData[$key]['coupon_id'] = $orders[$key]->ordersummary->where('type','discount')->pluck('amount_id')->first();
                $orderArrayData[$key]['coupon_code'] = $orders[$key]->ordersummary->where('type','discount')->pluck('name')->first();
                // Order Discount Coupon Data
                
                // $orderArrayData[$key]['subtotal'] = $orders[$key]->ordersummary->where('type','subtotal')->pluck('price')->first();
                
                // Order Subtotal
                $orderSubtotal = $orders[$key]->ordersummary->where('type','subtotal')->pluck('price')->first();
                // $orderDataasubtotal = $orderSubtotal - ($orderSubtotal/115*100);
                // $orderArrayData[$key]['subtotal'] = number_format($orderSubtotal - $orderDataasubtotal, 2, '.', '');
                $orderArrayData[$key]['subtotal'] = $orderSubtotal;
                // Order Subtotal
                
                // Order Shipping
                $orderShipping = $orders[$key]->ordersummary->where('type','shipping')->pluck('price')->first();
                $orderShipping += $orders[$key]->ordersummary->where('type','express')->pluck('price')->sum();
                $orderShippingTotal = $orderShipping - ($orderShipping/115*100);
                $orderArrayData[$key]['shipping'] = number_format($orderShipping - $orderShippingTotal, 2, '.', '');
                // print_r($orderArrayData[$key]['shipping']);die();
                // Order Shipping
                
                // Order Discount Coupon
                // ->orWhere('type','saveamounttotal')
                // $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','discount')->pluck('price')->sum();
                
                $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','discount')->pluck('price')->sum();
                // $orderDiscountRule = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                $orderDiscountCouponTotal = $orderDiscountCoupon - ($orderDiscountCoupon/115*100);
                $orderArrayData[$key]['discount'] = number_format($orderDiscountCoupon - $orderDiscountCouponTotal, 2, '.', '');
                // Order Discount Coupon
                
                // Order Total
                $orderTotal = $orders[$key]->ordersummary->where('type','total')->pluck('price')->first();
                $orderDataatotal = $orderTotal - ($orderTotal/115*100);
                $orderArrayData[$key]['total'] = number_format($orderTotal - $orderDataatotal, 2, '.', '');
                // Order Total
                
                // vat Total
                $orderArrayData[$key]['paymentmethod'] = $orderDataa['paymentmethod'];
                $orderArrayData[$key]['paymentid'] = $orderDataa['paymentid'];
                $orderArrayData[$key]['shippingMethod'] = $orderDataa['order_type'] == 1 ? $orderDataa['shippingMethod'] : 'Pickup From Store';
                $orderArrayData[$key]['include_tax'] = number_format($orders[$key]->ordersummary->where('type','total')->pluck('price')->first() - ($orderTotal - $orderDataatotal), 2, '.', '');
                // vat Total
                
                // Order Discount Rule
                // $orderDiscountRule = $orders[$key]->ordersummary->where('type','discount_rule')->pluck('price')->sum();
                // $orderDataaDiscountRuleTotal = $orderDiscountRule - ($orderDiscountRule/115*100);
                // $orderArrayData[$key]['discount_rule_data'] = number_format($orderDiscountRule - $orderDataaDiscountRuleTotal, 2, '.', '');
                // $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                
                $orderDiscountRule = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                $orderDataaDiscountRuleTotal = $orderDiscountRule - ($orderDiscountRule/115*100);
                $orderArrayData[$key]['discount_rule_data'] = number_format($orderDiscountRule - $orderDataaDiscountRuleTotal, 2, '.', '');
                // $orderArrayData[$key]['discount_rule_data'] = null;
                // Order Discount Rule
                
                // $orderArrayData[$key]['repalcedata'] = "[]";
                
                // Order Fees
                $orderFees = $orders[$key]->ordersummary->where('type','fee')->where('name','!=','COD Fees')->pluck('price')->sum();
                $orderDataaFeestotal = $orderFees - ($orderFees/115*100);
                // $orderArrayData[$key]['fees'] = number_format($orderFees - $orderDataaFeestotal, 2, '.', '');
                // Order Fees
                
                $orderArrayData[$key]['discount_rule'] = null;
                $orderArrayData[$key]['discountallowed'] = 0;
                $orderArrayData[$key]['giftvoucherallowed'] = 0;
                $orderArrayData[$key]['discount_rule_id'] = null;
                $orderArrayData[$key]['discount_rule_bulk'] = null;
                $orderArrayData[$key]['discount_rule_bulk_id'] = null;
                $orderArrayData[$key]['vat_discount'] = 0;
                $orderArrayData[$key]['vat_discount_amount'] = 0;
                
                // Order Express Delivery
                $orderExpressDelivery = $orders[$key]->ordersummary->where('type','express')->pluck('price')->sum();
                $orderDataaorderExpressDeliveryTotal = $orderExpressDelivery - ($orderExpressDelivery/115*100);
                $orderArrayData[$key]['express_option_price'] = number_format($orderExpressDelivery - $orderDataaorderExpressDeliveryTotal, 2, '.', '');
                // Order Express Delivery
                
                $orderArrayData[$key]['is_delivered'] = 0;
                
                // Order DoorStep
                $orderDoorStep = $orders[$key]->ordersummary->where('type','door_step_amount')->pluck('price')->sum();
                $orderDataaDoorStepTotal = $orderDoorStep - ($orderDoorStep/115*100);
                // $orderArrayData[$key]['door_step_amount'] = number_format($orderDoorStep - $orderDataaDoorStepTotal, 2, '.', '');
                // Order DoorStep
                
                
                // Order COD Additional Charges
                $orderCOD = $orders[$key]->ordersummary->where('type','fee')->where('name','=','COD Fees')->pluck('price')->sum();
                $orderCODTotal = $orderCOD - ($orderCOD/115*100);
                // $orderArrayData[$key]['cod_additional_charges'] = number_format($orderCOD - $orderCODTotal, 2, '.', '');
                // Order COD Additional Charges
                
                
                // $orderArrayData[$key]['cod_additional_charges'] = null;
                $orderArrayData[$key]['delivered_date'] = null;
                // $orderArrayData[$key]['waybill'] = null;
                // $orderArrayData[$key]['shipping_company'] = null;
                $orderArrayData[$key]['created_at'] = $orders[$key]->created_at->format('Y-m-d H:i:s');

                // Add schedule_date calculation
                $createdAt = $orders[$key]->created_at;
                $dayOfWeek = $createdAt->format('N');
                
                if ($dayOfWeek == 5) {
                    $scheduleDate = $createdAt->addDays(2)->format('Y-m-d');
                } else {
                    $scheduleDate = $createdAt->addDays(1)->format('Y-m-d');
                }
                
                $orderArrayData[$key]['schedule_date'] = $scheduleDate;

                foreach($orders[$key]->details as $k => $productData){
                    
                    
                    // if(!in_array($productData->productSku['sku'], ['SP3M', 'SP6M','SP12M'])){
                        if($productData->productSku->is_bundle === 1){
                            $explodeproducts = explode("+",$productData->productSku->ln_sku);
                            $bundleSkuupdate = '';
                            foreach($explodeproducts as $proKey => $explodeproduct){
                                    $bundleProduct = Product::where('ln_sku',$explodeproduct)->first(['id','sku','ln_sku','bundle_price']);
                                    // print_r($bundleProduct);die();
                                    // Product Unit Price 
                                    $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                                    $unitpriceupdate = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                                    
                                    // Product Total Price 
                                    $productDatatotal = $productData['total'] - ($productData['total']/115*100);
                                    $totalupdated = number_format($productData['total'] - $productDatatotal, 2, '.', '');
                                    
                                    // $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                                    // $productDatatotal = $productData['total'] - ($productData['total']/115*100);
                                    $regionalStock = OrderDetailRegionalQty::select('warehouse_code',DB::raw('SUM(qty) as quantity'))->where('order_detail_id',$productData['id'])->whereNot('qty',0)->groupBy('warehouse_code')->get();
                                    // print_r($totalupdated);die();
                                     if($regionalStock->count() == 0){
                                        $regionalStock = OrderDetail::select('quantity')->where('id',$productData['id'])->first();
                                        $regionalStock['warehouse_code'] = $previousCode;
                                    }
                                    $ArrayData = array(
                                        'id'=>$productData['id'],
                                        'order_id'=>$productData['order_id'],
                                        'product_id'=>$productData['product_id'],
                                        'regional_stock' => $regionalStock,
                                        'product_name'=>$productData['product_name'],
                                        'unit_price'=> $proKey > 0 ? 0 : $totalupdated,
                                        'quantity'=> $productData['quantity'],
                                        'total'=> $proKey > 0 ? 0 : $totalupdated,
                                        'product_sku'=>array(
                                            'id'=>$productData['id'],
                                            'sku'=>$explodeproduct,
                                            'is_bundle'=>1
                                            ),
                                        );
                                    $orderArrayData[$key]['order_detail_product'][] = $ArrayData; 
                                } 
                                
                        }
                        else{
                            
                            // Product Unit Price 
                            $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                            $unitpriceupdate = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                            
                            // Product Total Price 
                            $productDatatotal = $productData['total'] - ($productData['total']/115*100);
                            $totalupdated = number_format($productData['total'] - $productDatatotal, 2, '.', '');
                            // Product Total Price 
                            $regionalStock = OrderDetailRegionalQty::select('warehouse_code',DB::raw('SUM(qty) as quantity'))->where('order_detail_id',$productData['id'])->whereNot('qty',0)->groupBy('warehouse_code')->get();
                            if($regionalStock->count() == 0){
                                $regionalStock = OrderDetail::select('quantity')->where('id',$productData['id'])->first();
                                $regionalStock['warehouse_code'] = $previousCode;
                            }
                            $ArrayData = array(
                                        'id'=>$productData['id'],
                                        'order_id'=>$productData['order_id'],
                                        'product_id'=>$productData['product_id'],
                                        'regional_stock' => $regionalStock,
                                        'product_name'=>$productData['product_name'],
                                        'unit_price'=>$unitpriceupdate,
                                        'quantity'=>$productData['quantity'],
                                        'total'=>$totalupdated,
                                        'product_sku'=>array(
                                            'id'=>$productData['id'],
                                            'sku'=>$productData->productSku->ln_sku,
                                            'is_bundle'=>0
                                            ),
                                        );
                                    $orderArrayData[$key]['order_detail_product'][] = $ArrayData; 
                        }
                    // }
                    
                }
                
                // Shiiping Data
                if(isset($orders[$key]->Address)){
                    $orderArrayData[$key]['user_shipping_data']['id'] = $orders[$key]->Address->id;
                    $orderArrayData[$key]['user_shipping_data']['customer_id'] = $orders[$key]->Address->customer_id;
                    $orderArrayData[$key]['user_shipping_data']['first_name'] = $orders[$key]->Address->first_name;
                    $orderArrayData[$key]['user_shipping_data']['last_name'] = $orders[$key]->Address->last_name;
                    $orderArrayData[$key]['user_shipping_data']['phone_number'] = $orders[$key]->Address->phone_number;
                    $orderArrayData[$key]['user_shipping_data']['state_id'] = $orders[$key]->Address->state_id;
                    $orderArrayData[$key]['user_shipping_data']['address'] = $orders[$key]->Address->address;
                    $orderArrayData[$key]['user_shipping_data']['address_label'] = $orders[$key]->Address->address_label;
                    
                    if($orders[$key]->Address->stateData){
                        $orderArrayData[$key]['user_shipping_data']['state_data']['id'] = $orders[$key]->Address->stateData->id;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['name'] = $orders[$key]->Address->stateData->name;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['ln_city_code'] = $orders[$key]->Address->stateData->ln_city_code;
                    }else{
                        $orderArrayData[$key]['user_shipping_data']['state_data']['id'] = null;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['name'] = null;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['ln_city_code'] = null;
                    }
                }
                // Shiiping Data
                
                // User Data
                
                if($orders[$key]->UserDetail){
                    $orderArrayData[$key]['user_detail']['id'] = $orders[$key]->UserDetail->id;
                    $orderArrayData[$key]['user_detail']['firstname'] = $orders[$key]->UserDetail->first_name;
                    $orderArrayData[$key]['user_detail']['lastname'] = $orders[$key]->UserDetail->last_name;
                    $orderArrayData[$key]['user_detail']['phone'] = $orders[$key]->UserDetail->phone_number;
                    $orderArrayData[$key]['user_detail']['email'] = $orders[$key]->UserDetail->email;
                }else{
                    $orderArrayData[$key]['user_detail']['id'] = null;
                    $orderArrayData[$key]['user_detail']['firstname'] = null;
                    $orderArrayData[$key]['user_detail']['lastname'] = null;
                    $orderArrayData[$key]['user_detail']['phone'] = null;
                    $orderArrayData[$key]['user_detail']['email'] = null;
                }
                // User Data
                
                
                // print_r($orderArrayData[$key]);die();
            }
            
            // print_r($orders->toArray());die();
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
    
    // public function getERPOrders(Request $request){
    //     // die();
    //     $success = false;
    //     $setdata = $request->all(); 
    //     $orders = [];
        
    //     if(isset($setdata['credentials']) && $setdata['credentials'] == 'TamkeenStores2@'){
    //         $date_created = date('Y-m-d', strtotime($request->created_at));
    //         // with('orderDetailProduct:id,order_id,product_id,product_name,unit_price,quantity,total','orderDetailProduct.productSku','userShippingData:id,customer_id,first_name,last_name,phone_number,state_id,address,address_label','userShippingData.stateData:id,name,ln_city_code','UserDetail:id,firstname,lastname,phone,email')->
    //         // select(['id','order_no','erp_status','customer_id','shipping_id','billing_id','coupon_id','coupon_code','subtotal','shipping','discount','total','status','paymentmethod','paymentid','shippingMethod','include_tax','discount_rule_data','repalcedata','fees','discount_rule','discountallowed','giftvoucherallowed','discount_rule_id','discount_rule_bulk','discount_rule_bulk_id','vat_discount','vat_discount_amount','express_option_price','is_delivered','door_step_amount','cod_additional_charges','delivered_date','waybill','shipping_company','created_at'])->/**/
    //         $orders = Order::with('details.productSku','ordersummary','Address.stateData','UserDetail')->where('erp_status', 0)->whereNotIn('status',['5','7','8'])->whereDate('created_at', $date_created)->select(['id','order_no','erp_status','customer_id','shipping_id','status','paymentmethod','paymentid','shippingMethod','created_at'])->get();
            
        
    //         if($orders->toArray()){
    //             $success = true;
    //         }
            
    //         $orderArray = $orders->toArray();
    //         $orderArrayData = array();
            
    //         foreach($orderArray as $key => $orderDataa){
                
    //                 // print_r($orders[$key]->Address);die();
    //                 // print_r($orders[$key]->ordersummary->where('type','fee')->pluck('price')->sum());die();
    //             $orderArrayData[$key]['id'] = $orderDataa['id'];
    //             $orderArrayData[$key]['order_no'] = $orderDataa['order_no'];
    //             $orderArrayData[$key]['erp_status'] = $orderDataa['erp_status'];
    //             $orderArrayData[$key]['customer_id'] = $orderDataa['customer_id'];
    //             $orderArrayData[$key]['shipping_id'] = $orderDataa['shipping_id'];
    //             $orderArrayData[$key]['order_no'] = $orderDataa['order_no'];
    //             $orderArrayData[$key]['billing_id'] = 0;
                
    //             // Order Discount Coupon Data
    //             $orderArrayData[$key]['coupon_id'] = $orders[$key]->ordersummary->where('type','discount')->pluck('amount_id')->first();
    //             $orderArrayData[$key]['coupon_code'] = $orders[$key]->ordersummary->where('type','discount')->pluck('name')->first();
    //             // Order Discount Coupon Data
                
    //             // $orderArrayData[$key]['subtotal'] = $orders[$key]->ordersummary->where('type','subtotal')->pluck('price')->first();
                
    //             // Order Subtotal
    //             $orderSubtotal = $orders[$key]->ordersummary->where('type','subtotal')->pluck('price')->first();
    //             // $orderDataasubtotal = $orderSubtotal - ($orderSubtotal/115*100);
    //             // $orderArrayData[$key]['subtotal'] = number_format($orderSubtotal - $orderDataasubtotal, 2, '.', '');
    //             $orderArrayData[$key]['subtotal'] = $orderSubtotal;
    //             // Order Subtotal
                
    //             // Order Shipping
    //             $orderShipping = $orders[$key]->ordersummary->where('type','shipping')->pluck('price')->first();
    //             $orderShipping += $orders[$key]->ordersummary->where('type','express')->pluck('price')->sum();
    //             $orderShippingTotal = $orderShipping - ($orderShipping/115*100);
    //             $orderArrayData[$key]['shipping'] = number_format($orderShipping - $orderShippingTotal, 2, '.', '');
    //             // Order Shipping
                
    //             // Order Discount Coupon
    //             // ->orWhere('type','saveamounttotal')
    //             // $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','discount')->pluck('price')->sum();
                
    //             $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','discount')->pluck('price')->sum();
    //             // $orderDiscountRule = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
    //             $orderDiscountCouponTotal = $orderDiscountCoupon - ($orderDiscountCoupon/115*100);
    //             $orderArrayData[$key]['discount'] = number_format($orderDiscountCoupon - $orderDiscountCouponTotal, 2, '.', '');
    //             // Order Discount Coupon
                
    //             // Order Total
    //             $orderTotal = $orders[$key]->ordersummary->where('type','total')->pluck('price')->first();
    //             $orderDataatotal = $orderTotal - ($orderTotal/115*100);
    //             $orderArrayData[$key]['total'] = number_format($orderTotal - $orderDataatotal, 2, '.', '');
    //             // Order Total
                
    //             // vat Total
    //             $orderArrayData[$key]['paymentmethod'] = $orderDataa['paymentmethod'];
    //             $orderArrayData[$key]['paymentid'] = $orderDataa['paymentid'];
    //             $orderArrayData[$key]['shippingMethod'] = $orderDataa['shippingMethod'];
    //             $orderArrayData[$key]['include_tax'] = number_format($orders[$key]->ordersummary->where('type','total')->pluck('price')->first() - ($orderTotal - $orderDataatotal), 2, '.', '');
    //             // vat Total
                
    //             // Order Discount Rule
    //             // $orderDiscountRule = $orders[$key]->ordersummary->where('type','discount_rule')->pluck('price')->sum();
    //             // $orderDataaDiscountRuleTotal = $orderDiscountRule - ($orderDiscountRule/115*100);
    //             // $orderArrayData[$key]['discount_rule_data'] = number_format($orderDiscountRule - $orderDataaDiscountRuleTotal, 2, '.', '');
    //             // $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                
    //             $orderDiscountRule = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
    //             $orderDataaDiscountRuleTotal = $orderDiscountRule - ($orderDiscountRule/115*100);
    //             $orderArrayData[$key]['discount_rule_data'] = number_format($orderDiscountRule - $orderDataaDiscountRuleTotal, 2, '.', '');
    //             // $orderArrayData[$key]['discount_rule_data'] = null;
    //             // Order Discount Rule
                
    //             $orderArrayData[$key]['repalcedata'] = "[]";
                
    //             // Order Fees
    //             $orderFees = $orders[$key]->ordersummary->where('type','fee')->where('name','!=','COD Fees')->pluck('price')->sum();
    //             $orderDataaFeestotal = $orderFees - ($orderFees/115*100);
    //             $orderArrayData[$key]['fees'] = number_format($orderFees - $orderDataaFeestotal, 2, '.', '');
    //             // Order Fees
                
    //             $orderArrayData[$key]['discount_rule'] = null;
    //             $orderArrayData[$key]['discountallowed'] = 0;
    //             $orderArrayData[$key]['giftvoucherallowed'] = 0;
    //             $orderArrayData[$key]['discount_rule_id'] = null;
    //             $orderArrayData[$key]['discount_rule_bulk'] = null;
    //             $orderArrayData[$key]['discount_rule_bulk_id'] = null;
    //             $orderArrayData[$key]['vat_discount'] = 0;
    //             $orderArrayData[$key]['vat_discount_amount'] = 0;
                
    //             // Order Express Delivery
    //             $orderExpressDelivery = $orders[$key]->ordersummary->where('type','express')->pluck('price')->sum();
    //             $orderDataaorderExpressDeliveryTotal = $orderExpressDelivery - ($orderExpressDelivery/115*100);
    //             $orderArrayData[$key]['express_option_price'] = number_format($orderExpressDelivery - $orderDataaorderExpressDeliveryTotal, 2, '.', '');
    //             // Order Express Delivery
                
    //             $orderArrayData[$key]['is_delivered'] = 0;
                
    //             // Order DoorStep
    //             $orderDoorStep = $orders[$key]->ordersummary->where('type','door_step_amount')->pluck('price')->sum();
    //             $orderDataaDoorStepTotal = $orderDoorStep - ($orderDoorStep/115*100);
    //             $orderArrayData[$key]['door_step_amount'] = number_format($orderDoorStep - $orderDataaDoorStepTotal, 2, '.', '');
    //             // Order DoorStep
                
                
    //             // Order COD Additional Charges
    //             $orderCOD = $orders[$key]->ordersummary->where('type','fee')->where('name','=','COD Fees')->pluck('price')->sum();
    //             $orderCODTotal = $orderCOD - ($orderCOD/115*100);
    //             $orderArrayData[$key]['cod_additional_charges'] = number_format($orderCOD - $orderCODTotal, 2, '.', '');
    //             // Order COD Additional Charges
                
                
    //             // $orderArrayData[$key]['cod_additional_charges'] = null;
    //             $orderArrayData[$key]['delivered_date'] = null;
    //             $orderArrayData[$key]['waybill'] = null;
    //             $orderArrayData[$key]['shipping_company'] = null;
    //             $orderArrayData[$key]['created_at'] = $orders[$key]->created_at->format('Y-m-d H:i:s');
    //             foreach($orders[$key]->details as $k => $productData){
                    
                    
                    
    //                 if($productData->productSku->is_bundle === 1){
    //                     $explodeproducts = explode("+",$productData->productSku->ln_sku);
    //                     $bundleSkuupdate = '';
    //                     foreach($explodeproducts as $proKey => $explodeproduct){
    //                             $bundleProduct = Product::where('ln_sku',$explodeproduct)->first(['id','sku','ln_sku','bundle_price']);
    //                             // print_r($bundleProduct);die();
    //                             // Product Unit Price 
    //                             $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
    //                             $unitpriceupdate = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                                
    //                             // Product Total Price 
    //                             $productDatatotal = $productData['total'] - ($productData['total']/115*100);
    //                             $totalupdated = number_format($productData['total'] - $productDatatotal, 2, '.', '');
                                
    //                             // $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
    //                             // $productDatatotal = $productData['total'] - ($productData['total']/115*100);
    //                              $regionalStock = OrderDetailRegionalQty::select('warehouse_code',DB::raw('SUM(qty) as quantity'))->where('order_detail_id',$productData['id'])->whereNot('qty',0)->groupBy('warehouse_code')->get();
    //                             // print_r($totalupdated);die();
    //                             if($regionalStock->count() == 0){
    //                                 $regionalStock = OrderDetail::select('quantity')->where('id',$productData['id'])->first();
    //                                 $regionalStock['warehouse_code'] = 'OLN1';
    //                             }
    //                             $ArrayData = array(
    //                                 'id'=>$productData['id'],
    //                                 'order_id'=>$productData['order_id'],
    //                                 'product_id'=>$productData['product_id'],
    //                                 'regional_stock' => $regionalStock,
    //                                 'product_name'=>$productData['product_name'],
    //                                 'unit_price'=> $proKey > 0 ? 0 : $totalupdated,
    //                                 'quantity'=> $productData['quantity'],
    //                                 'total'=> $proKey > 0 ? 0 : $totalupdated,
    //                                 'product_sku'=>array(
    //                                     'id'=>$productData['id'],
    //                                     'sku'=>$explodeproduct,
    //                                     'is_bundle'=>1
    //                                     ),
    //                                 );
    //                             $orderArrayData[$key]['order_detail_product'][] = $ArrayData; 
    //                         } 
                            
    //                 }
    //                 else{
                        
    //                     // Product Unit Price 
    //                     $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
    //                     $unitpriceupdate = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                        
    //                     // Product Total Price 
    //                     $productDatatotal = $productData['total'] - ($productData['total']/115*100);
    //                     $totalupdated = number_format($productData['total'] - $productDatatotal, 2, '.', '');
    //                     // Product Total Price 
    //                      $regionalStock = OrderDetailRegionalQty::select('warehouse_code',DB::raw('SUM(qty) as quantity'))->where('order_detail_id',$productData['id'])->whereNot('qty',0)->groupBy('warehouse_code')->get();
    //                      if($regionalStock->count() == 0){
    //                         $regionalStock = OrderDetail::select('quantity')->where('id',$productData['id'])->first();
    //                         $regionalStock['warehouse_code'] = 'OLN1';
    //                     }
    //                     $ArrayData = array(
    //                                 'id'=>$productData['id'],
    //                                 'order_id'=>$productData['order_id'],
    //                                 'product_id'=>$productData['product_id'],
    //                                 'regional_stock' => $regionalStock,
    //                                 'product_name'=>$productData['product_name'],
    //                                 'unit_price'=>$unitpriceupdate,
    //                                 'quantity'=>$productData['quantity'],
    //                                 'total'=>$totalupdated,
    //                                 'product_sku'=>array(
    //                                     'id'=>$productData['id'],
    //                                     'sku'=>$productData->productSku->ln_sku,
    //                                     'is_bundle'=>0
    //                                     ),
    //                                 );
    //                             $orderArrayData[$key]['order_detail_product'][] = $ArrayData; 
    //                 }
                    
    //             }
                
    //             // Shiiping Data
    //             if(isset($orders[$key]->Address)){
    //                 $orderArrayData[$key]['user_shipping_data']['id'] = $orders[$key]->Address->id;
    //                 $orderArrayData[$key]['user_shipping_data']['customer_id'] = $orders[$key]->Address->customer_id;
    //                 $orderArrayData[$key]['user_shipping_data']['first_name'] = $orders[$key]->Address->first_name;
    //                 $orderArrayData[$key]['user_shipping_data']['last_name'] = $orders[$key]->Address->last_name;
    //                 $orderArrayData[$key]['user_shipping_data']['phone_number'] = $orders[$key]->Address->phone_number;
    //                 $orderArrayData[$key]['user_shipping_data']['state_id'] = $orders[$key]->Address->state_id;
    //                 $orderArrayData[$key]['user_shipping_data']['address'] = $orders[$key]->Address->address;
    //                 $orderArrayData[$key]['user_shipping_data']['address_label'] = $orders[$key]->Address->address_label;
                    
    //                 if($orders[$key]->Address->stateData){
    //                     $orderArrayData[$key]['user_shipping_data']['state_data']['id'] = $orders[$key]->Address->stateData->id;
    //                     $orderArrayData[$key]['user_shipping_data']['state_data']['name'] = $orders[$key]->Address->stateData->name;
    //                     $orderArrayData[$key]['user_shipping_data']['state_data']['ln_city_code'] = $orders[$key]->Address->stateData->ln_city_code;
    //                 }else{
    //                     $orderArrayData[$key]['user_shipping_data']['state_data']['id'] = null;
    //                     $orderArrayData[$key]['user_shipping_data']['state_data']['name'] = null;
    //                     $orderArrayData[$key]['user_shipping_data']['state_data']['ln_city_code'] = null;
    //                 }
    //             }
    //             // Shiiping Data
                
    //             // User Data
                
    //             if($orders[$key]->UserDetail){
    //                 $orderArrayData[$key]['user_detail']['id'] = $orders[$key]->UserDetail->id;
    //                 $orderArrayData[$key]['user_detail']['firstname'] = $orders[$key]->UserDetail->first_name;
    //                 $orderArrayData[$key]['user_detail']['lastname'] = $orders[$key]->UserDetail->last_name;
    //                 $orderArrayData[$key]['user_detail']['phone'] = $orders[$key]->UserDetail->phone_number;
    //                 $orderArrayData[$key]['user_detail']['email'] = $orders[$key]->UserDetail->email;
    //             }else{
    //                 $orderArrayData[$key]['user_detail']['id'] = null;
    //                 $orderArrayData[$key]['user_detail']['firstname'] = null;
    //                 $orderArrayData[$key]['user_detail']['lastname'] = null;
    //                 $orderArrayData[$key]['user_detail']['phone'] = null;
    //                 $orderArrayData[$key]['user_detail']['email'] = null;
    //             }
    //             // User Data
                
                
    //             // print_r($orderArrayData[$key]);die();
    //         }
            
    //         // print_r($orders->toArray());die();
    //     }
    //     $response = ['success' => $success,'data' => $orderArrayData];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
    // public function UpdateLninforById($id,$data = false,Request $request){
    //     // print_r($id);
    //     $arrayids = explode(',', $id);
    //     $arraydata = explode(',', $data);
    //     // print_r($arrayids);
    //     // echo '<br/>';
    //     // print_r($data);
    //     // print_r($arraydata);
    //     // die();
    //     $success = false;
    //     $message = 'Please add update value';
    //     if(sizeof($arraydata)){
    //         $orders = Order::whereIn('order_no', $arrayids)->get(['id','order_no','erp_status']);
    //         if(sizeof($orders)){
    //             // print_r($orders);
    //             foreach($orders as $key => $order){
    //                 if(isset($arraydata[$key]) && $arraydata[$key] != null){
    //                     $order = Order::where('order_no', $order->order_no)->update(['erp_status'=>$arraydata[$key],'erp_fetch_date'=>Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d'),'erp_fetch_time'=>Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('H:i:s')]);
    //                     if($order){
    //                         $success = true;
    //                         $message = 'Order has been Updated!';
    //                     }
    //                 }
                    
    //             }
    //         }else{
    //             $message = 'order number does not exist!';
    //         }
            
    //         // if(!$order){
    //         //     $message = 'order number does not exist!';
    //         // }
    //         // if($order){
    //         //     $order->ln_infor = $data;
    //         //     $order->update();
    //         //     $success = true;
    //         //     $message = 'Order has been Updated!';
    //         // }
    //     }
        
    //     return response()->json(['success' => $success,'message' => $message]);
        
    // }
    public function UpdateLninforById($id, $data = false, Request $request) {
        $arrayids = explode(',', $id);
        $arraydata = explode(',', $data);
        $arraylndata = isset($request->ln) && $request->ln != null ? explode(',', $request->ln) : [];
        $success = false;
        $message = 'Please add update value';

        if (sizeof($arraydata)) {
            $orders = Order::whereIn('order_no', $arrayids)->get(['id', 'order_no', 'erp_status', 'madac_id', 'order_type', 'otp_code', 'shipping_id', 'lang', 'customer_id', 'delivery_date']);

            if (sizeof($orders)) {
                foreach ($orders as $key => $order) {
                    if (isset($arraydata[$key]) && $arraydata[$key] != null) {
                        $madacid = $arraylndata[$key] ?? null;

                        $orderData = Order::where('order_no', $order->order_no)->update([
                            'erp_status' => $arraydata[$key],
                            'erp_fetch_date' => Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d'),
                            'erp_fetch_time' => Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('H:i:s'),
                            'madac_id' => $madacid
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
                                    "text" => (string)($order->otp_code ?? '')
                                ]
                            ];
                            $response = NotificationHelper::whatsappmessage("+966".$order->UserDetail->phone_number,'pickupfromstore_2025_secondmessage_final',$order->lang,$paramsPickUpFromStore);
                            // print_r($response);die;
                            $phone = str_replace("-","","+966".$order->UserDetail->phone_number);
                            $phone = str_replace("_","",$phone);
                            $responsesms = false;
                            // pickup from store
                            $customer_name = optional($order->Address)->first_name . ' ' . optional($order->Address)->last_name;
                            $orderCheck_number = $order->order_no ?? '';
                            $amount_of_order = optional($order->ordersummary()->where('type', 'total')->first())->price ?? '';
                            $otp_code = $order->otp_code ?? ''; // Ensure this is defined earlier
                            $showroom_name = $order->lang == 'ar' ? optional($order->warehouse)->showroom_arabic : optional($order->warehouse)->showroom;
                            $showroom_location = optional($order->warehouse)->direction_button ?? '';
                            if ($order->lang == 'en') {
                                $message1 = "Dear {$customer_name},\n\n";
                                $message2 = "Your order {$orderCheck_number} has been ready for collection from the store. The confirmation number is {$otp_code}.\n\n";
                                $message3 = "The showroom collection timings are:\n";
                                $message4 = "Saturday to Thursday: 09:30 AM to 12:00 AM\n";
                                $message5 = "Friday: 03:30 PM to 12:00 AM\n\n";
                                $message6 = "Thank you,\nTamkeen Stores";
                                
                                $message_en = $message1 . $message2 . $message3 . $message4 . $message5 . $message6;
                                $responsesms = NotificationHelper::sms($phone, $message_en);
        
                            } else {
                                $message1_ar = "عي/عيتي {$customer_name},\n\n";
                                $message2_ar = "طل {$orderCheck_number} اه لستا ن اعرض، رم لأكد هو {$otp_code}.\n\n";
                                $message3_ar = "واعد تم اللت من لمعاض ي:\n";
                                $message4_ar = "من ت لى ميس: من 9:30 صبا إى 12:00 صاحً\n";
                                $message5_ar = "الجمة: ن 3:30 مء إى 12:00 صاا\n\n";
                                $message6_ar = "شرً ك\nمعار تين";
                                
                                $message_ar = $message1_ar . $message2_ar . $message3_ar . $message4_ar . $message5_ar . $message6_ar;
        
                                $responsesms = NotificationHelper::sms($phone, $message_ar);
                            }
                        }
                        if($order->order_type != 1){
                            $checkShipment = OrderShipment::where('order_id', $order->id)->first();
    
                            if (!$checkShipment) {
                                $orderDatePlusOne = Carbon::parse($order->created_at)->addDay()->format('Y-m-d');
                                $checkShipmentCondition = OrderDetail::with('OrderDetailRegionalQty.warehouseData')->where('order_id', $order->id)->get();
                                $express = [];
                                $normal = [];
    
    
                                if (count($checkShipmentCondition) >= 1) {
                                    foreach ($checkShipmentCondition as $val) {
                                        $checkPro = Product::find($val->product_id);
                                        $warehouse = (!empty($val->OrderDetailRegionalQty) && isset($val->OrderDetailRegionalQty[0]->warehouseData)) 
                                        ? $val->OrderDetailRegionalQty[0]->warehouseData 
                                        : null;
                                        $warehouseId = isset($warehouse) && isset($warehouse->id) ? $warehouse->id : 0;
                                        $logisticUserId = 0;
                                        $waybillCreatorId = 182926;
    
                                        if ($warehouseId) {
                                            $warehouseCode = $warehouse->ln_code;
                                            $waybillCity = $warehouse->waybill_city;
    
                                            // if ($waybillCity) {
                                                $logisticUserId = User::where('role_id', 49)
                                                    ->whereIn('id', function ($query) use ($warehouseId) {
                                                        $query->select('user_id')
                                                            ->from('user_warehouses')
                                                            ->where('warehouse_id', $warehouseId);
                                                    })
                                                    ->value('id');
                                            // } 
                                        }
    
                                        if ($checkPro) {
                                            $shipmentData = [
                                                'order_detail_id' => $val->id,
                                                'quantity' => $val->expressproduct == 1 ? $val->express_qty : $val->quantity,
                                                'product_id' => $checkPro->id,
                                                'warehouse' => $warehouseId,
                                                'logistic_supervisor' => $logisticUserId,
                                                'waybill_creator_id' => $waybillCreatorId,
                                            ];
    
                                            if ($val->expressproduct == 1) {
                                                $express[$warehouseId][] = $shipmentData;
    
                                                if ($val->quantity > $val->express_qty) {
                                                    $normal[$warehouseId][] = [
                                                        'order_detail_id' => $val->id,
                                                        'quantity' => $val->quantity - $val->express_qty,
                                                        'product_id' => $checkPro->id,
                                                        'warehouse' => $warehouseId,
                                                        'logistic_supervisor' => $logisticUserId,
                                                        'waybill_creator_id' => $waybillCreatorId,
                                                    ];
                                                }
                                            } else {
                                                $normal[$warehouseId][] = $shipmentData;
                                            }
                                        }
                                    }
                                }
    
                                // Normal Shipments (shipment_type = 0)
                                foreach ($normal as $warehouseId => $items) {
                                    $number = hexdec(uniqid());
                                    $ran = substr($number, -3);
                                    $shipmentNo = 'SH-' . $madacid . $ran;
    
                                    $shipment = OrderShipment::create([
                                        'order_id' => $order->id,
                                        'shipment_type' => 0,
                                        'shipment_no' => $shipmentNo,
                                        'warehouse' => $warehouseId,
                                        'logistic_supervisor' => $items[0]['logistic_supervisor'] ?? null, // Get the logistic supervisor ID for this warehouse
                                        'waybill_creator_id' => $items[0]['waybill_creator_id'] ?? null,
                                        'preferred_date' => (!empty($order->delivery_date) && $order->delivery_date != '0') ? $order->delivery_date : null,
                                    ]);
    
                                    ShipmentActivity::create([
                                        'shipment_id' => $shipment->id,
                                        // 'user_id' => $userid,
                                        'comment' => 'New shipment created',
                                        'comment_arabic' => 'تم  ش يه',
                                    ]);

                                    Log::info('Shipment Created Through ERP LN update API', [
                                        'order_no' => $order->order_no,
                                        'shipment_id' => $shipment->id,
                                        'type' => 'Normal',
                                        'shipment_no' => $shipment->shipment_no
                                    ]);
    
                                    $con = 0;
                                    foreach ($items as $val) {
                                        $checkPro = Product::find($val['product_id']);
                                        $barcodeNo = null;
    
                                        for ($i = 0; $i < $val['quantity']; $i++) {
                                            $con++;
                                            if ($checkPro && $checkPro->installation_product != 1) {
                                                $barcodeNo = 'SH-' . $madacid . $ran . '000' . $con;
                                            }
    
                                            ShipmentDetail::create([
                                                'order_detail_id' => $val['order_detail_id'],
                                                'shipment_id' => $shipment->id,
                                                'quantity' => 1,
                                                'barcode' => $barcodeNo,
                                            ]);
                                        }
                                    }
    
                                    ShipmentStatusTimeline::where('shipment_id', $shipment->id)->where('status', 0)->delete();
                                    ShipmentStatusTimeline::create([
                                        'shipment_id' => $shipment->id,
                                        'status' => 0,
                                    ]);
                                }
    
                                // Express Shipments (shipment_type = 1)
                                foreach ($express as $warehouseId => $items) {
                                    $number = hexdec(uniqid());
                                    $ran = substr($number, -3);
                                    $shipmentNo = 'SH-' . $madacid . $ran;
    
                                    $shipment = OrderShipment::create([
                                        'order_id' => $order->id,
                                        'shipment_type' => 1,
                                        'shipment_no' => $shipmentNo,
                                        'warehouse' => $warehouseId,
                                        'preferred_date' => $orderDatePlusOne,
                                        'logistic_supervisor' => $items[0]['logistic_supervisor'], // Get the logistic supervisor ID for this warehouse
                                        'waybill_creator_id' => $items[0]['waybill_creator_id'] ?? null,
                                    ]);
    
                                    ShipmentActivity::create([
                                        'shipment_id' => $shipment->id,
                                        // 'user_id' => $userid,
                                        'comment' => 'New shipment created',
                                        'comment_arabic' => 'م نشا شح ج',
                                    ]);

                                    Log::info('Shipment Created Through ERP LN update API', [
                                        'order_no' => $order->order_no,
                                        'shipment_id' => $shipment->id,
                                        'type' => 'Express',
                                        'shipment_no' => $shipment->shipment_no
                                    ]);
    
                                    $con = 0;
                                    foreach ($items as $val) {
                                        $checkPro = Product::find($val['product_id']);
                                        $barcodeNo = null;
    
                                        for ($i = 0; $i < $val['quantity']; $i++) {
                                            $con++;
                                            if ($checkPro && $checkPro->installation_product != 1) {
                                                $barcodeNo = 'SH-' . $madacid . $ran . '000' . $con;
                                            }
    
                                            ShipmentDetail::create([
                                                'order_detail_id' => $val['order_detail_id'],
                                                'shipment_id' => $shipment->id,
                                                'quantity' => 1,
                                                'barcode' => $barcodeNo,
                                            ]);
                                        }
                                    }
    
                                    ShipmentStatusTimeline::where('shipment_id', $shipment->id)->where('status', 0)->delete();
                                    ShipmentStatusTimeline::create([
                                        'shipment_id' => $shipment->id,
                                        'status' => 0,
                                    ]);
                                }
                            }
                        }
                    }
                }

                $success = true;
                $message = 'Order has been Updated!';
            } else {
                $message = 'Order number does not exist!';
            }
        }

        return response()->json(['success' => $success, 'message' => $message]);
    }
    
    public function UpdateStatusLninforById($id,$data = false,Request $request){
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
        
        return response()->json(['success' => $success,'message' => $message]);
        
    }
    
    
    public function orderExport()
    {   
        // print_r('test');die();
        $generalsetting = GeneralSetting::first();
        echo $initial_date = $generalsetting->orderexportdate;
        echo '<br/>';
        echo $final_date = date("Y-m-d", strtotime('+1 day', strtotime($initial_date)));
        // print_r($final_date);die;
        $generalsetting->orderexportdate = $final_date;
        $generalsetting->save();
        
        $orders = Order::with('details.productSku','ordersummary','Address.stateData','UserDetail')->where('status', '<', 3)->whereDate('created_at', $final_date)->get();
        // $orders = Order::with('orderdetailData.productDataa','orderData','billingAddressData','shippingAddressData.stateData','customerData')->where('status', '<', 3)->whereDate('created_at', $final_date)->get();
        //$orders = Order::with('orderdetailData.productDataa','billingAddressData','shippingAddressData.stateData','customerData')->whereIn('order_no', ['TKS00201','TKS00202','TKS00203','TKS00204','TKS00205','TKS00206','TKS00207','TKS00208','TKS00209'])->get();
        
        echo '<br/>';
        $orderData = [];
        foreach ($orders as $order) {
            $orderSubtotal = $order->ordersummary->where('type','subtotal')->pluck('price')->first();
            $ordertotal = $order->ordersummary->where('type','total')->pluck('price')->first();
            $epressDeliveryOption = $order->ordersummary->where('type','express')->pluck('price')->sum();
            $orderDoorStep = $order->ordersummary->where('type','door_step_amount')->pluck('price')->sum();
            $orderShipping = $order->ordersummary->where('type','shipping')->pluck('price')->first();
            if(isset($order->Address)){
                $data = [
                    'date_created' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                    "order_no" => $order->order_no,
                    "email" => isset($order->UserDetail->email) ? $order->UserDetail->email : null,
                    "subtotal" => $orderSubtotal,
                    'madac_id'=>$order->madac_id,
                    //"customer_ip_address" => $order->get_customer_ip_address(),
                    "total" => $ordertotal,
                    "status" => 'processing',
                    "payment_method_title" => $order->paymentmethod,
                    'express_delivery_amount' => $epressDeliveryOption,
                    'door_step_amount' => $orderDoorStep,
                    "include_tax" => $orderShipping,
                    'first_name' => $order->Address->first_name,
                    "items" => [],
                    'billing_address' => [
                            'first_name' => $order->Address->first_name,
                            'last_name' => $order->Address->last_name,
                            'phone_number' => $order->Address->phone_number,
                            'city_id' => isset($order->Address->stateData->city_code) ? $order->Address->stateData->city_code : null,
                            'state_id' => isset($order->Address->stateData->city_code) ? $order->Address->stateData->city_code : null,
                            'zip' => $order->Address->zip,
                            'address' => $order->Address->address,
                            'address_option' => $order->Address->address_option,
                            'shippinginstractions' => $order->Address->shippinginstractions,
                        ], 
                ];
        
                foreach ($order->details as $item_id => $product) {
                    $data["items"][] = [
                        "sku" => (isset($product->productSku->sku)) ? $product->productSku->sku : '',
                        //"sku" => $product->productSku->sku,
                        "name" => $product->product_name,
                        "image" => 'https://images.tamkeenstores.com.sa/assets/images/'.$product->product_image,
                        "sale_price" => $product->unit_price,
                        "quantity" => $product->quantity,
                        "price" => $product->unit_price,
                    ];
                }
                $orderData[] = $data;
            }
            Order::where('id',$order->id)->update(['status'=>'2']);
        }
        // print_r($orderData);die();
        $data = ["data" => json_encode($orderData)];
        
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            "https://dashboard.tamkeenstores.com.sa/import-data"
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type: multipart/form-data"]);
        
        $return = curl_exec($ch);
        curl_close($ch);
        var_dump($return);
    }
    
    public function orderExportbyId() {   
        $id = $_GET['id'];
        if(isset($id)){
            echo $id;
            // $order = Order::with('orderdetailData.productDataa','billingAddressData','shippingAddressData.stateData','customerData')->where('order_no', $id)->first();
            $order = Order::with('details.productSku','ordersummary','Address.stateData','UserDetail')->where('order_no', $id)->first();
             //print_r($order);die();
            if($order){
                $orderData = [];
                $orderSubtotal = $order->ordersummary->where('type','subtotal')->pluck('price')->first();
                $ordertotal = $order->ordersummary->where('type','total')->pluck('price')->first();
                $epressDeliveryOption = $order->ordersummary->where('type','express')->pluck('price')->sum();
                $orderDoorStep = $order->ordersummary->where('type','door_step_amount')->pluck('price')->sum();
                $orderShipping = $order->ordersummary->where('type','shipping')->pluck('price')->first();
                if(isset($order->Address)){
                    $data = [
                        'date_created' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                        "order_no" => $order->order_no,
                        "email" => isset($order->UserDetail->email) ? $order->UserDetail->email : null,
                        "subtotal" => $orderSubtotal,
                        'madac_id'=>$order->madac_id,
                        //"customer_ip_address" => $order->get_customer_ip_address(),
                        "total" => $ordertotal,
                        "status" => 'processing',
                        "payment_method_title" => $order->paymentmethod,
                        'express_delivery_amount' => $epressDeliveryOption,
                        'door_step_amount' => $orderDoorStep,
                        "include_tax" => $orderShipping,
                        'first_name' => $order->Address->first_name,
                        "items" => [],
                        'billing_address' => [
                                'first_name' => $order->Address->first_name,
                                'last_name' => $order->Address->last_name,
                                'phone_number' => $order->Address->phone_number,
                                'city_id' => $order->Address->stateData->city_code,
                                'state_id' => $order->Address->stateData->city_code,
                                'zip' => $order->Address->zip,
                                'address' => $order->Address->address,
                                'address_option' => $order->Address->address_option,
                                'shippinginstractions' => $order->Address->shippinginstractions,
                            ], 
                    ];
            
                    foreach ($order->details as $item_id => $product) {
                        $data["items"][] = [
                            "sku" => (isset($product->productSku->sku)) ? $product->productSku->sku : '',
                            //"sku" => $product->productSku->sku,
                            "name" => $product->product_name,
                            "image" => 'https://images.tamkeenstores.com.sa/assets/images/'.$product->product_image,
                            "sale_price" => $product->unit_price,
                            "quantity" => $product->quantity,
                            "price" => $product->unit_price,
                        ];
                    }
                    $orderData[] = $data;
                }
                // print_r($orderData);die();
                Order::where('id',$order->id)->update(['status'=>'2']);
                $data = ["data" => json_encode($orderData)];
                // print_r($data);die();
                $ch = curl_init();
                curl_setopt(
                    $ch,
                    CURLOPT_URL,
                    "https://dashboard.tamkeenstores.com.sa/import-data"
                );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type: multipart/form-data"]);
                
                $return = curl_exec($ch);
                curl_close($ch);
                return redirect()->away('https://dashboard.tamkeenstores.com.sa/processing');
            }
            else{
                echo "Order number not found!";
            }
        }
    }  
    
    public function getcheckERPOrders(Request $request){
        
        // die();
        $success = false;
        $setdata = $request->all(); 
        $orders = [];
        
        if(isset($setdata['credentials']) && $setdata['credentials'] == 'TamkeenStores2@'){
            $date_created = date('Y-m-d', strtotime($request->created_at));
            // with('orderDetailProduct:id,order_id,product_id,product_name,unit_price,quantity,total','orderDetailProduct.productSku','userShippingData:id,customer_id,first_name,last_name,phone_number,state_id,address,address_label','userShippingData.stateData:id,name,ln_city_code','UserDetail:id,firstname,lastname,phone,email')->
            // select(['id','order_no','erp_status','customer_id','shipping_id','billing_id','coupon_id','coupon_code','subtotal','shipping','discount','total','status','paymentmethod','paymentid','shippingMethod','include_tax','discount_rule_data','repalcedata','fees','discount_rule','discountallowed','giftvoucherallowed','discount_rule_id','discount_rule_bulk','discount_rule_bulk_id','vat_discount','vat_discount_amount','express_option_price','is_delivered','door_step_amount','cod_additional_charges','delivered_date','waybill','shipping_company','created_at'])->/**/
            $orders = Order::with('details.productSku','ordersummary','Address.stateData','UserDetail')
            // ->where('order_no','TKS21412613')
            // ->where('erp_status', 0)
            ->whereNotIn('status',['5','7','8'])
            // ->where('id',21422622)
            ->whereDate('created_at', $date_created)->select(['id','order_no','erp_status','customer_id','shipping_id','status','paymentmethod','paymentid','shippingMethod', 'otp_code','created_at'])->get();
            // 
            if($orders->toArray()){
                $success = true;
            }
            
            $orderArray = $orders->toArray();
            $orderArrayData = array();
            $previousCode = 'OLN1';
            foreach($orderArray as $key => $orderDataa){
                
                    // print_r($orders[$key]->Address);die();
                    // print_r($orders[$key]->ordersummary->where('type','fee')->pluck('price')->sum());die();
                $orderArrayData[$key]['id'] = $orderDataa['id'];
                $orderArrayData[$key]['order_no'] = $orderDataa['order_no'];
                $orderArrayData[$key]['erp_status'] = $orderDataa['erp_status'];
                $orderArrayData[$key]['customer_id'] = $orderDataa['customer_id'];
                $orderArrayData[$key]['shipping_id'] = $orderDataa['shipping_id'];
                $orderArrayData[$key]['order_no'] = $orderDataa['order_no'];
                $orderArrayData[$key]['billing_id'] = 0;
                
                // Order Discount Coupon Data
                $orderArrayData[$key]['coupon_id'] = $orders[$key]->ordersummary->where('type','discount')->pluck('amount_id')->first();
                $orderArrayData[$key]['coupon_code'] = $orders[$key]->ordersummary->where('type','discount')->pluck('name')->first();
                // Order Discount Coupon Data
                
                // $orderArrayData[$key]['subtotal'] = $orders[$key]->ordersummary->where('type','subtotal')->pluck('price')->first();
                
                // Order Subtotal
                $orderSubtotal = $orders[$key]->ordersummary->where('type','subtotal')->pluck('price')->first();
                // $orderDataasubtotal = $orderSubtotal - ($orderSubtotal/115*100);
                // $orderArrayData[$key]['subtotal'] = number_format($orderSubtotal - $orderDataasubtotal, 2, '.', '');
                $orderArrayData[$key]['subtotal'] = $orderSubtotal;
                // Order Subtotal
                
                
                
                // Order Shipping
                $orderShipping = $orders[$key]->ordersummary->where('type','shipping')->pluck('price')->first();
                $orderShipping += $orders[$key]->ordersummary->where('type','express')->pluck('price')->sum();
                $orderShippingTotal = $orderShipping - ($orderShipping/115*100);
                $orderArrayData[$key]['shipping'] = number_format($orderShipping - $orderShippingTotal, 2, '.', '');
                // Order Shipping
                
                // Order Express Delivery
                $orderExpressDelivery = $orders[$key]->ordersummary->where('type','express')->pluck('price')->sum();
                $orderDataaorderExpressDeliveryTotal = $orderExpressDelivery - ($orderExpressDelivery/115*100);
                // $orderArrayData[$key]['express_option_price'] = number_format($orderExpressDelivery - $orderDataaorderExpressDeliveryTotal, 2, '.', '');
                $orderArrayData[$key]['express_option_price'] = number_format($orderExpressDelivery - $orderDataaorderExpressDeliveryTotal, 2, '.', '');
                // Order Express Delivery
                
                // Order Discount Coupon
                // ->orWhere('type','saveamounttotal')
                // $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','discount')->pluck('price')->sum();
                
                $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','discount')->pluck('price')->sum();
                // $orderDiscountRule = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                $orderDiscountCouponTotal = $orderDiscountCoupon - ($orderDiscountCoupon/115*100);
                $orderArrayData[$key]['discount'] = number_format($orderDiscountCoupon - $orderDiscountCouponTotal, 2, '.', '');
                // Order Discount Coupon
                
                // Order Total
                $orderTotal = $orders[$key]->ordersummary->where('type','total')->pluck('price')->first();
                $orderDataatotal = $orderTotal - ($orderTotal/115*100);
                $orderArrayData[$key]['total'] = number_format($orderTotal - $orderDataatotal, 2, '.', '');
                // Order Total
                
                // vat Total
                $orderArrayData[$key]['paymentmethod'] = $orderDataa['paymentmethod'];
                $orderArrayData[$key]['paymentid'] = $orderDataa['paymentid'];
                $orderArrayData[$key]['shippingMethod'] = $orderDataa['shippingMethod'];
                $orderArrayData[$key]['include_tax'] = number_format($orders[$key]->ordersummary->where('type','total')->pluck('price')->first() - ($orderTotal - $orderDataatotal), 2, '.', '');
                // vat Total
                
                // Order Discount Rule
                // $orderDiscountRule = $orders[$key]->ordersummary->where('type','discount_rule')->pluck('price')->sum();
                // $orderDataaDiscountRuleTotal = $orderDiscountRule - ($orderDiscountRule/115*100);
                // $orderArrayData[$key]['discount_rule_data'] = number_format($orderDiscountRule - $orderDataaDiscountRuleTotal, 2, '.', '');
                // $orderDiscountCoupon = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                
                $orderDiscountRule = $orders[$key]->ordersummary->where('type','saveamounttotal')->pluck('price')->sum();
                $orderDataaDiscountRuleTotal = $orderDiscountRule - ($orderDiscountRule/115*100);
                $orderArrayData[$key]['discount_rule_data'] = number_format($orderDiscountRule - $orderDataaDiscountRuleTotal, 2, '.', '');
                // $orderArrayData[$key]['discount_rule_data'] = null;
                // Order Discount Rule
                
                $orderArrayData[$key]['repalcedata'] = "[]";
                
                // Order Fees
                $orderFees = $orders[$key]->ordersummary->where('type','fee')->where('name','!=','COD Fees')->pluck('price')->sum();
                $orderDataaFeestotal = $orderFees - ($orderFees/115*100);
                $orderArrayData[$key]['fees'] = number_format($orderFees - $orderDataaFeestotal, 2, '.', '');
                // Order Fees
                
                $orderArrayData[$key]['discount_rule'] = null;
                $orderArrayData[$key]['discountallowed'] = 0;
                $orderArrayData[$key]['giftvoucherallowed'] = 0;
                $orderArrayData[$key]['discount_rule_id'] = null;
                $orderArrayData[$key]['discount_rule_bulk'] = null;
                $orderArrayData[$key]['discount_rule_bulk_id'] = null;
                $orderArrayData[$key]['vat_discount'] = 0;
                $orderArrayData[$key]['vat_discount_amount'] = 0;
                
                
                
                $orderArrayData[$key]['is_delivered'] = 0;
                
                // Order DoorStep
                $orderDoorStep = $orders[$key]->ordersummary->where('type','door_step_amount')->pluck('price')->sum();
                $orderDataaDoorStepTotal = $orderDoorStep - ($orderDoorStep/115*100);
                $orderArrayData[$key]['door_step_amount'] = number_format($orderDoorStep - $orderDataaDoorStepTotal, 2, '.', '');
                // Order DoorStep
                
                
                // Order COD Additional Charges
                $orderCOD = $orders[$key]->ordersummary->where('type','fee')->where('name','=','COD Fees')->pluck('price')->sum();
                $orderCODTotal = $orderCOD - ($orderCOD/115*100);
                $orderArrayData[$key]['cod_additional_charges'] = number_format($orderCOD - $orderCODTotal, 2, '.', '');
                // Order COD Additional Charges
                
                
                // $orderArrayData[$key]['cod_additional_charges'] = null;
                $orderArrayData[$key]['delivered_date'] = null;
                $orderArrayData[$key]['waybill'] = null;
                $orderArrayData[$key]['shipping_company'] = null;
                $orderArrayData[$key]['created_at'] = $orders[$key]->created_at->format('Y-m-d H:i:s');

                // Add schedule_date calculation
                $createdAt = $orders[$key]->created_at;
                $dayOfWeek = $createdAt->format('N');
                
                if ($dayOfWeek == 5) {
                    $scheduleDate = $createdAt->addDays(2)->format('Y-m-d');
                } else {
                    $scheduleDate = $createdAt->addDays(1)->format('Y-m-d');
                }
                
                $orderArrayData[$key]['schedule_date'] = $scheduleDate;

                foreach($orders[$key]->details as $k => $productData){
                    // if(!in_array($productData->productSku['sku'], ['SP3M', 'SP6M','SP12M'])){
                    
                        if($productData->productSku->is_bundle === 1){
                            $explodeproducts = explode("+",$productData->productSku->ln_sku);
                            $bundleSkuupdate = '';
                            foreach($explodeproducts as $proKey => $explodeproduct){
                                    $bundleProduct = Product::where('ln_sku',$explodeproduct)->first(['id','sku','ln_sku','bundle_price']);
                                    // print_r($bundleProduct);die();
                                    // Product Unit Price 
                                    $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                                    $unitpriceupdate = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                                    
                                    // Product Total Price 
                                    $productDatatotal = $productData['total'] - ($productData['total']/115*100);
                                    $totalupdated = number_format($productData['total'] - $productDatatotal, 2, '.', '');
                                    
                                    // $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                                    // $productDatatotal = $productData['total'] - ($productData['total']/115*100);
                                    $regionalStock = OrderDetailRegionalQty::select('warehouse_code',DB::raw('SUM(qty) as quantity'))->where('order_detail_id',$productData['id'])->whereNot('qty',0)->groupBy('warehouse_code')->get();
                                    // print_r($totalupdated);die();
                                   if($regionalStock->count() == 0){
                                       $regionalStock = OrderDetail::select('quantity')->where('id',$productData['id'])->first();
                                        $regionalStock['warehouse_code'] = $previousCode;
                                    }else{
                                        $previousCode = $regionalStock[0]->warehouse_code;
                                    }
                                    $ArrayData = array(
                                        'id'=>$productData['id'],
                                        'order_id'=>$productData['order_id'],
                                        'product_id'=>$productData['product_id'],
                                        'product_name'=>$productData['product_name'],
                                        'regional_stock' => $regionalStock,
                                        'unit_price'=> $proKey > 0 ? 0 : $totalupdated,
                                        'quantity'=> $productData['quantity'],
                                        'total'=> $proKey > 0 ? 0 : $totalupdated,
                                        'product_sku'=>array(
                                            'id'=>$productData['id'],
                                            'sku'=>$explodeproduct,
                                            'is_bundle'=>1
                                            ),
                                        );
                                    $orderArrayData[$key]['order_detail_product'][] = $ArrayData; 
                                } 
                                
                        }
                        else{
                            
                            // Product Unit Price 
                            $productDatasubtotal = $productData['unit_price'] - ($productData['unit_price']/115*100);
                            $unitpriceupdate = number_format($productData['unit_price'] - $productDatasubtotal, 2, '.', '');
                            
                            // Product Total Price 
                            $productDatatotal = $productData['total'] - ($productData['total']/115*100);
                            $totalupdated = number_format($productData['total'] - $productDatatotal, 2, '.', '');
                            
                            
                            // Product Total Price 
                            
                            $regionalStock = OrderDetailRegionalQty::select('warehouse_code',DB::raw('SUM(qty) as quantity'))->where('order_detail_id',$productData['id'])->whereNot('qty',0)->groupBy('warehouse_code')->get();
                            
                            if($regionalStock->count() == 0){
                               $regionalStock = OrderDetail::select('quantity')->where('id',$productData['id'])->first();
                                $regionalStock['warehouse_code'] = $previousCode;
                            }else{
                                $previousCode = $regionalStock[0]->warehouse_code;
                            }
                            $ArrayData = array(
                                        'id'=>$productData['id'],
                                        'order_id'=>$productData['order_id'],
                                        'product_id'=>$productData['product_id'],
                                        'product_name'=>$productData['product_name'],
                                        'regional_stock' => $regionalStock,
                                        'unit_price'=>$unitpriceupdate,
                                        'quantity'=>$productData['quantity'],
                                        'total'=>$totalupdated,
                                        'product_sku'=>array(
                                            'id'=>$productData['id'],
                                            'sku'=>$productData->productSku->ln_sku,
                                            'is_bundle'=>0
                                            ),
                                        );
                                    $orderArrayData[$key]['order_detail_product'][] = $ArrayData; 
                        }
                        
                    // }
                    
                }
                
                // Shiiping Data
                if(isset($orders[$key]->Address)){
                    $orderArrayData[$key]['user_shipping_data']['id'] = $orders[$key]->Address->id;
                    $orderArrayData[$key]['user_shipping_data']['customer_id'] = $orders[$key]->Address->customer_id;
                    $orderArrayData[$key]['user_shipping_data']['first_name'] = $orders[$key]->Address->first_name;
                    $orderArrayData[$key]['user_shipping_data']['last_name'] = $orders[$key]->Address->last_name;
                    $orderArrayData[$key]['user_shipping_data']['phone_number'] = $orders[$key]->Address->phone_number;
                    $orderArrayData[$key]['user_shipping_data']['state_id'] = $orders[$key]->Address->state_id;
                    $orderArrayData[$key]['user_shipping_data']['address'] = $orders[$key]->Address->address;
                    $orderArrayData[$key]['user_shipping_data']['address_label'] = $orders[$key]->Address->address_label;
                    
                    if($orders[$key]->Address->stateData){
                        $orderArrayData[$key]['user_shipping_data']['state_data']['id'] = $orders[$key]->Address->stateData->id;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['name'] = $orders[$key]->Address->stateData->name;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['ln_city_code'] = $orders[$key]->Address->stateData->ln_city_code;
                    }else{
                        $orderArrayData[$key]['user_shipping_data']['state_data']['id'] = null;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['name'] = null;
                        $orderArrayData[$key]['user_shipping_data']['state_data']['ln_city_code'] = null;
                    }
                }
                // Shiiping Data
                
                // User Data
                
                if($orders[$key]->UserDetail){
                    $orderArrayData[$key]['user_detail']['id'] = $orders[$key]->UserDetail->id;
                    $orderArrayData[$key]['user_detail']['firstname'] = $orders[$key]->UserDetail->first_name;
                    $orderArrayData[$key]['user_detail']['lastname'] = $orders[$key]->UserDetail->last_name;
                    $orderArrayData[$key]['user_detail']['phone'] = $orders[$key]->UserDetail->phone_number;
                    $orderArrayData[$key]['user_detail']['email'] = $orders[$key]->UserDetail->email;
                }else{
                    $orderArrayData[$key]['user_detail']['id'] = null;
                    $orderArrayData[$key]['user_detail']['firstname'] = null;
                    $orderArrayData[$key]['user_detail']['lastname'] = null;
                    $orderArrayData[$key]['user_detail']['phone'] = null;
                    $orderArrayData[$key]['user_detail']['email'] = null;
                }
                // User Data
                
                
                // print_r($orderArrayData[$key]);die();
            }
            
            // print_r($orders->toArray());die();
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
    
    public function criteoProductFeedData(Request $request) {
        $success = false;
        $products = [];
        // $products = Product::
        //     where('status',1)
        // ->where('quantity','>=', 1)
        // ->whereNot('price','<',50)
        // ->whereNot('sale_price','<',50)
        // ->with('featuredImage:id,image')
        // ->get(['id', 'sku','name','name_arabic','price','sale_price','description','slug', 'feature_image']);
        
        $liveStockSums = DB::table('livestock')
            ->select('sku', DB::raw('SUM(qty) as total_qty'))
            ->whereIn('city', ['ONL1', 'KUW101'])
            ->groupBy('sku');
        
        $products = Product::select([
                'products.id',
                'products.sku',
                'products.name',
                'products.name_arabic',
                'products.price',
                'products.sale_price',
                'products.description',
                'products.slug',
                'products.feature_image'
            ])
            // ->addSelect(DB::raw('stock.total_qty'))
            ->joinSub($liveStockSums, 'stock', function ($join) {
                $join->on('products.sku', '=', 'stock.sku')
                     ->where('stock.total_qty', '>', 0);
            })
            ->where('products.status', 1)
            ->where('products.price', '>=', 50)
            ->where('products.sale_price', '>=', 50)
            ->with('featuredImage:id,image')
            ->get();

        
        
        $productData = $products->toArray();
        foreach($products as $key => $product){
            
            if(isset($product->featuredImage)){
                $productData[$key]['image_url'] = 'https://images.tamkeenstores.com.sa/assets/new-media/'.$product->featuredImage->image;
            }
            else{
                $productData[$key]['image_url'] = null;
            }
            $productData[$key]['product_link'] = 'https://tamkeenstores.com.sa/en/product/'.$product->slug;
        }
        if($products){
            $success = true;
        }
        $response = ['success' => true, 'products' => $productData];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function couponStatus($status,Request $request) {
        $success = false;
        $products = [];
        $coupons = Coupon::get(['id', 'coupon_code','status']);
        
        
        // print_r($coupons[0]->status);die();
        foreach($coupons as $key => $coupon){
            $couponupdate = Coupon::where('id',$coupon->id)->update(['status' => $status]);
            // print_r($couponupdate);die();
        }
        if($coupons){
            $success = true;
        }
        $response = ['success' => true];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function NotificationCounts(Request $request){
        $data = $request->all();
        $success = false;
        
        if(isset($data['id']) && $data['id']){
            $notificationData = Notification::where('id',$data['id'])->first();
            
            if($notificationData){
                if(isset($data['mobileapp']) && $data['mobileapp']){
                    $notificationData->app_counts = $notificationData->app_counts + 1;
                    $notificationData->save();
                    $success = true;
                }
                
                if(isset($data['desktop']) && $data['desktop']){
                    $notificationData->web_counts = $notificationData->web_counts + 1;
                    $notificationData->save();
                    $success = true;
                }
            }
        }
        
        $response = ['success' => $success];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        
    }
    
    
    public function POEmailSend(Request $request){
        $success = false;
        // where('id',181775)->
        $users = User::whereNull('emailstatus')->limit(120)->get(['id','email','emailstatus']);
        
        // print_r($users->count());die();
        foreach($users as $key => $user){
            $success = true;
            $email = str_replace(' ', '', $user->email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $userUpdated = User::where('id',$user->id)->update(array('emailstatus' => '0'));
            }else{
                try {
                    $MailStatus = Mail::to($user->email)->send(new PendingOrderEmails());
                    $userUpdated = User::where('id',$user->id)->update(array('emailstatus' => '1'));
                    
                
                  } catch(\Exception $e) {
                      $userUpdated = User::where('id',$user->id)->update(array('emailstatus' => '0'));
                }
            }
        }
        echo $success;
    }
    
    public function storeCacheFlush($type){
        
        CacheViewJob::dispatch($type);
        
        $response = ['success' => true];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function deleteCacheCycle(Request $request){
        
        $success = false;
        $hours = 24;
        $twentyfourhours = Carbon::now()->subHours($hours);
            
        // $cachestores = CacheStores::where('created_at', '<=', $twentyfourhours)->pluck('key')->toArray();
        $cachestores = CacheStores::where('type','extradata')->pluck('key')->toArray();
        // print_r($cachestores);die();
        if($cachestores){
            foreach($cachestores as $key => $cachestore){
                Cache::forget($cachestore);
                CacheStores::where('key',$cachestore)->delete();
            }
            // $CacheStoresDelete = CacheStores::whereIn('key',$cachestores)->delete();
            // if($CacheStoresDelete){
                $success = true;
            // }
        }
        
        $response = ['success' => $success];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function cacheFlushByKey($key){
        $success = false;
        
        $getKey = CacheStores::where('key',$key)->first(['key']);
        if($getKey){
            Cache::forget($getKey->key);
            CacheStores::where('key',$getKey->key)->delete();
            $success = true;
        }
        
        $response = ['success' => $success];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        
    }
    
    public function getERPLiveStock(Request $request){
        $success = false;
        
        $liveStock = LiveStock::with('product')->get();
        
        if($liveStock){
            $success = true;
        }
        
        $response = ['success' => $success,'data'=>$liveStock];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getProductlivestock(Request $request){
        $success = false;
        
        $liveStock = Product::with('warehouseData')->where('id',4080)->get();
        
        if($liveStock){
            $success = true;
        }
        
        $response = ['success' => $success,'data'=>$liveStock];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    // public function socialMediaProductFeedData(Request $request) {
    //     $success = false;
    //     $products = [];
    //     // $products = Product::
    //     //     where('status',1)
    //     // ->where('quantity','>=', 1)
    //     // ->whereNot('price','<',50)
    //     // ->whereNot('sale_price','<',50)
    //     // ->with('featuredImage:id,image')
    //     // ->get(['id', 'sku','name','name_arabic','price','sale_price','description','slug', 'feature_image']);
        
    //     $liveStockSums = DB::table('livestock')
    //         ->select('sku', DB::raw('SUM(qty) as total_qty'))
    //         ->whereIn('city', ['ONL1', 'KUW101'])
    //         ->groupBy('sku');
        
    //     $products = Product::select([
    //             'products.id',
    //             'products.sku',
    //             'products.name',
    //             'products.name_arabic',
    //             'products.price',
    //             'products.sale_price',
    //             'products.description_arabic',
    //             'products.slug',
    //             'products.feature_image',
    //             'products.brands'
    //         ])
    //         // ->addSelect(DB::raw('stock.total_qty'))
    //         ->joinSub($liveStockSums, 'stock', function ($join) {
    //             $join->on('products.sku', '=', 'stock.sku')
    //                  ->where('stock.total_qty', '>', 0);
    //         })
    //         ->where('products.status', 1)
    //         ->where('products.price', '>=', 50)
    //         ->where('products.sale_price', '>=', 50)
    //         ->with('featuredImage:id,image','brand:id,name')
    //         ->get();
            
            

        
    //      if($request->test){
    //          print_r($products->count());die();
    //      }
    //     $productData = Array();
    //     foreach($products as $key => $product){
    //         if($request->twitter){
    //             $productData[$key]['id'] =  $product->sku;
    //         }else{
    //             $productData[$key]['sku_id'] =  $product->sku;
    //         }
    //         $productData[$key]['title'] =  $product->name_arabic;
    //         $productData[$key]['description'] =  $product->description_arabic;
    //         $productData[$key]['availability'] =  "in stock";
    //         $productData[$key]['condition'] =  "new";
    //         $productData[$key]['price'] = min($product->price, $product->sale_price)." SAR";
    //         $productData[$key]['link'] = 'https://tamkeenstores.com.sa/en/product/'.$product->slug;
    //         $image = $product->featuredImage->image ?? null;
    //         $productData[$key]['image_link'] = $image 
    //             ? 'https://images.tamkeenstores.com.sa/assets/new-media/' . $image 
    //             : 'https://images.tamkeenstores.com.sa/assets/new-media/3f4a05b645bdf91af2a0d9598e9526181714129744.png';
            
    //         $productData[$key]['brand'] =  $product->brand ? $product->brand->name : null;
            
    //     }
    //     if($products){
    //         $success = true;
    //     }
    //     $response = ['success' => true, 'products' => $productData];
    //     $responsejson = json_encode($response);
    //     $data = gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Cache-Control' => 'no-cache',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
    public function socialMediaProductFeedData(Request $request){
        $data = [];
        $setdata = $request->all();
        
        $liveStockSums = DB::table('livestock')
            ->select('sku', DB::raw('SUM(qty) as total_qty'))
            ->whereIn('city', ['ONL1', 'KUW101'])
            ->groupBy('sku');
        
        $products = Product::select([
                'products.id',
                'products.sku',
                'products.name',
                'products.name_arabic',
                'products.price',
                'products.sale_price',
                'products.description_arabic',
                'products.slug',
                'products.feature_image',
                'products.brands'
            ])
            // ->addSelect(DB::raw('stock.total_qty'))
            ->joinSub($liveStockSums, 'stock', function ($join) {
                $join->on('products.sku', '=', 'stock.sku')
                     ->where('stock.total_qty', '>', 0);
            })
            ->where('products.status', 1)
            ->where('products.price', '>=', 50)
            ->where('products.sale_price', '>=', 50)
            ->with('featuredImage:id,image','brand:id,name')
            ->limit(5)
            ->get();
            
        foreach($products as $key => $productData){
                
                $item = [
                    'title' => $productData->name_arabic,
                    'description' => $productData->description_arabic,
                    'g:availability' => 'In stock',
                    'g:condition' => 'New',
                    'link' => 'https://www.tamkeenstores.com.sa/ar/product/'.$productData->slug,
                    'g:image_link' => 'https://images.tamkeenstores.com.sa/assets/new-media/'.$productData->featuredImage->image,
                    'g:price' => $productData->price.' SAR',
                    'g:sale_price' => $productData->promotional_price > 0 ? $productData->sale_price - $productData->promotional_price.' SAR' : $productData->sale_price.' SAR',
                    'g:brand' =>$productData->brand->name
                ];
                if ($request->twitter) {
                    $item['g:sku'] = $productData->sku;
                } else {
                    $item['g:id'] = $productData->sku;
                }
                $data[] = $item;
        }
        
        
        if($request->tiktok){
            header('Content-Type: application/rss+xml; charset=UTF-8');

            // Create the base XML with RSS and namespace
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');
            
            // Add channel information
            $channel = $xml->addChild('channel');
            $channel->addChild('title', 'Tamkeen Stores Product Feed');
            $channel->addChild('link', 'https://www.tamkeenstores.com.sa/');
            $channel->addChild('description', 'Product catalog for TikTok Ads');
            
            foreach ($products as $product) {
                $item = $channel->addChild('item');
            
                $item->addChild('g:id', $product['sku'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:title', $product['name_arabic'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:description', $product['description_arabic'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:availability', 'In stock', 'http://base.google.com/ns/1.0');
                $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
                $item->addChild('g:price', number_format($product['price'], 2) . ' SAR', 'http://base.google.com/ns/1.0');
            
                if (!empty($product['sale_price'])) {
                    $item->addChild('g:sale_price', number_format($product['sale_price'], 2) . ' SAR', 'http://base.google.com/ns/1.0');
                }
            
                $item->addChild('g:link', 'https://www.tamkeenstores.com.sa/ar/product/'.$product['slug'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:image_link', 'https://images.tamkeenstores.com.sa/assets/new-media/'.$product['featuredImage']['image'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:brand', $product['brand']['name'], 'http://base.google.com/ns/1.0');
            
            }
            
            // Output the XML
            return response($xml->asXML(), 200, [
                'Content-Type' => 'application/rss+xml; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="tiktok_product_feed.xml"',
            ]);

        }
        
        if (empty($data)) {
            return response()->json(['message' => 'Access Denied'], 404);
        }
        
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel></channel></rss>');
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
    public function webengageProductFeedData($lang,Request $request){
        $data = [];
        $setdata = $request->all();
        
        $liveStockSums = DB::table('livestock')
            ->select('sku', DB::raw('SUM(qty) as total_qty'))
            // ->whereIn('city', ['ONL1', 'KUW101'])
            ->groupBy('sku');
        
        $products = Product::with([
                'productcategory' => function($query) {
                    $query->where('productcategories.status', 1)
                          ->where('menu', 1)
                          ->select('productcategories.id', 'name','name_arabic', 'slug')
                          ->orderByDesc('productcategories.created_at');
                }
            ])->select([
                'products.id',
                'products.sku',
                'products.name',
                'products.name_arabic',
                'products.price',
                'products.sale_price',
                'products.description',
                'products.description_arabic',
                'products.slug',
                'products.feature_image',
                'products.brands',
                DB::raw('stock.total_qty as stock_quantity')
            ])
            // ->addSelect(DB::raw('stock.total_qty'))
            ->joinSub($liveStockSums, 'stock', function ($join) {
                $join->on('products.sku', '=', 'stock.sku')
                     ->where('stock.total_qty', '>', 0);
            })
            ->where('products.status', 1)
            ->where('products.price', '>=', 50)
            ->where('products.sale_price', '>=', 50)
            ->with('featuredImage:id,image','brand:id,name')
            // ->limit(1000)
            ->get();
        
            header('Content-Type: application/rss+xml; charset=UTF-8');
    
            // Create the base XML with RSS and namespace
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');
            $g = 'http://base.google.com/ns/1.0';
            // Add channel information
            $channel = $xml->addChild('channel');
            $channel->addChild('title', 'Tamkeen Stores Product Feed');
            if($lang == 'en'){
                $channel->addChild('link', 'https://www.tamkeenstores.com.sa/en');
            }else{
                $channel->addChild('link', 'https://www.tamkeenstores.com.sa/ar');
            }
            $channel->addChild('description', 'Product catalog');
            
            foreach ($products as $product) {
                    
                    $subcategories = [];
                    if($product->productcategory){
                        foreach ($product->productcategory as $categoryData) {
                            $subcategories[] = $lang == 'en' ? $categoryData->name : $categoryData->name_arabic;
                        }
                    }
                    $categoryPath = implode(' > ', $subcategories);
                    
                $category = $product->productcategory->first(); 
                $item = $channel->addChild('item');
                $item->addChild('g:sku', $product['sku'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:item_id', $product['sku'], 'http://base.google.com/ns/1.0');
                if($lang == 'en'){
                    $item->addChild('g:title', htmlspecialchars($product['name']), $g);
                //     $item->addChild('g:description', $product['description'], 'http://base.google.com/ns/1.0');
                    $item->addChild('g:category', $category->name ?? "", 'http://base.google.com/ns/1.0');
                }else{
                    $item->addChild('g:title', htmlspecialchars($product['name_arabic']), $g);
                //     $item->addChild('g:description', $product['description_arabic'], 'http://base.google.com/ns/1.0');
                    $item->addChild('g:category', $category->name_arabic ?? "", 'http://base.google.com/ns/1.0');
                }
                $item->addChild('g:product_url', 'https://www.tamkeenstores.com.sa/'.$lang.'/product/'.$product['slug'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:sub_categories', htmlspecialchars($categoryPath) , $g);
                $item->addChild('g:brand', $product['brand']['name'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:picture_url', 'https://images.tamkeenstores.com.sa/assets/new-media/'.$product['featuredImage']['image'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:qty', $product['stock_quantity'], 'http://base.google.com/ns/1.0');
                $item->addChild('g:price', $product['price'], 'http://base.google.com/ns/1.0');
                if (!empty($product['sale_price'])) {
                    $item->addChild('g:sale_price', $product['sale_price'], 'http://base.google.com/ns/1.0');
                }
                
                $item->addChild('g:availability', 'In stock', 'http://base.google.com/ns/1.0');
                $item->addChild('g:condition', 'New', 'http://base.google.com/ns/1.0');
                
            }
            
            
            if($request->download){
            // Output the XML
                return response($xml->asXML(), 200, [
                    'Content-Type' => 'application/rss+xml; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="product_feed_'.$lang.'.xml"',
                ]);
        
            }else {
                // Render XML in browser
                header('Content-Type: application/rss+xml; charset=UTF-8');
                echo $xml->asXML();
                exit;
            }
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel></channel></rss>');
        $this->arrayToXml($data, $xmlData);
        return response($xmlData->asXML(), 200)->header('Content-Type', 'application/xml');
    }

}