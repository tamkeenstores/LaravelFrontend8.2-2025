<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubTags;
use App\Models\Tag;
use App\Traits\CrudTrait;

class SubTagsApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'sub_tags';
    protected $relationKey = 'sub_tags_id';


    public function model() {
        $data = ['limit' => -1, 'model' => SubTags::class, 'sort' => ['id','asc']];
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
        return ['tag_id' => 'parentData:id,name'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['tags' => Tag::where('status','=',1)->get(['id as value', 'name as label'])];
    }
    
    public function subTagsList($id) 
    {
        $subtags = SubTags::with('parentData')->where('tag_id', $id)->get();
        return response()->json(['subtags' => $subtags, 'id' => $id]);
        // return view('admin/tag/sub-index', compact('subtags', 'id'));
    }
}
