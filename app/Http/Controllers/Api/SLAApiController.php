<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SLA;
use App\Traits\CrudTrait;

class SLAApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'SLA';
    protected $relationKey = 'SLA_id';


    public function model() {
        $data = ['limit' => -1, 'model' => SLA::class, 'sort' => ['id','desc']];
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
