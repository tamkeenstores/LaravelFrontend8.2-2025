<?php

namespace App\Exports;

use App\Models\ProductPriceHistory;
use App\Models\ProductStockHistory;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\ProductTag;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportProductHistory implements FromCollection, WithHeadings
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
        $instock = isset($this->r['instockdata']) == true ? 1 : 0;
        $enabled = isset($this->r['enableddata']) == true ? 1 : 0;
        $pro = isset($this->r['filter_pro']) ? $this->r['filter_pro'] : null;
        $brands = isset($this->r['filter_brands']) ? $this->r['filter_brands'] : null;
        $cats = isset($this->r['filter_cats']) ? $this->r['filter_cats'] : [];
        $Productcategory = CategoryProduct::whereIn('category_id', $cats)->pluck('product_id')->toArray();
        $tags = isset($this->r['filter_tags']) ? $this->r['filter_tags'] : [];
        $tagsdata = ProductTag::whereIn('sub_tag_id', $tags)->pluck('product_id')->toArray();
        
        $selects = [
            'products.sku',
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 1), ',', -1) AS sale_1st"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 2), ',', -1) AS sale_2nd"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 3), ',', -1) AS sale_3rd"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 4), ',', -1) AS sale_4th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 5), ',', -1) AS sale_5th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 6), ',', -1) AS sale_6th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 7), ',', -1) AS sale_7th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 8), ',', -1) AS sale_8th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 9), ',', -1) AS sale_9th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(s_price, ',,,,'), ',', 10), ',', -1) AS sale_10th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 1), ',', -1) AS qty_1st"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 2), ',', -1) AS qty_2nd"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 3), ',', -1) AS qty_3rd"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 4), ',', -1) AS qty_4th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 5), ',', -1) AS qty_5th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 6), ',', -1) AS qty_6th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 7), ',', -1) AS qty_7th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 8), ',', -1) AS qty_8th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 9), ',', -1) AS qty_9th"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(concat(quan, ',,,,'), ',', 10), ',', -1) AS qty_10th"),
            
        ];
        
        $products = Product::select($selects)
        ->join(DB::raw("(select group_concat(sale_price) as s_price,product_id from product_price_history group by product_id) product_price_history"), function($join) {
            $join->on('product_price_history.product_id', '=', 'products.id');
        })
        ->join(DB::raw("(select group_concat(qty) as quan,product_id from product_stock_history group by product_id) product_stock_history"), function($join) {
            $join->on('product_stock_history.product_id', '=', 'products.id');
        })
        ->when($pro, function ($q) use ($pro) {
            return $q->whereIn('products.id', $pro);
        })
        ->when($brands, function ($q) use ($brands) {
            return $q->whereIn('products.brands', $brands);
        })
        ->when($Productcategory, function ($q) use ($Productcategory) {
            return $q->whereIn('products.id',$Productcategory);
        })
        ->when($tagsdata, function ($q) use ($tagsdata) {
            return $q->whereIn('products.id',$tagsdata);
        })
        ->when($instock !== 0, function ($q) use ($instock) {
            return $q->where('products.quantity', '>', '0');
        })
        ->when($enabled !== 0, function ($q) use ($enabled) {
            return $q->where('products.status', $enabled);
        })
        ->groupBy('products.id')
        ->get();

        return $products;
    }
    
    public function headings(): array
    {
        return [
            'sku',
            'sale_1st',
            'sale_2nd',
            'sale_3rd',
            'sale_4th',
            'sale_5th',
            'sale_6th',
            'sale_7th',
            'sale_8th',
            'sale_9th',
            'sale_10th',
            'qty_1st',
            'qty_2nd',
            'qty_3rd',
            'qty_4th',
            'qty_5th',
            'qty_6th',
            'qty_7th',
            'qty_8th',
            'qty_9th',
            'qty_10th'
        ];
    }
}