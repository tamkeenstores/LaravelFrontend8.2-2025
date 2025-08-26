<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerSegmentation;
use App\Models\SegmentationCondition;
use App\Traits\CrudTrait;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\States;
use App\Models\Region;
use App\Models\Coupon;

class CustomerSegmentationController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'cusomter_segmentation';
    protected $relationKey = 'cusomter_segmentation_id';


    public function model() {
        $data = ['limit' => -1, 'model' => CustomerSegmentation::class, 'sort' => ['id','asc']];
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
        return ['segment_id' => 'conditions'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         'cities' => States::where('country_id','191')->get(['id as value', 'name as label']),
         'regions' => Region::get(['id as value', 'name as label']),
         'coupons' => Coupon::where('status','=',1)->get(['id as value', 'coupon_code as label']),
         ];
    }
    
    // public function index(Request $request){
    //     $data = CustomerSegmentation::orderBy('id', 'desc')->get();
        
    //     $response = [
    //         'data' => $data
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
    public function store(Request $request) {
        $success = false;
        $message = '';
        $customerSegmentation = CustomerSegmentation::create([
            'name' => isset($request->name) ? $request->name : null,
            'name_arabic' => isset($request->name_arabic) ? $request->name_arabic : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'status' => isset($request->status) ? $request->status : 0,
            'new_user' => isset($request->new_user) ? $request->new_user : 0,
            'email_template_type' => isset($request->email_template) ? $request->email_template : null,
            'wp_template_type' => isset($request->wp_template) ? $request->wp_template : null,
            'sms' => isset($request->sms) ? $request->sms : null,
            'sms_status' => isset($request->sms) ? 1 : 0,
            'sms_arabic' => isset($request->sms_arabic) ? $request->sms_arabic : null,
            'sms_arabic_status' => isset($request->sms_arabic) ? 1 : 0,
            'send_type' => isset($request->send_type) ? $request->send_type : null,
        ]);
        
        if (isset($request->condition_type)) {
            $segments = [
                'segment_id' => $customerSegmentation->id,
                'condition_type' => $request->condition_type,
                'coupon_id' => ($request->condition_type == 1 && count($request->coupon_id) >= 1) ? implode(',', $request->coupon_id) : null,
                'payment_method' => ($request->condition_type == 2 && count($request->payment_method) >= 1) ? implode(',', $request->payment_method) : null,
                'orders_count' => ($request->condition_type == 3 && $request->orders_count) ? $request->orders_count : null,
                'last_order_days' => ($request->condition_type == 4 && $request->last_order_days) ? $request->last_order_days : null,
                'first_order_days' => ($request->condition_type == 5 && $request->first_order_days) ? $request->first_order_days : null,
                'order_min_value' => ($request->condition_type == 6 && $request->order_min_value) ? $request->order_min_value : null,
                'order_max_value' => ($request->condition_type == 6 && $request->order_max_value) ? $request->order_max_value : null,
                'user_gender' => ($request->condition_type == 7 && count($request->user_gender) >= 1) ? implode(',', $request->user_gender) : null,
                'user_dob' => ($request->condition_type == 8 && $request->dob) ? $request->dob : null,
                'registration_date' => ($request->condition_type == 9 && $request->registration_date) ? $request->registration_date : null,
                'shipping_cities' => ($request->condition_type == 12 && count($request->shipping_cities) >= 1) ? implode(',', $request->shipping_cities) : null,
                'shipping_region' => ($request->condition_type == 13 && count($request->shipping_region) >= 1) ? implode(',', $request->shipping_region) : null,
                'cart_count' => ($request->condition_type == 14 && $request->cart_count) ? $request->cart_count : null,
                'wishlist_brands' => ($request->condition_type == 15 && count($request->wishlist_brands) >= 1) ? implode(',', $request->wishlist_brands) : null,
                'wishlist_products' => ($request->condition_type == 16 && count($request->wishlist_products) >= 1) ? implode(',', $request->wishlist_products) : null,
                'wishlist_cats' => ($request->condition_type == 17 && count($request->wishlist_cats) >= 1) ? implode(',', $request->wishlist_cats) : null
            ];
            
            SegmentationCondition::create($segments);
        }
        $success = true;
        $message = 'Customer Segmentation Has been created!';
        
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
}
