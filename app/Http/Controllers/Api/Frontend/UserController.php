<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\States;
use App\Models\Region;
use App\Models\Order;
use App\Models\shippingAddress;
use App\Models\OtpVerification;
use App\Models\CategoryProduct;
use App\Models\LoyaltySetting;
use App\Models\LoyaltyHistory;
use App\Models\StoreLocatorCity;
use App\Helper\ConditionSetup_helper;
use App\Models\EmailTemplate;
use App\Models\OrderDetail;
use App\Models\ProductReview;
use App\Models\Maintenance;
use App\Models\Productcategory;
use App\Helper\NotificationHelper;
use App\Models\WalletHistory;
use App\Models\WalletSetting;
use App\Models\companyTypes;
use App\Models\InternalTicket;
use App\Models\InternalTicketOrderDetails;
use App\Models\LoginAttempts;
use App\Models\InternalTicketHistory;
use App\Models\CacheStores;
use Illuminate\Support\Facades\Cache;
use DB;
use Mail;
// use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\StoreLocator;

class UserController extends Controller
{
    
    public function userImgUpload(Request $request) {
        if ($request->hasFile('file')) {
            $fileName1 = null;
            $file = request()->File('file');
            $fileName1 = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
            $file->move(public_path('/assets/user-images'), $fileName1);
            $imageurl = $fileName1;
            return json_encode(['img' => $imageurl]);
        }
    }
    
