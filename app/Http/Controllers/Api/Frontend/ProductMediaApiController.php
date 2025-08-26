<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductMediaApiController extends Controller
{
    public function MediaVideoUpload(Request $request) {
        $success = false;
        $fileName = '';
        $videoName = '';
        if ($request->file('file') != null) {
            $file = $request->file('file');
            $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
            $videoName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
            $path = $file->move(public_path('/assets/new-media'), $fileName);
            $success = true;
        }
        return json_encode(['success' => $success, 'id' => $videoName]);
    }
}
