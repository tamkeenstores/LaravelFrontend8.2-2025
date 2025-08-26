<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compare;
use App\Models\User;

class CompareController extends Controller
{
    
    public function getCompareProduct($id) {
        $comparedata = Compare::where('user_id', $id)->pluck('product_id');
        $response = [
            'comparedata' => $comparedata,
            
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function getCompare($id) {
        
        // $wishlistdata = Wishlists::where('user_id', $id)->pluck('product_id');
        // $filters = ['take' => 20, 'page' => 1];
        // $filters['productbyid'] = $wishlistdata;
        // $productFirstData = ProductListingHelper::productData($filters);
        
        $user = false;
        $success = false;
        $user = User::where(function($query) use ($id){
            return $query->whereHas('compares', function($q) use($id){
                $q->where('user_id', $id);
            });
        })
        ->where('id',$id)
        ->with('compares:id,user_id,product_id','compares.product:id,name_arabic,slug,name,sku,price,sale_price,brands,feature_image,cashback_amount,cashback_title,cashback_title_arabic','compares.product.featuredImage:id,image' ,
        'compares.product.brand:id,name,name_arabic',
        'compares.product.tags:id,tag_id,name,name_arabic',
        'compares.product.tags.parentData:id,name,name_arabic',
        'compares.product.multiFreeGiftData:id,product_id,free_gift_sku,free_gift_qty',
        'compares.product.multiFreeGiftData.productSkuData:id,sku,name,name_arabic,sale_price,price,brands,slug,pre_order,feature_image,no_of_days',
        'compares.product.multiFreeGiftData.productSkuData.featuredImage:id,image',
        'compares.product.multiFreeGiftData.productSkuData.brand:id,name,name_arabic'
        )
        ->select(['id'])
        ->first();
        
        if ($user) {
        $success = true;
        $parentDataNames = $user->compares->flatMap(function ($compare) {
            return $compare->product->tags->map(function ($tag) {
                return $tag->parentData;
            });
        })->unique()->values()->all();
    }
    else {
        $parentDataNames = null;
    }
    // print_r($parentDataNames);die();
        
        if($user){
            $success = true;
        }
        $response = [
            'user' => $user,
            'parentnames' => $parentDataNames,
            'success' => $success
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function checkCompareProduct(Request $request) {
        $data = $request->all();
        $success = false;
        $compare = Compare::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($compare){
            $success = true;
        }
        $response = [
            'success' => $success
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function addCompare(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $comparecount = Compare::where('user_id',$data['user_id'])->count();
        if($comparecount > 3) {
            return response()->json(['success' => false, 'msg' => true, 'message' => 'User has Already 4 Compares']);
        }
        else {
        $compareData = Compare::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($compareData){
            $msg = 'This product already added in the Compare!';
        }else{
            $compare = Compare::create([
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
            ]);
            $success = true;
            $msg = 'This product has been added in the Compare!';
        }
        }
            
        $response = [
            'success' => $success,
            'msg' => $msg
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function removeCompare(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $compareData = Compare::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($compareData){
            $compareData->delete();
            $success = true;
            $msg = 'This product has been deleted in the Compare!';
        }else{
            $msg = 'This product has not in the Compare!';
        }
            
        $response = [
            'success' => $success,
            'msg' => $msg
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function removeAllCompare(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $compareData = Compare::where('user_id',$data['user_id'])->get();
        if($compareData){
            $compareData->each->delete();
            $success = true;
            $msg = 'This product has been deleted in the Compare!';
        }else{
            $msg = 'This product has not in the Compare!';
        }
            
        $response = [
            'success' => $success,
            'msg' => $msg
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function checkMultiCompareProduct($productids,$userid) {
        $dataresult = [];
        $products_id = explode(',', $productids);
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $id){
                $dataresult[$id] = array('compare'=>false);
                $compareData = Compare::where('user_id',$userid)->where('product_id',$id)->first();
                if($compareData)
                    $dataresult[$id]['compare'] = true;
            }
        }
        
        $response = [
            'data' => $dataresult,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
