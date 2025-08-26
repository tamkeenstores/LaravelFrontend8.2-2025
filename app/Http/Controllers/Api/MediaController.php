<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;
use File;
use App\Helper\Media_helper;

class MediaController extends Controller
{
    
    public function index() {
        $media = Media::get(['id', 'file_url', 'title', 'title_arabic', 'alt', 'alt_arabic', 'details', 'mobile', 'desktop']);
        return json_encode(['data' => $media]);
    }
    
    public function MediaImageUpload(Request $request) {
        $success = false;
        $upload = false;
        if ($request->hasFile('file')) {
            $image = request()->File('file');
            $upload = Media_helper::mediaupload($image);
            $success = true;
        }
        return json_encode(['success' => $success, 'id' => $upload]);
    }
    
    public function storeMedia(Request $request) {
        $success = false;
        $message = 'something went wrong!';
        if(isset($request['id']) && $request['id'] != null) {
            $id = $request['id'];
            $mediadata = Media::where('id', $id)->first();
            $mediadata->uploaded_by = null;
            $mediadata->title = $request['title'];
            $mediadata->title_arabic = $request['title_arabic'];
            $mediadata->alt = $request['alt'];
            $mediadata->alt_arabic = $request['alt_arabic'];
            $mediadata->details = $request['details'];
            $mediadata->mobile = $request['mobile'];
            $mediadata->desktop = $request['desktop'];
            $mediadata->update();   
            $success = true;
            $message = 'Media Updated succesfully!';
        }
        return json_encode(['success' => $success, 'message' => $message]);
    }
    
    public function delete($id) {
        $success = false;
        if($id){
            $media = Media::find($id);
            $media->delete();
            $success = true;
        }
        return json_encode(['success' => $success]);
    }
    
    public function multidelete(Request $request)
    {
        $success = false;
        $message = 'no data deleted!';
        if($request->id) {
            $ids = $request->id;
            $deletemultiple = Media::whereIn('id',$ids)->get();
            $deletemultiple->each->delete();
            $message = 'Media Has been deleted!';
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => $message]);
    }
}
