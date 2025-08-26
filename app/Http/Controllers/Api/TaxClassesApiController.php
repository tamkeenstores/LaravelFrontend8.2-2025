<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TaxClasses;
use App\Traits\CrudTrait;

class TaxClassesApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'taxclasses';
    protected $relationKey = 'taxclasses_id';


    public function model() {
        $data = ['limit' => -1, 'model' => TaxClasses::class, 'sort' => ['id','asc']];
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
        // return ['id' => 'productsCount'];
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
