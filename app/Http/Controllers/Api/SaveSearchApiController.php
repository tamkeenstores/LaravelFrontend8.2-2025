<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaveSearch;
use App\Traits\CrudTrait;
use DB;

class SaveSearchApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'save_search';
    protected $relationKey = 'save_search_id';
    
    public function index(Request $request){
        $data = SaveSearch::with('userData')->select('user_id', DB::raw('GROUP_CONCAT(`key`) as key_list'))->groupBy('user_id')->get();
        
    //     $formattedData = $data->reduce(function ($carry, $item) {
    //     $userId = $item->user_id;
    //     $key = $item->key;
    
    //     if (!isset($carry[$userId])) {
    //         $carry[$userId] = (object)[
    //             'id' => $item->id,
    //             'user_id' => $userId,
    //             'key' => $key,
    //             'userData' => $item->userData,
    //         ];
    //     } else {
    //         $carry[$userId]->key .= ", $key";
    //     }
    
    //     return $carry;
    // }, []);
        
        $response = [
            'data' => $data,
            // 'userdata' => $userData,
            // 'formatdata' => $formattedData,
            // 'formattedKeys' => $formattedKeys,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }


    public function model() {
        $data = ['limit' => -1, 'model' => SaveSearch::class, 'sort' => ['id','asc']];
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
