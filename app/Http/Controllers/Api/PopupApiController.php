<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Popup;
use App\Traits\CrudTrait;

class PopupApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'popup';
    protected $relationKey = 'popup_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Popup::class, 'sort' => ['id','desc']];
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
        return ['featureimage' => 'featuredImage:id,image'];
    }

    public function arrayData(){
        return ['popup_devices' => 0];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
}
