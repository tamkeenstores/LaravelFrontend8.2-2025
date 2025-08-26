<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Brand;
use App\Models\ProductMedia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportBrand implements FromCollection, WithHeadings
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
        $body = [];
        foreach ($this->r as $key => $r) {
            // print_r($key);die();
            if($r == 1 && $key != 'tproducts' && $key != 'brand_image_media' && $key != 'brand_app_image_media' && $key != 'link' && $key != 'category')   
            $body[] = 'brands.'.$key;
            if($r == 1 && $key == 'category')
            $body[] = DB::raw('group_concat(DISTINCT cats.name) as category');
            // if($r == 1 && $key == 'sub_tags')   
            // $body[] = DB::raw('group_concat(childs.name) as subtag');
        }
        
        $selects = [];
        if($this->r['brand_image_media'])
        $selects['brand_image_media'] = ProductMedia::selectRaw(DB::raw("CONCAT('https://react.tamkeenstores.com.sa/assets/new-media/','',image) AS brand_image_media"))->whereColumn('brands.brand_image_media', 'product_media.id');
        if($this->r['brand_app_image_media'])
        $selects['brand_app_image_media'] = ProductMedia::selectRaw(DB::raw("CONCAT('https://react.tamkeenstores.com.sa/assets/new-media/','',image) AS brand_app_image_media"))->whereColumn('brands.brand_app_image_media', 'product_media.id');
        if($this->r['link'])
        $selects['link'] = DB::raw("CONCAT('https://tamkeenstores.com.sa/en/brand/',' ',brands.slug) AS link");
        
        $tpro = isset($this->r['tproducts']) && $this->r['tproducts'] == 1 ? true : false;
        $brands = Brand::select($body)
        ->addSelect($selects)
         ->when($tpro, function ($q) {
            return $q->withCount('productname');
        })
        ->leftJoin('brand_category as brandcategory', function($join) {
            $join->on('brands.id', '=', 'brandcategory.brand_id');
        })
        ->leftJoin('productcategories as cats', function($join) {
            $join->on('brandcategory.category_id', '=', 'cats.id');
        })
        ->groupBy('brands.id')
        ->get();
         
         return $brands;
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
