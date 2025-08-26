<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\User;
use App\Models\shippingAddress;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\OrderSummary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;
use Carbon\Carbon;

class ExportOrder implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public $r = [];
    
    public function __construct($data)
    {
        //print_r($data);die();
        $this->r = $data;
        // print_r($this->r);die();
    }
    
    public function collection()
    {
        $keys = $this->r;
        
        // $ordercheck = isset($keys['orderdata']) ? $keys['orderdata'] : null;
        // $productcheck = isset($keys['productdata']) ? $keys['productdata'] : null;
        // $customercheck = isset($keys['customerdata']) ? $keys['customerdata'] : null;
        // $shippingcheck = isset($keys['shippingdata']) ? $keys['shippingdata'] : null;
        
        $fromdate = isset($keys['fromdate']) ? $keys['fromdate'] : null;
        $todate = isset($keys['todate']) ? $keys['todate'] : null;
        $statusfilter = isset($keys['filterstatus']) ? $keys['filterstatus'] : null;
        $paymentfilter = isset($keys['filterpayment']) ? $keys['filterpayment'] : null;

        // Define status labels
        $statusLabels = [
            '0' => 'Received',
            '1' => 'Confirmed',
            '2' => 'Processing',
            '3' => 'Out for Delivery',
            '4' => 'Delivered',
            '5' => 'Cancel',
            '6' => 'Refund',
            '7' => 'Failed',
            '8' => 'Pending Payment',
        ];
        
        $selects = [];
        $selects[] = 'orderdata.order_no';

        if (isset($keys['status']) && $keys['status'] == true) {
            $selects[] = 'orderdata.status';
        }
        if(isset($keys['created_at']) && $keys['created_at'] == true) {
            $selects[] = 'orderdata.created_at';
        }
        if(isset($keys['customer_name']) && $keys['customer_name'] == true){
            $selects[] = DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name");
        }
        if(isset($keys['address']) && $keys['address'] == true) {
            $selects[] = 'shipping_address.address';
        }
        if(isset($keys['city']) && $keys['city'] == true) {
            $selects[] = DB::raw('city.name as city');
        }
        if(isset($keys['email']) && $keys['email'] == true) {
            $selects[] = DB::raw('email');
        }
        if(isset($keys['phone']) && $keys['phone'] == true) {
            $selects[] = DB::raw('users.phone_number as user_phone_number');
        }
        if(isset($keys['payment_method']) && $keys['payment_method'] == true) {
            $selects[] = 'orderdata.paymentmethod';
        }
        if(isset($keys['transaction_id']) && $keys['transaction_id'] == true) {
            $selects[] = 'orderdata.paymentid';
        }
        if(isset($keys['sub_total']) && $keys['sub_total'] == true) {
            $selects[] = DB::raw('group_concat(subtotaldata.price) as subtotal');
        }
        if(isset($keys['tax']) && $keys['tax'] == true) {
            $selects[] = DB::raw('group_concat(includetaxdata.price) as includetax');
        }
        if(isset($keys['door_step']) && $keys['door_step'] == true) {
            $selects[] = DB::raw('group_concat(door_step_amountsdata.price) as door_step_amounts');
        }
        // // if(isset($keys['cod_charges']) && $keys['cod_charges'] == true) {
        // //     $selects[] = DB::raw('group_concat(door_step_amountsdata.price) as door_step_amounts');
        // // }
        if(isset($keys['shipping_method']) && $keys['shipping_method'] == true) {
            $selects[] = 'orderdata.shippingMethod';
        }
        if(isset($keys['shipping']) && $keys['shipping'] == true) {
            $selects[] = DB::raw('group_concat(shippingsdata.price) as shippings');
        }
        // if(isset($keys['refund_amount']) && $keys['refund_amount'] == true) {
        //     $selects[] = DB::raw('group_concat(shippingsdata.price) as shippings');
        // }
        if(isset($keys['total']) && $keys['total'] == true) {
            $selects[] = DB::raw('group_concat(totaldata.price) as total');
        }
        // if(isset($keys['total_tax_amount']) && $keys['total_tax_amount'] == true) {
        //     $selects[] = DB::raw('group_concat(totaldata.price) as total');
        // }
        if(isset($keys['product_sku']) && $keys['product_sku'] == true) {
            $selects[] = DB::raw('productdata.sku as product_sku');
        }
        if(isset($keys['product_name']) && $keys['product_name'] == true) {
            $selects[] = DB::raw('order_detail.product_name as product_name');
        }
        if(isset($keys['quantity']) && $keys['quantity'] == true) {
            $selects[] = DB::raw('order_detail.quantity as product_quantity');
        }
        if(isset($keys['price']) && $keys['price'] == true) {
            $selects[] = DB::raw('order_detail.unit_price as product_price');
        }
        // if(isset($keys['product_condition']) && $keys['product_condition'] == true) {
        //     $selects[] = DB::raw('group_concat(productdata.product_condition) as product_condition');
        // }
        if(isset($keys['remaining_qty']) && $keys['remaining_qty'] == true) {
            $selects[] = DB::raw('group_concat(productdata.quantity) as remaining_qty');
        }
        if(isset($keys['coupon']) && $keys['coupon'] == true) {
            $selects[] = DB::raw('group_concat(discountsdata.name) as coupon_amounts');
        }

        // if($ordercheck == true){
            // $selectsData = ['paymentmethod', 'paymentid', 'shippingMethod', 'discountallowed', 'order.status', 'giftvoucherallowed', 'loyaltyFreeShipping', 'note', 'order.lang', 'erp_status', 'madac_id', 'userDevice', 'token', DB::raw('group_concat(subtotaldata.price) as subtotal'), DB::raw('group_concat(totaldata.price) as total'), DB::raw('group_concat(includetaxdata.price) as includetax'), DB::raw('group_concat(shippingsdata.price) as shippings'), DB::raw('group_concat(door_step_amountsdata.price) as door_step_amounts'), DB::raw('group_concat(discountsdata.price) as discounts'), DB::raw('group_concat(discount_rulesdata.price) as discount_rules')];
            // $selects = array_merge($selects, $selectsData);
        // }
        
        // if($customercheck == true){
        //     $selectsData = [DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"), 'email', DB::raw('users.phone_number as user_phone_number'), 'date_of_birth'];
        //     $selects = array_merge($selects, $selectsData);
        // }

        // if($shippingcheck == true){
        //     $selectsData = [DB::raw("CONCAT(shipping_address.first_name,' ',shipping_address.last_name) AS shipping_full_name"), DB::raw('shipping_address.phone_number as shipping_phone_number'), DB::raw('city.name as city'), 'zip', 'address', 'address_option'];
        //     $selects = array_merge($selects, $selectsData);
        // }

        // if($productcheck == true){
        //     $selectsData = [DB::raw('group_concat(order_detail.product_name) as product_name'), DB::raw('group_concat(productmedia.image) as product_image'), DB::raw('group_concat(order_detail.unit_price) as product_price'), DB::raw('group_concat(order_detail.quantity) as product_quantity'), DB::raw('group_concat(order_detail.total) as product_total')];
        //     $selects = array_merge($selects, $selectsData);
        // }


        $order = OrderDetail::select($selects)
        // ->when($ordercheck == true, function ($q) {
            ->leftJoin('order_summary as subtotaldata', function($join) {
                $join->on('order_detail.order_id', '=', 'subtotaldata.order_id');
                $join->on('subtotaldata.type', '=', DB::raw("'subtotal'"));
            })->leftJoin('order_summary as totaldata', function($join) {
                $join->on('totaldata.order_id', '=', 'order_detail.order_id');
                $join->on('totaldata.type', '=', DB::raw("'total'"));
            })->leftJoin('order_summary as includetaxdata', function($join) {
                $join->on('includetaxdata.order_id', '=', 'order_detail.order_id');
                $join->on('includetaxdata.type', '=', DB::raw("'include_tax'"));
            })->leftJoin('order_summary as shippingsdata', function($join) {
                $join->on('shippingsdata.order_id', '=', 'order_detail.order_id');
                $join->on('shippingsdata.type', '=', DB::raw("'shipping'"));
            })->leftJoin('order_summary as door_step_amountsdata', function($join) {
                $join->on('door_step_amountsdata.order_id', '=', 'order_detail.order_id');
                $join->on('door_step_amountsdata.type', '=', DB::raw("'door_step_amount'"));
            })
            ->leftJoin('order_summary as discountsdata', function($join) {
                $join->on('discountsdata.order_id', '=', 'order_detail.order_id');
                $join->on('discountsdata.type', '=', DB::raw("'discount'"));
            })
            // ->leftJoin(DB::raw("(select order_id, group_concat(price) as price from order_summary where type = 'discount_rule' group by order_id) discount_rulesdata"), function($join) {
            //     $join->on('discount_rulesdata.order_id', '=', 'order_detail.id');
            // })
        // })
        // ->when($customercheck == true, function ($q) {
            ->leftJoin('order as orderdata', function($join) {
                $join->on('order_detail.order_id', '=', 'orderdata.id');
            })
            ->leftJoin('users', function($join) {
                $join->on('users.id', '=', 'orderdata.customer_id');
            })
        // })
        // ->when($shippingcheck == true, function ($q) {
            ->leftJoin('shipping_address', function($join) {
                $join->on('orderdata.shipping_id', '=', 'shipping_address.id');
            })
            ->leftJoin('states as city', function($join) {
                $join->on('shipping_address.state_id', '=', 'city.id');
            })
        // })
        // ->when($productcheck == true, function ($q) {
            // return $q
            // ->leftJoin(DB::raw("(select order_id, group_concat(product_id) as product_id, group_concat(product_name) as product_name, group_concat(product_image) as product_image, group_concat(unit_price) as unit_price, group_concat(quantity) as quantity, group_concat(total) as total from order_detail group by order_id) order_detail"), function($join) {
            //     $join->on('order_detail.order_id', '=', 'order.id');
                
            // })
            ->leftJoin('products as productdata', function($join) {
                $join->on('order_detail.product_id', '=', 'productdata.id');
            })
            ->leftJoin('product_media as productmedia', function($join) {
                $join->on('order_detail.product_image', '=', 'productmedia.id');
            })
        // })
        ->when($fromdate != $todate, function ($q) use ($fromdate,$todate) {
            return $q->whereBetween('orderdata.created_at', [$fromdate, $todate]);
        })
        ->when($fromdate == $todate, function ($q) use ($fromdate,$todate) {
            return $q->whereDate('orderdata.created_at', $fromdate);
        })
        ->when($statusfilter, function ($q) use ($statusfilter) {
            return $q->whereIn('orderdata.status', $statusfilter);
        })
        ->when($paymentfilter, function ($q) use ($paymentfilter) {
            return $q->whereIn('orderdata.paymentmethod', $paymentfilter);
        })
        ->groupBy('order_detail.id')
        ->get();
        
        // Use transform() to change status values
        $order->transform(function ($item) use ($statusLabels) {
            if (isset($item['status']) && array_key_exists($item['status'], $statusLabels)) {
                $item['status'] = $statusLabels[$item['status']];
            } else {
                // Default status label if status is not found in statusLabels array
                $item['status'] = 'Unknown';
            }
            return $item;
        });


        return $order;
    }
    
    public function headings(): array
    {
        $keys = $this->r;
        // print_r($keys);die();
        
        $selects = [];
        $selects[] = 'order_no';
        if(isset($keys['status']) && $keys['status'] == 1){
            $selects[] = 'status';
        }
        if(isset($keys['created_at']) && $keys['created_at'] == 1){
            $selects[] = 'order_date';
        }
        if(isset($keys['customer_name']) && $keys['customer_name'] == 1){
            $selects[] = 'customer_name';
        }
        if(isset($keys['address']) && $keys['address'] == 1){
            $selects[] = 'shipping_address';
        }
        if(isset($keys['city']) && $keys['city'] == 1){
            $selects[] = 'city';
        }
        if(isset($keys['email']) && $keys['email'] == 1){
            $selects[] = 'email';
        }
        if(isset($keys['phone']) && $keys['phone'] == 1){
            $selects[] = 'phone';
        }
        if(isset($keys['payment_method']) && $keys['payment_method'] == 1){
            $selects[] = 'payment_method';
        }
        if(isset($keys['transaction_id']) && $keys['transaction_id'] == 1){
            $selects[] = 'transaction_id';
        }
        if(isset($keys['sub_total']) && $keys['sub_total'] == 1){
            $selects[] = 'sub_total';
        }
        if(isset($keys['tax']) && $keys['tax'] == 1){
            $selects[] = 'tax';
        }
        if(isset($keys['door_step']) && $keys['door_step'] == 1){
            $selects[] = 'door_step';
        }
        // if(isset($keys['cod_charges']) && $keys['cod_charges'] == 1){
        //     $selects[] = 'cod_charges';
        // }
        if(isset($keys['shipping_method']) && $keys['shipping_method'] == 1){
            $selects[] = 'shipping_method';
        }
        if(isset($keys['shipping']) && $keys['shipping'] == 1){
            $selects[] = 'shipping';
        }
        // if(isset($keys['refund_amount']) && $keys['refund_amount'] == 1){
        //     $selects[] = 'refund_amount';
        // }
        if(isset($keys['total']) && $keys['total'] == 1){
            $selects[] = 'total';
        }
        // if(isset($keys['total_tax_amount']) && $keys['total_tax_amount'] == 1){
        //     $selects[] = 'total_tax_amount';
        // }
        if(isset($keys['product_sku']) && $keys['product_sku'] == 1){
            $selects[] = 'product_sku';
        }
        if(isset($keys['product_name']) && $keys['product_name'] == 1){
            $selects[] = 'product_name';
        }
        if(isset($keys['quantity']) && $keys['quantity'] == 1){
            $selects[] = 'quantity';
        }
        if(isset($keys['price']) && $keys['price'] == 1){
            $selects[] = 'price';
        }
        // if(isset($keys['product_condition']) && $keys['product_condition'] == 1){
        //     $selects[] = 'product_condition';
        // }
        if(isset($keys['remaining_qty']) && $keys['remaining_qty'] == 1){
            $selects[] = 'remaining_qty';
        }
        if(isset($keys['coupon']) && $keys['coupon'] == 1){
            $selects[] = 'coupon';
        }



        // if(isset($keys['orderdata']) && $keys['orderdata'] == 1){
        //     $selectsData = [
        //         'paymentmethod',
        //         'paymentid',
        //         'shippingMethod',
        //         'discountallowed',
        //         'status',
        //         'giftvoucherallowed',
        //         'loyaltyFreeShipping',
        //         'note',
        //         'lang',
        //         'erp_status',
        //         'madac_id',
        //         'userDevice',
        //         'token',
        //         'subtotal',
        //         'total',
        //         'includetax',
        //         'shipping',
        //         'door_step_amount',
        //         'discount',
        //         'discount_rule',
        //     ];
        //     $selects = array_merge($selects, $selectsData);
        // }
        // if(isset($keys['customerdata']) && $keys['customerdata'] == 1){
        //     $selectsData = [
        //         'Full Name',
        //         'email',
        //         'phone_number',
        //         'dob'
        //     ];
        //     $selects = array_merge($selects, $selectsData);
        // }
        // if(isset($keys['shippingdata']) && $keys['shippingdata'] == 1){
        //     $selectsData = [
        //         'Shipping Full Name',
        //         'Shipping phone_number',
        //         'City',
        //         'zip',
        //         'address',
        //         'address_option',
        //     ];
        //     $selects = array_merge($selects, $selectsData);
        // }
        // if(isset($keys['productdata']) && $keys['productdata'] == 1){
        //     $selectsData = [
        //         'Product Name',
        //         'Product Image',
        //         'Product Price',
        //         'Product quantity',
        //         'Product total',
        //     ];
        //     $selects = array_merge($selects, $selectsData);
        // }
        
        return $selects;
        
    }
}
