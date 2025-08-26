<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromotionPopup;
// use App\Models\Media;
use App\Traits\CrudTrait;

class PromotionPopupApiController extends Controller
{
       use CrudTrait;
    protected $viewVariable = 'promotion_popup';
    protected $relationKey = 'promotion_popup_id';


    public function model() {
        $data = ['limit' => -1, 'model' => PromotionPopup::class, 'sort' => ['id','asc']];
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
        // return ['image_media' => 'ImageMedia'];
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
