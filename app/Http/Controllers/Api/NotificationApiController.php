<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\Brand;
use App\Models\NotificationToken;
use App\Traits\CrudTrait;
use App\Helper\NotificationHelper;
use Carbon\Carbon;
use App\Models\CacheStores;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class NotificationApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'notifications';
    protected $relationKey = 'notification_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Notification::class, 'sort' => ['id','desc']];
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
        return [];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label'])];
    }
    
    
    public function store(Request $request) 
    {
        $product = '';
        $brand = '';
        $cat = '';
        if($request->type == 1) {
            $type = 'Products';
            $product = Product::where('id', $request->product_id)->first();
        }
        elseif($request->type == 2) {
            $type = 'Brands';
            $brand = Brand::where('id', $request->brand_id)->first();
        }
        elseif($request->type == 3) {
            $type = 'Product Categories';
            $cat = Productcategory::where('id', $request->category_id)->first();
        }
        else{
            $type = '';
        }
        $notification = [
            'title' => isset($request->title) ? $request->title : null,
            'title_arabic' => isset($request->title_arabic) ? $request->title_arabic : null,
            'message' => isset($request->message) ? $request->message : null,
            'message_arabic' => isset($request->message_arabic) ? $request->message_arabic : null,
            'image' => isset($request->image) ? $request->image : null,
            'date' => isset($request->date) ? $request->date : null,
            'link' => isset($request->link) ? $request->link : null,
            'type' => isset($request->type) ? $request->type : null,
            'product_id' => isset($request->product_id) ? $request->product_id : null,
            'brand_id' => isset($request->brand_id) ? $request->brand_id : null,
            'category_id' => isset($request->category_id) ? $request->category_id : null,
            'for_web' => isset($request->for_web) ? $request->for_web : 0,
            'for_app' => isset($request->for_app) ? $request->for_app : 0,
            'instant_notification' => isset($request->instant_notification) ? $request->instant_notification : 0
        ];
        
        $notificationData = Notification::create($notification);
        
        $checklink = $notificationData->link;
        if(strpos($notificationData->link, "?") !== false){
            $checklink = $notificationData->link."&amp";
            $checklink = $checklink."notifications=".$notificationData->id;
        } else{
            $checklink = $notificationData->link.'?notifications='.$notificationData->id;
        }
        
        $notificationData->link = $checklink;
        $notificationData->save();
        
        if($request->instant_notification == 1 && ($request->for_app == 1 || $request->for_web == 1)) {
            $totalblogs = NotificationToken::count();
            $totalxml = $totalblogs / 1000;
            for ($i=0; $i < $totalxml; $i++) {
                if($request->for_web == 1 && $request->for_app == 1) {
                    // for web
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $checklink, $type);
                    // }
                    
                    // for app
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', '!=', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $checklink, $type);
                    // }
                    
                }
                elseif($request->for_web == 1) {
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray();   
                    
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $checklink, $type);
                    // }
                }
                elseif($request->for_app == 1) {
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', '!=','Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $checklink, $type);
                    // }
                }
            }
        }
        
        return response()->json(['success' => true, 'message' => 'Notification Has been Created Successfully!']);
    }
    
    public function update(Request $request, $id)
    {
        $product = '';
        $brand = '';
        $cat = '';
        if($request->type == 1) {
            $type = 'Products';
            $product = Product::where('id', $request->product_id)->first();
        }
        elseif($request->type == 2) {
            $type = 'Brands';
            $brand = Brand::where('id', $request->brand_id)->first();
        }
        elseif($request->type == 3) {
            $type = 'Product Categories';
            $cat = Productcategory::where('id', $request->category_id)->first();
        }
        else{
            $type = '';
        }
        
        if($request->instant_notification == 1 && ($request->for_app == 1 || $request->for_web == 1)) {
            $totalblogs = NotificationToken::count();
            $totalxml = $totalblogs / 1000;
            for ($i=0; $i < $totalxml; $i++) {
                
                if($request->for_web == 1 && $request->for_app == 1) {
                    // for web
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type);
                    // }
                    
                    // for app
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', '!=', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type);
                    // }
                    
                }
                elseif($request->for_web == 1) {
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray();   
                    
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://tamkeenstores.com.sa/ar/' . $request->link, $type);
                    // }
                }
                elseif($request->for_app == 1) {
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', '!=','Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    
                    // if($request->type == 1) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($product->slug) ? $product->slug : null);
                    // }
                    // elseif($request->type == 2) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($brand->slug) ? $brand->slug : null);
                    // }
                    // elseif($request->type == 3) {
                    //     $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type, isset($cat->slug) ? $cat->slug : null);
                    // }
                    // else {
                        $data = NotificationHelper::global_notification($tokenData, $request->title_arabic, $request->message_arabic, $request->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $request->link, $type);
                    // }
                }
            }
        }
        
        
        $notification = Notification::whereId($id)->update([
            'title' => isset($request->title) ? $request->title : null,
            'title_arabic' => isset($request->title_arabic) ? $request->title_arabic : null,
            'message' => isset($request->message) ? $request->message : null,
            'message_arabic' => isset($request->message_arabic) ? $request->message_arabic : null,
            'image' => isset($request->image) ? $request->image : null,
            'date' => isset($request->date) ? $request->date : null,
            'link' => isset($request->link) ? $request->link : null,
            'type' => isset($request->type) ? $request->type : null,
            'product_id' => isset($request->product_id) ? $request->product_id : null,
            'brand_id' => isset($request->brand_id) ? $request->brand_id : null,
            'category_id' => isset($request->category_id) ? $request->category_id : null,
            'for_web' => isset($request->for_web) ? $request->for_web : 0,
            'for_app' => isset($request->for_app) ? $request->for_app : 0,
            'instant_notification' => isset($request->instant_notification) ? $request->instant_notification : 0
        ]);
    
        return response()->json(['success' => true, 'message' => 'Notification updated successfully']);
    }
    
    
    public function FrontendNotificationsList() {
        $seconds = 86400; // 24 hours cache
        $lang = $request->lang ?? 'ar';
        $cacheKey = "notification_list_{$lang}"; // Added version
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                $notifications = Notification::where('for_web', 1)
                // ->where('instant_notification', 1)
                // ->orWhereDate('date', '>=', Carbon::now())
                ->where(function($query) {
                    $query->where('instant_notification', 1);
                    $query->orWhere('date', '<=', Carbon::now());
                })
                ->orderBy('id', 'desc')
                ->get();
                // Cache the complete response
                // Cache::put($cacheKey, $response, $seconds);
                $response = [
                    'data' => $notifications
                ];
                Cache::put($cacheKey, $response, $seconds);
                
            } catch (\Exception $e) {
                Log::error("Notification list API Error: " . $e->getMessage());
                $response = [
                    'error' => 'Failed to load notification list data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
            }
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function FrontendLatestNotification() {
        $seconds = 86400; // 24 hours cache
        $lang = $request->lang ?? 'ar';
        $cacheKey = "notification_list_first_{$lang}"; // Added version
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                $notifications = Notification::where('for_web', 1)->orderBy('id', 'DESC')->first();
               
                $response = [
                    'data' => $notifications
                ];
            } catch (\Exception $e) {
                Log::error("Product Discount Type API Error: " . $e->getMessage());
                $response = [
                    'error' => 'Failed to load notification first data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
            }
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
