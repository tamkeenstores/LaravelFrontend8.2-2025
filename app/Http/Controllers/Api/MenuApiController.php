<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Traits\CrudTrait;

class MenuApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'menu';
    protected $relationKey = 'menu_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Menu::class, 'sort' => ['id','asc']];
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
        return ['image_web' => 'ImageData:id,image'];
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
