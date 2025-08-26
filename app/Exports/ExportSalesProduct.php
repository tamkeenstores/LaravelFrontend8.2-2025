<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Product;
use DB;

class ExportSalesProduct implements FromCollection, WithHeadings
{
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
        
        $selectedProducts = count($this->r['products']) >= 1 ? $this->r['products'] : false;
        $selects = [
            'products.sku', 'products.name',  
            DB::raw('sum(order_detail.quantity) as s_qty'), 
            'products.sale_price as selling_pro_price',
            'products.price as pro_price',
            DB::raw('sum(products.sale_price) as t_revenue'),
        ];
        
        $topsellingproduct = Product::select($selects)
        ->groupBy('products.id')
        ->where('total', '>', 1)
        ->when($selectedProducts, function ($q) use ($selectedProducts) {
            return $q->whereIn('products.id', $selectedProducts);
        })
        ->leftJoin('order_detail', function($join) {
            $join->on('products.id', '=', 'order_detail.product_id');
        })
        ->when($single, function ($q) use ($single, $to) {
            return $q->whereDate('order_detail.created_at', $to);
        })
        ->when($single == false, function ($q) use ($single, $to) {
            return $q->whereBetween('order_detail.created_at', [$to, $this->r['from_date']]);
        }) 
        ->orderBy('s_qty', 'desc')
        ->having('s_qty', '>=', 1)
        ->get();
        return $topsellingproduct;
    }
    
    public function headings(): array
    {
        return [
            'Model No',
            'Title',
            'Qty Sold',
            'Sale Price',
            'Regular Price',
            'Total Sales Value',
        ];
    }
}