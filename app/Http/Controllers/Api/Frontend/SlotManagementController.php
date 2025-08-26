<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\States;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\LiveStock;
use App\Models\OrderShipment;

class SlotManagementController extends Controller
{
    public function checkShipmentAvailableDateForDelivery(Request $request)
    {
        $shipmentIds = $request->input('shipment_id');
        $dates = [];
        $regions = [];
    
        $shipments = OrderShipment::with('ShippingAddressShowroomOrder.stateData.region')
            // ->whereIn('id', $shipmentIds)
            ->where('id', $shipmentIds)
            ->get();
    
        $shipment = $shipments->first();
        if (!$shipment) {
            return response()->json(['error' => 'No shipment found'], 404);
        }
        
        $express = false;
    
        // express and city
        $express = $shipment->shipment_type == 1;
        $isShowroomOrder = $shipment->order_type == 1;
        if ($isShowroomOrder) {
            $city = isset($shipment->ShippingAddressShowroomOrder->stateData->id)
                ? $shipment->ShippingAddressShowroomOrder->stateData->id
                : 15;
                $cityName = isset($shipment->ShippingAddressShowroomOrder->stateData->name)
                ? $shipment->ShippingAddressShowroomOrder->stateData->name
                : 'Jeddah';
        } else {
            $city = isset($shipment->shipmentOrder->Address->stateData->id)
                ? $shipment->shipmentOrder->Address->stateData->id
                : 15;
            $cityName = isset($shipment->shipmentOrder->Address->stateData->name)
                ? $shipment->shipmentOrder->Address->stateData->name
                : 'Jeddah';
        }
    
        // Determine start date
        $startDate = $express ? Carbon::tomorrow() : Carbon::today()->addDays(5);
        $currentDate = $startDate;
    
        // Get the relevant warehouse for the city
        $warehouse = null;
        if (!$express) {
            $warehouse = Warehouse::with('warehouseRegions')
                ->whereHas('cityData', function ($q) use ($city) {
                    $q->where('city_id', $city);
                })
                ->whereHas('warehouseRegions', function ($q) use ($city) {
                    $q->where('id', '!=', 68);
                })
                ->where('status', 1)
                ->where('show_in_stock', 1)
                ->first();
        }
        // dd($warehouse);
        if (!$warehouse && $express != true) {
            $dates[] = Carbon::today()->addDays(7)->toDateString();
        } else {
            while (count($dates) < 15) {
            // Skip Fridays
            if ($currentDate->dayOfWeek != Carbon::FRIDAY) {
                $dateString = $currentDate->toDateString();
                $skipDate = false;
    
                if (!$express && $warehouse && $warehouse->warehouseRegions) {
                    // dd($warehouse->warehouseRegions);
                    $ordersCount = OrderShipment::when($isShowroomOrder,function ($q) use ($city) {
                        // For showroom orders
                        $q->whereHas('ShippingAddressShowroomOrder', function ($q) use ($city) {
                            $q->whereHas('stateData', function ($q1) use ($city) {
                                $q1->where('id', $city);
                            });
                        });
                    },
                    function ($q) use ($city) {
                        // For online orders
                        $q->whereHas('shipmentOrder.Address', function ($q) use ($city) {
                            $q->whereHas('stateData', function ($q1) use ($city) {
                                $q1->where('id', $city);
                            });
                        });
                    }
                )
                ->whereDate('preferred_date', $dateString)
                ->count();


                    $capacity = $warehouse->warehouseRegions->offline_capacity ?? 0;

                    if ($ordersCount >= $capacity) {
                        $skipDate = true;
                        break;
                    }
                    $regions = [
                        'region_id' => $warehouse->warehouseRegions->id,
                        'name' => $warehouse->warehouseRegions->name,
                        'capacity' => $capacity,
                        'orders_count' => $ordersCount
                    ];
                }
    
                if (!$skipDate) {
                    $dates[] = $dateString;
                }
            }
    
            $currentDate->addDay();
        }
        }
        $response = [
            'city' => $cityName,
            'express' => $express,
            'available_dates' => $dates,
            'regions' => $regions,
        ];
    
        $responseJson = json_encode($response);
        $data = gzencode($responseJson, 9);
    
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    
    public function checkAvailableDateForDelivery(Request $request) {
        $city = $request->input('city');
        $express = (int) $request->input('express');
        $productIds = $request->input('product_ids');
        $quantities = $request->input('quantities');

        $dates = [];
        $warehouses = [];
        $regions = [];
        $customerCityData = States::where('name', $city)->orWhere('name_arabic', $city)->first();
        $customerCity = 15;
        if($customerCityData)
            $customerCity = $customerCityData->id;

        if ($express == 1) {
            // Start from tomorrow
            $startDate = Carbon::tomorrow();
        } else {
            // $warehouses = $this->getUniqueWarehouses($productIds, $quantities, $customerCity);
            $warehouses = Warehouse::with('livestockData')->whereHas('cityData', function ($query) use ($customerCity) {
                $query->where('city_id', $customerCity);
            })
            ->whereHas('warehouseRegions', function ($query) use ($customerCity) {
                $query->whereNot('id', 68);
            })
            ->where('status', 1)
            ->where('show_in_stock', 1)
            ->first();
            // Start from today + 5 days
            $startDate = Carbon::today()->addDays(5);
        }

        $currentDate = $startDate;
        // print_r($warehouses);
        // die;
        if (!$warehouses && $express != 1) {
            $dates[] = Carbon::today()->addDays(7)->toDateString();
        } else {
            while (count($dates) < 15) {
            // Exclude Friday (dayOfWeek == 5)
            if ($currentDate->dayOfWeek !== 5) {
                $dateString = $currentDate->toDateString();

                $skipDate = false;
                if ($express != 1){
                    // foreach ($warehouses as $warehouseCode) {
                    //     $warehouse = Warehouse::with(['warehouseRegions.ordersData' => function ($q) use ($dateString) {
                    //         $q->where('delivery_date', $dateString);
                    //     }])->where('ln_code', $warehouseCode)->first();
                    //         // dd($warehouse);
                    //     if (!$warehouse) continue;
                    //     // print_r($warehouse->toArray());die;
                    //     if($warehouse->warehouseRegions){
                    //         $region = $warehouse->warehouseRegions;
                    //         $regions[] = $region->name;
                    //         $capacity = $region->online_capacity ?? 0;
                    //         $ordersCount = $region->ordersData->count();

                    //         if ($ordersCount >= $capacity) {
                    //             $skipDate = true;
                    //             // break 2; // Skip this date completely
                    //         }
                    //     }
                    // }
                    if($warehouses){
                        $warehouse = Warehouse::with(['warehouseRegions.ordersData' => function ($q) use ($dateString) {
                            $q->where('delivery_date', $dateString);
                        }])->where('ln_code', $warehouses->ln_code)->first();
                            // dd($warehouse);
                        // print_r($warehouse->toArray());die;
                        if($warehouse && $warehouse->warehouseRegions){
                            $region = $warehouse->warehouseRegions ?? '';
                            $regions = $warehouse->warehouseRegions->name ?? '';
                            $capacity = $region->online_capacity ?? 0;
                            $ordersCount = isset($region->ordersData) ? $region->ordersData->count() : 0;
                            // dd($region->ordersData);
                            if ($ordersCount >= $capacity) {
                                $skipDate = true;
                                // break 2; // Skip this date completely
                            }
                        }
                    }
                }
                

                if (!$skipDate) {
                    $dates[] = $dateString;
                }
            }

            $currentDate->addDay();
        }
        }
        $response = [
            'city' => $city,
            'express' => $express,
            'available_dates' => $dates,
            // 'warehouses' => $warehouses,
            // 'regions' => array_unique($regions),
            'regions' => $regions,
        ];
        
        $responseJson = json_encode($response);
        $data = gzencode($responseJson, 9);
        
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function getUniqueWarehouses(array $productIds, array $quantities, int $customerCity)
    {
        $allWarehouses = [];

        foreach ($productIds as $index => $productId) {
            $qty = $quantities[$index] ?? 0;
            if ($qty <= 0) continue;

            $distribution = $this->getWarehouseDistribution($productId, $qty, $customerCity);

            foreach ($distribution as $entry) {
                $allWarehouses[] = $entry['warehouse_code'];
            }
        }

        // Return unique codes only
        return array_values(array_unique($allWarehouses));
    }



    public function getWarehouseDistribution($productId, $quantity, $customerCity)
    {
        $product = Product::find($productId);
        if (!$product) return [];

        $regional_stockarray = [];
        $totalqty = $quantity;
        $removeQty = 3; // for OLN1 adjustment
        $otherWarehouses = ['OLN1'];
        $sort = $customerCity == 15 ? 'asc' : 'desc';

        // 1. Regional Warehouses (priority)
        $warehouses = Warehouse::with('livestockData')->whereHas('cityData', function ($query) use ($customerCity) {
            $query->where('city_id', $customerCity);
        })
        ->where('status', 1)
        ->where('show_in_stock', 1)
        ->orderBy('sort', $sort)
        ->get();

        foreach ($warehouses as $warehouse) {
            $stock = $warehouse->livestockData->firstWhere('ln_sku', $product->ln_sku);
            if (!$stock || $stock->qty <= 0) continue;

            $liveQty = (int) $stock->qty;

            // Exclude OLN1 quantity buffer
            if (!in_array($warehouse->ln_code, $otherWarehouses)) {
                if ($liveQty > $removeQty) {
                    $liveQty -= $removeQty;
                    $removeQty = 0;
                } else {
                    $removeQty -= $liveQty;
                    $liveQty = 0;
                }
            }

            if ($liveQty > 0) {
                if ($totalqty > $liveQty) {
                    $regional_stockarray[] = ['warehouse_code' => $warehouse->ln_code, 'qty' => $liveQty];
                    $totalqty -= $liveQty;
                } else {
                    $regional_stockarray[] = ['warehouse_code' => $warehouse->ln_code, 'qty' => $totalqty];
                    $totalqty = 0;
                    break;
                }
            }

            if ($totalqty <= 0) break;
        }

        // 2. Other Warehouses (fallback)
        if ($totalqty > 0) {
            $excludedIds = $warehouses->pluck('id')->toArray();
            $regionalmodules = Warehouse::whereNotIn('id', $excludedIds)
                ->where('status', 1)
                ->where('show_in_stock', 1)
                ->orderBy('sort', 'asc')
                ->get();

            foreach ($regionalmodules as $regionalmodule) {
                $regionalproduct = LiveStock::where('ln_sku', $product->ln_sku)
                    ->where('city', $regionalmodule->ln_code)
                    ->first();

                if (!$regionalproduct || $regionalproduct->qty <= 0) continue;

                if ($totalqty > $regionalproduct->qty) {
                    $regional_stockarray[] = ['warehouse_code' => $regionalmodule->ln_code, 'qty' => $regionalproduct->qty];
                    $totalqty -= $regionalproduct->qty;
                } else {
                    $regional_stockarray[] = ['warehouse_code' => $regionalmodule->ln_code, 'qty' => $totalqty];
                    $totalqty = 0;
                    break;
                }
            }
        }

        return $regional_stockarray;
    }

    
}
