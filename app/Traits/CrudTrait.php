<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;

trait CrudTrait
{
    abstract function model();
    abstract function models();
    abstract function validationRules($resource_id = 0);

    public function index()
    {   
        // print_r($this->model()['sort']);die();
        if($this->relations()) {
        $data = $this->model()['model']::with(explode('|', implode('|', $this->relations())))->limit($this->model()['limit'])->orderBy($this->model()['sort'][0], $this->model()['sort'][1])->get();
        }   else {
            $data = $this->model()['model']::limit($this->model()['limit'])->orderBy($this->model()['sort'][0], $this->model()['sort'][1])->get();
        }
        $response = [
            'data' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        // return response()->json(['data' => $data]);
    }

    public function create()
    {   
        $variables = [];
        $valdata = [];
        $data = [];
        if($this->models()) {
            foreach ($this->models() as $key => $value) {
                $variables[] = $key;
                $valdata[] = $value;
            }
            foreach ($variables as $key => $maindata) {
                $data[$maindata] = $valdata[$key];
            }
        }
        $response = [
            'data' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        // return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), $this->validationRules())->validate();
        $data = $request->all();
        // print_r($data);die();
        //print_r($data);die();
        foreach ($this->files() as $key => $value) {
            $fileName = null;
            if($value == 1)
                $data[$key] = [];
            if (request()->hasFile($key))  {
                if($value == 1){
                    foreach(request()->File($key) as $k => $image) {
                        $file = $image;
                        $fileName = md5($file->getClientOriginalName()) . time().$k . "." . $file->getClientOriginalExtension();
                        $file->move(public_path('/media/images'), $fileName);
                        $data[$key][] = $fileName;
                    }
                }
                else{
                    $file = request()->File($key);
                    $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
                    $file->move(public_path('/assets/new-media'), $fileName);
                    $data[$key] = $fileName;
                }
                
            }
            else {
                $data[$key] = null;
            }
        }
        $relations = [];
        foreach ($this->relations() as $key => $value) {
            $value = explode(':', $value)[0];
            if (isset($data[$key]) && is_array($data[$key])) {
                $relations[$value] = $data[$key];
                unset($data[$key]);
            }   
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if(isset($this->arrayData()[$key])){
                    $data[$key] = $this->arrayData()[$key] == 0 ? implode(',',$value) : json_encode($value); 
                }
            }
        }
        $item = $this->model()['model']::create($data);
        foreach ($relations as $key => $value) {
            if(method_exists($item->$key(), 'attach')){
                $item->$key()->attach($value);
            }   else{
                $array = [];
                foreach ($value as $v) {
                    if (is_array($v))
                        $array = $v;
                    else
                        $array = [array_search($key,$this->relations()) => $v];
                    $item->$key()->create($array);
                }
            }
            
        }
        $response = ['success' => true, 'item' => $item, 'message' => $this->viewVariable. ' Has been created!'];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function edit($resource_id)
    {
        if($this->relations()) {
        $editdata = $this->model()['model']::with(explode('|', implode('|', $this->relations())))->findOrFail($resource_id);
        }   else {
            $editdata = $this->model()['model']::findOrFail($resource_id);
        }
        $variables = [];
        $valdata = [];
        $data = [];
        if($this->models()) {
            foreach ($this->models() as $key => $value) {
                $variables[] = $key;
                $valdata[] = $value;
            }
            foreach ($variables as $key => $maindata) {
                $data[$maindata] = $valdata[$key];
            }
        }
        $response = [
            'editdata' => $editdata,
            'data' => $data
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
        // return response()->json(['editdata' => $editdata, 'data' => $data]);
    }

    public function update(Request $request, $resource_id)
    {
        $resource = $this->model()['model']::findOrFail($resource_id);

        Validator::make($request->all(), $this->validationRules($resource_id))->validate();
        $data = $request->all();
        unset($data['_token']);
        unset($data['_method']);


        foreach ($this->files() as $key => $value) {
            $fileName = null;
            if($value == 1)
                $data[$key] = [];

            if (request()->hasFile($key))  {
                if($value == 1){
                    foreach(request()->File($key) as $k => $image) {
                        $file = $image;
                        $fileName = md5($file->getClientOriginalName()) . time().$k . "." . $file->getClientOriginalExtension();
                        $file->move(public_path('/media/images'), $fileName);
                        $data[$key][] = $fileName;
                    }
                }
                else{
                    $file = request()->File($key);
                    $fileName = md5($file->getClientOriginalName()) . time() . 'e'. "." . $file->getClientOriginalExtension();
                    $file->move(public_path('/assets'), $fileName);
                    $data[$key] = $fileName;
                }
                
            }
            else {
                unset($data[$key]);
            }
        }
        $relations = [];
        foreach ($this->relations() as $key => $value) {
    $value = explode(':', $value)[0];
            if (isset($data[$key]) && is_array($data[$key])) {
                $relations[$value] = $data[$key];
                unset($data[$key]);
            }
            
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if(isset($this->arrayData()[$key])){
                    $data[$key] = $this->arrayData()[$key] == 0 ? implode(',',$value) : json_encode($value); 
                }
            }
        }
        
        $this->model()['model']::where('id', $resource_id)->update($data);
        if($this->relations()) {
            $item = $this->model()['model']::with(explode('|', implode('|', $this->relations())))->findOrFail($resource_id);
        }
        else{
            $item = $this->model()['model']::findOrFail($resource_id);
        }
        foreach ($relations as $key => $value) {
            if(method_exists($item->$key(), 'attach')){
                $item->$key()->detach();
                $item->$key()->attach($value);
            }else{
                $item->$key()->delete();
                $array = [];
                foreach ($value as $v) {
                    if (is_array($v))
                        $array = $v;
                    else
                        $array = [array_search($key,$this->relations()) => $v];
                        $item->$key()->create($array);
                }
            }
            
        }


        $resource->update($data);
        $response = ['success' => true, 'message' => $this->viewVariable. ' Has been updated!'];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function destroy($resource_id)
    {
        if($this->relations()) {
            $resource = $this->model()['model']::with(explode('|', implode('|', $this->relations())))->findOrFail($resource_id);
        }
        else 
        {
            $resource = $this->model()['model']::findOrFail($resource_id);
        }
        if($this->relations()) {
            foreach ($this->relations() as $key => $value) {
    $value = explode(':', $value)[0];
                if(method_exists($resource->$value(), 'attach')){
                    $resource->$value()->detach();
                }elseif(method_exists($resource->$value(), 'makeMany')){
                    $resource->$value()->delete();
                }
            }
        }
        $resource->delete();
        $response = ['success' => true, 'message' => $this->viewVariable. ' Has been deleted!'];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function multidelete(Request $request)
    {
        $success = false;
        if(isset($request->id) || isset($request->type)) {
            $ids = $request->id;
            $type = $request->type;
            
            if($this->relations()) {
                $deletemultiple = $this->model()['model']::with(explode('|', implode('|', $this->relations())))->whereIn('id',$ids)->get();
            } else {
                $deletemultiple = $this->model()['model']::whereIn('id',$ids)->get();
            }
            
            if($this->relations()) {
                foreach($deletemultiple as $k => $resource){
                    foreach ($this->relations() as $key => $value) {
                        $value = explode(':', $value)[0];
                        if(method_exists($resource->$value(), 'attach')){
                            $resource->$value()->detach();
                        }elseif(method_exists($resource->$value(), 'makeMany')){
                            $resource->$value()->delete();
                        }
                    }
                }
            }
            $deletemultiple->each->delete();
            $success = true;
            
            // multi work
            // if($type == 1) {
            //     // Enable Status Work
            //     $multiple = $this->model()['model']::whereIn('id',$ids)->get();
            //     foreach($multiple as $ke => $resource){
            //         $resource->status = 1;
            //         $resource->update();
            //     }
            //     $success = true;
            // }
            // elseif($type == 2) {
            //     // Disable Status Work
            //     $multiple = $this->model()['model']::whereIn('id',$ids)->get();
            //     foreach($multiple as $kd => $resource){
            //         $resource->status = 0;
            //         $resource->update();
            //     }
            //     $success = true;
            // }
            // elseif($type == 3) {
            //     // Delete Work
            //     if($this->relations()) {
            //         $deletemultiple = $this->model()['model']::with(explode('|', implode('|', $this->relations())))->whereIn('id',$ids)->get();
            //     } else {
            //         $deletemultiple = $this->model()['model']::whereIn('id',$ids)->get();
            //     }
                
            //     if($this->relations()) {
            //         foreach($deletemultiple as $k => $resource){
            //             // print_r($resource);die();
            //             foreach ($this->relations() as $key => $value) {
            //                 $value = explode(':', $value)[0];
            //                 if(method_exists($resource->$value(), 'attach')){
            //                     $resource->$value()->detach();
            //                 }elseif(method_exists($resource->$value(), 'makeMany')){
            //                     $resource->$value()->delete();
            //                 }
            //             }
            //         }
            //     }
            //     $deletemultiple->each->delete();
            //     $success = true;
            // }
        }
        $response = ['success' => $success, 'message' => $this->viewVariable. ' Has been deleted!'];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}