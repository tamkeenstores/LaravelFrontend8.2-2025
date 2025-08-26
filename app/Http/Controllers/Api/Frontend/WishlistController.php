<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wishlists;
use App\Helper\ProductListingHelper;
use App\Helper\ProductListingHelperNew;

class WishlistController extends Controller
{
    public function getWishlistProduct($id) {
        $wishlistdata = Wishlists::where('user_id', $id)->pluck('product_id');
        $response = [
            'wishlistdata' => $wishlistdata,
            
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    public function getWishlist($id,Request $request) {
        $requestdata = $request->all();
        $wishlistdata = Wishlists::where('user_id', $id)->pluck('product_id');
        $filters = ['take' => 20, 'page' => 1];
        $filters['productbyid'] = $wishlistdata;
        $productFirstData = ProductListingHelper::productData($filters);
        $productData = Wishlists::where('user_id', $id)->pluck('product_id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        if(isset($requestdata['city'])) {
            if(isset($productData) && sizeof($productData)) {
                foreach($productData as $id){
                    $extra_multi_data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false);
                    $wishlistData = false;
                    $freeGiftData = ProductListingHelper::productFreeGifts($id,$requestdata['city']);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSale($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    // $fbtData = ProductListingHelper::productFBT($id,$requestdata['city']);
                    // if($fbtData)
                    //     $extra_multi_data[$id]['fbtData'] = $fbtData;
                    // $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,$requestdata['city']);
                    // if($expressdeliveryData)
                    //     $extra_multi_data[$id]['expressdeliveryData'] = $expressdeliveryData;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$requestdata['city'],$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    // if(isset($requestdata['user_id'])) {
                    //     $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    //     if($wishlist) {
                    //         $wishlistData = true;
                    //     }
                    // }
                    $wishlistData = true;
                    $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                }
            }
        }
        
        // $user = false;
        // $success = false;
        // $user = User::where(function($query) use ($id){
        //     return $query->whereHas('wishlists', function($q) use($id){
        //         $q->where('user_id', $id);
        //     });
        // })
        // ->where('id',$id)
        // ->with('wishlists.product:id,name_arabic,name,sku')
        // ->select(['id'])
        // ->first();
        // if($user){
        //     $success = true;
        // }
        $response = [
            'user' => $productFirstData,
            'extra_multi_data' => $extra_multi_data
            // 'success' => $success
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function getWishlistRegional($id,Request $request) {
        $requestdata = $request->all();
        $wishlistdata = Wishlists::where('user_id', $id)->pluck('product_id');
        $filters = ['take' => 20, 'page' => 1];
        $filters['productbyid'] = $wishlistdata;
        $productFirstData = ProductListingHelper::productDataRegional($filters);
        $productData = Wishlists::where('user_id', $id)->pluck('product_id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        if(isset($requestdata['city'])) {
            if(isset($productData) && sizeof($productData)) {
                foreach($productData as $id){
                    $extra_multi_data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false);
                    $wishlistData = false;
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegional($id,$requestdata['city']);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSale($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    // $fbtData = ProductListingHelper::productFBT($id,$requestdata['city']);
                    // if($fbtData)
                    //     $extra_multi_data[$id]['fbtData'] = $fbtData;
                    // $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,$requestdata['city']);
                    // if($expressdeliveryData)
                    //     $extra_multi_data[$id]['expressdeliveryData'] = $expressdeliveryData;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$requestdata['city'],$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    // if(isset($requestdata['user_id'])) {
                    //     $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    //     if($wishlist) {
                    //         $wishlistData = true;
                    //     }
                    // }
                    $wishlistData = true;
                    $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                }
            }
        }
        
        // $user = false;
        // $success = false;
        // $user = User::where(function($query) use ($id){
        //     return $query->whereHas('wishlists', function($q) use($id){
        //         $q->where('user_id', $id);
        //     });
        // })
        // ->where('id',$id)
        // ->with('wishlists.product:id,name_arabic,name,sku')
        // ->select(['id'])
        // ->first();
        // if($user){
        //     $success = true;
        // }
        $response = [
            'user' => $productFirstData,
            'extra_multi_data' => $extra_multi_data
            // 'success' => $success
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
     public function getWishlistRegionalNew($id, $city,Request $request) {
        $requestdata = $request->all();
        $wishlistdata = Wishlists::where('user_id', $id)->pluck('product_id');
        $filters = ['take' => 20, 'page' => 1];
        $filters['productbyid'] = $wishlistdata;
        $productFirstData = ProductListingHelperNew::productData($filters, false, false, $city);
        $productData = Wishlists::where('user_id', $id)->pluck('product_id')->toArray();
        
        // Product Extra Data multi
        $extra_multi_data = [];
        if(isset($requestdata['city'])) {
            if(isset($productData) && sizeof($productData)) {
                foreach($productData as $id){
                    $extra_multi_data[$id] = array('freegiftdata'=>false,'fbtData'=>false,'expressdeliveryData'=>false,'badgeData'=>false, 'flashData'=>false,'wishlistData' => false);
                    $wishlistData = false;
                    $freeGiftData = ProductListingHelper::productFreeGiftsRegionalNew($id,$requestdata['city']);
                    if($freeGiftData)
                        $extra_multi_data[$id]['freegiftData'] = $freeGiftData;
                    $flash = ProductListingHelper::productFlashSaleNew($id);
                    if($flash)
                        $extra_multi_data[$id]['flashData'] = $flash;
                    // $fbtData = ProductListingHelper::productFBT($id,$requestdata['city']);
                    // if($fbtData)
                    //     $extra_multi_data[$id]['fbtData'] = $fbtData;
                    // $expressdeliveryData = ProductListingHelper::productExpressDelivery($id,$requestdata['city']);
                    // if($expressdeliveryData)
                    //     $extra_multi_data[$id]['expressdeliveryData'] = $expressdeliveryData;
                    $mobbadge = true;
                    $badgeData = ProductListingHelper::productBadge($id,$requestdata['city'],$mobbadge);
                    if($badgeData)
                        $extra_multi_data[$id]['badgeData'] = $badgeData;
                    // if(isset($requestdata['user_id'])) {
                    //     $wishlist = Wishlists::where('user_id',$requestdata['user_id'])->where('product_id',$id)->first();
                    //     if($wishlist) {
                    //         $wishlistData = true;
                    //     }
                    // }
                    $wishlistData = true;
                    $extra_multi_data[$id]['wishlistData'] = $wishlistData == 1 ? true : false;
                }
            }
        }
        
        // $user = false;
        // $success = false;
        // $user = User::where(function($query) use ($id){
        //     return $query->whereHas('wishlists', function($q) use($id){
        //         $q->where('user_id', $id);
        //     });
        // })
        // ->where('id',$id)
        // ->with('wishlists.product:id,name_arabic,name,sku')
        // ->select(['id'])
        // ->first();
        // if($user){
        //     $success = true;
        // }
        $response = [
            'user' => $productFirstData,
            'extra_multi_data' => $extra_multi_data
            // 'success' => $success
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function checkWishlistProduct(Request $request) {
        $data = $request->all();
        $success = false;
        $wishlists = Wishlists::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($wishlists){
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
    
    public function addWishlist(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $wislistsData = Wishlists::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($wislistsData){
            $msg = 'This product already added in the wishlist!';
        }else{
            $wislists = Wishlists::create([
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
            ]);
            $success = true;
            $msg = 'This product has been added in the wishlist!';
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
    
    public function removeWishlist(Request $request) {
        $data = $request->all();
        $success = false;
        $msg = '';
        
        $wislistsData = Wishlists::where('user_id',$data['user_id'])->where('product_id',$data['product_id'])->first();
        if($wislistsData){
            $wislistsData->delete();
            $success = true;
            $msg = 'This product has been deleted in the wishlist!';
        }else{
            $msg = 'This product has not in the wishlist!';
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
    
    public function checkMultiWishlistProduct($productids,$userid) {
        $dataresult = [];
        $products_id = explode(',', $productids);
        if(isset($products_id) && sizeof($products_id)){
            foreach($products_id as $id){
                $dataresult[$id] = array('wishlist'=>false);
                $wishlistData = Wishlists::where('user_id',$userid)->where('product_id',$id)->first();
                if($wishlistData)
                    $dataresult[$id]['wishlist'] = true;
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
