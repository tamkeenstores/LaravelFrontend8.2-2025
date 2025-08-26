<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlertMessage;
use App\Traits\CrudTrait;

class AlertMessageApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'alert_message';
    protected $relationKey = 'alert_message_id';

    public function customindex() {
        $data = AlertMessage::first()->get();
        return response()->json(['data' => $data]);
    }

    public function model() {
        $data = ['limit' => -1, 'model' => AlertMessage::class, 'sort' => ['id','asc']];
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
