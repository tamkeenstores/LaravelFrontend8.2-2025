<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offers;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Models\Product;
use App\Traits\CrudTrait;

class OffersApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'offers';
    protected $relationKey = 'offers_id';

    //  public function index() {
    //     $data = Offers::with('Categories:id,name,name_arabic', 'Brands:id,name,name_arabic')
    //     ->get();
        
    //     //  $data = Brandspotlight::get();
    //     return response()->json(['data' => $data]);
    // }

    public function model() {
        $data = ['limit' => -1, 'model' => Offers::class, 'sort' => ['id','asc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        // return ['product_id' => 'Products', 'category_id' => 'Categories', 'brand_id' => 'Brands'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        // return ['offers_categories' => Productcategory::select(['id','name'])->where('status', 1)->get(), 'offers_products' => Product::select(['id','sku'])->where('status', 1)->get()
        // , 'offers_brands' => Brand::select(['id','name'])->where('status', 1)->get()];
    }
    
}
