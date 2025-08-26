<?php

namespace App\Exports;

use App\Models\NotifyProduct;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportNotifyProduct implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'product_id')   
            $body[] = $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
        }
        
        $selects = [];
        if($this->r['product_id'])
        $selects['product_id'] = Product::selectRaw(DB::raw('group_concat(sku) as product_id'))
            ->whereColumn('notify_product.product_id', 'products.id');
        
        $sub = isset($this->r['product_id']) && $this->r['product_id'] == 1 ? true : false;
        $notify = NotifyProduct::select($body)->addSelect($selects)
        ->when($sub, function ($q) {
            return $q->with('productData');
        })
        ->get();
        // else
        // $tag = Tag::with('childs')->select(explode(',', implode(',', $body)))->get();
        return $notify;
    }
    
    public function headings(): array
    {
        // return [
        //     'id',
        //     'name',
        //     'name_arabic',
        //     'sorting',
        //     'sub_tags',
        //     'status'
        // ];
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1)   
            $body[] = $key;
        }
        //print_r($body);die();
        return $body;
    }
}
