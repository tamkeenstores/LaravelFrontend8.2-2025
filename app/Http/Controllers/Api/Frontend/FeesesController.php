<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fees;
use App\Models\ExpressDelivery;
use App\Models\DoorStepDelivery;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\LiveStock;
use App\Models\Warehouse;
use App\Models\Product;
use DB;
use Mail;

class FeesesController extends Controller
{
    public function getFees(Request $request) {
        $payment = $request->paymentmethod;
        $amount = $request->amount;
        $data = Fees::where('status', 1)
        ->where(function ($query) use ($payment) {
            return $query->whereNull('payment_method')->orWhereRaw("FIND_IN_SET('$payment', payment_method)");
        })
        ->where(function ($query) use ($amount) {
            return $query->whereNull('min_amount')->orWhere('min_amount', '<=', $amount);
        })
        ->where(function ($query) use ($amount) {
            return $query->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
        })
        // ->when($amount, function ($query) use ($amount) {
        //     return $query->where('min_amount', '<=', $amount)
        //         ->where('max_amount', '>=', $amount)->select('id', 'name', 'name_arabic', 'type', 'payment_method', 'amount', 'min_amount'
        //     ,'max_amount', 'max_cap_amount', 'status');
        // })
        // ->select('id', 'name', 'name_arabic','amount', 'status')
        ->get();
        
        
        $fees = [];
        // if($amount) {
            if(sizeof($data)){
                foreach($data as $key => $value){
                    // $feeses = [];
                    if($value->type == 1){
        	              $value['amount'] = number_format($value->amount,2);
                    }
    	            if($value->type == 2){
        	            $amount = number_format($amount / $value->amount);
        	            if($value->max_cap_amount && $value->max_cap_amount < $amount){
        	              $value['amount'] = number_format($value->max_cap_amount,2);
        	            }
        	            else {
        	                $value['amount'] = number_format($amount,2);
        	            }
                    }
                    
                    $fees[] = [
                        'id' => $value->id,
                        'title' => $value->name,
                        'title_arabic' => $value->name_arabic,
                        'amount' => $value->amount,
                    ];
                }
            }
        // }
        
        
    // 	if($amount) {              
    //         $response = [
    //             'data' => $fees,
    //         ];
    // 	}
    // 	else {
    	    $response = [
                'data' => $fees,
            ];
    // 	}
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getExpressRegionalNew(Request $request) {
        $id = $request->productids;
        $qty = $request->productqty;
        $ids = [];
        $quantities = [];
        $newdata = false;
        $city = $request->city;
        foreach ($id as $key => $val) {
            $query = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
            ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
            ->leftJoin('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
            ->leftJoin('states as s', 'wc.city_id', '=', 's.id')
            ->select(
                'p.id as product_id',
                DB::raw('MAX(w.id) as id'),
                DB::raw('MAX(w.express_name) as title'),
                DB::raw('MAX(w.express_name_arabic) as title_arabic'),
                DB::raw('MAX(w.express_days) as num_of_days'),
                DB::raw('MAX(w.express_price) as price'),
                DB::raw('SUM(CASE WHEN w.ln_code IN ("OLN1", "KUW101") THEN livestock.qty ELSE 0 END) as special_qty'),
                DB::raw('SUM(CASE WHEN w.ln_code NOT IN ("OLN1", "KUW101") THEN livestock.qty ELSE 0 END) as regular_qty')
            )
            ->where('p.id', $val)
            ->where('w.status', 1)
            ->where('w.show_in_express', 1)
            ->groupBy('p.id');
            if ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('s.name', $city)
                        ->orWhere('s.name_arabic', $city);
                });
            }

            $data = $query->first();
            
            if (!$data) {
                // If no data found, treat as failure
                $ids = [];
                $quantities = [];
                $newdata = false;
                break;
            }
            
            // $totalQty = $data->regular_qty;
            $totalQty = $data->regular_qty ?? 0;
          	$specialQty = $data->special_qty ?? 0;
            
            
            // For Jeddah, only count special warehouses (OLN1, KUW101)
            if ($city === 'Jeddah' || $city === 'جدة') {
                $totalQty = $specialQty;
            } else {
                // Subtract 3 from regular quantity and add special quantity
                $totalQty = max(0, $totalQty - 3) + $specialQty;
            }

            // Limit quantity to a maximum of 10
            $totalQty = min($totalQty, 10);
            $data->qty = $totalQty;
            if($data && $data->qty >= $qty[$key]) {

               $ids[] = $val;
               $quantities[] = $data->qty;
               $newdata = $data;
            } else {
                // If any product is not in express, reset data and break the loop
                $ids = [];
                $quantities = [];
                $newdata = false;
                break;
            }
        }
        
        
        
        if ($newdata) {
            $newdata->applied_id = $ids;
            $newdata->applied_qtys = $qty;
            $newdata = $newdata->toArray();
        }

