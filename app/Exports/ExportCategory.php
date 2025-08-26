<?php

namespace App\Exports;
use App\Models\Productcategory;
use App\Models\ProductMedia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportCategory implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'parent_id' && $key != 'tproducts' && $key != 'filtercategory')   
            $body[] = 'productcategories.'.$key;
            if($r == 1 && $key == 'parent_id')   
            $body[] = 'parent.name as parent';
            if($r == 1 && $key == 'filtercategory')
            $body[] = DB::raw('group_concat(DISTINCT subtags.name) as subtags');
        }
        //print_r($body);die();
        
        // DB::connection()->enableQueryLog();
        //if($this->r['parent_id'])
        $selects = [];
        if($this->r['web_image_media'])
        $selects['web_image_media'] = ProductMedia::selectRaw(DB::raw('group_concat(image) as web_image_media'))->whereColumn('productcategories.web_image_media', 'product_media.id');
        if($this->r['mobile_image_media'])
        $selects['mobile_image_media'] = ProductMedia::selectRaw(DB::raw('group_concat(image) as mobile_image_media'))->whereColumn('productcategories.mobile_image_media', 'product_media.id');
        
        $tpro = isset($this->r['tproducts']) && $this->r['tproducts'] == 1 ? true : false;
        $cat = Productcategory::select($body)
        ->addSelect($selects)
        ->leftJoin('productcategories as parent', function($join) {
            $join->on('productcategories.parent_id', '=', 'parent.id');
        })
        ->leftJoin('filter_category as filtercategory', function($join) {
                $join->on('productcategories.id', '=', 'filtercategory.category_id');
            })
            ->leftJoin('sub_tags as subtags', function($join) {
                $join->on('filtercategory.filter_category_id', '=', 'subtags.id');
            })
        ->when($tpro, function ($q) {
            return $q->withCount('productname');
        })
        // ->addSelect(['tproducts' => Productcategory::selectRaw(DB::raw('(name) as parent'))
        //     ->whereColumn('productcategories.parent_id', 'productcategories.id')
        // ])
        ->groupBy('productcategories.id')
        ->get();
        //else
        //$cat = Productcategory::with('category', 'productname')->select(explode(',', implode(',', $body)))->withCount('productname')->get();
        
        // print_r($cat);die;
        return $cat;
        // $queries = DB::getQueryLog();
        // print_r($cat->toArray());die;
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
