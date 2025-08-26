<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpecialOffer;
// use App\Models\Media;
use App\Models\Productcategory;
use App\Traits\CrudTrait;

class SpecialOfferController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'special_offer';
    protected $relationKey = 'special_offer_id';


    public function model() {
        $data = ['limit' => -1, 'model' => SpecialOffer::class, 'sort' => ['id','asc']];
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
        return ['category_id' => 'categoryData'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['categories' => Productcategory::get(['id', 'name', 'name_arabic'])];
    }
}
