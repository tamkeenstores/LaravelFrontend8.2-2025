<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fees;
use App\Traits\CrudTrait;

class FeesApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'fees';
    protected $relationKey = 'fees_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Fees::class, 'sort' => ['id','desc']];
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
        return ['payment_method' => 0];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
}
