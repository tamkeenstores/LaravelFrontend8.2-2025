<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectSale;
use App\Models\States;
use App\Traits\CrudTrait;

class ProjectSaleApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'project_sale';
    protected $relationKey = 'project_sale_id';


    public function model() {
        $data = ['limit' => -1, 'model' => ProjectSale::class, 'sort' => ['id','desc']];
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
        // return [];
        return ['citydata_id' => 'citydata:id,name'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['states' => States::where('country_id','191')->orderby('id', 'DESC')->get(['id as value', 'name as label'])];
    }
}
