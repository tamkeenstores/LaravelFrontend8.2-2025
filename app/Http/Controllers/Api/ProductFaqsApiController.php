<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductFaqs;
use App\Traits\CrudTrait;

class ProductFaqsApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'product_faqs';
    protected $relationKey = 'product_faqs_id';

    public function model() {
        $data = ['limit' => -1, 'model' => ProductFaqs::class, 'sort' => ['id','desc']];
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
        return [];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
}
