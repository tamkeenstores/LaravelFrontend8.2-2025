<?php

namespace App\Exports;

use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportPriceAlert implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'product_id' && $key != 'status' && $key != 'user_email' && $key != 'user_phone')   
            $body[] = $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
        }
        $selects = [];
        if($this->r['product_id'])
        $selects['product_id'] = Product::selectRaw(DB::raw('group_concat(sku) as product'))
            ->whereColumn('price_alert.product_id', 'products.id');
        // if($this->r['user_email'])
        $selects['user_email'] = User::selectRaw(DB::raw('group_concat(email) as user_email'))
            ->whereColumn('price_alert.user_id', 'users.id');
        $selects['user_phone'] = User::selectRaw(DB::raw('group_concat(phone_number) as user_phone'))
            ->whereColumn('price_alert.user_id', 'users.id');
            
        
        $sub = isset($this->r['product']) && $this->r['product'] == 1 ? true : false;
        $pricealert = PriceAlert::select($body)->addSelect($selects)
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
            $body[] = $key == 'product_id' ? 'product_sku' : $key;
        }
        return $body;
    }
}
