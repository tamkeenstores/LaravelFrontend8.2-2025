<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Productcategory;
use DB;
use App\Models\Warehouse;
use App\Models\LiveStock;


class PickupFromStoreController extends Controller
{

    public function index($prod_id, $city, $type) {
        // type 0 = city, type 1 = warehouse
        $warehosueSingle = Warehouse::
            with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
            ->select(
                'warehouse.id',
                'warehouse.name',
                'warehouse.name_arabic',
                'warehouse.address',
                'warehouse.status', 
                'warehouse.store_id', 
                'warehouse.warehousetype',
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
            ->where('livestock.qty','>',0)
            ->where('warehouse.status', 1)
            ->where('show_in_express', 1)
            ->where('warehousetype', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->groupBy('warehouse.id', 'livestock.qty')
            ->first();
            
        if(!$warehosueSingle) {
            $warehosueSingle = Warehouse::
                with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
                ->select(
                    'warehouse.id',
                    'warehouse.name',
                    'warehouse.name_arabic',
                    'warehouse.address',
                    // 'livestock.qty',
                    'warehouse.status', 
                    'warehouse.store_id', 
                    'warehouse.warehousetype',
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
                ->where('livestock.qty','>',0)
                ->where('warehouse.status', 1)
                ->where('show_in_express', 1)
                ->where('warehousetype', 1)
                ->orderBy('warehouse.sort', 'asc')
                ->groupBy('warehouse.id', 'livestock.qty')
                ->first();
        }

            
        $warehouse = Warehouse::
            with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
            ->select(
                'warehouse.id',
                'warehouse.name',
                'warehouse.name_arabic',
                'warehouse.address',
                'warehouse.status', 
                'warehouse.store_id', 
                'warehouse.warehousetype',
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
            ->where('livestock.qty','>',0)
            ->where('warehouse.status', 1)
            ->where('show_in_express', 1)
            ->where('warehousetype', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->get();
        
        $response = [
            'warehouse_single' => $warehosueSingle,
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

    public function getwarehouseCart(Request $request) {
        // print_r($request->all());die;
        $qtys = $request->qty;
        $productId = $request->product_id;
        $city = $request->city;
        $storeId = $request->store_id;
        $type = $request->type;
        $success = false;
        //$sku = [];

        
        
        $productIds = $productId; 
        $productQuantities = $qtys; 
        
        
        $warehouses = Warehouse::with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
            ->select(
                'warehouse.id',
                'warehouse.name',
                'warehouse.name_arabic',
                'warehouse.address',
                'warehouse.status',
                'warehouse.store_id',
                'warehouse.warehousetype',
                'products.id as product_id', 
                'livestock.qty as livestock_qty',
                'warehouse.ln_code'
            )
            ->join('products', function($join) use ($productIds) {
                $join->on('products.id', '=', DB::raw("'" . implode("' OR products.id = '", $productIds) . "'"));
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
            ->where('show_in_express', 1)
            ->where('warehousetype', 1)
            ->orderBy('warehouse.sort', 'asc')
            ->groupBy('warehouse.id', 'products.id', 'livestock.qty')
            ->get();
        
        $validWarehouses = [];
        
        foreach ($warehouses as $warehouse) {
            $isAllProductsAvailable = true;
        
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
                    ->exists();
        
                if (!$productInWarehouse) {
                    $isAllProductsAvailable = false;
                    break;
                }
            }
        
            if ($isAllProductsAvailable) {
                $validWarehouses[] = $warehouse;
            }
        }
        
        $warehouse = Warehouse::with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
            ->select(
                'warehouse.id',
                'warehouse.name',
                'warehouse.name_arabic',
                'warehouse.address',
                'warehouse.status',
                'warehouse.store_id',
                'warehouse.warehousetype',
                'products.id as product_id',
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
            ->where('livestock.qty', '>', 0)
            ->where('warehouse.status', 1)
            ->where('show_in_express', 1)
            ->where('warehousetype', 1)
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
                // echo $productSku;die;
                $productInWarehouse = LiveStock
                    ::where('sku', $productSku)
                    ->where('city', $warehouse->ln_code)
                    ->where('qty', '>=', $requiredQty)
                    ->exists();
        
                if (!$productInWarehouse) {
                    $isAllProductsAvailable = false;
                    break; 
                }
            }
        } else {
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
        $data = Warehouse::
            with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
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
            ->where('show_in_express', 1)
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
    
    public function getSelectedWarehouse($id) {
        $warehouse = Warehouse::
        with('showroomData.storeCity:id,name,name_arabic', 'showroomData.storeRegions:id,name,name_arabic')
        ->select(
            'warehouse.id',
            'warehouse.name',
            'warehouse.name_arabic',
            'warehouse.address',
            'warehouse.status', 
            'warehouse.store_id', 
            'warehouse.warehousetype',
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
        ->where('show_in_express', 1)
        ->where('warehousetype', 1)
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

}