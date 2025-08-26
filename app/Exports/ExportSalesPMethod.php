<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportSalesPMethod implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $r = [];
    
    public function __construct($request)
    {
        $this->r = $request;
    }
    
    public function collection()
    {
        $body = [];
        $single = false;
        $to = $this->r['to_date'];
        if($this->r['to_date'] == $this->r['from_date']) {
            $single = true;
        }
        
        $selectedPMethod = count($this->r['pmethod']) >= 1 ? $this->r['pmethod'] : false;
        $selects = [
            'order.paymentmethod as name',
            DB::raw('ROUND(sum(order_detail.quantity)) as qty'),
            DB::raw('ROUND(sum(order_detail.total)) as sales'),
        ];

        $topsellingmethods = Order::
        select($selects)
        ->when($selectedPMethod, function ($q) use ($selectedPMethod) {
            return $q->whereIn('order.paymentmethod', $selectedPMethod);
        })
        ->leftJoin('order_detail', function($join) {
            $join->on('order_detail.order_id', '=', 'order.id');
        })
        ->where('order.paymentmethod', '!=', null)
        ->groupBy('order.paymentmethod')
        ->when($single, function ($q) use ($single, $to) {
            return $q->whereDate('order.created_at', $to);
        })
        ->when($single == false, function ($q) use ($single, $to) {
            return $q->whereBetween('order.created_at', [$to, $this->r['from_date']]);
        }) 
        // ->whereBetween('order.created_at', [$this->r['to_date'], $this->r['from_date']])
        ->get();
        
        return $topsellingmethods;
    }
    
    public function headings(): array
    {
        return [
            'Payment Method',
            'Qty Sold',
            'Total Sales Value',
        ];
    }
}