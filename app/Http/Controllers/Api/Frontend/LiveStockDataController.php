<?php

namespace App\Http\Controllers\Api\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveStock;
use App\Models\LiveStockLogs;

class LiveStockDataController extends Controller
{
    public function LiveStockDataFunction($sku, Request $request)
    {
        $success = false;
        $dataRecorded = [];
        // Validate request and credentials
        if ($request->has('credentials') && $request->credentials === 'TamkeenStores@9200') {
            $data = $request->except('credentials'); // Remove credentials from request data
            foreach ($data as $city => $qty) {
                $record = LiveStock::where('city', $city)->where('sku', $sku)->first();
                if ($record) {
                    // Update existing record
                    $record->update(['qty' => $qty]);
                    LiveStockLogs::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'showroom_code' => $city,
                        'quantity' => $qty,
                        'user_id' => 0,
                        'user_name' => 'ERP',
                    ]);
                } else {
                    // Create new record
                    $record = LiveStock::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'qty' => $qty,
                        'city' => $city,
                        'city_code' => $city,
                    ]);
                    LiveStockLogs::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'showroom_code' => $city,
                        'quantity' => $qty,
                        'user_id' => 0,
                        'user_name' => 'ERP',
                        ]);
                }
                if ($record) {
                    $dataRecorded[] = $record;
                    $success = true;
                }
            }
        }
        // Prepare response
        $response = ['status' => $success, 'data' => $dataRecorded];
        $responseJson = json_encode($response);
        $compressedData = gzencode($responseJson, 9);
        return response($compressedData)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length' => strlen($compressedData),
            'Content-Encoding' => 'gzip',
        ]);
    }
    
    public function LiveStockDataFunctionUpdate(Request $request)
    {
        $success = false;
        $dataRecorded = [];
        // Validate request and credentials
        // return $request->all();
        if ($request->has('credentials') && $request->credentials === 'TamkeenStores@9200') {
            $sku = $request->sku;
            $data = $request->except(['credentials','sku']); // Remove credentials from request data
            
            // return $data;
            foreach ($data as $city => $qty) {
                $record = LiveStock::where('city', $city)->where('sku', $sku)->first();
                if ($record) {
                    // Update existing record
                    $record->update(['qty' => $qty]);
                    LiveStockLogs::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'showroom_code' => $city,
                        'quantity' => $qty,
                        'user_id' => 0,
                        'user_name' => 'ERP',
                    ]);
                } else {
                    // Create new record
                    $record = LiveStock::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'qty' => $qty,
                        'city' => $city,
                        'city_code' => $city,
                    ]);
                    LiveStockLogs::create([
                        'sku' => $request->sku,
                        'ln_sku' => $request->sku,
                        'showroom_code' => $city,
                        'quantity' => $qty,
                        'user_id' => 0,
                        'user_name' => 'ERP',
                        ]);
                }
                if ($record) {
                    $dataRecorded[] = $record;
                    $success = true;
                }
            }
        }
        // Prepare response
        $response = ['status' => $success, 'data' => $dataRecorded];
        $responseJson = json_encode($response);
        $compressedData = gzencode($responseJson, 9);
        return response($compressedData)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length' => strlen($compressedData),
            'Content-Encoding' => 'gzip',
        ]);
    }
  
  public function updateLivesSockData(Request $request)
    {
        $success = false;
        $dataRecorded = [];
        // Validate request and credentials
        // return $request->all();
            $sku = $request->sku;
            $data = $request->except('sku'); // Remove credentials from request data
            foreach ($data as $city => $qty) {
                $record = LiveStock::where('city', $city)->where('sku', $sku)->first();
                if ($record) {
                    // Update existing record
                    $record->update(['qty' => $qty]);
                    LiveStockLogs::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'showroom_code' => $city,
                        'quantity' => $qty,
                        'user_id' => 0,
                        'user_name' => 'ERP',
                    ]);
                } else {
                    // Create new record
                    $record = LiveStock::create([
                        'sku' => $sku,
                        'ln_sku' => $sku,
                        'qty' => $qty,
                        'city' => $city,
                        'city_code' => $city,
                    ]);
                    LiveStockLogs::create([
                        'sku' => $request->sku,
                        'ln_sku' => $request->sku,
                        'showroom_code' => $city,
                        'quantity' => $qty,
                        'user_id' => 0,
                        'user_name' => 'ERP',
                        ]);
                }
                if ($record) {
                    $dataRecorded[] = $record;
                    $success = true;
                }
            }
        // Prepare response
        $response = ['status' => $success, 'data' => $dataRecorded];
        $responseJson = json_encode($response);
        $compressedData = gzencode($responseJson, 9);
        return response($compressedData)->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Content-Length' => strlen($compressedData),
            'Content-Encoding' => 'gzip',
        ]);
    }
    
}