    public function getLogin(Request $request) {
        $data = $request->all();
        $user = false;
        $success = false;
        if($data['phone_number']){
            $user = User::with('shippingAddressDataDefault')->where('phone_number',$data['phone_number'])->first(['id','phone_number', 'profile_img']);
            if($user){
                $success = true;
            }
        }
        
        $response = [
            'user' => $user,
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
    
    public function getRegister(Request $request) {
        $data = $request->all();
        $user = false;
        $success = false;
        $mail = false;
        if($data['phone_number']){
            $user = User::where('phone_number',$data['phone_number'])->first(['id','phone_number']);
            if($user){
                $success = false;
            }else{
                $success = true;
                $user = User::where('email',$data['email'])->first(['id','phone_number','email']);
                if($user){
                    $mail = true;
                    $success = false;
                }else{
                    $userData = User::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'phone_number' => $data['phone_number'],
                        'role_id' => isset($data['role_id']) ? $data['role_id'] : 2,
                        'email' => $data['email'],
                        'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : null,
                        'notes' => isset($data['notes']) ? $data['notes'] : null,
                        'status' => isset($data['status']) ? $data['status'] : 0,
                        'password' => isset($data['password']) ? $data['password'] : "password2024",
                        'user_device' => isset($data['user_device']) ? $data['user_device'] : null,
                        'lang' => isset($data['lang']) ? $data['lang'] : 'ar',
                        'gender' => isset($data['gender']) ? $data['gender'] : null,
                    ]);
                    $user = ['id'=>$userData->id];
                    $success = true;
                }
            }
        }
        
        $response = [
            'success' => $success,
            'user' => $user,
            'mail' => $mail
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function updateuser(Request $request){
        $success = false;
        $data = $request->all();
        $user = User::where('id', $data['user_id'])->first();
        
        if($user) {
            $userdata = User::whereId($data['user_id'])->update([
                'first_name' => isset($data['first_name']) ? $data['first_name'] : $user->first_name,
                'last_name' => isset($data['last_name']) ? $data['last_name'] : $user->last_name,
                // 'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : $user->phone_number,
                'email' => isset($data['email']) ? $data['email'] : $user->email,
                'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : $user->date_of_birth,
                'gender' => isset($data['gender']) ? $data['gender'] : $user->gender,
                'profile_img' => isset($data['profile_image']) ? $data['profile_image'] : $user->profile_image,
            ]);
            $success = true;   
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
    
    
    public function getUserData($id, $device = 'desktop') {
        if($device == 'desktop') {
            $ordlimitval = 2;
        }
        else {
            $ordlimitval = 2;
        }
        $user = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'users.first_name', 'users.last_name', 'users.phone_number', 'users.email', 'users.created_at', 'users.lang', 'users.profile_img', 'date_of_birth', 'gender', 'user_device', 'loyaltypoints')
        ->with([
            'shippingAddressDataDefault:id,state_id,customer_id,address,address_option,address_label', 'shippingAddressDataDefault.stateData:id,name,name_arabic,region'
            , 'shippingAddressDataDefault.stateData.region:id,name,name_arabic',
            'OrdersData' => function ($q) use ($ordlimitval) {
                return $q->select('id', 'order_no', 'customer_id', 'status', 'created_at')->withCount('details')->limit($ordlimitval)->orderBy('id','desc');   
            },
            'OrdersData.ordersummary' => function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            }])
        ->first();
        $totalRevenue = 0;
        $lastPurchaseDate = null;
        $ordersCount = 0;
        if($user) {
            if ($user->relationLoaded('OrdersData') && $user->OrdersData->isNotEmpty()) {
                $orders = $user->OrdersData;
                
                $orderStats = DB::table('order')
                                ->join('order_summary', function ($join) {
                                    $join->on('order.id', '=', 'order_summary.order_id')
                                         ->where('order_summary.type', 'subtotal');
                                })
                                ->where('order.customer_id', $user->id)
                                ->whereNotIn('order.status', [5, 6, 7, 8])
                                ->select(
                                    DB::raw('COUNT(DISTINCT order.id) as total_orders_count'),
                                    DB::raw('SUM(order_summary.price) as total_revenue')
                                )
                                ->first();
                $lastPurchaseDate = optional($orders->sortByDesc('created_at')->first())->created_at;
            }   
        }
        $userWebengageData = [
            'account_creation_date' => $user->created_at,
            'backend_user_id'       => $user->id,
            'last_purchase_date'    => $lastPurchaseDate,
            'store_language'        => $user->lang ?? 'ar',
            'total_purchases' =>    $orderStats->total_orders_count ?? 0,
            'total_revenue' =>      $orderStats->total_revenue ?? 0,
        ];
    
        $response = [
            'userdata' => $user,
            'user_webengage_data' => $userWebengageData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUserDataDuplicate($id, $device = 'desktop') {
        if($device == 'desktop') {
            $ordlimitval = 4;
        }
        else {
            $ordlimitval = 5;
        }
        $user = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'users.first_name', 'users.last_name', 'users.phone_number', 'users.email', 'users.created_at', 'users.lang', 'users.profile_img', 'date_of_birth', 'gender', 'user_device', 'loyaltypoints')
        ->with([
            'shippingAddressDataDefault:id,state_id,customer_id,address,address_option,address_label', 'shippingAddressDataDefault.stateData:id,name,name_arabic,region'
            , 'shippingAddressDataDefault.stateData.region:id,name,name_arabic',
            'shippingAddressData.stateData.region:id,name,name_arabic',
            'OrdersData' => function ($q) use ($ordlimitval) {
                return $q->select('id', 'order_no', 'customer_id', 'status', 'created_at')->withCount('details')->limit($ordlimitval)->orderBy('id','desc');   
            },
            'OrdersData.ordersummary' => function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            }])
        ->first();
        $totalRevenue = 0;
        $lastPurchaseDate = null;
        $ordersCount = 0;
        if($user) {
            if ($user->relationLoaded('OrdersData') && $user->OrdersData->isNotEmpty()) {
                $orders = $user->OrdersData;
                
                $orderStats = DB::table('order as o')
                ->where('o.customer_id', $user->id)
                ->whereNotIn('o.status', [5, 6, 7, 8])
                ->selectRaw('COUNT(*) as total_orders_count')
                ->selectSub(function ($query) use ($user) {
                    $query->from('order_summary as os')
                          ->selectRaw('SUM(os.price)')
                          ->where('os.type', 'subtotal')
                          ->whereIn('os.order_id', function ($sub) use ($user) {
                              $sub->from('order')
                                  ->select('id')
                                  ->where('customer_id', $user->id)
                                  ->whereNotIn('status', [5, 6, 7, 8]);
                          });
                }, 'total_revenue')
                ->first();
                $lastPurchaseDate = optional($orders->sortByDesc('created_at')->first())->created_at;
            }   
        }
        $userWebengageData = [
            'account_creation_date' => $user->created_at,
            'backend_user_id'       => $user->id,
            'last_purchase_date'    => $lastPurchaseDate,
            'store_language'        => $user->lang ?? 'ar',
            'total_purchases' =>    $orderStats->total_orders_count ?? 0,
            'total_revenue' =>      $orderStats->total_revenue ?? 0,
        ];
    
        $response = [
            'userdata' => $user,
            'user_webengage_data' => $userWebengageData,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUserWallet($id, $device = 'desktop') {
        $user = User::where('id', $id)->with('WalletHistory')->first(['id','amount']);
        $response = [
            'userdata' => $user,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    // public function getUserOrderData($id) {
    //     $orderdata = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'loyaltypoints')
    //     ->with([
    //         'OrdersData' => function ($q) {
    //             return $q->select('id', 'order_no', 'customer_id', 'status', 'created_at')->withCount('details')->whereNotIn('status',['5','7','8'])->orderBy('id','desc');   
    //         },
    //         'OrdersData.ordersummary' => function ($que) {
    //             return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
    //         }])
    //     ->first();
    //     $response = [
    //         'orderdata' => $orderdata,
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    public function getUserOrderData($id) {
        $orderdata = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'loyaltypoints')
        ->with([
            'OrdersData' => function ($q) {
                return $q->select('id', 'order_no', 'customer_id', 'status', 'created_at')->withCount('details')->whereNotIn('status',['5','7','8'])->orderBy('id','desc');   
            },
            'OrdersData.ordersummary' => function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            },
            'OrdersData.shipmentOrder' => function ($que) {
                return $que->select('id', 'order_id','shipment_no');   
            }])
        ->first();
        $response = [
            'orderdata' => $orderdata,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }    
    public function getUserDeliveredOrderData($id) {
        $orderdata = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'loyaltypoints')
        ->with([
            'DeliveredOrdersData' => function ($q) {
                return $q->select('id', 'order_no', 'customer_id', 'status', 'created_at')->withCount('details')->whereNotIn('status',['5','7','8'])->orderBy('id','desc');   
            },
            'DeliveredOrdersData.ordersummary' => function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            }])
        ->first();
        $response = [
            'orderdata' => $orderdata,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUserAddressData($id) {
        $addressdata = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"),'loyaltypoints')
        ->with([
            'shippingAddressData:id,state_id,customer_id,address,address_option,address_label,make_default,shippinginstractions',
            'shippingAddressData.stateData:id,name,name_arabic,region',
            'shippingAddressData.stateData.region:id,name,name_arabic'])
        ->first();
        
        
        // Separate addresses with make_default=1 and others
        $defaultAddress = null;
        $otherAddresses = [];
        
        if(isset($addressdata->shippingAddressData)) {
            foreach ($addressdata->shippingAddressData as $address) {
                if ($address->make_default == 1) {
                    $defaultAddress = $address;
                } else {
                    $otherAddresses[] = $address;
                }
            }   
        }
        
        // Merge with default address on top
        $sortedAddresses = $defaultAddress ? collect([$defaultAddress])->merge($otherAddresses) : $otherAddresses;
        
        if(isset($addressdata->shippingAddressData)) {
            $addressdata->shippingAddressData = $sortedAddresses;
        }
        
        $response = [
            // 'addressdata' => $addressdata,
            'addresses' => $sortedAddresses,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getOrderData($id) {
        $orderdata = Order::where('id', $id)->select('id', 'order_no', 'shipping_id', 'customer_id', 'paymentmethod', 'status', 'delivery_date', 'created_at', 'order_type','store_id',
         \DB::raw('(SELECT num_of_days FROM express_deliveries WHERE id = (SELECT group_concat(amount_id) FROM order_summary WHERE order_id = order.id) LIMIT 1) AS express_days'))->withCount('details')
        // 'ordersummary' => function ($query) {
        //     $query->select('id', 'name', 'order_id', 'price', 'type')
        //         ->when('type', 'total', function ($query) {
        //             $query->addSelect(\DB::raw('price - (price / 1.15) as vat'));
        //         });
        // }
        ->with([
            'ordersummary' => function ($que) {
                return $que->select('id','name', 'order_id','amount_id','price', 'type');   
            }, 'ordersummary.ExpressData:id,num_of_days', 'details:id,order_id,product_id,total,quantity,expressproduct,express_qty,pre_order,unit_price,is_video_add','details.ugcOrderData:id,order_detail_id,status', 'details.productData:id,name,name_arabic,price,sale_price,brands,feature_image,sku',
            'details.productData.brand:id,name,name_arabic,brand_image_media', 'details.productData.brand.BrandMediaImage:id,image' , 'details.productData.productcategory:id,name,status,arabyads', 'details.productData.featuredImage:id,image',
            'warehouse:id,showroom,showroom_arabic,showroom_address_arabic,showroom_address,showroom_erp_code,waybill_city,region',
            'warehouse.waybillCityData:id,name,name_arabic',
            'warehouse.warehouseRegions:id,name,name_arabic'
            ,'Address:id,customer_id,state_id,address,address_option,make_default,address_label', 'Address.stateData:id,name,name_arabic,region',
            'Address.stateData.region:id,name,name_arabic', 'Address.userData:id,first_name,last_name,email,phone_number', 'ordersummary.couponData:id,connect_with_arabyads,coupon_code'])
        ->first();
        
        $detaildata = $orderdata->details->values();

        $coupon = '';
        $arabyads_check = 0;
        $total = 0;
        if($orderdata->ordersummary->where('type', 'discount')) {
            $coupon = $orderdata->ordersummary->where('type', 'discount')->pluck('name');
            $coupondata = $orderdata->ordersummary->where('type', 'discount')->first();
            $arabyads_check = isset($coupondata->couponData[0]) ? $coupondata->couponData[0]->connect_with_arabyads : 0;
        }
        if($orderdata->ordersummary->where('type', 'total')) {
            $total = $orderdata->ordersummary->where('type', 'total')->pluck('price');
        }

        $items = [];
        $arabyadsDetail = [];
        foreach ($detaildata as $detail) {
            $arabrow = [];
            $productIdNew = $detail->productData->id ?? null;

            if ($productIdNew) {
                $proCats = CategoryProduct::where('product_id', $detail->productData->id)->pluck('category_id')->toArray();
            }
            $item = [
                "item_id" => $detail->productData->sku, // Assuming 'id' from product_data is used for item_id
                "item_name" => $detail->productData->name,
                "item_category" => isset($detail->productData->productcategory[0]) ? $detail->productData->productcategory[0]->name : null,
                "item_category2" => isset($detail->productData->productcategory[1]) ? $detail->productData->productcategory[1]->name : null,
                "item_category3" => isset($detail->productData->productcategory[2]) ? $detail->productData->productcategory[2]->name : null,
                "discount" => number_format($detail->productData->price - $detail->productData->sale_price, 2), // Calculating discount
                "price" => number_format($detail->productData->sale_price, 2),
                "quantity" => $detail->quantity, 
                "item_brand" => $detail->productData->brand ? $detail->productData->brand->name : '',
            ];
        
            $items[] = $item;

            // only for arabyads
            $arabYadsCats = $detail->productData->productcategory->where('status', 1)->where('arabyads', 1);
            foreach ($arabYadsCats as $k => $value) {
                $arabrow['item_category'.($k+1)] = $value->name;
            }

            $catdata = array(
                $arabrow
            ); 

            $arabyads_items = array(
                'categories'=> $catdata, 
                'name'=> $detail->productData->name, 
                'price' => $detail->productData->price. '.00', 
            );
            array_push($arabyadsDetail, $arabyads_items);
        }

        $response = [
            'orderdata' => $orderdata,
            'items' => $items,
            'coupon' => isset($coupon[0]) ? $coupon[0] : $coupon,
            'arabyads_check' => $arabyads_check,
            'total' => isset($total[0]) ? $total[0] : $total,
            'arabyads_items' => $arabyadsDetail,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    // public function getOrderDataThankYou($id) {
    //     $orderdata = Order::where('id', $id)->select('id', 'order_no', 'shipping_id', 'customer_id', 'paymentmethod', 'status', 'delivery_date', 'created_at',
    //      \DB::raw('(SELECT num_of_days FROM express_deliveries WHERE id = (SELECT group_concat(amount_id) FROM order_summary WHERE order_id = order.id) LIMIT 1) AS express_days', 'loyalty_shipping'))->withCount('details')
    //     // 'ordersummary' => function ($query) {
    //     //     $query->select('id', 'name', 'order_id', 'price', 'type')
    //     //         ->when('type', 'total', function ($query) {
    //     //             $query->addSelect(\DB::raw('price - (price / 1.15) as vat'));
    //     //         });
    //     // }
    //     ->with([
    //         'ordersummary' => function ($que) {
    //             return $que->select('id','name', 'order_id','amount_id','price', 'type');   
    //         }, 'ordersummary.ExpressData:id,num_of_days', 'details:id,order_id,product_id,total,quantity,expressproduct,express_qty,pre_order,unit_price', 'details.productData:id,name,name_arabic,price,sale_price,brands,feature_image,sku,slug',
    //         'details.productData.brand:id,name,name_arabic,brand_image_media', 'details.productData.brand.BrandMediaImage:id,image' , 'details.productData.productcategory:id,name,status,arabyads', 'details.productData.featuredImage:id,image'
    //         ,'Address:id,customer_id,state_id,address,address_option,make_default,address_label', 'Address.stateData:id,name,name_arabic,region',
    //         'Address.stateData.region:id,name,name_arabic', 'Address.userData:id,first_name,last_name,email,phone_number', 'ordersummary.couponData:id,connect_with_arabyads,coupon_code'])
    //     ->first();
        
    //     $detaildata = $orderdata->details->values();

    //     $coupon = '';
    //     $arabyads_check = 0;
    //     $total = 0;
    //     if($orderdata->ordersummary->where('type', 'discount')) {
    //         $coupon = $orderdata->ordersummary->where('type', 'discount')->pluck('name');
    //         $coupondata = $orderdata->ordersummary->where('type', 'discount')->first();
    //         $arabyads_check = isset($coupondata->couponData[0]) ? $coupondata->couponData[0]->connect_with_arabyads : 0;
    //     }
    //     if($orderdata->ordersummary->where('type', 'total')) {
    //         $total = $orderdata->ordersummary->where('type', 'total')->pluck('price');
    //     }

    //     $items = [];
    //     $arabyadsDetail = [];
    //     foreach ($detaildata as $dk => $detail) {
    //         $proCats = CategoryProduct::where('product_id', $detail->productData->id)->pluck('category_id')->toArray();
    //         $item = [
    //             "item_id" => $detail->productData->sku, // Assuming 'id' from product_data is used for item_id
    //             "item_name" => $detail->productData->name,
    //             "item_category" => isset($detail->productData->productcategory[0]) ? $detail->productData->productcategory[0]->name : null,
    //             "item_category2" => isset($detail->productData->productcategory[1]) ? $detail->productData->productcategory[1]->name : null,
    //             "item_category3" => isset($detail->productData->productcategory[2]) ? $detail->productData->productcategory[2]->name : null,
    //             "discount" => number_format($detail->productData->price - $detail->productData->sale_price, 2), // Calculating discount
    //             "price" => number_format($detail->productData->sale_price, 2),
    //             "quantity" => $detail->quantity, 
    //             "item_brand" => $detail->productData->brand ? $detail->productData->brand->name : '',
    //         ];
        
    //         $items[] = $item;

    //         // only for arabyads
            
    //         $arabrow = [];
    //         $arabYadsCats = $detail->productData->productcategory->where('status', 1)->where('arabyads', 1);
    //         foreach ($arabYadsCats as $k => $value) {
    //             $arabrow[] = $value->name;
    //         }

    //         // $catdata = array(
    //         //     $arabrow
    //         // ); 

    //         // $arabyads_items = array(
    //         //     'categories'=> $arabrow, 
    //         //     'name'=> $detail->productData->name, 
    //         //     'price' => $detail->productData->price. '.00', 
    //         // );
            
    //         $arabyadsDetail[$dk]['categories'] = $arabrow;
    //         $arabyadsDetail[$dk]['name'] = $detail->productData->name;
    //         $arabyadsDetail[$dk]['price'] = $detail->productData->sale_price ? number_format($detail->productData->sale_price, 2) :number_format($detail->productData->price, 2);
    //         // array_push($arabyadsDetail, $arabyads_items);
    //     }

    //     $response = [
    //         'orderdata' => $orderdata,
    //         'items' => $items,
    //         'coupon' => isset($coupon[0]) ? $coupon[0] : $coupon,
    //         'arabyads_check' => $arabyads_check,
    //         'total' => isset($total[0]) ? $total[0] : $total,
    //         'arabyads_items' => $arabyadsDetail,
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }

    public function getOrderDataThankYou($id) {
        $orderdata = Order::where('id', $id)->select('id', 'order_no', 'shipping_id', 'customer_id', 'paymentmethod', 'status', 'delivery_date', 'created_at', 'order_type','store_id',
         \DB::raw('(SELECT num_of_days FROM express_deliveries WHERE id = (SELECT group_concat(amount_id) FROM order_summary WHERE order_id = order.id) LIMIT 1) AS express_days', 'loyalty_shipping'))->withCount('details')
        // 'ordersummary' => function ($query) {
        //     $query->select('id', 'name', 'order_id', 'price', 'type')
        //         ->when('type', 'total', function ($query) {
        //             $query->addSelect(\DB::raw('price - (price / 1.15) as vat'));
        //         });
        // }
        ->with([
            'ordersummary' => function ($que) {
                return $que->select('id','name', 'order_id','amount_id','price', 'type');   
            }, 'ordersummary.ExpressData:id,num_of_days', 'details:id,order_id,product_id,total,quantity,expressproduct,express_qty,pre_order,unit_price', 'details.productData:id,name,name_arabic,price,sale_price,brands,feature_image,sku,slug',
            'details.productData.brand:id,name,name_arabic,brand_image_media', 'details.productData.brand.BrandMediaImage:id,image' , 
            // 'details.productData.productcategory:id,name,status,arabyads',
            'details.productData.productcategory' => function ($query) {
                $query->where('menu', 1)->where('status', 1)
                      ->select('productcategories.id','productcategories.name','productcategories.status','productcategories.arabyads');
            },
            'details.productData.productcategory:id,name,status,arabyads',
            'details.productData.featuredImage:id,image',
            'warehouse:id,showroom,showroom_arabic,showroom_address_arabic,showroom_address,showroom_erp_code,waybill_city,region',
            'warehouse.waybillCityData:id,name,name_arabic',
            'warehouse.warehouseRegions:id,name,name_arabic'
            ,'Address:id,customer_id,state_id,address,address_option,make_default,address_label', 'Address.stateData:id,name,name_arabic,region',
            'Address.stateData.region:id,name,name_arabic', 'Address.userData:id,first_name,last_name,email,phone_number', 'ordersummary.couponData:id,connect_with_arabyads,coupon_code'])
        ->first();
        
        $detaildata = $orderdata->details->values();

        $coupon = '';
        $arabyads_check = 0;
        $total = 0;
        if($orderdata->ordersummary->where('type', 'discount')) {
            $coupon = $orderdata->ordersummary->where('type', 'discount')->pluck('name');
            $coupondata = $orderdata->ordersummary->where('type', 'discount')->first();
            $arabyads_check = isset($coupondata->couponData[0]) ? $coupondata->couponData[0]->connect_with_arabyads : 0;
        }
        if($orderdata->ordersummary->where('type', 'total')) {
            $total = $orderdata->ordersummary->where('type', 'total')->pluck('price');
        }
        $items = [];
        $arabyadsDetail = [];
        foreach ($detaildata as $dk => $detail) {
            $proCats = CategoryProduct::where('product_id', $detail->productData->id)->pluck('category_id')->toArray();
            $item = [
                "item_id" => $detail->productData->sku, // Assuming 'id' from product_data is used for item_id
                "item_name" => $detail->productData->name,
                "item_category" => isset($detail->productData->productcategory[0]) ? $detail->productData->productcategory[0]->name : null,
                "item_category2" => isset($detail->productData->productcategory[1]) ? $detail->productData->productcategory[1]->name : null,
                "item_category3" => isset($detail->productData->productcategory[2]) ? $detail->productData->productcategory[2]->name : null,
                "discount" => number_format($detail->productData->price - $detail->productData->sale_price, 2), // Calculating discount
                "price" => number_format($detail->productData->sale_price, 2),
                "quantity" => $detail->quantity, 
                "item_brand" => $detail->productData->brand ? $detail->productData->brand->name : '',
            ];
        
            $items[] = $item;
            
            //bread

            // only for arabyads
            
            $arabrow = [];
            $arabYadsCats = $detail->productData->productcategory->where('status', 1)->where('arabyads', 1);
            foreach ($arabYadsCats as $k => $value) {
                $arabrow[] = $value->name;
            }

            // $catdata = array(
            //     $arabrow
            // ); 

            // $arabyads_items = array(
            //     'categories'=> $arabrow, 
            //     'name'=> $detail->productData->name, 
            //     'price' => $detail->productData->price. '.00', 
            // );
            
            $arabyadsDetail[$dk]['categories'] = $arabrow;
            $arabyadsDetail[$dk]['name'] = $detail->productData->name;
            $arabyadsDetail[$dk]['price'] = $detail->productData->sale_price ? number_format($detail->productData->sale_price, 2) :number_format($detail->productData->price, 2);
            // array_push($arabyadsDetail, $arabyads_items);
        }

        $response = [
            'orderdata' => $orderdata,
            'items' => $items,
            'coupon' => isset($coupon[0]) ? $coupon[0] : $coupon,
            'arabyads_check' => $arabyads_check,
            'total' => isset($total[0]) ? $total[0] : $total,
            'arabyads_items' => $arabyadsDetail
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }    
    public function getCheckOrderReview($id,$user_id) {
        $orderdata = ProductReview::where('orderdetail_id', $id)->where('user_id',$user_id)->get();
        $reviewData = [];
        foreach ($orderdata as $detail) {
            $reviewData[$detail->product_sku]['title'] = $detail->title; 
            $reviewData[$detail->product_sku]['review'] = $detail->review;
            $reviewData[$detail->product_sku]['rating'] = $detail->rating; 
        }

        $response = [
            'data' => $reviewData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getCheckMaintenanceProduct($id) {
        $MaintenanceData = Maintenance::where('order_no', $id)->get();
        $maintenanceProduct = [];
        foreach ($MaintenanceData as $detail) {
            $maintenanceProduct[$detail->orderdetail_id]['subject'] = $detail->subject; 
            $maintenanceProduct[$detail->orderdetail_id]['comment'] = $detail->comment;
        }

        $response = [
            'data' => $maintenanceProduct,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function addProductReview(Request $request) {
        $data = $request->all();
        $success = false;
        
        
        foreach($data['addrating'] as $key => $ProductReviewData){
            if(isset($data['addrating'][$key])){
                $ProductReview = ProductReview::create([
                    'orderdetail_id' => isset($data['order_id']) ? $data['order_id'] : null,
                    'product_sku' => $key,
                    'rating' => $ProductReviewData,
                    'title' => isset($data['addtitle'][$key]) ? $data['addtitle'][$key] : null,
                    'review' => isset($data['addreview'][$key]) ? $data['addreview'][$key] : null,
                    'user_id' => isset($data['user_id'][$key]) ? $data['user_id'][$key] : null,
                    'anonymous' => 0,
                    'status' => 0,
                ]);
                if($ProductReview){
                    $success = true;
                }
            }
        }
        
        
        $response = [
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
        
    
    public function getUserProfileData($id) {
        $user = User::where('id', $id)
        ->select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'first_name', 'last_name', 'email', 'phone_number', 'profile_img', 'loyaltypoints','amount', 'date_of_birth', 'gender', 'created_at')
        ->with('OrdersData','shippingAddressDataDefault:id,customer_id,state_id', 'shippingAddressDataDefault.stateData:id,name,name_arabic')
        ->withCount('wishlists')->withCount('compares')->withCount('ConfirmedOrdersData')->first();
        
        $totalRevenue = 0;
        $lastPurchaseDate = null;
        $ordersCount = 0;
    
        if ($user->OrdersData->isNotEmpty()) {
            $orders = $user->OrdersData;
            
            $orderStats = DB::table('order')
                            ->join('order_summary', function ($join) {
                                $join->on('order.id', '=', 'order_summary.order_id')
                                     ->where('order_summary.type', 'subtotal');
                            })
                            ->where('order.customer_id', $user->id)
                            ->whereNotIn('order.status', [5, 6, 7, 8])
                            ->select(
                                DB::raw('COUNT(DISTINCT order.id) as total_orders_count'),
                                DB::raw('SUM(order_summary.price) as total_revenue')
                            )
                            ->first();
    
    
            // dd($orderStats);
            $lastPurchaseDate = optional($orders->sortByDesc('created_at')->first())->created_at;
        }
        $userWebengageData = [
            'account_creation_date' => $user->created_at,
            'backend_user_id'       => $user->id,
            'last_purchase_date'    => $lastPurchaseDate,
            'store_language'        => $user->lang ?? 'ar',
            'total_purchases' =>    $orderStats->total_orders_count ?? 0,
            'total_revenue' =>      $orderStats->total_revenue ?? 0,
        ];
        $response = [
            'user' => $user,
            'user_webengage_data' => $userWebengageData
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getAddressData($id) {
        $address = shippingAddress::where('id', $id)->select('id', 'address', 'address_option', 'state_id', 'make_default', 'shippinginstractions', 'address_label')
        ->With('stateData:id,name,name_arabic,region', 'stateData.region:id,name,name_arabic')->first();
        $cities = States::where('country_id','191')->get(['id as value', 'name as label']);
        $arabiccities = States::where('country_id','191')->get(['id as value', 'name_arabic as label']);
        $regions = Region::where('status', 1)->get(['id as value', 'name as label']);
        $arabicregions = Region::get(['id as value', 'name_arabic as label']);
        $response = [
            'address' => $address,
            'arabiccities' => $arabiccities,
            'cities' => $cities,
            'regions' => $regions,
            'arabicregions' => $arabicregions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function DeleteAddress($id) {
        $address = shippingAddress::find($id);
        // print_r($address);die();
        $address->delete();
        $response = [
            'success' => 'true',
            'message' => 'Address deleted Successfully!',
            // 'regions' => $regions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    
    public function getCities($lang) {
        $seconds = 86400;
        if(Cache::has('citiescache_'.$lang))
            $response = Cache::get('citiescache_'.$lang);
        else{
            if($lang === 'en'){
                $cities = States::where('country_id','191')->get(['id as value', 'name as label', 'name_arabic as extra_label']);
            }else{
                $cities = States::where('country_id','191')->get(['id as value', 'name_arabic as label', 'name as extra label']);
            }
            $response = [
                'cities' => $cities,
            ];
            // CacheStores::create([
            //     'key' => 'citiescache_'.$lang,
            //     'type' => 'citiescache_'.$lang
            // ]);
            Cache::remember('citiescache_'.$lang, $seconds, function () use ($response) {
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
    
    public function onlyCity($city_name) {
        
        $seconds = 86400;
        if(Cache::has('onlycity_'.$city_name))
            $response = Cache::get('onlycity_'.$city_name);
        else{
            $cities = States::where('country_id','191')->where('name', $city_name)->orWhere('name_arabic', $city_name)->first(['id','name','name_arabic']);
            
            // $showroom = StoreLocator::whereHas('cities', function ($query) use ($city_name) {
            //                     $query->where('name', $city_name)->orWhere('name_arabic', $city_name);
            //             })->get(['id','name','name_arabic','address','lat','lng','phone_number','direction_button','time']);
            
            $response = [
                'cities' => $cities,
                // 'showrooms' => $showroom,
            ];
            // CacheStores::create([
            //     'key' => 'onlycity_'.$city_name,
            //     'type' => 'onlycity_'.$city_name
            // ]);
            Cache::remember('onlycity_'.$city_name, $seconds, function () use ($response) {
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
    
    public function RegionCitiesNew($id) {
        $storeCities = StoreLocatorCity::pluck('city_id')->toArray();
        $region = Region::with(['cityname' => function($query) use ($storeCities) {
            $query->whereIn('id', array_unique($storeCities));
        }])
        ->find($id);
        if ($region) {
            $citynames = $region->cityname;
        
            $citynamesArray = $citynames->map(function ($city) {
                return [
                    'value' => $city->id,
                    'label' => $city->name,
                ];
            })->toArray();
            
            $citynamesarabicArray = $citynames->map(function ($city) {
                return [
                    'value' => $city->id,
                    'label' => $city->name_arabic,
                ];
            })->toArray();
        }
        
        $response = [
            'regions' => $region,
            'cities' => $citynamesArray,
            'citiesarabic' => $citynamesarabicArray,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function RegionCities($id) {
        
        $region = Region::with('cityname:id,name,name_arabic,region')->find($id);
        if ($region) {
            $citynames = $region->cityname;
        
            $citynamesArray = $citynames->map(function ($city) {
                return [
                    'value' => $city->id,
                    'label' => $city->name,
                ];
            })->toArray();
            
            $citynamesarabicArray = $citynames->map(function ($city) {
                return [
                    'value' => $city->id,
                    'label' => $city->name_arabic,
                ];
            })->toArray();
        }
        
        $response = [
            'regions' => $region,
            'cities' => $citynamesArray,
            'citiesarabic' => $citynamesarabicArray,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function CreateAddress(Request $request) {
        // print_r($request->all());die();
        $user = User::where('id', $request->user_id)->first();
        // print_r($user);
        
        if($request->make_default == 1){
           $defaultdata = shippingAddress::where('customer_id', $request->user_id)->where('make_default', 1)->first();
           if($defaultdata){
                $defaultdata->make_default = 0;
                $defaultdata->update();
           }
        }
        
        $address = shippingAddress::create([
            'customer_id' => isset($request->user_id) ? $request->user_id : null,
            'first_name' => isset($user->first_name) ? $user->first_name : null,
            'last_name' => isset($user->last_name) ? $user->last_name : null,
            'phone_number' => isset($user->phone_number) ? $user->phone_number : null,
            'address' => isset($request->address) ? $request->address : null,
            'state_id' => isset($request->state_id) ? $request->state_id : null,
            'shippinginstractions' => isset($request->shippinginstractions) ? $request->shippinginstractions : null,
            'make_default' => isset($request->make_default) ? $request->make_default : 0,
            'address_label' => isset($request->address_label) ? $request->address_label : 0,
            'country_id' => 191,
        ]);
        
        $response = [
            'success' => 'true',
            'message' => 'Address Added Successfully!',
            'addressid' => $address->id,
            // 'regions' => $regions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function UpdateAddress(Request $request, $id) {
        // print_r($request->all());die();
        $user = User::where('id', $request->user_id)->first();
        
        if($request->make_default == 1){
           $defaultdata = shippingAddress::where('customer_id', $request->user_id)->where('make_default', 1)->first();
           if($defaultdata){
                $defaultdata->make_default = 0;
                $defaultdata->update();
           }
        }
        
        // print_r($user);
        $address = shippingAddress::whereId($id)->update([
            'customer_id' => isset($request->user_id) ? $request->user_id : null,
            'first_name' => isset($user->first_name) ? $user->first_name : null,
            'last_name' => isset($user->last_name) ? $user->last_name : null,
            'phone_number' => isset($user->phone_number) ? $user->phone_number : null,
            'address' => isset($request->address) ? $request->address : null,
            'state_id' => isset($request->state_id) ? $request->state_id : null,
            'shippinginstractions' => isset($request->shippinginstractions) ? $request->shippinginstractions : null,
            'make_default' => isset($request->make_default) ? $request->make_default : 0,
            'address_label' => isset($request->address_label) ? $request->address_label : 0,
            'country_id' => 191,
        ]);
        
        $response = [
            'success' => 'true',
            'message' => 'Address updated Successfully!',
            // 'regions' => $regions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getRegData() {
        $regions = Region::get(['id as value', 'name as label']);
        $arabicregions = Region::get(['id as value', 'name_arabic as label']);
        
        $response = [
            'regions' => $regions,
            'arabicregions' =>$arabicregions,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function CheckUserPhone(Request $request) {
        // die;
        $data = $request->all();
        $ipAddress = $request->ip();
        $userAgent = $request->header('User-Agent');
        // print_r($userAgent);die;
        $user = false;
        $success = false;
        $msg = '';
        $lang = isset($data['lang']) ? $data['lang'] : 'ar';


        $secretKey = '6Ld5cE0qAAAAAHQ3J_hev7nvhAtq-bcbAMxL0haP';
        $token = isset($data['token']) ? $data['token'] : '';
        // Initialize cURL session
        $ch = curl_init();
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Prepare data for POST request
        $datas = [
            'secret' => $secretKey,
            'response' => $token
        ];

        // Set the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datas));
        // Execute the request and get the response
        $response = curl_exec($ch);
        // Close cURL session
        curl_close($ch);
        // Decode the response
        $responseData = json_decode($response, true);
        // Check the response
        // if ($responseData['success'] == true) {
            if($data['phone_number']){
                // $ipAddressCheck = LoginAttempts::where('ip_address', $ipAddress)->where('phone_number', $data['phone_number'])->last();

                // if($ipAddressCheck) {
                    // Find or create a login attempt record
                    $LoginAttempts = LoginAttempts::firstOrCreate(
                        ['phone_number' => $data['phone_number']],
                        ['failed_attempts' => 0, 'locked_until' => null, 'ip_address' => $ipAddress]
                    );

                    // Check if locked out
                    // if ($ipAddressCheck->locked_until && now()->lessThan($ipAddressCheck->locked_until)) {
                    //     return response()->json(['success' => false, 'msg' => 'Too many attempts. Try again later.'], 429);
                    // }
                // }

                $user = User::with('shippingAddressDataDefault')->where('phone_number',$data['phone_number'])->first(['id','phone_number']);
                if($user){
                    // if($ipAddressCheck) {
                        // Increment failed attempts
                        // $LoginAttempts->failed_attempts++;
                    // }
                    // // additional work for usman bhai phone number OTP

                    // if ($LoginAttempts->failed_attempts >= 2) {
                    //     // Lock the account for 3 hours
                    //     $LoginAttempts->locked_until = now()->addHours(3);
                    //     $msg = 'Too many attempts. You are locked out for 3 hours.';
                    //     $success = false;
                    // }
                    // else {
                        if($data['phone_number'] == '568061029') {
                            $checkotp = OtpVerification::where('phone_number', '568061029')->where('otp_code', '001122')->first();
                            $otp = '001122';
                            if(!$checkotp) {
                                $otpSave = OtpVerification::create([
                                    'phone_number' => '568061029',
                                    'otp_code' => $otp,
                                ]); 
                            }
                            //$messageArOpt = 'هو ك التحق اص بك. للحفاظ عل منك، لا تشارك ذ لكد مع أي شخ.';
                            $messageArOpt = ' هو كود التقق الاص بك للحفاظ عى أانك لا تشارك ذا اكود مع أي شخ';

                            if ($lang == 'ar') {
                                ConditionSetup_helper::sms('+966568061029', '' . $otp . ' ' . $messageArOpt);
                            } else
                                ConditionSetup_helper::sms('+966568061029',''.$otp.' is your verification code. For your security, do not share this code.');
                            $params = [
                                [
                                  "type" => "text", 
                                  "text" => $otp
                                ]
                            ];
                            $msg = NotificationHelper::whatsappmessage("+966568061029",'pickup_from_store_code_authentication',$lang,$params,false,$otp);
                            // additional work for usman bhai phone number OTP
                        }   else {
                            $number = hexdec(uniqid());
                            $varray = str_split($number);
                            $len = sizeof($varray);
                            $otp = array_slice($varray, $len-6, $len);
                            $otp = implode(",", $otp);
                            $otp = str_replace(',', '', $otp);
                            OtpVerification::where('phone_number', $request->phone_number)->delete();
                            $otpSave = OtpVerification::create([
                                'phone_number' => $request->phone_number,
                                'otp_code' => $otp,
                            ]); 
                            if($lang == 'ar')
                               ConditionSetup_helper::sms('+966'.$otpSave['phone_number'],''.$otp.' ه كو التحقق الخاص بك لحفا على أمانك لا تشاك هذ الكود مع أي شخص ');
                            else {
                               $res = ConditionSetup_helper::sms('+966'.$otpSave['phone_number'],''.$otp.' is your verification code. For your security, do not share this code.');
                               \Log::info('English SMS sent', [
                                    'phone' => '+966' . $otpSave['phone_number'],
                                    'response' => $res,
                                    'message' => $res
                                ]);
                            }
                            //   dd($res);
                            $params = [
                                [
                                   "type" => "text", 
                                   "text" => $otp
                                ]
                            ];
                            $msg = NotificationHelper::whatsappmessage("+966".$otpSave['phone_number'],'pickup_from_store_code_authentication',$lang,$params,false,$otp);
                            // print_r($msg);
                            
                        }
                        $success = true;
                    // }

                    // if($ipAddressCheck) {
                        // $LoginAttempts->save();
                    // }
                }
            }
        // } else {
        //     $msg = 'Verification failed.';
        // }
        
        $response = [
            'user' => $user,
            'success' => $success,
            'msg' => $msg
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function otpCheck(Request $request) {
        // $validator = Validator::make($request->all(), [
        //     'phone_number' => 'required',
        //     'otp_code' => 'required',
        // ]);
        $device = isset($request->device) ? $request->device : 'desktop';
        // where('status',1)->
        $walletsData  = WalletSetting::where('all_user',1)
        ->when($device == 'desktop', function ($q) {
            return $q->whereRaw("FIND_IN_SET('1', all_user_device)");
        })
        ->when($device != 'desktop', function ($q) {
            return $q->whereRaw("FIND_IN_SET('2', all_user_device)");
        })->first();
        
        $otp = OtpVerification::where('phone_number', $request->phone_number)->where('otp_code', $request->otp_code)->first();
      //   return $otp;
        if($otp){
            // $user = User::where('phone_number', $request->phone_number)->first();
            $user = User::with('shippingAddressDataDefault.stateData')->where('phone_number', $request->phone_number)->first();
            
            if ($user) {
                // Auth::login($user);
                if($request->phone_number != '568061029') {
                    $delete_otp = $otp->delete();   
                }
                
                // $emailtemplate = EmailTemplate::where('purpose_template', 2)->first();
           
                // Mail::send('email.' .$emailtemplate->file_path, ['emailtemplate' => $emailtemplate, 'user' => $user], function ($message) use ($user) {
                //     $message->to($user->email)
                //     ->subject('Login To Tamkeen Stores');
                // });
                if($walletsData){
                    $wallet = WalletHistory::where('user_id',$user->id)->where('wallet_type','login')->first();
                    if(!$wallet){
                        $currentAmount = $user->amount + $walletsData->all_user_amount;
                        $user->amount = $currentAmount;
                        $user->save();
                        
                        $walletHistory = WalletHistory::create([
                            'user_id' => $user->id,
                            'order_id' => null,
                            'type' => 1,
                            'amount' => $walletsData->all_user_amount,
                            'description' => 'Login',
                            'description_arabic' => 'Login',
                            'wallet_type' => 'login',
                            'title' => 'Login',
                            'title_arabic' => 'Login',
                            'current_amount' => $currentAmount,
                            'status' => 0,
                        ]);
                    }
                }
                
                return response()->json(['success'=> true, 'user_id' => $user->id , 'new'=> false, 'message'=>'otp matched','user'=>$user]);
            }
        }
        else{
            return response()->json(['success'=> false, 'message'=>'otp not matched']);
        }
    }
    
    public function RegisterPhoneCheck(Request $request) {
        $data = $request->all();
        $user = false;
        $success = false;
        $lang = isset($data['lang']) ? $data['lang'] : 'ar';
        if($data['phone_number']){
            // $user = User::with('shippingAddressDataDefault')->where('phone_number',$data['phone_number'])->first(['id','phone_number']);
            // if($user){
                $number = hexdec(uniqid());
                $varray = str_split($number);
                $len = sizeof($varray);
                $otp = array_slice($varray, $len-6, $len);
                $otp = implode(",", $otp);
                $otp = str_replace(',', '', $otp);
                
                $otpSave = OtpVerification::create([
                    'phone_number' => $request->phone_number,
                    'otp_code' => $otp,
                ]);
                
                $messageArOpt = ' هو كو التقق الخا بك للفاظ على أمك لا تشرك هذا الد مع أي شخص';

                if($lang == 'ar')
                    ConditionSetup_helper::sms('+966'.$otpSave['phone_number'], '' . $otp . ' ' . $messageArOpt);
                else
                    ConditionSetup_helper::sms('+966'.$otpSave['phone_number'],''.$otp.' is your verification code. For your security, do not share this code.');
                $params = [
                    [
                       "type" => "text", 
                       "text" => $otp
                    ]
                ];
                $msg = NotificationHelper::whatsappmessage("+966".$otpSave['phone_number'],'test',$lang,$params,false,$otp);
                $success = true;
            // }
        }
        
        $response = [
            'user' => $user,
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
    
    public function RegisterUser(Request $request) {
        
        $device = isset($data['device']) ? $data['device'] : 'desktop';
        // where('status',1)->
        $walletsData  = WalletSetting::where('new_user',1)
        ->when($device == 'desktop', function ($q) {
            return $q->whereRaw("FIND_IN_SET('1', new_user_device)");
        })
        ->when($device != 'desktop', function ($q) {
            return $q->whereRaw("FIND_IN_SET('2', new_user_device)");
        })->first();
        
        $LoyaltySetting = LoyaltySetting::where('extra_reward_newuser',1)->first();
        $data = $request->all();
        $user = false;
        $success = false;
        $userNew = '';
        if($data['phone_number']){
            $user = User::where('phone_number',$data['phone_number'])->first(['id','phone_number']);
            if($user){
                $success = false;
            }else{
                // $success = true;
                // $user = User::where('email',$data['email'])->first(['id','phone_number','email']);
                // if($user){
                //     $success = false;
                // }else{
                    $userData = User::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'phone_number' => $data['phone_number'],
                        'role_id' => isset($data['role_id']) ? $data['role_id'] : 2,
                        'email' => $data['email'],
                        'date_of_birth' => isset($data['date_of_birth']) ? $data['date_of_birth'] : null,
                        'notes' => isset($data['notes']) ? $data['notes'] : null,
                        'status' => isset($data['status']) ? $data['status'] : 0,
                        'password' => isset($data['password']) ? $data['password'] : "password2024",
                        'user_device' => isset($data['user_device']) ? $data['user_device'] : null,
                        'lang' => isset($data['lang']) ? $data['lang'] : 'ar',
                        'gender' => isset($data['gender']) ? $data['gender'] : null,
                    ]);
                    $fullName = '';
                    $fullName = ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '');
                    
                    //mail send to new user
                    $emailtemplate = EmailTemplate::where('purpose_template', 2)->first();
                    // if($emailtemplate) {
                    //     Mail::send('email.welcome-to-tamkeen-stores', ['emailtemplate' => $emailtemplate, 'user' => $data], function ($message) use ($data) {
                    //         $message->from('welcome@tamkeenstores.com.sa')
                    //         ->to($data['email'])
                    //         ->subject('Welcome To Tamkeen Stores');
                    //     });
                    // }
                    if ($emailtemplate && !empty($emailtemplate->page_content)) {
                        Mail::send([], [], function ($message) use ($emailtemplate, $data) {
                            $message->from('welcome@tamkeenstores.com.sa')
                                    ->to($data['email'])
                                    ->subject('Welcome To Tamkeen Stores')
                                    ->html($emailtemplate->page_content);
                        });
                    }
                    
                    if($walletsData){
                        $userData->amount = $walletsData->new_user_amount;
                        $userData->save();
                        $walletHistorySignUp = WalletHistory::create([
                            'user_id' => $userData->id,
                            'order_id' => null,
                            'type' => 1,
                            'amount' =>  $walletsData->new_user_amount,
                            'description' => 'Sign Up',
                            'description_arabic' => 'Sign Up',
                            'wallet_type' => 'signup',
                            'title' => 'Sign Up',
                            'title_arabic' => 'Sign Up',
                            'current_amount' => $walletsData->new_user_amount,
                            'status' => 0,
                        ]);
                    }
                    
                    if($LoyaltySetting){
                        $LoyaltyHistory = LoyaltyHistory::create([
                            'user_id' => $userData->id,
                            'title' => 'Registration Reward',
                            'title_arabic' => 'فأ الل',
                            'calculate_type' => 1,
                            'points' => $LoyaltySetting->reward_newuser_amount,
                            'order_id' => null,
                            'status' => 1,
                        ]);
                            
                            $currentAmount = $userData->amount + $LoyaltySetting->reward_newuser_amount;
                            $userData->amount = $currentAmount;
                            $userData->save();
                            
                            $walletHistory = WalletHistory::create([
                                'user_id' => $userData->id,
                                'order_id' => null,
                                'type' => 1,
                                'amount' => $LoyaltySetting->reward_newuser_amount,
                                'description' => 'Registration Reward',
                                'description_arabic' => 'كفة تجل',
                                'wallet_type' => 'loyalty',
                                'title' => 'Registration Reward',
                                'title_arabic' => ' س',
                                'current_amount' => $currentAmount,
                                'status' => 0,
                            ]);
                            
                            
                            $currentAmountMinus = $currentAmount - $LoyaltySetting->reward_newuser_amount;
                            $userData->amount = $currentAmountMinus;
                            $userData->save();
                            
                            $walletHistory = WalletHistory::create([
                                'user_id' => $userData->id,
                                'order_id' => null,
                                'type' => 0,
                                'amount' => $LoyaltySetting->reward_newuser_amount,
                                'description' => 'Registration Reward',
                                'description_arabic' => 'افة لس',
                                'wallet_type' => 'loyalty',
                                'title' => 'Registration Reward',
                                'title_arabic' => 'كف ل',
                                'current_amount' => $currentAmountMinus,
                                'status' => 0,
                            ]);
                    }
                    if($userData && isset($userData->id)){
                        $userNew = User::where('id', $userData->id)->select('id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'first_name', 'last_name', 'email', 'phone_number', 'profile_img', 'loyaltypoints','amount')
                                ->with('shippingAddressDataDefault:id,customer_id,state_id', 'shippingAddressDataDefault.stateData:id,name,name_arabic')
                                ->withCount('wishlists')->withCount('compares')->withCount('ConfirmedOrdersData')->first();
                    }
                    $user = ['id'=>$userData->id, 'fullName'=> $fullName];
                    $success = true;
                }
            // }
        }
        
        $response = [
            'success' => $success,
            'user' => $user,
            'userNew' => $userNew
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function otpRegisterCheck(Request $request) {
        // $validator = Validator::make($request->all(), [
        //     'phone_number' => 'required',
        //     'otp_code' => 'required',
        // ]);

        $otp = OtpVerification::where('phone_number', $request->phone_number)->where('otp_code', $request->otp_code)->first();
        if($otp){
            // $user = User::where('phone_number', $request->phone_number)->first();
            // if ($user) {
                // Auth::login($user);
                $delete_otp = $otp->delete();
                return response()->json(['success'=> true, 'new'=> false, 'message'=>'otp matched']);
            // }
        }
        else{
            return response()->json(['success'=> false, 'message'=>'otp not matched']);
        }
    }
    
    public function ResendOtp(Request $request) {
        $data = $request->all();
        $user = false;
        $success = false;
        $lang = isset($data['lang']) ? $data['lang'] : 'ar';
        $msg = '';

        if ($data['phone_number']) {
            // Find or create a login attempt record
            // $LoginAttempts = LoginAttempts::firstOrCreate(
            //     ['phone_number' => $data['phone_number']],
            //     ['failed_attempts' => 0, 'locked_until' => null]
            // );

            // Check if locked out
            // if ($LoginAttempts->locked_until && now()->lessThan($LoginAttempts->locked_until)) {
            //     return response()->json(['success' => false, 'msg' => 'Too many attempts. Try again later.'], 429);
            // }

            // Delete previous OTPs for this phone number
            OtpVerification::where('phone_number', $data['phone_number'])->delete();

            
            // Increment failed attempts
            // $LoginAttempts->failed_attempts++;

            // if ($LoginAttempts->failed_attempts >= 2) {
            //     // Lock the account for 3 hours
            //     $LoginAttempts->locked_until = now()->addHours(3);
            //     $msg = 'Too many attempts. You are locked out for 3 hours.';
            //     $success = false;
            // } 
            // else {
                // Your existing OTP generation logic
                $number = hexdec(uniqid());
                $varray = str_split($number);
                $len = sizeof($varray);
                $otp = array_slice($varray, $len - 6, $len);
                $otp = implode(",", $otp);
                $otp = str_replace(',', '', $otp);
                
                // Save the new OTP
                OtpVerification::create([
                    'phone_number' => $request->phone_number,
                    'otp_code' => $otp,
                ]);
                $messageArOpt = ' هو كو التحقق اخاص بك لحفا على أمانك لا شاك هذا الكود مع ي شخص';
                
                // Send OTP via SMS
                if ($lang == 'ar') {
                    ConditionSetup_helper::sms('+966' . $request->phone_number, '' . $otp . ' ' . $messageArOpt);
                } else {
                    ConditionSetup_helper::sms('+966' . $request->phone_number, "$otp is your verification code. For your security, do not share this code.");
                }
                $msg = 'OTP sent successfully.';
                $success = true;
            // }

            // $LoginAttempts->save();
        }

        $response = [
            'user' => $user,
            'success' => $success,
            'msg' => $msg
        ];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson, 9);

        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    
    public function userDelete(Request $request) {
        $data = $request->all();
        $user = false;
        $success = false;
        if($data['user_id']){
                $deleteuser = User::where('id',$data['user_id'])->first();
                $deleteuser->delete();
                
                if($deleteuser){
                    $success = true;
                }
        }
        
        $response = [
            'user' => $user,
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
    
    public function getCompanyTypes() {
        $data = companyTypes::where('status',1)->where('website_status',1)->get(['id as value', 'name as label', 'name_arabic as label_extra']);
        
        $response = [
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
    
    public function createEcommerceTicket(Request $request){
        $data = $request->all();
        $success = false;
        $id = false;
        $ticket = InternalTicket::create([
            'title' => isset($request->title) ? $request->title : null,
            'emergency' => isset($request->emergency) ? $request->emergency : null,
            'follow_up' => isset($request->followUp) ? $request->followUp : null,
            'status' => isset($request->status) ? $request->status : null,
            'subject' => isset($request->subject) ? $request->subject : null,
            'details' => isset($request->details) ? $request->details : null,
            'input_channel' => isset($request->input_channel) ? $request->input_channel : null,
            'type' => isset($request->type) ? $request->type : null,
            'department' => isset($request->department) ? $request->department : null,
            'section' => isset($request->section) ? $request->section : null,
            'branch' => isset($request->branch) ? $request->branch : null,
            'branch_type' => isset($request->branch_type) ? $request->branch_type : null,
            'assignee' => isset($request->assignee) ? $request->assignee : null,
            'assignee_type' => isset($request->assignee_type) ? $request->assignee_type : null,
            'purchased_from' => isset($request->purchased_from) ? $request->purchased_from : null,
            'urgency' => isset($request->urgency) ? $request->urgency : null,
            'impact' => isset($request->impact) ? $request->impact : null,
            'customer_id' => isset($request->customer_id) ? $request->customer_id : null,
            'order_id' => isset($request->order_id) ? $request->order_id : null,
            'user_id' => isset($request->user_id) ? $request->user_id : null,
            'order_type' => isset($request->order_type) ? $request->order_type : null,
            // 'image' => isset($request->image) ? $request->image : null,
            'service_no' => isset($request->service_no) ? $request->service_no : null,
            'value' => isset($request->value) ? $request->value : null,
            'sla' => isset($request->sla) ? $request->sla : null,
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
                // foreach($request->order_detail_id as $key => $value){
                    InternalTicketOrderDetails::create([
                        'ticket_id' => $ticket->id,
                        'order_detail_id' => $request->order_detail_id
                    ]);
                // }
            }
            // if($request->tags){
            //     foreach($request->tags as $key => $value){
            //         TicketTag::create([
            //             'ticket_id' => $ticket->id,
            //             'tag_id' => $value['tag_id']
            //         ]);
            //     }
            // }
            // if($request->image) {
            //     foreach($request->image as $ky => $val){
            //         InternalTicketMedia::create([
            //             'ticket_id' => $ticket->id,
            //             'file' => $val
            //         ]);
            //     }
            // }
            InternalTicketHistory::create([
                'comment' => 'Ticket Create from Website.',
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
}