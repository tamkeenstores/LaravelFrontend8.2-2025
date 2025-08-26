<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use DB;

class ExportErpOrder implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $r = [];
    
    public function __construct($data)
    {
        $this->r = $data;
    }
    
    public function collection()
    {
        $keys = $this->r;
        
        $fromdate = isset($keys['fromdate']) ? $keys['fromdate'] : null;
        $todate = isset($keys['todate']) ? $keys['todate'] : null;
        
        $order = Order::whereNotIn('status',['5','7','8'])->select(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y') as order_date"), DB::raw("DATE_FORMAT(created_at, '%H:%i:%s') as order_time"),
        'order_no',
        DB::raw("CASE 
            WHEN status = 0 THEN 'Order Recieved'
            WHEN status = 1 THEN 'Order Confirmed'
            WHEN status = 2 THEN 'Processing'
            WHEN status = 3 THEN 'Out for Delivery'
            WHEN status = 4 THEN 'Delivered'
            WHEN status = 5 THEN 'Cancel'
            WHEN status = 6 THEN 'Refund'
            WHEN status = 7 THEN 'Failed'
            WHEN status = 8 THEN 'Pending Payment'
            ELSE 'Unknown'
        END as status"),
        DB::raw("CASE 
            WHEN erp_status = 0 THEN 'Not Fetch'
            WHEN erp_status = 1 THEN 'Fetch'
            ELSE 'Unknown'
        END as erp_status"),
        'erp_fetch_date', 'erp_fetch_time')
        ->when($fromdate, function ($q) use ($fromdate,$todate) {
            return $q->whereBetween('created_at', [$fromdate, $todate]);
        })
        ->get();
        
        return $order;
    }
    
    public function headings(): array
    {
        return [
            'order_date',
            'order_time',
            'order_no',
            'status',
            'erp_status',
            'erp_fetch_date',
            'erp_fetch_time'
        ];
    }
}
