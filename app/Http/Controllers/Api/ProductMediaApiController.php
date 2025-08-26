<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductMedia;
use App\Traits\CrudTrait;

class ProductMediaApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'product_media';
    protected $relationKey = 'product_media';


    public function model() {
        $data = ['limit' => -1, 'model' => ProductMedia::class, 'sort' => ['id','desc']];
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
    
    public function MediaImageUpload(Request $request) {
        $success = false;
        $fileName = [];
        $imageName = [];
        if ($request->file('file')!=null) {
            foreach (request()->File('file') as $fileData) {
                $file = $fileData;
                $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                $imageName[] = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                
                // $fileName = $file->getClientOriginalName();
                // $imageName[] = $file->getClientOriginalName();
                $path = $file->move(public_path('/assets/new-media'), $fileName);
                $success = true;
            }
        }
        return json_encode(['success' => $success, 'id' => $imageName]);
    }
    
    public function store(Request $request) 
    {   
        $success = false;
        $message = 'Something went wrong. Please try again.';
        if (count($request->image['id']) >= 1) {
            $checkImage = ProductMedia::whereIn('image', $request->image['id'])->pluck('image')->toArray();
            $result = array_diff($request->image['id'],$checkImage);
            if(count($result) >= 1) {
                foreach ($result as $k => $value) {
                    $data = [
                        'title' => isset($request->title) ? $request->title : null,
                        'title_arabic' => isset($request->title_arabic) ? $request->title_arabic : null,
                        'alt' => isset($request->alt) ? $request->alt : null,
                        'alt_arabic' => isset($request->alt_arabic) ? $request->alt_arabic : null,
                        'details' => isset($request->details) ? $request->details : null,
                        'desktop' => isset($request->desktop) ? $request->desktop : 0,
                        'mobile' => isset($request->mobile) ? $request->mobile : 0,
                        'image' => isset($value) ? $value : null
                    ];
                    ProductMedia::create($data);
                    $success = true;
                    $message = 'Product Media Has been created!';
                }    
            }
            if(count($checkImage) >= 1 && count($checkImage) != count($request->image['id'])) {
                $success = true;
                $message = 'Product Media Has been created. Except ' . implode(', ', $checkImage);
            }
            elseif(count($checkImage) >= 1 && count($checkImage) == count($request->image['id'])) {
                $success = true;
                $message = 'All images already uploaded.';
            }
        }
        return response()->json(['success' => true, 'message' => $message]);
    }
}
