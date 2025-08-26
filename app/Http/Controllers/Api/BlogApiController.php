<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Traits\CrudTrait;

class BlogApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'blog';
    protected $relationKey = 'blog_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Blog::class, 'sort' => ['id','desc']];
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
        return ['tags_relation' => 'subTagsData:id,tag_id,name','categories_relation' => 'categoriesData:id,name','brands_relation' => 'BrandsData:id,name',
        'image_media' => 'BlogMediaImage:id,image',];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {   
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         ];
        // return ['subtags' => SubTags::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']),'brand' => Brand::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']),];
        // 'tags' => Tag::where('status','=',1)->orderby('id', 'DESC')->get(['id', 'name', 'name_arabic', 'slug'])];
    }
}
