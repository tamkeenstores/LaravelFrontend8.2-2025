<?php

namespace App\Exports;

use App\Models\Productcategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportSalesCategory implements FromCollection, WithHeadings
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
        
        $selectedCategories = count($this->r['categories']) >= 1 ? $this->r['categories'] : false;
        $selects = [
            'productcategories.name as name',
            DB::raw('ROUND(sum(order_detail.quantity)) as qty'),
            DB::raw('ROUND(sum(order_detail.total)) as sales'),
        ];

        $topsellingcats = Productcategory::
        select($selects)
        ->when($selectedCategories, function ($q) use ($selectedCategories) {
            return $q->whereIn('productcategories.id', $selectedCategories);
        })
        ->leftJoin('product_categories', function($join) {
            $join->on('product_categories.category_id', '=', 'productcategories.id');
        })
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('product_categories.product_id', '=', 'sellingpro.id');
        })
        ->leftJoin('order_detail', function($join) {
            $join->on('order_detail.product_id', '=', 'sellingpro.id');
        })
        ->groupBy('productcategories.id')
        ->where('productcategories.menu', 1)
        ->where('productcategories.status', 1)
        ->when($single, function ($q) use ($single, $to) {
            return $q->whereDate('order_detail.created_at', $to);
        })
        ->when($single == false, function ($q) use ($single, $to) {
            return $q->whereBetween('order_detail.created_at', [$to, $this->r['from_date']]);
        }) 
        // ->whereBetween('order_detail.created_at', [$this->r['to_date'], $this->r['from_date']])
        ->get();
        
        return $topsellingcats;
    }
    
    public function headings(): array
    {
        return [
            'Category',
            'Qty Sold',
            'Total Sales Value',
        ];
    }
}