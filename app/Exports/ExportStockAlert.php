<?php

namespace App\Exports;

use App\Models\StockAlert;
use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportStockAlert implements FromCollection, WithHeadings
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
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1 && $key != 'product_id' && $key != 'status' && $key != 'user_id')   
            $body[] = $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
        }
        $selects = [];
        if($this->r['product_id'])
        $selects['product_id'] = Product::selectRaw(DB::raw('group_concat(sku) as product'))
            ->whereColumn('stock_alert.product_id', 'products.id');
        if($this->r['user_id'])
        $selects['user_id'] = User::selectRaw(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name"))
            ->whereColumn('stock_alert.user_id', 'users.id');
        
        $sub = isset($this->r['product']) && $this->r['product'] == 1 ? true : false;
        $pricealert = StockAlert::select($body)->addSelect($selects)
        ->when($sub, function ($q) {
            return $q->with('productData');
        })
        ->get();
        
        return $pricealert;
    }
    
    public function headings(): array
    {
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1)   
            $body[] = $key == 'product' ? 'product sku' : $key;
        }
        return $body;
    }
}