       //  unset($data['brands']);
       //  unset($data['productcategory']);
       //  unset($data['products']);
       //  }
        // print_r($ids);die;
        $response = [
            'data' => $newdata ? $newdata : [],
            'quantities' => $quantities
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getExpressRegional(Request $request) {
        $id = $request->productids;
        $qty = $request->productqty;
        $ids = [];
        $quantities = [];
        $newdata = false;
        $city = $request->city;
        
        foreach ($id as $key => $val) {
            $data = LiveStock::join('warehouse as w', 'livestock.city', '=', 'w.ln_code')
            ->join('products as p', 'livestock.ln_sku', '=', 'p.sku')
            ->join('warehouse_city as wc', 'wc.warehouse_id', '=', 'w.id')
            ->join('states as s', 'wc.city_id', '=', 's.id')
            ->select(
                DB::raw('MAX(w.id) as id'),
                DB::raw('MAX(w.express_name) as title'),
                DB::raw('MAX(w.express_name_arabic) as title_arabic'),
                DB::raw('MAX(w.express_days) as num_of_days'),
                DB::raw('MAX(w.express_price) as price'),
                DB::raw('SUM(livestock.qty) as qty')
                // DB::raw('CASE 
                //             WHEN SUM(livestock.qty) > 10 THEN 10 
                //             WHEN SUM(livestock.qty) > 1 THEN SUM(livestock.qty) 
                //             ELSE 0 
                //          END as qty')
            )
            ->where('w.status', 1)
            ->where('w.show_in_express', 1)
            ->where('p.id', $val)
            ->where(function ($query) use ($city) {
                $query->where('s.name', $city)
                      ->orWhere('s.name_arabic', $city);
            })
            ->groupBy('livestock.sku')
            ->havingRaw('SUM(livestock.qty) >= 1')
            ->first();
            
            $expWarhouse = Warehouse::
            whereHas('cityData', function ($query) use ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('states.name', $city)
                      ->orWhere('states.name_arabic', $city);
                });
            })
            ->where('warehouse.status', 1)
            ->where('warehouse.show_in_express', 1)
            ->pluck('ln_code')->toArray();
            // print_r($data);die;
            
            if($data && (array_search('OLN1', $expWarhouse) === false && array_search('KUW101', $expWarhouse) === false)){
                $data->qty = $data->qty - 3;
            }
            if($data && $data->qty > 10)
                $data->qty = 10;
            if($data && $data->qty >= $qty[$key]) {

               $ids[] = $val;
               $quantities[] = $data->qty;
               $newdata = $data;
            } else {
                // If any product is not in express, reset data and break the loop
                $ids = [];
                $quantities = [];
                $newdata = false;
                break;
            }
        }
        
        if ($newdata) {
            $newdata->applied_id = $ids;
            $newdata->applied_qtys = $qty;
            $newdata = $newdata->toArray();
        }

        $response = [
            'data' => $newdata ? $newdata : [],
            'quantities' => $quantities
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function getExpress(Request $request) {
        $id = $request->productids;
        // print_r($id);die();
        $city = $request->city;
       $data = ExpressDelivery
        ::select(['express_deliveries.id','express_deliveries.title', 'express_deliveries.title_arabic', 'express_deliveries.num_of_days','express_deliveries.price'])
        ->with('brands.productname:id,brands','productcategory.productname:id','products.id')
        ->where('express_deliveries.status', 1)
        ->where(function($query) use ($city){
            return $query->whereHas('citydata', function($q) use($city){
                $q->where('states.name', $city)->orWhere('states.name_arabic', $city);
            });
        })
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->whereIn('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->whereIn('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->whereIn('products.id', $id);
            })
            ;
        })
        ->first();
        if($data){
        $ids = [];
        $brandsin = $data->brands->pluck('productname')->flatten()->whereIn('id', $id)->pluck('id')->toArray();
        $ids = array_merge($ids,$brandsin);
        $brandsin = $data->productcategory->pluck('productname')->flatten()->whereIn('id', $id)->pluck('id')->toArray();
        $ids = array_merge($ids,$brandsin);
        $brandsin = $data->products->whereIn('id', $id)->pluck('id')->toArray();
        $ids = array_merge($ids,$brandsin);
        
        $data->applied_id = $ids;
        $data = $data->toarray();
        unset($data['brands']);
        unset($data['productcategory']);
        unset($data['products']);
        }
        // print_r($ids);die;
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
    
    public function getDoorStep(Request $request) {
        $id = $request->productids;
       $data = DoorStepDelivery
        ::select(['door_step_delivery.id','door_step_delivery.title', 'door_step_delivery.title_arabic', 'door_step_delivery.type','door_step_delivery.price'])
        ->where('door_step_delivery.status', 1)
        ->where(function($query) use ($id){
            return $query->whereHas('products', function($q) use($id){
                $q->whereIn('products.id', $id);
            })
            ->orWhereHas('brands.productname', function($q) use($id){
                $q->whereIn('products.id', $id);
            })
            ->orWhereHas('productcategory.productname', function($q) use($id){
                $q->whereIn('products.id', $id);
            })
            ;
        })
        ->first();
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
    
    public function Maintenance(Request $request) {
        $success = false;
        $data = $request->all();
        $randomNumber = mt_rand(100000, 999999);
        $ticketId = 'MAN' . $randomNumber;
        
        $maintenance = Maintenance::create([
            'order_no' => $data['order_no'],
            'orderdetail_id' => $data['orderdetail_id'],
            'product_id' => $data['product_id'],
            'subject' => isset($data['subject']) ? $data['subject'] : null,
            'comment' => $data['comment'],
            'time' => isset($data['time']) ? $data['time'] : null,
            'user_id' => $data['user_id'],
            'ticket_id' => $ticketId,
        ]);
        if($maintenance){
            // $getUser = User::where('id', $data['user_id'])->first();
            // if($getUser) {
            //     try {
            //         Mail::send('email.pending-order-email-template', [], function ($message) use ($getUser) {
            //             $message->to($getUser->email)
            //             ->subject('New Maintenance');
            //         });
            //       } catch(\Exception $e) {
            //             // echo "erro";
            //     }  
            // }
            
            $success = true;
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
}
