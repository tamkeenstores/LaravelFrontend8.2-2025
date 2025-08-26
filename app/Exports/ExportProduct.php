<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\ShippingClasses;
use App\Models\ProductTag;
use App\Models\Brand;
use App\Models\ProductMedia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportProduct implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'view_product' && $key != 'tag' && $key != 'category' && $key != 'parent_category' && $key != 'sub_category' && $key != 'upsale_product' && $key != 'gallery' && $key != 'image_title' && $key != 'specification' && $key != 'key_feature' && $key != 'enableddata' && $key != 'instockdata' && $key != 'relatedbrand' && $key != 'relatedcategory' )   
            $body[] = 'products.'.$key;
            if($r == 1 && $key == 'tag')
            $body[] = DB::raw('group_concat(DISTINCT subtags.name) as subtag');
            if($r == 1 && $key == 'category')
            $body[] = DB::raw('group_concat(DISTINCT productcatego.name ORDER BY productcatego.id ASC) as procat');
            if($r == 1 && $key == 'upsale_product')
            $body[] = DB::raw('group_concat(pro.name) as proname');
            if($r == 1 && $key == 'gallery')
            $body[] = DB::raw('group_concat(DISTINCT CONCAT("https://react.tamkeenstores.com.sa/assets/new-media/","",promedia.image)) as promedia');
            if($r == 1 && $key == 'relatedbrand')
            $body[] = DB::raw('group_concat(DISTINCT bra.name) as related_brand');
            if($r == 1 && $key == 'relatedcategory')
            $body[] = DB::raw('group_concat(DISTINCT relcategory.name) as related_category');
            if($r == 1 && $key == 'parent_category')
            $body[] = DB::raw('group_concat(DISTINCT parencat.name) as parent_category');
            if($r == 1 && $key == 'sub_category')
            $body[] = DB::raw('group_concat(DISTINCT sub_cat.name) as sub_category');
            // if($r == 1 && $key == 'link')
            // $body[] = DB::raw('CONCAT("https://tamkeenstores.com.sa/en/", "", products.slug) as link');
            //$body[] = 'subtags.name as subtag';
            // $body[] = DB::raw('group_concat(subtags.name) as subtag');
            // if($r == 1 && $key == 'sub_tags')   
            // $body[] = DB::raw('group_concat(childs.name) as subtag');
        }
        // print_r($body);die;
        $instock = $this->r['instockdata'];
        $enabled = $this->r['enableddata'];
        $ids = [];
        // $proid = Product::pluck('id')->toArray();
        $pro = isset($this->r['filter_pro']) ? $this->r['filter_pro'] : null;
        if($pro) {
        if(sizeof($ids) >= 1) {
            $ids = array_intersect($pro, $ids);
        }
        else {
            $ids = $pro;
        }
        }
        
        $brands = isset($this->r['filter_brands']) ? $this->r['filter_brands'] : null;
        $cats = isset($this->r['filter_cats']) ? $this->r['filter_cats'] : [];
        $Productcategory = CategoryProduct::whereIn('category_id', $cats)->pluck('product_id')->toArray();
        if($Productcategory) {
        if(sizeof($ids) >= 1) {
            
                $ids = array_intersect($Productcategory, $ids);

        }
        else {
            $ids = $Productcategory;
        }
        }
        $tags = isset($this->r['filter_tags']) ? $this->r['filter_tags'] : [];
        $tagsdata = ProductTag::whereIn('sub_tag_id', $tags)->pluck('product_id')->toArray();
        if($tagsdata) {
        if(sizeof($ids) >= 1) {
            
                $ids = array_intersect($tagsdata, $ids);

        }
        else {
            $ids = $tagsdata;
        }
        }
        $selects = [];
        if($this->r['shipping_class'])
        $selects['shipping_class'] = ShippingClasses::selectRaw(DB::raw('group_concat(name) as shipping_class'))->whereColumn('products.shipping_class', 'shipping_classes.id');
        if($this->r['brands'])
        $selects['brands'] = Brand::selectRaw(DB::raw('group_concat(name) as brands'))->whereColumn('products.brands', 'brands.id');
        if($this->r['feature_image'])
        $selects['feature_image'] = ProductMedia::selectRaw(DB::raw("CONCAT('https://react.tamkeenstores.com.sa/assets/new-media/','',product_media.image) AS feature_image"))->whereColumn('products.feature_image', 'product_media.id');
        if($this->r['image_title'])
        $selects['image_title'] = ProductMedia::selectRaw(DB::raw('group_concat(title) as image_title'))->whereColumn('products.feature_image', 'product_media.id');
        
        // print_r($ids);die();
            $products = Product::select($body)
            // ->leftJoin('product_upsale as upsale_product', function($join) {
            //     $join->on('upsale_product.product_id', '=', 'products.id');
            // })
            
            
            ->leftJoin('product_tag as producttag', function($join) {
                $join->on('products.id', '=', 'producttag.product_id');
            })
            ->leftJoin('sub_tags as subtags', function($join) {
                $join->on('producttag.sub_tag_id', '=', 'subtags.id');
            })
            
            ->leftJoin('product_categories as productcat', function($join) {
                $join->on('products.id', '=', 'productcat.product_id');
            })
            ->leftJoin('productcategories as productcatego', function($join) {
                $join->on('productcat.category_id', '=', 'productcatego.id');
            })
            ->leftJoin('productcategories as parencat', function($join) {
                $join->on('productcat.category_id', '=', 'parencat.id')
                ->whereNull('parencat.parent_id')
                ->where('parencat.menu', '=', 1);
            })
            ->leftJoin('productcategories as sub_cat', function($join) {
                $join->on('productcat.category_id', '=', 'sub_cat.id')
                    ->whereNotNull('sub_cat.parent_id')
                    ->where('sub_cat.menu', '=', 1);
            })
            
            ->leftJoin('product_upsale as upsale', function($join) {
                $join->on('products.id', '=', 'upsale.product_id');
            })
            ->leftJoin('products as pro', function($join) {
                $join->on('upsale.upsale_id', '=', 'pro.id');
            })
            
            ->leftJoin('product_gallery as gallery', function($join) {
                $join->on('products.id', '=', 'gallery.product_id');
            })
            ->leftJoin('product_media as promedia', function($join) {
                $join->on('gallery.image', '=', 'promedia.id');
            })
            ->leftJoin('product_related_brands as prorelbrand', function($join) {
                $join->on('products.id', '=', 'prorelbrand.product_id');
            })
            ->leftJoin('brands as bra', function($join) {
                $join->on('prorelbrand.brand_id', '=', 'bra.id');
            })
            ->leftJoin('product_related_category as prorelcat', function($join) {
                $join->on('products.id', '=', 'prorelcat.product_id');
            })
            ->leftJoin('productcategories as relcategory', function($join) {
                $join->on('prorelcat.category_id', '=', 'relcategory.id');
            })
            ->addSelect($selects)
            //->join('product_tag', 'products.id', '=', 'product_tag.product_id')
            // ->join('product_tag', 'products.id', '=', 'product_tag.product_id')
            
            
            // ->leftJoin('product_tag as producttag', function($join) {
            //     $join->on('product_tag.product_id', '=', 'products.id');
            // })
            // ->leftJoin('sub_tags as subtag', function($join) {
            //     $join->on('sub_tags.id', '=', 'product_tag.sub_tag_id');
            // })
            // ->join('product_tag', 'product_tag.product_id', '=', 'products.id')
            // ->join('sub_tags', 'sub_tags.id', '=', 'product_tag.sub_tag_id')
            // ->join('tags', 'product_tag', '=', 'sub_tags')
            // ,'tag' => ProductTag::selectRaw(DB::raw('group_concat(sub_tag_id) as subtags'))
            // ->whereColumn('product_tag.sub_tag_id', 'sub_tags.id')
            ->when($ids, function ($q) use ($ids) {
                return $q->whereIn('products.id', $ids);
            })
            ->when($brands, function ($q) use ($brands) {
                return $q->whereIn('products.brands', $brands);
            })
            ->when($instock !== false, function ($q) use ($instock) {
                return $q->where('products.quantity', '>', '0');
            })
            ->when($enabled !== false, function ($q) use ($enabled) {
                return $q->where('products.status', $enabled);
            })
            // ->when($Productcategory, function ($q) use ($Productcategory) {
            //     return $q->whereIn('id',$Productcategory);
            // })
            // ->when($tagsdata, function ($q) use ($tagsdata) {
            //     return $q->whereIn('id',$tagsdata);
            // })
            ->groupBy('products.id')
            ->get();
        // }
        // print_r($products->toArray());die();
        // unset($products['enabled']);
        return $products;
    }
    
    public function headings(): array
    {
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1 && $key != 'enableddata' && $key != 'instockdata')   
            $body[] = $key;
        }
        
        return $body;
    }
}
