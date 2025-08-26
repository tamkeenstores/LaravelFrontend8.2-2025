<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Media;
use App\Models\BlogSliders;
use App\Traits\CrudTrait;

class BlogSlidersApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'blog_sliders';
    protected $relationKey = 'blog_sliders_id';


    public function model() {
        $data = ['limit' => -1, 'model' => BlogSliders::class, 'sort' => ['id','asc']];
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
        // return ['slider_image_one' => 'SliderImageOne', 'slider_image_two' => 'SliderImageTwo', 'slider_image_three' => 'SliderImageThree', 'slider_image_four' => 'SliderImageFour'
        // , 'slider_image_five' => 'SliderImageFive', 'slider_image_six' => 'SliderImageSix'];
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
