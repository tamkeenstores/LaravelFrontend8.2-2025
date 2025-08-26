<?php

namespace App\Exports;

use App\Models\ProductSpecifications;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\ProductTag;
use App\Models\ProductSpecsDetails;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportProductSpecs implements FromCollection, WithHeadings
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
        
        $products = Product::select(['sku', 'specdetails.specs_en as specs_en', 'specdetails.value_en as value_en','specdetails.specs_ar as specs_ar',
        'specdetails.value_ar as value_ar'])
        ->addSelect(
        [
            'heading_en' => ProductSpecifications::selectRaw(DB::raw('group_concat(heading_en) as heading_en'))
            ->whereColumn('products_specifications.product_id', 'products.id'),
            'heading_ar' => ProductSpecifications::selectRaw(DB::raw('group_concat(heading_ar) as heading_ar'))
            ->whereColumn('products_specifications.product_id', 'products.id'),
            // 'specs_en' => ProductSpecsDetails::selectRaw(DB::raw('group_concat(specs_en) as specs_en'))
            // ->whereColumn('product_specs_details.specs_id', 'products_specifications.id'),
            // 'value_en' => ProductSpecsDetails::selectRaw(DB::raw('group_concat(value_en) as value_en'))
            // ->whereColumn('product_specs_details.specs_id', 'products_specifications.id'),
            // 'specs_ar' => ProductSpecsDetails::selectRaw(DB::raw('group_concat(specs_ar) as specs_ar'))
            // ->whereColumn('product_specs_details.specs_id', 'products_specifications.id'),
            // 'value_ar' => ProductSpecsDetails::selectRaw(DB::raw('group_concat(value_ar) as value_ar'))
            // ->whereColumn('product_specs_details.specs_id', 'products_specifications.id')
        ]
        )
        ->leftJoin('products_specifications as prospec', function($join) {
            $join->on('products.id', '=', 'prospec.product_id');
        })
        ->leftJoin('product_specs_details as specdetails', function($join) {
            $join->on('prospec.id', '=', 'specdetails.specs_id');
        })
        ->when($pro, function ($q) use ($pro) {
            return $q->whereIn('products.id', $pro);
        })
        ->when($brands, function ($q) use ($brands) {
            return $q->whereIn('brands', $brands);
        })
        ->when($Productcategory, function ($q) use ($Productcategory) {
            return $q->whereIn('products.id',$Productcategory);
        })
        ->when($tagsdata, function ($q) use ($tagsdata) {
            return $q->whereIn('products.id',$tagsdata);
        })
        ->get();

        return $products;
        
        // add select old code
        // 'specs_en' => ProductSpecifications::selectRaw(DB::raw('group_concat(specs_en) as specs_en'))
        //     ->whereColumn('products_specifications.product_id', 'products.id'),
        //     'value_en' => ProductSpecifications::selectRaw(DB::raw('group_concat(value_en) as value_en'))
        //     ->whereColumn('products_specifications.product_id', 'products.id'),
        //     'specs_ar' => ProductSpecifications::selectRaw(DB::raw('group_concat(specs_ar) as specs_ar'))
        //     ->whereColumn('products_specifications.product_id', 'products.id'),
        //     'value_ar' => ProductSpecifications::selectRaw(DB::raw('group_concat(value_ar) as value_ar'))
        //     ->whereColumn('products_specifications.product_id', 'products.id')
        
    }
    
    public function headings(): array
    {
        return [
            'sku',
            'heading_en',
            'heading_ar',
            'specs_en', 
            'value_en',
            'specs_ar',
            'value_ar'
        ];
    }
}
