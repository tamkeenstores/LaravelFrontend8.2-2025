<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusTimeLine;
use App\Models\OrderSummary;
use App\Models\RolePermission;
use App\Models\Productcategory;
use App\Models\ProductMedia;
use App\Models\Product;
use App\Models\ProductGallery;
use App\Models\Brand;
use App\Models\ProductFeatures;
use App\Models\ProductReview;
use App\Models\CategoryProduct;
use App\Models\ProductSpecifications;
use App\Models\shippingAddress;
use App\Models\ProductTag;
use App\Models\SubTags;
use App\Models\Tag;
use App\Models\EmailTemplate;
use App\Models\States;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;
use Session;
use Mail; 
use App\Mail\ProcessOrder;
use App\Traits\CrudTrait;

class UserApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'users';
    protected $relationKey = 'users_id';
    
    public function updateBlacklist($id, Request $request) {
        $success = false;
        $user = User::where('id', $id)->first();
        if($user) {
            $user->blacklist = $request['blacklist'];
            $user->update();
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
    
    public function BlackListIndex(Request $request) {
            // print_r($request->all());die;
            $name = $request['name'];
            $phone = $request['phone'];
            $dob = $request['dob'];
            $role = $request['role'];
            $lang = $request['lang'];
            $status = $request['status'];
            
            $search = $request['search'];
            $order = $request['sort'];
            $take = isset($request['page_size']) ? $request['page_size'] : 100;
            $pageNumber = isset($request['page']) ? $request['page'] : 1;
            
            $data = User::with('role:id,name','shippingAddressDataDefault.stateData')
            ->where('blacklist', '=', 1)
            // ->where('role_id', '!=', '1')
            ->when($name, function ($q) use ($name) {
                return $q->where("first_name","LIKE","%{$name}%")
                ->orWhere("last_name","LIKE","%{$name}%")
                ->orWhereRaw(
                    "concat(first_name, ' ', last_name) like '%" . $name . "%' "
                );
            })
            ->with('lastOrder', function($query) {
                return $query->select(['id', 'order_no', 'customer_id', 'shipping_id', 'created_at'])->latest()->first();
            })
            ->withCount('OrdersData')
            ->when($search, function ($q) use ($search) {
                return $q->where(function($query) use($search){
                    return $query
                    ->where("first_name","LIKE","%{$search}%")
                    ->orWhere("last_name","LIKE","%{$search}%")
                    ->orWhere("phone_number","LIKE","%{$search}%")
                    ->orWhere("email","LIKE","%{$search}%")
                    // ->orWhereRaw(
                    //     "concat(first_name, ' ', last_name) like '%" . $name . "%' "
                    // )
                        ->orWhere("date_of_birth","LIKE","%{$search}%")
                        ->orWhere("role_id","LIKE","%{$search}%")
                        ->orWhere("lang","LIKE","%{$search}%")
                        ->orWhere("status","LIKE","%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->when($phone, function ($q) use ($phone) {
                return $q->where("phone_number","LIKE","%{$phone}%");
            })
            ->when($order, function ($q) use ($order) {
                return $q->orderBy($order[0], $order[1]);
            })
            ->when($dob, function ($q) use ($dob) {
                return $q->where('date_of_birth', $dob);
            })
            ->when($role, function ($q) use ($role) {
                return $q->whereIn('role_id', $role);
            })
            ->when($lang, function ($q) use ($lang) {
                return $q->where('lang', $lang);
            })
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate($take, ['*'], 'page', $pageNumber);
            $roles = Role::get(['id as value', 'name as label']);
            $response = [
                'data' => $data,
                'roles' => $roles
            ];
            $responsejson=json_encode($response);
            $data=gzencode($responsejson,9);
            return response($data)->withHeaders([
                'Content-type' => 'application/json; charset=utf-8',
                'Content-Length'=> strlen($data),
                'Content-Encoding' => 'gzip'
            ]);
    }

    public function index(Request $request) {
            $name = $request['name'];
            $phone = $request['phone'];
            $dob = $request['dob'];
            $role = $request['role'];
            $lang = $request['lang'];
            $status = $request['status'];
            
            $search = $request['search'];
            $order = $request['sort'];
            $take = isset($request['page_size']) ? $request['page_size'] : 100;
            $pageNumber = isset($request['page']) ? $request['page'] : 1;
            
            $data = User::with('role:id,name','shippingAddressDataDefault.stateData')
            ->with('lastOrder', function($query) {
                return $query->select(['id', 'order_no', 'customer_id', 'shipping_id', 'created_at'])->latest()->first();
            })
            ->withCount('OrdersData')
            ->when($name, function ($q) use ($name) {
                return $q->where("first_name","LIKE","%{$name}%")
                ->orWhere("last_name","LIKE","%{$name}%")
                ->orWhereRaw(
                    "concat(first_name, ' ', last_name) like '%" . $name . "%' "
                );
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function($query) use($search){
                    return $query
                    ->where("first_name","LIKE","%{$search}%")
                    ->orWhere("last_name","LIKE","%{$search}%")
                    ->orWhere("phone_number","LIKE","%{$search}%")
                    ->orWhere("email","LIKE","%{$search}%")
                    // ->orWhereRaw(
                    //     "concat(first_name, ' ', last_name) like '%" . $name . "%' "
                    // )
                        ->orWhere("date_of_birth","LIKE","%{$search}%")
                        ->orWhere("role_id","LIKE","%{$search}%")
                        ->orWhere("lang","LIKE","%{$search}%")
                        ->orWhere("status","LIKE","%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->when($phone, function ($q) use ($phone) {
                return $q->where("phone_number","LIKE","%{$phone}%");
            })
            ->when($order, function ($q) use ($order) {
                return $q->orderBy($order[0], $order[1]);
            })
            ->when($dob, function ($q) use ($dob) {
                return $q->where('date_of_birth', $dob);
            })
            ->when($role, function ($q) use ($role) {
                return $q->whereIn('role_id', $role);
            })
            ->when($lang, function ($q) use ($lang) {
                return $q->where('lang', $lang);
            })
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate($take, ['*'], 'page', $pageNumber);
        $roles = Role::get(['id as value', 'name as label']);
        $response = [
            'data' => $data,
            'roles' => $roles
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function model() {
        $data = ['limit' => 1000, 'model' => User::class, 'sort' => ['id','desc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        if($resource_id == 0){
            return ['phone_number' => 'unique:users', 'email' => 'unique:users'];
        }
        else {
            return [];
        }
    }

    public function files(){
        return [];
    }

    public function relations(){
         return ['role_id' => 'role:id,name', 'shipping_data' => 'shippingAddressData','default_shipping' => 'shippingAddressDataDefault'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['roles' => Role::get(['id as value', 'name as label'])];
    }
    
    public function store(Request $request) 
    {
        $email = $request->email;
        $exists = User::where('email', $email)->count();
        // print_r($exists);die();
        if($exists >= 1){
            $success = false;
            // $product = true;
            return response()->json(['success' => $success, 'errors' => true, 'message' => 'Email is already used!']);
        }
        $phonenumber = $request->phone_number;
        $existsphone = User::where('phone_number', $phonenumber)->count();
        // print_r($exists);die();
        if($existsphone >= 1){
            $success = false;
            return response()->json(['success' => $success, 'errors' => true, 'message' => 'Phone Number is already used!']);
        }
        
        $hashedPassword = Hash::make($request->password);
        
        $user = User::create([
            'first_name' => isset($request->first_name) ? $request->first_name : null,
            'last_name' => isset($request->last_name) ? $request->last_name : null,
            'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
            'email' => isset($request->email) ? $request->email : null,
            'date_of_birth' => isset($request->date_of_birth) ? $request->date_of_birth : null,
            'gender' => isset($request->gender) ? $request->gender : null,
            'password' => isset($hashedPassword) ? $hashedPassword : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'status' => isset($request->status) ? $request->status : 0,
            'role_id' => isset($request->role_id) ? $request->role_id : null,
        ]);
        
        return response()->json(['success' => true, 'message' => 'User Has been created!']);
    }
    
    public function update(Request $request, $id) {
        $hashedPassword = Hash::make($request->password);
        
        $user = User::whereId($id)->update([
            'first_name' => isset($request->first_name) ? $request->first_name : null,
            'last_name' => isset($request->last_name) ? $request->last_name : null,
            'phone_number' => isset($request->phone_number) ? $request->phone_number : null,
            'email' => isset($request->email) ? $request->email : null,
            'date_of_birth' => isset($request->date_of_birth) ? $request->date_of_birth : null,
            'gender' => isset($request->gender) ? $request->gender : null,
            'password' => isset($hashedPassword) ? $hashedPassword : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'status' => isset($request->status) ? $request->status : 0,
            'role_id' => isset($request->role_id) ? $request->role_id : null,
        ]);
        
        return response()->json(['success' => true, 'message' => 'User Has been updated!']);
    }
    
    public function userviewdata(Request $request) {
        $id = $request->id;
        $order = Order::where('customer_id', $id)->get();
        $ordercount = $order->count();
        $orderto = Order::where('customer_id', $id)->pluck('id')->toArray();
        if($orderto > 0) {
            $ordertotal = OrderSummary::whereIn('order_id', $orderto)->whereBetween('created_at', 
                            [Carbon::now()->subMonth(10), Carbon::now()]
                        )->where('type', 'total')->sum('price');
        }
        else {
            $ordertotal = null;
        }
        
        $pendingcount = Order::where('customer_id', $id)->where('status', 8)->count();
        
        $refundcount = Order::where('customer_id', $id)->where('status', 6)->count();
        
        $userdta = User::Where('id', $id)->select('id')->with(['loyaltypointsdata' => function ($query) {
            $query->where('calculate_type', '1');
        }])->withSum('loyaltypointsdata','points')->first();
        
        $usercity = shippingAddress::with('stateData:id,name')->where('customer_id', $id)->select('id', 'customer_id', 'state_id')->where('make_default', 1)->first();
        // if($usercity === null) {
        //     $statename = States::where('id', $usercity)->pluck('name');
        // }
        // else {
        //     $statename = null;
        // }
        $Orderlisting = Order::where('order.customer_id', $id)->with('UserDetail:id,first_name,last_name,phone_number')
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
        ->orderBy('id', 'desc')
        ->get();
        
        $emailtemplate = EmailTemplate::where('status', 1)->select('id as key', 'name as label', 'page_content')->get();
        
        return response()->json(['ordercount' => $ordercount, 'ordertotal' => $ordertotal, 'pendingcount' => $pendingcount, 'refundcount' => $refundcount,'usercityname' => $usercity
        , 'orderlistingdata' => $Orderlisting, 'pointsdata' => $userdta, 'emailtemplate' => $emailtemplate]);
    }
    
    public function getAllAddresses(Request $request) {
        // print_r($request->id);die();
        
        
        // print_r($request->all());die();
        
        
        $addressdata = shippingAddress::with('stateData:id,name')->withCount('orders')->where('customer_id', $request->id)->get();
        
        
        $response = [
            'addressdata' => $addressdata,
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
        $addressdata = shippingAddress::where('id', $id)->first();
        $addressdata->delete();
        
        
        $response = [
            'success' => true,
            'message' => 'Address Deleted Successfully!'
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function UserOrderSendEmail(Request $request) {
        $id = $request->id;
        $orderid = $request->orderid;
        $emailtemplate = $request->emailcontent;
        $templateid = $request->templateid;
        $success = false;
        if($id) {
            $data = User::where('id', $id)->first();
            $order = order::where('id', $orderid)->first();
            $emailtemplate = EmailTemplate::where('id', $templateid)->first();
           
            Mail::send('email.' .$emailtemplate->file_path, ['emailtemplate' => $emailtemplate, 'order' => $order], function ($message) use ($data) {
                $message->to($data->email)
                ->subject('Processing Order');
            });
            
            
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Email Sended Successfully!!']);
        
    }
    
    public function UserAddressData(Request $request) {
        
        
        // print_r($request->all());die();
        
        $addressdata = shippingAddress::where('customer_id', $request->id)->pluck('id')->toArray();
        
        $firstaddresscount = Order::where('shipping_id', $addressdata[0])->count();
        if($addressdata == 1) {
            $secondaddresscount = Order::where('shipping_id', $addressdata[1])->count();
        }
        else {
            $secondaddresscount = null;
        }
        if($addressdata == 2) {
            $thirdaddresscount = Order::where('shipping_id', $addressdata[2])->count();
        }
        else {
            $thirdaddresscount = null;
        }
        // if($addressdata > 0) {
        //     $orders = Order::whereIn('shipping_id', $addressdata)->get('id');
        // }
        // else {
        //     $orders = null;
        // }
        
        
        // print_r($secondaddresscount);die();
        
        return response()->json(['firstaddresscount' => $firstaddresscount, 'secondaddresscount' => $secondaddresscount,'thirdaddresscount' => $thirdaddresscount]);
    }


    // Getting live users

    public function file_get_contents_curl( $url ) { 

        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_URL, $url);  
        $data = curl_exec($ch); 
        curl_close($ch); 

        return $data; 
    } 

    public function getLiveUsers() {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://oldpartners.tamkeenstores.com.sa/api/get-all-users/1'); 

        $response = json_decode($response->getBody(), true); // The API response data
        $success = false;
        $data = collect($response);
        // print_r(count($data['users']['data']));die();
        foreach ($data['users']['data'] as $key => $value) {
            print_r($value);die;
            // $hashedPassword = Hash::make($value['password']);
            // print_r($hashedPassword);die;
            $userfind = User::where('id', $value['id'])->first();
            if(!$userfind) {
                $user = new User([
                    'id' => $value['id'],
                    'first_name' => $value['firstname'],
                    'last_name' => $value['lastname'],
                    'phone_number' => $value['phone'],
                    'email' => $value['email'],
                    'email_verified_at' => $value['email_verified_at'],
                    'password' => $value['password'] != null ? $value['password'] : 'password2024',
                    'date_of_birth' => null,
                    'notes' => null,
                    'user_device' => $value['user_device'],
                    'loyaltypoints' => $value['loyaltypoints'],
                    'lang' => $value['lang'],
                    'role_id' => $value['role_id'],
                    'status' => 1,
                    'blacklist' => 0,
                    'gender' => null,
                    'profile_img' => null,
                    'remember_token' => null,
                    'deleted_at' => isset($value['deleted_at']) ? $value['deleted_at'] : null,
                    'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    'updated_at' => isset($value['updated_at']) ? $value['updated_at'] : date('Y-m-d H:i:s'),
                ]);

                $user->save();
            }
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
    
    public function getLiveOrders() {
        // $client = new \GuzzleHttp\Client();
        // $response = $client->request('GET', 'https://oldpartners.tamkeenstores.com.sa/api/get-all-orders'); 

        // $response = json_decode($response->getBody(), true); // The API response data
        // $success = false;
        // $orders = collect($response);
        // //print_r($orders['data']['last_page']);die;
        // for ($i=0; $i < $orders['data']['last_page']; $i++) { 
            $client = new \GuzzleHttp\Client();
            // $response = $client->request('GET', 'https://partners.tamkeenstores.com.sa/api/get-all-orders/'.$i+1); 
            $response = $client->request('GET', 'https://oldpartners.tamkeenstores.com.sa/api/get-all-orders/47'); 
    
            $response = json_decode($response->getBody(), true); // The API response data
            $success = false;
            $data = collect($response);
            foreach ($data['data']['data'] as $key => $value) {
                // print_r($value);die();
                $orderfind = Order::where('id', $value['id'])->withTrashed()->first();
                if(!$orderfind) {
                    print_r($value['order_no']);die;
                    // $order = new Order([
                    //     'id' => $value['id'],
                    //     'order_no' => $value['order_no'],
                    //     'customer_id' => $value['customer_id'],
                    //     'shipping_id' => $value['shipping_id'],
                    //     'status' => $value['status'],
                    //     'paymentmethod' => $value['paymentmethod'],
                    //     'paymentid' => $value['paymentid'],
                    //     'shippingMethod' => $value['shippingMethod'],
                    //     'discountallowed' => $value['discountallowed'],
                    //     'giftvoucherallowed' => $value['giftvoucherallowed'],
                    //     'loyaltyFreeShipping' => $value['loyaltyFreeShipping'],
                    //     'note' => $value['note'],
                    //     'lang' => $value['lang'],
                    //     'erp_status' => $value['erp_status'],
                    //     // 'madac_id' => $value['note'],  Yeh field ka data nhi ha api ma
                    //     'userDevice' => $value['userDevice'],
                    //     'token' => $value['token'],
                    //     'deleted_at' => isset($value['deleted_at']) ? $value['deleted_at'] : null,
                    //     'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    //     'updated_at' => isset($value['updated_at']) ? $value['updated_at'] : date('Y-m-d H:i:s'),
                    // ]);
                    
                    // $discount_rule_data = ($value['discount_rule_data']) ? json_decode($value['discount_rule_data'], true) : [];
                    
                    // // $ordersummarydata = [
                    // //           'order_id' => $value['id'],
                    // // ];
                    
                    
                    // if($value['subtotal'] != null && $value['subtotal'] > 0){
                    //     $subtotal = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'subtotal',
                    //         'type' => 'subtotal',
                    //         'calculation_type' => 1,
                    //         'price' => isset($value['subtotal']) ? $value['subtotal'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'subtotal';
                    //     // $ordersummarydata['calculation_type'] = 1;
                    //     // $ordersummarydata['price'] = isset($value['subtotal']) ? $value['subtotal'] : null;
                    // }
                    
                    // if($value['include_tax'] != null && $value['include_tax'] > 0){
                    //     $includetax = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'include tax',
                    //         'type' => 'include_tax',
                    //         'calculation_type' => 1,
                    //         'price' => isset($value['include_tax']) ? $value['include_tax'] : null,
                    //     ]);
                        
                        
                    //     // $ordersummarydata['name'] = 'include tax';
                    //     // $ordersummarydata['calculation_type'] = 1;
                    //     // $ordersummarydata['price'] = isset($value['include_tax']) ? $value['include_tax'] : null;
                    // }
                    
                    // if($value['shipping'] != null && $value['shipping'] > 0){
                    //     $shipping = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'shipping',
                    //         'type' => 'shipping',
                    //         'calculation_type' => 1,
                    //         'price' => isset($value['shipping']) ? $value['shipping'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'shipping';
                    //     // $ordersummarydata['calculation_type'] = 1;
                    //     // $ordersummarydata['price'] = isset($value['shipping']) ? $value['shipping'] : null;
                    // }
                    
                    // if($value['discount'] != null && $value['discount'] > '0'){
                    //     $discount = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'discount',
                    //         'type' => 'discount',
                    //         'calculation_type' => 0,
                    //         'price' => isset($value['discount']) ? $value['discount'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'discount';
                    //     // $ordersummarydata['calculation_type'] = 0;
                    //     // $ordersummarydata['price'] = isset($value['discount']) ? $value['discount'] : null;
                    // }
                    
                    // if($value['total'] != null && $value['total'] > 0){
                    //     $total = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'total',
                    //         'type' => 'total',
                    //         'calculation_type' => 1,
                    //         'price' => isset($value['total']) ? $value['total'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'total';
                    //     // $ordersummarydata['calculation_type'] = 1;
                    //     // // $ordersummarydata['type'] = isset($value['totalnewtype']) ? $value['totalnewtype'] : null;
                    //     // $ordersummarydata['price'] = isset($value['total']) ? $value['total'] : null;
                    // }
                    
                    
                    // if($value['vat_discount'] > 0){
                    //     $vatdiscount = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'vat discount',
                    //         'type' => 'vat_discount',
                    //         'calculation_type' => 0,
                    //         'amount_id' => isset($value['vat_discount_amount']) ? $value['vat_discount_amount'] : null,
                    //         'price' => isset($value['vat_discount']) ? $value['vat_discount'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'vat discount';
                    //     // $ordersummarydata['calculation_type'] = 0;
                    //     // $ordersummarydata['price'] = isset($value['vat_discount']) ? $value['vat_discount'] : null;
                    // }
                    
                    // if($value['loyaltyDiscount'] != null && $value['loyaltyDiscount'] > 0){
                    //     $loyaltydiscount = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'loyalty discount',
                    //         'type' => 'loyalty_discount',
                    //         'calculation_type' => 0,
                    //         'price' => isset($value['loyaltyDiscount']) ? $value['loyaltyDiscount'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'loyalty discount';
                    //     // $ordersummarydata['calculation_type'] = 0;
                    //     // $ordersummarydata['price'] = isset($value['loyaltyDiscount']) ? $value['loyaltyDiscount'] : null;
                    // }
                    
                    // if($value['express_option_id'] != null && $value['express_option_label'] != null && $value['express_option_price'] != null){
                    //     $expressoption = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => isset($value['express_option_label']) ? $value['express_option_label'] : null,
                    //         'type' => 'express_option',
                    //         'calculation_type' => 0,
                    //         'price' => isset($value['express_option_price']) ? $value['express_option_price'] : null,
                    //         'amount_id' => isset($value['express_option_id']) ? $value['express_option_id'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = isset($value['express_option_label']) ? $value['express_option_label'] : null;
                    //     // $ordersummarydata['calculation_type'] = 0;
                    //     // $ordersummarydata['price'] = isset($value['express_option_price']) ? $value['express_option_price'] : null;
                    //     // $ordersummarydata['amount_id'] = isset($value['express_option_id']) ? $value['express_option_id'] : null;
                    // }
                    
                    // if($value['door_step_amount'] != null && $value['door_step_amount'] > 0){
                    //     $doorstepamount = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'door step amount',
                    //         'type' => 'door_step_amount',
                    //         'calculation_type' => 1,
                    //         'price' => isset($value['door_step_amount']) ? $value['door_step_amount'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'door step amount';
                    //     // $ordersummarydata['calculation_type'] = 1;
                    //     // $ordersummarydata['price'] = isset($value['door_step_amount']) ? $value['door_step_amount'] : null;
                    // }
                    
                    // if($value['cod_additional_charges'] != null && $value['cod_additional_charges'] > 0){
                    //     $codadditional = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => 'cod additional charges',
                    //         'type' => 'cod_additional_charges',
                    //         'calculation_type' => 1,
                    //         'price' => isset($value['cod_additional_charges']) ? $value['cod_additional_charges'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = 'cod additional charges';
                    //     // $ordersummarydata['calculation_type'] = 1;
                    //     // $ordersummarydata['price'] = isset($value['cod_additional_charges']) ? $value['cod_additional_charges'] : null;
                    // }
                    
                    // if($value['giftcardamount'] != null && $value['giftcardtitle'] != null){
                    //     $giftcardamount = OrderSummary::create([
                    //         'order_id' => $value['id'],
                    //         'name' => isset($value['giftcardtitle']) ? $value['giftcardtitle'] : null,
                    //         'type' => 'gift_card',
                    //         'calculation_type' => 0,
                    //         'price' => isset($value['giftcardamount']) ? $value['giftcardamount'] : null,
                    //     ]);
                        
                    //     // $ordersummarydata['name'] = isset($value['giftcardtitle']) ? $value['giftcardtitle'] : null;
                    //     // $ordersummarydata['calculation_type'] = 0;
                    //     // $ordersummarydata['price'] = isset($value['giftcardamount']) ? $value['giftcardamount'] : null;
                    // }
                    // if($discount_rule_data != null) {
                    //     foreach ($discount_rule_data as $key => $rule) {
                    //         $ruledata = OrderSummary::create([
                    //             'order_id' => $value['id'],
                    //             'name' => isset($rule['name']) ? $rule['name'] : null,
                    //             'type' => 'discount_rule',
                    //             'amount_id' => isset($rule['id']) ? $rule['id'] : null,
                    //             'calculation_type' => 0,
                    //             'price' => isset($rule['price']) ? $rule['price'] : null,
                    //         ]);
                            
                    //         // $ordersummarydata['name'] = isset($rule['name']) ? $rule['name'] : null;
                    //         // $ordersummarydata['calculation_type'] = 0;
                    //         // $ordersummarydata['price'] = isset($rule['price']) ? $rule['price'] : null;
                    //     }
                    // }
                    
                    // // OrderSummary::create($ordersummarydata);
    
                    // // store Order Details
                    // $orderdetail = $value['orderdetail_data'];
                    // if(isset($orderdetail)) {
                    //     foreach ($orderdetail as $key => $detail) {
                            
                    //         $getFile = 'https://oldpartners.tamkeenstores.com.sa/assets/images/' . $detail['product_image'];
                    //         // check file
                    //         $ch = curl_init($getFile);
                    //         curl_setopt($ch, CURLOPT_NOBODY, true);
                    //         curl_exec($ch);
                    //         $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    //         curl_close($ch);
            
                    //         // store file
                    //         if($code == 200 && isset($detail['product_image'])) {
                    //             $data = $this->file_get_contents_curl( $getFile );
                    //             $cats = ProductMedia::where('image', $detail['product_image'])->first();
                                
                    //             if($cats) {
                    //                 $images = ProductMedia::whereId($cats->id)->update([
                    //                     // 'id' => isset($cats) ? $cats->id : null
                    //                     'image' => $detail['product_image'],
                    //                     'desktop' => 1,
                    //                 ]);
                    //             }
                    //             else {
                    //                 file_put_contents(public_path('/assets/new-media/'). $detail['product_image'], $data ); 
                    //                 $image = new ProductMedia([
                    //                     'image' => $detail['product_image'],
                    //                     'desktop' => 1,
                    //                 ]);
                    //                 $image->save();
                    //             }
                    //         }
                            
                            
                    //             if(isset($detail['id'])) {
                    //                 $newdetail = OrderDetail::create([
                    //                     'order_id' => $value['id'],
                    //                     'product_id' => $detail['product_id'],
                    //                     'product_name' => $detail['product_name'],
                    //                     'product_image' => isset($cats) ? $cats->id : isset($image),                                    
                    //                     'unit_price' => $detail['unit_price'],
                    //                     'quantity' => $detail['quantity'],
                    //                     'total' => $detail['total'],
                    //                     'enable_vat' => $detail['enable_vat'],
                    //                     'expressproduct' => $detail['expressproduct'],
                    //                     'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    //                     'updated_at' => isset($value['updated_at']) ? $value['updated_at'] : date('Y-m-d H:i:s'),
                    //                 ]);
                    //             }
                    //       }   
                    // }
                    
                    // // Order Status Timeline
                    // if($value['status'] == 0) {
                    //     $statustimeline = OrderStatusTimeLine::create([
                    //     'order_id' => $value['id'],
                    //     'status' => $value['status'],
                    //     'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    //     ]);
                    // }
                    // if($value['status'] == 8) {
                    //     $statustimeline = OrderStatusTimeLine::create([
                    //     'order_id' => $value['id'],
                    //     'status' => $value['status'],
                    //     'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    //     ]);
                    // }
                    // if($value['status'] != 0 && $value['status'] != 8) {
                    //     $pendingtimeline = OrderStatusTimeLine::create([
                    //     'order_id' => $value['id'],
                    //     'status' => 0,
                    //     'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    //     ]);
                        
                    //     $statustimeline = OrderStatusTimeLine::create([
                    //     'order_id' => $value['id'],
                    //     'status' => $value['status'],
                    //     'created_at' => isset($value['updated_at']) ? $value['updated_at'] : date('Y-m-d H:i:s'),
                    //     ]);
                    // }
                    
    
    
                    // $order->save();
                    $success = true;
                }
            }
        // }
        
        return response()->json(['success' => $success]);
    }
    
    public function getLiveProductReviews() {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://oldpartners.tamkeenstores.com.sa/api/get-all-product-reviews/'); 

        $response = json_decode($response->getBody(), true); // The API response data
        $success = false;
        $data = collect($response);
        foreach ($data['data'] as $key => $value) {
        
            $reviewfind = ProductReview::where('id', $value['id'])->first();
            if(!$reviewfind) {

                $productreview = new ProductReview([
                    'id' => $value['id'],
                    'orderdetail_id' => $value['orderdetail_id'],
                    'product_sku' => $value['product_sku'],
                    'rating' => $value['rating'],
                    'title' => $value['title'],
                    'review' => $value['review'],
                    'user_id' => $value['user_id'],
                    'anonymous' => $value['anonymous'],
                    'status' => $value['status'],
                    'created_at' => isset($value['created_at']) ? $value['created_at'] : date('Y-m-d H:i:s'),
                    'updated_at' => isset($value['updated_at']) ? $value['updated_at'] : date('Y-m-d H:i:s'),
                ]);

                $productreview->save();
            }
            $success = true;
        }
        
        return response()->json(['success' => $success]);
    }

    // Update waybill of orders
    public function OrderWaybillUpdate() {

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://dashboard.tamkeenstores.com.sa/api/order-waybill-update'); 

        $response = json_decode($response->getBody(), true); // The API response data
        // die('stopped');
        $success = false;
        $data = collect($response);
        // print_r($data);die();
        foreach ($data['orders'] as $key => $value) {
            $orderCheck = Order::where('order_no', $value['order_no'])->first();
            if($orderCheck) {
                $orderCheck->madac_id = $value['madac_id_tt'] != null ? $value['madac_id_tt'] : $value['madac_id'];
                $orderCheck->waybill_type = $value['type'] != null ? $value['type'] : null;
                $orderCheck->naqeel_waybill = $value['waybill'] != null ? $value['waybill'] : null;
                $orderCheck->samsa_waybill = $value['samsa'] != null ? $value['samsa'] : null;
                $orderCheck->aramex_waybill = $value['aramex'] != null ? $value['aramex'] : null;
                $orderCheck->logestechs_waybill = $value['logestechs_barcode'] != null ? $value['logestechs_barcode'] : null;
                $orderCheck->starlink_waybill = $value['starlink'] != null ? $value['starlink'] : null;
                $orderCheck->shipa_waybill = $value['shipa_ref'] != null ? $value['shipa_ref'] : null;
                $orderCheck->flow_waybill = $value['flow_ref_no'] != null ? $value['flow_ref_no'] : null;

                $orderStatus = $value['status'];
                $newstatus = '';
                if($orderStatus == '1' || $orderStatus == 'confirm') {
                    $orderCheck->status = 1;
                    $newstatus = 1;
                }
                elseif($orderStatus == '2') {
                    $orderCheck->status = 3;
                    $newstatus = 3;
                }
                elseif($orderStatus == '3') {
                    $orderCheck->status = 4;
                    $newstatus = 4;
                }
                elseif($orderStatus == '4' || $orderStatus == 'return') {
                    $orderCheck->status = 6;
                    $newstatus = 6;
                }
                elseif($orderStatus == '5') {
                    $orderCheck->status = 5;
                    $newstatus = 5;
                }
                elseif($orderStatus == 'processing') {
                    $orderCheck->status = 2;
                    $newstatus = 2;
                }
                $timeline = OrderStatusTimeLine::where('order_id', $orderCheck->id)->latest()->first();
                // print_r($value['updated_at']);die;

                // if($orderCheck->status == 0 || $newstatus == 4)
                // {
                    if($timeline->status != 1 && $newstatus == 1) {
                        OrderStatusTimeLine::create([
                            'order_id' => $orderCheck->id,
                            'status' => 1,
                            'created_at' => $value['updated_at'],
                        ]);
                    } 
                    if($timeline->status != 2 && $newstatus >= 2 && $newstatus < 5) {
                        OrderStatusTimeLine::create([
                            'order_id' => $orderCheck->id,
                            'status' => 2,
                            'created_at' => $value['updated_at'],
                        ]);
                    } 
                    if($timeline->status != 3 && $newstatus >= 3  && $newstatus < 5) {
                        OrderStatusTimeLine::create([
                            'order_id' => $orderCheck->id,
                            'status' => 3,
                            'created_at' => $value['updated_at'],
                        ]);   
                    }
                    if($timeline->status != 4 && $newstatus == 4 && $newstatus < 5) {
                        OrderStatusTimeLine::create([
                            'order_id' => $orderCheck->id,
                            'status' => 4,
                            'created_at' => $value['updated_at'],
                        ]);   
                    }
                    if($timeline->status != 5 && $newstatus == 5) {
                        OrderStatusTimeLine::create([
                            'order_id' => $orderCheck->id,
                            'status' => 5,
                            'created_at' => $value['updated_at'],
                        ]); 
                    }
                    if($timeline->status != 6 && $newstatus == 6) {
                        OrderStatusTimeLine::create([
                            'order_id' => $orderCheck->id,
                            'status' => 6,
                            'created_at' => $value['updated_at'],
                        ]); 
                    }
                // }
                // else {
                //     $check = OrderStatusTimeLine::where('order_id', $orderCheck->id)->where('status', $newstatus)->count();
                //     if($check >= 1){
                //         $timeline = false;
                //     }
                //     else {
                //         if($newstatus != '') {
                //             OrderStatusTimeLine::create([
                //                 'order_id' => $orderCheck->id,
                //                 'status' => $newstatus != '' ? $newstatus : null,
                //                 'created_at' => Carbon::now(),
                //             ]);
                //         }
                //     }
                // }
                $orderCheck->update();
                $success = true;
            }
        }
        return response()->json(['success' => $success]);
    }
    
    public function getPermission($userid) {
        $permissions = '';
        if($userid) {
            $user = User::where('id', $userid)->first(['role_id']);
            $permissions = RolePermission::with('module')->where('role_id', $user->role_id)->get();
        }
        return response()->json(['data' => $permissions]);
    }
    
    public function customLogin(Request $request)
    {
        $success = false;
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $credentials = $request->only('email','password');
        if (Auth::attempt($credentials)) {
            $success = true;
            $check = true;
            $userid = User::
            where('email', $request->email)
            ->select('id','email', 'role_id', 'first_name', 'last_name')
            ->first();
            
            return response()->json(['success' => $success,'userid' => $userid, 'check' => $check,'message' => 'You Are Successfully Logged In!']);
        }
        
        
        
        return response()->json(['success' => $success,  'message' => 'Login details are not valid']);
    }
    
    public function ForgetPassword(Request $request)
    {
      $success = true;
      $request->validate([
          'email' => 'required|email|exists:users',
      ]);

      $token = Str::random(64);
      $email = $request->email;

      DB::table('password_reset_tokens')->insert([
          'email' => $request->email, 
          'token' => $token, 
          'created_at' => Carbon::now()
        ]);

      Mail::send('email.forgetPassword', ['token' => $token], function($message) use($request){
          $message->to($request->email);
          $message->subject('Reset Password');
      });
      
        return response()->json(['success' => $success, 'email' => $email,'token' => $token, 'message' => 'We have e-mailed password reset link!']);
    //   return back()->with('message', 'We have e-mailed your password reset link!');
    }
    
    public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_reset_tokens')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
  
          if(!$updatePassword){
              return response()->json(['success' => false,'message' => 'Invalid token!']);
            //   return back()->withInput()->with('error', 'Invalid token!');
          }
          if($request->password != null){
            $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
          }
          DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();
            
        return response()->json(['success' => true,'message' => 'Your password has been changed!']);
        //   return redirect('/admin/login')->with('message', 'Your password has been changed!');
      }
      

      
    public function RolesDataPermi(Request $request)
    {
        $success = false;
        $modules = [];
        $userId = $request->user_id;
        if($userId) {
            $user = User::where('id', $userId)->get('role_id');
            
            
            $modules = RolePermission::where('role_id', $user)->get();
            
            $success = true;
            return response()->json(['success' => $success,'data' => $modules]);
        }
        return response()->json(['success' => $success,'data' => $modules]);
        
    }  
    
    
    public function updateAmazonStock(Request $request)
    {
        $sku = $request->query('sku');
        $qty = $request->query('qty');
        // Validate qty is numeric
        if (!is_numeric($qty)) {
            $response = [
                'status' => 'error',
                'message' => 'Quantity must be a number'
            ];
            $responseJson = json_encode($response);
            $data = gzencode($responseJson, 9);

            return response($data, 400)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Content-Length' => strlen($data),
                    'Content-Encoding' => 'gzip'
                ]);
        }

        // Convert qty to integer
        $qty = (int) $qty;

        // Update directly in one query
        $updated = Product::where('sku', $sku)
            ->update([
                'amazon_quantity' => $qty,
                'updated_at' => now()
            ]);

        // If no product updated
        if (!$updated) {
            $response = [
                'status' => 'error',
                'message' => 'Product not found'
            ];
            $responseJson = json_encode($response);
            $data = gzencode($responseJson, 9);

            return response($data, 404)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Content-Length' => strlen($data),
                    'Content-Encoding' => 'gzip'
                ]);
        }

        // Success
        $response = [
            'status' => 'success',
            'message' => 'Amazon quantity updated successfully',
            'data' => [
                'sku' => $sku,
                'amazon_quantity' => $qty
            ]
        ];

        $responseJson = json_encode($response);
        $data = gzencode($responseJson, 9);

        return response($data, 200)
        ->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

}