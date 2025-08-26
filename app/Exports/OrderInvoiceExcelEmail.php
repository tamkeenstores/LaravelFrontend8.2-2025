<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderInvoiceExcelEmail implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }
    
    
    public function collection()
    {
        
        $data = [];
        foreach ($this->orders as $order) {
            foreach ($order->details as $detailData) {
                $product_ids = OrderDetail::where('order_id', $order->id)->pluck('product_id')->toArray();
                $product_skus = Product::where('id', $detailData->product_id)->first();
                $data[] = [
                    'ordernumber' => $order->order_no,
                    'order_date' => $order->created_at,
                    'customer_name' => $order->UserDetail ? $order->UserDetail->first_name . ' ' . $order->UserDetail->last_name : null,
                    'city' => isset($order->Address->stateData->name) ? $order->Address->stateData->name : null,
                    'address' => $order->Address ? $order->Address->address : null,
                    'customer_phone' => $order->UserDetail ? $order->UserDetail->phone_number : null,
                    'product_skus' => $product_skus ? $product_skus->sku : null,
                    'product_qty' => $detailData->quantity,
                ];
            }
        }

        return collect($data);
    }
    
    public function headings(): array
    {
        return [
            'ordernumber',
            'order_date',
            'customer_name',
            'city',
            'address',
            'customer_phone',
            'product_sku',
            'product_qty',
        ];
    }
}
