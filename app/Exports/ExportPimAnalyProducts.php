<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Product;
use DB;

class ExportPimAnalyProducts implements FromCollection, WithHeadings
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
            'products.name', 'products.sku', 
            DB::raw('sum(products.sale_price) as t_revenue'), 
            'products.sale_price as selling_pro_price', 
            DB::raw('sum(order_detail.quantity) as s_qty'), 
            DB::raw('CONVERT(products.quantity, char) as r_qty')
        ];
        for ($i=0; $i < $currentmonth; $i++) { 
            $selects[] =  DB::raw('CONVERT(SUM(IF(MONTH(order_detail.created_at) = '.($i+1).', order_detail.quantity, 0)), char) as '.($i+1).'_qty');
        }
        if ($currentmonth < 12) {
            for ($i=$currentmonth; $i < 12; $i++) { 
                $selects[] =  DB::raw('round(sum(order_detail.quantity) / '.$currentmonth.') as '.($i+1).'_qty');
            }
        }
        
        // top 10 selling products
        $topsellingproduct = Product::select($selects)
        ->groupBy('products.id')
        ->where('total', '>', 1)
        ->leftJoin('order_detail', function($join) {
            $join->on('products.id', '=', 'order_detail.product_id');
        })
        ->where( DB::raw('YEAR(order_detail.created_at)'), '=', $this->r )
        ->orderBy('s_qty', 'desc')
        ->having('s_qty', '>=', 1)
        ->get();
        return $topsellingproduct;
    }
    
    public function headings(): array
    {
        return [
            'Products',
            'SKU',
            'T.Revenue',
            'S.Price',
            'S.Qty',
            'R.Qty',
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ];
    }
}
