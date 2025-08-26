<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUser;
use App\Traits\CrudTrait;

class TagApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'tags';
    protected $relationKey = 'tags_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Tag::class, 'sort' => ['id','desc']];
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
        return ['id' => 'childs', 'image_id' => 'FeatureImage:id,image'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
    
    // public function multidelete(Request $request) {
    //     $success = false;
    //     if(isset($request->id)) {
    //         $ids = $request->id;
    //         $deletetags = Tag::whereIn('id',$ids)->get();
    //         $deletetags->each->delete();
    //         $success = true;
    //     }
    //     return response()->json(['success' => $success, 'message' => 'Selected Tags Has been deleted!']);
            
    // }
    
    public function exportUsers(Request $request){
        return Excel::download(new ExportUser($request->all()), 'tags.xlsx');
    }
}
