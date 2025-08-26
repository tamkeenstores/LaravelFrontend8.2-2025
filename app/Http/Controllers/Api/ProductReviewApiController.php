<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Traits\CrudTrait;

class ProductReviewApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'product_review';
    protected $relationKey = 'product_review_id';


    public function model() {
        $data = ['limit' => -1, 'model' => ProductReview::class, 'sort' => ['id','desc']];
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
        return ['userdata_id' => 'UserData:id,first_name,last_name,phone_number', 'detaildata_id' => 'OrderDetailData'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
    
    public function ApproveStatus(Request $request) {
        $id = $request->id;
        $success = false;
        if($id) {
            $data = ProductReview::whereId($id)->update([
                'status' => isset($request->status) ? $request->status : 0,
            ]);
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Status Successfully Updated!']);
        
    }
    
    public function DeclineStatus(Request $request) {
        $id = $request->id;
        $success = false;
        if($id) {
            $data = ProductReview::whereId($id)->update([
                'status' => isset($request->status) ? $request->status : 0,
            ]);
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Status Successfully Updated!']);
        
    }
}
