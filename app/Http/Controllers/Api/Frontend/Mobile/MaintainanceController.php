<?php

namespace App\Http\Controllers\Api\Frontend\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\MaintainenceDisProducts;
use App\Models\MaintainanceProducts;
use App\Models\Maintenance;
use DB;

class MaintainanceController extends Controller
{
    public function getUserDataMob($id) {
        $user = User::where('users.id', $id)->select('users.id', DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"))
        ->with([
            'OrdersData' => function ($q) {
                return $q->select('id', 'order_no', 'customer_id', 'status', 'created_at')->withCount('details')->limit(5)->orderBy('id','desc');   
            },
            'OrdersData.ordersummary' => function ($que) {
                return $que->where('type', 'subtotal')->select('id', 'order_id','price');   
            }, 'OrdersData.details:id,order_id,product_id,product_name,product_image', 'OrdersData.details.productData:id,name,name_arabic,brands',
            'OrdersData.details.productData.brand:id,name,name_arabic,brand_app_image_media','OrdersData.details.productData.brand.BrandMediaAppImage:id,image'])
        ->first();
        $dispros = MaintainenceDisProducts::get();
        $response = [
            'userdata' => $user,
            'dispros' => $dispros
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getProductDataByid(Request $request) {
        $data = $request->input('products');
        
        $products = Product::whereIn('id', $data)
        ->select('id', 'name', 'name_arabic', 'feature_image', 'brands')
        ->with('featuredImage:id,image','brand:id,brand_app_image_media','brand.BrandMediaAppImage:id,image')
        ->get();
        
        $response = [
            'data' => $products
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function StoreMaintainance(Request $request) {
        $data = json_decode($request->getContent(), true);
        $user = User::where('id', $data['user_id'])->first();
        $randomNumber = mt_rand(100000, 999999);
        $ticketId = 'MAN' . $randomNumber;
    
        $man = Maintenance::create([
            'first_name' => isset($user->first_name) ? $user->first_name : null,
            'last_name' => isset($user->last_name) ? $user->last_name : null,
            'phone_number' => isset($user->phone_number) ? $user->phone_number : null,
            'ticket_id' => $ticketId,
            'radio_value' => $data['radio_value'],
        ]);
                
        if (isset($data['products'])) {
            foreach ($data['products'] as $index => $productId) {
                
                $dataForProduct = isset($data['details'][$index]) ? $data['details'][$index] : null;
                if (is_array($dataForProduct)) {
                    $dataForProduct = implode(',', $dataForProduct); // Convert array to string
                }
                MaintainanceProducts::create([
                    'maintainance_id' => $man->id,
                    'product_id' => $productId,
                    'details' => $dataForProduct,
                ]);
            }
        }

        
        
        $response = [
            'success' => true,
            'message' => 'Maintainance Created Successfully!'
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
