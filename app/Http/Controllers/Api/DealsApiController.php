<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deals;
use App\Traits\CrudTrait;

class DealsApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'deals';
    protected $relationKey = 'deals_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Deals::class, 'sort' => ['id','asc']];
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
        // return ['app_category_link' => 'cat', 'image_media' => 'ImageMedia', 'mobile_image_media' => 'MobileImageMedia', 'image_media' => 'ModifyImageMedia'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        // return ['category' => Productcategory::get(['id', 'name', 'name_arabic', 'slug', 'mobilename', 'mobilename_arabic'])];
    }
}
