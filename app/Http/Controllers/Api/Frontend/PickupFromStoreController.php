<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Productcategory;
use DB;
use App\Models\Warehouse;
use App\Models\LiveStock;
use App\Models\Order;


class PickupFromStoreController extends Controller
{

    public function index($prod_id, $city, $type, Request $request) {
        $prod_qty = $request->product_qty ?? 0;
        $lang = isset($request->lang) ? $request->lang : 'ar';
        $sortCity = isset($request->sortCity) ? $request->sortCity : 'Jeddah';
        //columns name
        $Wname = $lang == 'ar' ? 'warehouse.name_arabic' :  'warehouse.name';
        $Sname = $lang == 'ar' ? 'states.name_arabic' :  'states.name';
        $Addressname = $lang == 'ar' ? 'address_arabic' :  'address';
        //new
        $WSName = $lang == 'ar' ? 'warehouse.showroom_arabic' : 'warehouse.showroom';
        $WSAddress = $lang == 'ar' ? 'warehouse.showroom_address_arabic' : 'warehouse.showroom_address';
        // type 0 = city, type 1 = warehouse
        $specificIds = [149, 123, 120, 150, 151, 131, 152, 16, 115, 153, 117];
        // $showroomFields = ['id', $Sname, $Addressname, 'close_time', 'direction_button', 'waybill_city', 'open_time', 'close_time', 'weekend_open_time', 'weekend_close_time'];
        $cityFields = ['states.id', $Sname];
        $warehosueSingle = Warehouse::with([
                // 'showroomData' => function ($query) use ($showroomFields) {
                //     $query->select($showroomFields);
                // },
                // 'showroomData.storeCity' => function ($query) use ($cityFields) {
                //     $query->select($cityFields);
                // }
                'waybillCityData' => function ($query) use ($cityFields) {
                    $query->select($cityFields);
                },
            ])
            ->select(
                'warehouse.id',
                $Wname,
                'warehouse.status', 
                'warehouse.store_id', 
                // 'warehouse.warehousetype',
                'warehouse.pickup_from_store',
                $WSName,
                'warehouse.showroom_location',
                $WSAddress,  
                'waybill_city',
                DB::raw('CASE 
                            WHEN livestock.qty > 12 THEN 10
                            WHEN livestock.qty > 1 THEN livestock.qty - 3
                            ELSE 0
                        END as qty')
            )
            ->join('products', function($join) use($prod_id) {
                $join->on('products.sku', '=', DB::raw("'" . $prod_id . "'"));
            })   
            ->join('livestock', function($join) {
                $join->on('livestock.city', '=', 'warehouse.ln_code')
                ->on('livestock.sku', '=', 'products.sku');
            })
            ->when($type == 0, function ($q) use ($city) {
                return $q->whereHas('cityData', function ($query) use ($city) {
                    $query->where(function ($qa) use ($city) {
                        $qa->where('states.name', $city)
                          ->orWhere('states.name_arabic', $city);
                    });
                });
            })
            ->when($type == 1, function ($q) use ($city) {
                return $q->where('warehouse.id', $city);
            })
            ->where('livestock.qty','>',3)
            ->where('warehouse.status', 1)
            // ->where('show_in_express', 1)
            // ->where('show_in_stock', 1)
            // ->where('warehousetype', 1)
            ->where('pickup_from_store', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->groupBy('warehouse.id', 'livestock.qty')
            ->first();
            
        if(!$warehosueSingle) {
            $warehosueSingle = Warehouse::
                with([
                    // 'showroomData' => function ($query) use ($showroomFields) {
                    //     $query->select($showroomFields);
                    // },
                    // 'showroomData.storeCity' => function ($query) use ($cityFields) {
                    //     $query->select($cityFields);
                    // }
                    'waybillCityData' => function ($query) use ($cityFields) {
                        $query->select($cityFields);
                    },
                ])
                // with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
                // ->whereIn('warehouse.id', $specificIds)
                ->select(
                    'warehouse.id',
                    $Wname,
                    // 'livestock.qty',
                    'warehouse.status', 
                    'warehouse.store_id', 
                    // 'warehouse.warehousetype',
                    'warehouse.pickup_from_store',
                    $WSName,
                    'warehouse.showroom_location',
                    $WSAddress,   
                    'waybill_city',
                    DB::raw('CASE 
                            WHEN livestock.qty > 12 THEN 10
                            WHEN livestock.qty > 1 THEN livestock.qty - 3
                            ELSE 0
                        END as qty')
                )
                ->join('products', function($join) use($prod_id) {
                    $join->on('products.sku', '=', DB::raw("'" . $prod_id . "'"));
                })   
                ->join('livestock', function($join) {
                    $join->on('livestock.city', '=', 'warehouse.ln_code')
                    ->on('livestock.sku', '=', 'products.sku');
                })
                ->where('livestock.qty','>',3)
                ->where('warehouse.status', 1)
                // ->where('show_in_express', 1)
                // ->where('show_in_stock', 1)
                // ->where('warehousetype', 1)
                 ->where('pickup_from_store', 1)
                ->orderBy('warehouse.sort', 'asc')
                ->groupBy('warehouse.id', 'livestock.qty')
                ->first();
        }
        
            
        $warehouse = Warehouse::
            with([
                // 'showroomData' => function ($query) use ($showroomFields) {
                //     $query->select($showroomFields);
                // },
                // 'showroomData.storeCity' => function ($query) use ($cityFields) {
                //     $query->select($cityFields);
                // }
                'waybillCityData' => function ($query) use ($cityFields) {
                    $query->select($cityFields);
                },
            ])
            ->select(
                'warehouse.id',
                $Wname,
                'warehouse.status', 
                'warehouse.store_id', 
               // 'warehouse.warehousetype',
                'warehouse.pickup_from_store',
                $WSName,
                'warehouse.showroom_location',
                $WSAddress,   
                'waybill_city',
                DB::raw('CASE 
                            WHEN livestock.qty > 12 THEN 10
                            WHEN livestock.qty > 1 THEN livestock.qty - 3
                            ELSE 0
                        END as qty')
            )
            ->join('products', function($join) use($prod_id) {
                $join->on('products.sku', '=', DB::raw("'" . $prod_id . "'"));
            })   
            // ->join('livestock', function($join) {
            //     $join->on('livestock.city', '=', 'warehouse.ln_code')
            //     ->on('livestock.sku', '=', 'products.sku');
            // })
            ->leftJoin('livestock', function($join) {
                $join->on('livestock.city', '=', 'warehouse.ln_code')
                ->on('livestock.sku', '=', 'products.sku');
            })
            // ->where('livestock.qty','>',3)
            ->where('warehouse.status', 1)
            // ->where('show_in_express', 1)
            // ->where('show_in_stock', 1)
            // ->where('warehousetype', 1)
             ->where('pickup_from_store', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->get();
            $sortedWarehouse = $warehouse->sortByDesc(function ($item) use ($sortCity) {
                $cityMatch = (
                    // $item->showroomData->storeCity->name == $sortCity ||
                    // $item->showroomData->storeCity->name_arabic == $sortCity
                    optional($item->waybillCityData)->name == $sortCity ||
                    optional($item->waybillCityData)->name_arabic == $sortCity
                ) && $item->qty > 0 ? 1 : 0;
            
                return [$cityMatch, $item->qty];
            })->values();
            // print_r($prod_qty);
        
        if(count($sortedWarehouse) >= 1){
            foreach ($sortedWarehouse as $index => $vWare) {
                //loop for warehouse qty check
                $requiredQty = $prod_qty;
                // $productInWarehouse = LiveStock::where('sku', $prod_id)
                //     ->where('city', $vWare->ln_code)
                //     ->where('qty', '>', $requiredQty)
                //     ->exists();
                // if (!$productInWarehouse) {
                //   $sortedWarehouse[$index]->qty = 0;
                // }
                if($prod_qty > $vWare->qty){
                    $vWare->qty = 0;
                }
            }
        }
        if($warehosueSingle){
            if($prod_qty > $warehosueSingle->qty){
                $warehosueSingle->qty = 0;
                 $warehosueSingle = count($sortedWarehouse) >= 1 ? $sortedWarehouse[0] : collect;
            }
        }
        
        $response = [
            'warehouse_single' => $warehosueSingle,
            'warehouse' => $sortedWarehouse
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function getwarehouseCart(Request $request) {
        // print_r($request->all());die;
        $lang = isset($request->lang) ? $request->lang : 'ar';
        $qtys = $request->qty;
        $productId = $request->product_id;
        $city = $request->city;
        $storeId = $request->store_id;
        $type = $request->type;
        $success = false;
        
        $productIds = $productId; 
        $productCount = count($productIds);
        $productQuantities = $qtys; 
        
        //columns name
        $Wname = $lang == 'ar' ? 'warehouse.name_arabic' :  'warehouse.name';
        $Sname = $lang == 'ar' ? 'name_arabic' :  'name';
        $Addressname = $lang == 'ar' ? 'address_arabic' :  'address';
        //new
        $WSName = $lang == 'ar' ? 'warehouse.showroom_arabic' : 'warehouse.showroom';
        $WSAddress = $lang == 'ar' ? 'warehouse.showroom_address_arabic' : 'warehouse.showroom_address';
        // type 0 = city, type 1 = warehouse
        $showroomFields = ['id', 'waybill_city'];
        $cityFields = ['id', $Sname];
        
        $warehouses = Warehouse::with([
                'waybillCityData' => function ($query) use ($cityFields) {
                    $query->select($cityFields);
                },
            ])
            ->select(
                'warehouse.id',
                $WSName,
                'warehouse.status',
                'warehouse.store_id',
                'warehouse.pickup_from_store',
                $WSName,
                'warehouse.showroom_location',
                $WSAddress,   
                'waybill_city',
                'livestock.qty as livestock_qty',
                'warehouse.ln_code'
            )
            ->join('products', function($join) use ($productIds) {
                $join->on('products.id', '=', DB::raw("'" . implode("' OR products.id = '", $productIds) . "'"))->where('products.eligible_for_pickup', 1);
            })
            ->leftJoin('livestock', function($join) {
                $join->on('livestock.city', '=', 'warehouse.ln_code')
                     ->on('livestock.sku', '=', 'products.sku'); 
            })
            // ->when($type == 0, function ($q) use ($city) {
            //     return $q->whereHas('cityData', function ($query) use ($city) {
            //         $query->where(function ($qa) use ($city) {
            //             $qa->where('states.name', $city)
            //               ->orWhere('states.name_arabic', $city);
            //         });
            //     });
            // })
            // ->when($type == 1, function ($q) use ($city) {
            //     return $q->where('warehouse.id', $city);
            // })
            //->where('livestock.qty', '>', 0)
            ->where('warehouse.status', 1)
            // ->where('show_in_express', 1)
            // ->where('show_in_stock', 1)
            // ->where('warehousetype', 1)
            ->where('pickup_from_store', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->groupBy('warehouse.id', 'livestock.qty')
            ->get();
            
            $validWarehouses = $warehouses->unique('id')->values();
            $validWarehouses = $validWarehouses->sortByDesc(function ($item) use ($city) {
                $cityMatch = (
                    // $item->showroomData->storeCity->name == $city ||
                    // $item->showroomData->storeCity->name_arabic == $city
                    optional($item->waybillCityData)->name == $city ||
                    optional($item->waybillCityData)->name_arabic == $city
                ) && $item->livestock_qty > 0 ? 1 : 0;
            
                return [$cityMatch, $item->livestock_qty];
            })->values();
            
        $warehouse = Warehouse::with([
                'waybillCityData' => function ($query) use ($cityFields) {
                    $query->select($cityFields);
                },
            ])
            ->select(
                'warehouse.id',
                $WSName,
                $WSAddress,
                'warehouse.status',
                'warehouse.store_id',
                'warehouse.pickup_from_store',
                $WSName,
                'warehouse.showroom_location',
                $WSAddress,  
                'waybill_city', 
                'livestock.qty as livestock_qty',
                'warehouse.ln_code'
            )
            ->join('products', function($join) use ($productIds) {
                $join->on('products.id', '=', DB::raw("'" . implode("' OR products.id = '", $productIds) . "'"));
            })
            ->join('livestock', function($join) {
                $join->on('livestock.city', '=', 'warehouse.ln_code')
                     ->on('livestock.sku', '=', 'products.sku');
            })
            ->when($type == 0, function ($q) use ($city) {
                return $q->whereHas('cityData', function ($query) use ($city) {
                    $query->where(function ($qa) use ($city) {
                        $qa->where('states.name', $city)
                           ->orWhere('states.name_arabic', $city);
                    });
                });
            })
            ->when($type == 1, function ($q) use ($storeId) {
                return $q->where('warehouse.id', $storeId);
            })
            // ->where('livestock.qty', '>', 0)
            ->where('warehouse.status', 1)
            // ->where('show_in_express', 1)
            // ->where('show_in_stock', 1)
            // ->where('warehousetype', 1)
            ->where('pickup_from_store', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->groupBy('warehouse.id', 'products.id', 'livestock.qty')
            ->first();
        
        $isAllProductsAvailable = true;
        
        if ($warehouse) {
            foreach ($productIds as $index => $productId) {
                $requiredQty = $productQuantities[$index] + 3;
        
                $proData = Product::where('id', $productId)->first();
                if (!$proData) {
                    $isAllProductsAvailable = false;
                    break;
                }
                $productSku = $proData->sku;
                $productInWarehouse = LiveStock
                    ::where('sku', $productSku)
                    ->where('city', $warehouse->ln_code)
                    ->where('qty', '>=', $requiredQty)
                    ->first();
                // print_r($productInWarehouse);
                if (!$productInWarehouse) {
                    $isAllProductsAvailable = false;
                    break; 
                }
            }
            // die;
        } else {
            $isAllProductsAvailable = false;
        }
        
        // if(!$isAllProductsAvailable && sizeof($validWarehouses) >= 1){
        //     $isAllProductsAvailable = true;
        //     $warehouse = $validWarehouses[0];
        // }
        
        foreach ($validWarehouses as $index => $vWare) {
            // $allAvailable = true;
            foreach ($productIds as $i => $productId) {
                $requiredQty = $productQuantities[$i] + 3;
    
                $proData = Product::where('id', $productId)->first();
                // if (!$proData) {
                //     $allAvailable = false;
                //     break;
                // }
    
                $productSku = $proData->sku;
    
                $productInWarehouse = LiveStock::where('sku', $productSku)
                    ->where('city', $vWare->ln_code)
                    ->where('qty', '>=', $requiredQty)
                    ->first();
                if (!$productInWarehouse) {
                    // $allAvailable = false;
                    $validWarehouses[$index]->livestock_qty = 0; // Update warehouse qty
                }
                // if (!$productInWarehouse) {
                //     $allAvailable = false;
                //     break;
                // }
            }
        }
        if (!$isAllProductsAvailable && sizeof($validWarehouses) >= 1) {
            $isAllProductsAvailable = true;
            
        
            foreach ($validWarehouses as $index => $vWare) {
                $allAvailable = true;
        
                
                
                //loop for warehouse qty check
                foreach ($productIds as $i => $productId) {
                    $requiredQty = $productQuantities[$i] + 3;
        
                    $proData = Product::where('id', $productId)->first();
                    if (!$proData) {
                        $allAvailable = false;
                        break;
                    }
        
                    $productSku = $proData->sku;
        
                    $productInWarehouse = LiveStock::where('sku', $productSku)
                        ->where('city', $vWare->ln_code)
                        ->where('qty', '>=', $requiredQty)
                        ->first();
                    if (!$productInWarehouse) {
                        $allAvailable = false;
                    }
                }
        
                if ($allAvailable) {
                    $warehouse = $vWare;
                    break;
                } else {
                    $isAllProductsAvailable = false;
                    $warehouse = false;
                    $validWarehouses = collect();
                }
            }
        }

        
        $allEligible = Product::whereIn('id', $productIds)
            ->where('eligible_for_pickup', 1)
            ->count();
    
        if ($allEligible != $productCount) {
            $warehouse = null;
            $validWarehouses = collect();
            $isAllProductsAvailable = false;
        }
        
        
        $response = [
            'warehouse_single' => $warehouse,
            'success' => $isAllProductsAvailable,
            'warehouses' => $validWarehouses,
        ];

        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);

    }
    
    public function getWarehouse() {
        $specificIds = [149, 123, 120, 150, 151, 131, 152, 16, 115, 153, 117];
        $data = Warehouse::
            with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
            // ->whereIn('warehouse.id', $specificIds)
            ->select(
                'warehouse.id',
                'warehouse.name',
                'warehouse.name_arabic',
                'warehouse.address',
                'warehouse.status',
                'warehouse.store_id',
                'warehouse.warehousetype',
                'warehouse.show_in_express'
            )
            ->join('livestock', function ($join) {
                $join->on('livestock.city', '=', 'warehouse.ln_code');
            })
            ->where('livestock.qty', '>', 0)
            ->where('warehouse.status', 1)
            // ->where('show_in_express', 1)
            ->where('show_in_stock', 1)
            ->where('warehousetype', 1)
            ->whereHas('showroomData')
            // ->orderBy('warehouse.sort', 'asc')
            ->distinct() // Ensures unique warehouses
            ->get();

                
        $response = [
            'warehouse' => $data
        ];
            
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getSelectedWarehouse($id, Request $request) {
        $lang = $request->lang ?? 'ar';
        $specificIds = [149, 123, 120, 150, 151, 131, 152, 16, 115, 153, 117];
        //columns name
        $Wname = $lang == 'ar' ? 'warehouse.name_arabic' :  'warehouse.name';
        $Sname = $lang == 'ar' ? 'name_arabic' :  'name';
        $Addressname = $lang == 'ar' ? 'address_arabic' :  'address';
        //new
        $WSName = $lang == 'ar' ? 'warehouse.showroom_arabic' : 'warehouse.showroom';
        $WSAddress = $lang == 'ar' ? 'warehouse.showroom_address_arabic' : 'warehouse.showroom_address';
        $cityFields = ['id', $Sname];
        
        $warehouse = Warehouse::with([
                  'showroomData' => function ($query) {
                    $query->select('id', 'region');
                },
                // 'showroomData.storeCity' => function ($query) use ($cityFields) {
                //     $query->select($cityFields);
                // }
                'showroomData.storeRegions' => function ($query) use ($cityFields) {
                    $query->select($cityFields);
                },
                'waybillCityData' => function ($query) use ($cityFields) {
                    $query->select($cityFields);
                },
            ])
            ->select(
                'warehouse.id',
                $WSName,
                $WSAddress,
                'warehouse.status',
                'warehouse.store_id',
                // 'warehouse.warehousetype',
                'warehouse.pickup_from_store',
                $WSName,
                'warehouse.showroom_location',
                $WSAddress,  
                'waybill_city',
                DB::raw('CASE 
                            WHEN livestock.qty > 12 THEN 10
                            WHEN livestock.qty > 1 THEN livestock.qty - 3
                            ELSE 0
                        END as qty')
            ) 
            ->join('livestock', function($join) {
                $join->on('livestock.city', '=', 'warehouse.ln_code');
            })
            ->where('warehouse.id', $id)
            ->where('livestock.qty','>',0)
            ->where('warehouse.status', 1)
            // ->where('show_in_express', 1)
            // ->where('show_in_stock', 1)
            // ->where('warehousetype', 1)
            ->where('pickup_from_store', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->groupBy('warehouse.id', 'livestock.qty')
            ->first();
        
        $response = [
            'warehouse' => $warehouse
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function pickupStoreStatusUpdate($orderNo, $status){
        // $statusValue = [
        //     '1' => 'Ready For Collection', // confirmed
        //     4 => 'Picked From Showroom',  // Delivered
        //     5 => 'Pickup Cancel',  // Cancel
        // ];
        // if (!array_key_exists($status, $statusValue)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid status provided'
        //     ], 400);
        // }
        $statusValue = [
            'Ready For Collection' => 1,  // confirmed
            'Picked From Showroom' => 4,  // Delivered
            'Pickup Cancel' => 5,         // Cancel
        ];
    
        $statusKey = array_flip($statusValue);
    
        if (!in_array($status, $statusValue)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided'
            ], 400);
        }
        

        $order = Order::where('order_no', $orderNo)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order Number not found'
            ], 400);
        }

        $order->status = $status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Status Updated Successfully'
        ], 200);
    }

}