<?php

namespace App\Exports;

use App\Models\PriceAlert;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Order;
use DB;

class ExportSalesCities implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $r = [];
    
    public function __construct($date)
    {
        $this->r = $date;
    }
    
    public function collection()
    {
        // print_r($this->r);die();
        $body = [];
        // foreach ($this->r as $key => $r) {
        //     if($r == 1 && $key != 'product_id')   
        //     $body[] = $key;
        // }
        $currentmonth = date('m');
        if($this->r < date('Y')){
            $currentmonth = 12;
        }
        // $currentmonth = 10;
        $selects = [
            'states.name as city',
            DB::raw('SUM(totalqty.qty) as totalqty'),
            DB::raw('ROUND(sum(totalorder.price)) as totalamount'), 
        ];
        for ($i=0; $i < $currentmonth; $i++) { 
            $selects[] =  DB::raw('CONVERT(ROUND(SUM(IF(MONTH(order.created_at) = '.($i+1).', totalorder.price, 0))), char) as '.($i+1).'_total');
        }
        if ($currentmonth < 12) {
            for ($i=$currentmonth; $i < 12; $i++) { 
                $selects[] =  DB::raw('round(sum(totalorder.price) / '.$currentmonth.') as '.($i+1).'_total');
            }
        }

        $cityChart = Order::select($selects)
        ->Join('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
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
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        // ->whereNotNull('states.name')
        ->orderBy('totalamount', 'DESC')
        ->groupBy('states.id')
        ->where( DB::raw('YEAR(totalorder.created_at)'), '=', $this->r )
        ->get();

        return $cityChart;
    }
    
    public function headings(): array
    {
        return [
            'Cities',
            'S.Qty',
            'T.Revenue',
            'Jan (SR)',
            'Feb (SR)',
            'Mar (SR)',
            'Apr (SR)',
            'May (SR)',
            'Jun (SR)',
            'Jul (SR)',
            'Aug (SR)',
            'Sep (SR)',
            'Oct (SR)',
            'Nov (SR)',
            'Dec (SR)',
        ];
    }
}