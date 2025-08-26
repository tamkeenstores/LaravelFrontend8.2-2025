<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FreeGiftBadge;
use App\Models\Productcategory;
// use App\Models\Product;
// use App\Models\Brand;
use App\Traits\CrudTrait;

class FreeGiftBadgeController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'free_gift_badge';
    protected $relationKey = 'free_gift_badge_id';


    public function model() {
        $data = ['limit' => -1, 'model' => FreeGiftBadge::class, 'sort' => ['id','asc']];
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
        return ['badge_id' => 'Badgeproducts', 'badge_id' => 'Badgecategories', 'badge_id' => 'BadgeBrands'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
        // return ['badge_categories' => Productcategory::where('status', 1)->get(['id', 'name', 'name_arabic']),
        // 'badge_product' => Product::where('status', 1)->get(['id', 'name', 'sku', 'name_arabic']),
        // 'badge_brands' => Brand::where('status', 1)->get(['id', 'name', 'name_arabic'])];
    }
}
