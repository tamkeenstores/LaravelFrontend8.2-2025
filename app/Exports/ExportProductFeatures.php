<?php

namespace App\Exports;

use App\Models\ProductFeatures;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\ProductTag;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportProductFeatures implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $r = [];
    
    public function __construct($data)
    {
        //print_r($data);die();
        $this->r = $data;
        // print_r($this->r);die();
    }
    
    public function collection()
    {
        $instock = isset($this->r['in_stock']) == true ? 1 : 0;
        $enabled = isset($this->r['enabled']) == true ? 1 : 0;
        $pro = isset($this->r['filter_pro']) ? $this->r['filter_pro'] : null;
        $brands = isset($this->r['filter_brands']) ? $this->r['filter_brands'] : null;
        $cats = isset($this->r['filter_cats']) ? $this->r['filter_cats'] : [];
        $Productcategory = CategoryProduct::whereIn('category_id', $cats)->pluck('product_id')->toArray();
        $tags = isset($this->r['filter_tags']) ? $this->r['filter_tags'] : [];
        $tagsdata = ProductTag::whereIn('sub_tag_id', $tags)->pluck('product_id')->toArray();
        
        $products = Product::select(['sku'])
        ->addSelect(
        [
            'feature_en' => ProductFeatures::selectRaw(DB::raw('group_concat(feature_en) as feature_en'))
            ->whereColumn('products_key_features.product_id', 'products.id'),
            'feature_ar' => ProductFeatures::selectRaw(DB::raw('group_concat(feature_ar) as feature_ar'))
            ->whereColumn('products_key_features.product_id', 'products.id'),
            'feature_image_link' => ProductFeatures::selectRaw(DB::raw('group_concat(feature_image_link) as feature_image_link'))
            ->whereColumn('products_key_features.product_id', 'products.id')
        ]
        )
        ->when($pro, function ($q) use ($pro) {
            return $q->whereIn('id', $pro);
        })
        ->when($brands, function ($q) use ($brands) {
            return $q->whereIn('brands', $brands);
        })
        ->when($Productcategory, function ($q) use ($Productcategory) {
            return $q->whereIn('id',$Productcategory);
        })
        ->when($tagsdata, function ($q) use ($tagsdata) {
            return $q->whereIn('id',$tagsdata);
        })
        ->get();
        //print_r($products->toArray());die;
        return $products;
        // return ProductFeatures::select(['id', 'product_id', 'feature_en', 'feature_ar', 'feature_image_link'])
        // ->addSelect(['product_id' => Product::selectRaw(DB::raw('group_concat(sku) as sku'))
        //     ->whereColumn('products_key_features.product_id', 'products.id')
        // ])
        // ->get();
    }
    
    public function headings(): array
    {
        return [
            // 'id',
            'sku',
            'feature_en',
            'feature_ar',
            'feature_image_link',
        ];
    }
}
