<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Badge;
// use App\Models\Media;
use App\Models\Productcategory;
use App\Models\Product;
use App\Traits\CrudTrait;

class BadgeApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'badge';
    protected $relationKey = 'badge_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Badge::class, 'sort' => ['id','asc']];
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
        return ['product_id' => 'products:id,name,sku', 'category_id' => 'productcategory:id,name', 'badge_slider_id' => 'BadgeSlider:id,image'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'products' => Product::where('status','=',1)->get(['id as value', 'sku as label'])];
    }
}
