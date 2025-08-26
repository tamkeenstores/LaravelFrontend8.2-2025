<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShowroomOrder;
use App\Models\States;
use App\Models\Area;
use App\Models\Product;
use App\Models\ShippingAddressShowroomOrder;
use App\Models\ShowroomOrderDetail;
use App\Models\Warehouse;
use App\Models\ShowroomOrderComments;
use App\Models\ShipmentComments;
use App\Models\OrderShipment;
use App\Models\SecondDbOrderShipment;
use App\Models\ShipmentDetail;
use App\Models\ShowroomOrderInvoices;
use App\Models\ShipmentStatusTimeline;
use App\Models\ShowroomOrderStatusTimeline;
use App\Models\User;
use App\Models\ShowroomOrderUsers;
use App\Models\StoreLocator;
use App\Models\ShowroomOrderStores;
use App\Models\InstallationShipment;
use App\Models\InstallationShipmentDetail;
use App\Models\InstallationDetails;
use App\Models\InstallationComments;
use App\Models\InstallationShipmentAddress;
use App\Models\InstallationStatusTimeline;
use App\Models\InstallationShipmentStores;
use App\Models\InstallationOrderUsers;
use App\Models\WarehouseShipment;
use App\Models\ErpOrderShipment;


class ShipmentTrackingController extends Controller
{
     //shipment tracking
    public function shipmentTracking($slug) {
        $success = false;
        $shipment = OrderShipment::with('ShippingAddressShowroomOrder','shipmentOrder:id,shipping_id','shipmentOrder.details.productData','shipmentOrder.Address.stateData','ShippingAddressShowroomOrder.stateData','riderData','SOdetails.productData','warehouse')->where('shipment_no', $slug)->orderBydesc('id')->first();
        if($shipment) {
            $success = true;
        }
        $response = [
            'success' => $success,
            'data' => $shipment
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    // public function shipmentTracking($slug) {
    //     $success = false;
    //     $shipment = SecondDbOrderShipment::with('ShippingAddressShowroomOrder','shipmentOrder:id,shipping_id','shipmentOrder.details.productData','shipmentOrder.Address.stateData','ShippingAddressShowroomOrder.stateData','riderData','SOdetails.productData','warehouse')->where('shipment_no', $slug)->orderBydesc('id')->first();
    //     if($shipment) {
    //         $success = true;
    //     }
    //     $response = [
    //         'success' => $success,
    //         'data' => $shipment
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
    public function shipmentTrackingLocation(Request $request,$id) {
        $shipment =  OrderShipment::where('shipment_no',$id)->orderBydesc('id')->first();
        $shipment->current_location = $request->input('current_location');
        $shipment->save();
    
        return response()->json($shipment, 200);
    }
    
    public function ShipmentTrackingDetail($id) {
        $success = false;
        $order = OrderShipment::
        with(['ShippingAddressShowroomOrder', 'warehouse.waybillCityData', 'statustimeline', 'shipmentTransits.warehouseData', 'shipmentTransits.riderData', 'riderData', 'ShippingAddressShowroomOrder.stateData', 'shipmentshowroomDetailsData.productData.featuredImage',  'shipmentshowroomDetailsData.replaceproductData.featuredImage', 'shipmentDetailsData.detailData.productData.featuredImage', 'shipmentDetailsData.replaceproductData.featuredImage', 'shipmentOrder' => function ($query) {
            $query->with(['UserDetail', 
              'Address', 
              'comments.UserDetail:id,first_name,last_name',
              'Address.stateData'
            ]);
        }])
        ->withCount('shipmentDetailsData', 'SOdetails','shipmentTransits')
        ->where('id', $id)
        ->first();
        
        if($order) {
            $success = true;
        }
        
        $response = [
            'success' => $success,
            'data' => $order
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
