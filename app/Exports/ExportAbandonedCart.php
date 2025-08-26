<?php

namespace App\Exports;

use App\Models\AbandonedCart;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportAbandonedCart implements FromCollection, WithHeadings
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
        $change = '"';
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1 && $key == 'id')
            $body[] = 'abandoned_cart.id as id';
            if($r == 1 && $key == 'created_at')
            $body[] = DB::raw("DATE_FORMAT(abandoned_cart.created_at, '%d-%m-%Y %H:%i') AS date");
            if($r == 1 && $key == 'sku')
            $body[] = DB::raw("REPLACE(REPLACE(REPLACE(GROUP_CONCAT(JSON_EXTRACT(abandoned_cart.cartdata, '$.products[*].sku') SEPARATOR ','), '[', ''), ']', ''), '".$change."','') AS sku");
            if($r == 1 && $key == 'user_id')
            $body[] = DB::raw("(SELECT email FROM users WHERE abandoned_cart.user_id = users.id) AS customer_details");
            if($r == 1 && $key == 'firstemail')
            $body[] = DB::raw('CONVERT(abandoned_cart.firstemail,char) as firstemail');
        }
        
        return AbandonedCart::select($body)
        ->groupBy('abandoned_cart.id')
        ->get();
    }
    
    public function headings(): array
    {
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1)   
            $body[] = $key;
        }
        
        return $body;
    }
}
