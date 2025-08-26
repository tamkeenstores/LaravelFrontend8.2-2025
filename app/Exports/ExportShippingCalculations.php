<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Order;
use App\Models\OrderSummary;
use DB;

class ExportShippingCalculations implements FromCollection, WithHeadings
{
    public $r = [];
    
    public function __construct($request)
    {
        $this->r = $request;
    }
    
    public function collection()
    {   
        $body = [];
        // from month
        $timestamp = strtotime($this->r['from_date']);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);
        
        // to month
        $to_timestamp = strtotime($this->r['to_date']);
        $to_month = date('m', $to_timestamp);
        $to_year = date('Y', $to_timestamp);
        $currentmonth = $month;
        
        $selects = [
            'states.name as city',
        ];
        for ($i= $to_month - 1; $i < $currentmonth; $i++) { 
            $selects[] =  DB::raw('CONVERT(ROUND(SUM(IF(MONTH(order.created_at) = '.($i+1).', totalorder.price, 0))), char) as '.($i+1).'_total');
        }
        $cityChart = Order::select($selects)
        ->Join('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'shipping'"));
        })
        ->Join('shipping_address', function($join) {
            $join->on('order.shipping_id', '=', 'shipping_address.id');
        })
        ->Join('states', function($join) {
            $join->on('states.id', '=', 'shipping_address.state_id');
        })
        ->Join(DB::raw("(select sum(quantity) as qty, order_id from `order` join order_detail on order.id = order_detail.order_id group by order.id) totalqty"), function($join) {
            $join->on('order.id', '=', 'totalqty.order_id');
        })
        ->groupBy('states.id')
        ->where( DB::raw('YEAR(totalorder.created_at)'), '=', $year )
        ->get();

        return $cityChart;
    }
    
    public function headings(): array
    {
        $head = [
            'Cities (SR)',
        ];
        $timestamp = strtotime($this->r['from_date']);
        $month = date('m', $timestamp);
        $to_timestamp = strtotime($this->r['to_date']);
        $to_month = date('m', $to_timestamp);
        for ($i= $to_month - 1; $i < $month; $i++) {
            $head[] = date("F", mktime(0, 0, 0, $i+1, 10));
        }
        return $head;
    }
}

?>