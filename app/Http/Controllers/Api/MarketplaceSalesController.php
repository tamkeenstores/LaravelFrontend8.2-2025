<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketplaceSales;
use App\Traits\CrudTrait;

class MarketplaceSalesController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'marketplacesales';
    protected $relationKey = 'marketplacesales_id';


    public function model() {
        $data = ['limit' => -1, 'model' => MarketplaceSales::class, 'sort' => ['id','desc']];
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
        return ['user_data' => 'UserDetail'];
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
