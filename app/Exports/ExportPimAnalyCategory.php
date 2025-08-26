<?php

namespace App\Exports;

use App\Models\Productcategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportPimAnalyCategory implements FromCollection, WithHeadings
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
        $currentmonth = date('m');
        if($this->r < date('Y')){
            $currentmonth = 12;
        }

        $selects = [
            'productcategories.name as name',
            DB::raw('CONVERT(productcategories.clicks, char) as clicks'),
            DB::raw('CONVERT(productcategories.status, char) as status'),
            DB::raw('ROUND(sum(order_detail.total)) as sales'),
        ];

        for ($i=0; $i < $currentmonth; $i++) { 
            $selects[] =  DB::raw('CONVERT(SUM(IF(MONTH(order_detail.created_at) = '.($i+1).', order_detail.total, 0)), char) as '.($i+1).'_totalamount');
        }
        if ($currentmonth < 12) {
            for ($i=$currentmonth; $i < 12; $i++) { 
                $selects[] =  DB::raw('round(sum(order_detail.total) / '.$currentmonth.') as '.($i+1).'_totalamount');
            }
        }

        $topsellingcats = Productcategory::
        select($selects)
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
        ->where( DB::raw('YEAR(order_detail.created_at)'), '=', $this->r )
        ->get();
        
        return $topsellingcats;
    }
    
    public function headings(): array
    {
        return [
            'Categories',
            'Clicks',
            'Status',
            'Sales (SR)',
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