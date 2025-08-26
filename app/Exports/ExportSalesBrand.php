<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportSalesBrand implements FromCollection, WithHeadings
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
        
        $selectedBrands = count($this->r['brands']) >= 1 ? $this->r['brands'] : false;
        $selects = [
            'brands.name as name',
            DB::raw('ROUND(sum(order_detail.quantity)) as qty'),
            DB::raw('ROUND(sum(order_detail.total)) as sales'),
        ];

        $topsellingbrands = Brand::
        select($selects)
        ->groupBy('brands.id')
        ->where('total', '>', 1)
        ->when($selectedBrands, function ($q) use ($selectedBrands) {
            return $q->whereIn('brands.id', $selectedBrands);
        })
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('brands.id', '=', 'sellingpro.brands');
        })
        ->leftJoin('order_detail', function($join) {
            $join->on('sellingpro.id', '=', 'order_detail.product_id');
        })
        ->where('brands.status', 1)
        ->orderBy('sales', 'DESC')
        ->when($single, function ($q) use ($single, $to) {
            return $q->whereDate('order_detail.created_at', $to);
        })
        ->when($single == false, function ($q) use ($single, $to) {
            return $q->whereBetween('order_detail.created_at', [$to, $this->r['from_date']]);
        }) 
        // ->whereBetween('order_detail.created_at', [$this->r['to_date'], $this->r['from_date']])
        ->get();

        return $topsellingbrands;
    }
    
    public function headings(): array
    {
        return [
            'Brand',
            'Qty Sold',
            'Total Sales Value'
        ];
    }
}

?>