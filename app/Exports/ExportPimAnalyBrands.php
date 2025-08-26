<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportPimAnalyBrands implements FromCollection, WithHeadings
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
            'brands.name as name',
            DB::raw('CONVERT(brands.clicks, char) as clicks'),
            DB::raw('CONVERT(brands.status, char) as status'),
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

        $topsellingbrands = Brand::
        select($selects)
        ->groupBy('brands.id')
        ->where('total', '>', 1)
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('brands.id', '=', 'sellingpro.brands');
        })
        ->leftJoin('order_detail', function($join) {
            $join->on('sellingpro.id', '=', 'order_detail.product_id');
        })
        ->where('brands.status', 1)
        ->orderBy('sales', 'DESC')
        ->where( DB::raw('YEAR(order_detail.created_at)'), '=', $this->r )
        ->get();

        return $topsellingbrands;
    }
    
    public function headings(): array
    {
        return [
            'Brands',
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
