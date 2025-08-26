<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logs;
use App\Traits\CrudTrait;

class LogsController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'logs';
    protected $relationKey = 'logs_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Logs::class, 'sort' => ['id','asc']];
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
        return ['userdata_id' => 'userData'];
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
