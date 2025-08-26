<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserGeneratedContent;
use App\Models\UserGeneratedContentCategories;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\OrderDetail;

class UserGeneratedContentApiController extends Controller
{
    public function index(Request $request) {
        $categories = [];
        $userId = $request->user_id;
        $categoryId = $request->category_id;
        $userGeneratedContent = UserGeneratedContent::where('user_id', $userId)
            // ->with('ugcCategory:id,name,name_arabic')
            // ->when($categoryId, function ($query, $categoryId) {
            //     $query->whereHas('ugcCategory', function ($q) use ($categoryId) {
            //         $q->where('id', $categoryId);
            //     });
            // })
            ->where('status', 1)
            ->get(['id','user_id','facebook_link','twitter_link','tiktok_link','instagram_link','youtube_link', 'video_link', 'status']);
        $success = true;
        $message = 'User Generated Content Retrieved Successfully';
        // if(!$userGeneratedContent->isEmpty()) {
        //     $categoryIds = UserGeneratedContentCategories::whereIn('ugc_id', $userGeneratedContent->pluck('id'))
        //     ->pluck('category_id')
        //     ->toArray();
        //     if (!empty($categoryIds)) {
        //         $categories = Productcategory::whereIn('id', $categoryIds)
        //             ->get(['id', 'name', 'name_arabic']);
        //     }
        //     $success = false;
        //     $message = 'No User Generated Content Found';
        // }
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $userGeneratedContent,
            // 'category' => $categories
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function store() {
        $success = false;
        $message = 'User Generated Content Created Successfully';
        $userGeneratedContent = null;
        try {
            $data = request()->validate([
                'user_id' => 'required|integer',
            ]);
            $allData = request()->all();
            $userGeneratedContent = UserGeneratedContent::create([
                'user_id' => $data['user_id'],  
                'facebook_link' => $allData['facebook_link'] ?? null,
                'twitter_link' => $allData['twitter_link'] ?? null, 
                'tiktok_link' => $allData['tiktok_link'] ?? null, 
                'instagram_link' => $allData['instagram_link'] ?? null, 
                'youtube_link' => $allData['youtube_link'] ?? null,
                'video_link' => $allData['video_link'] ?? null,
                'order_detail_id' => $allData['order_detail_id'] ?? null,
                'order_id' => $allData['order_id'] ?? null,
                'product_id' => $allData['product_id'] ?? null,
                'status' => 3,
            ]);
            $product = Product::find($allData['product_id']);
            if ($product) {
                $categories = $product->productcategory;
                $validCategories = $categories->filter(function ($category) {
                    return $category->menu == 1 && $category->parent_id != null;
                });
                // return $validCategories;
                if ($validCategories->isNotEmpty()) {
                    $insertData = $validCategories->map(function ($category) use ($userGeneratedContent) {
                        return [
                            'ugc_id' => $userGeneratedContent->id, 
                            'category_id' => $category->id,
                        ];
                    })->toArray();
                    UserGeneratedContentCategories::insert($insertData);
                } 
            }
            if (!empty($allData['order_detail_id']) && $userGeneratedContent) {
                OrderDetail::where('id', $allData['order_detail_id'])->update(['is_video_add' => 1]);
            }
            if($userGeneratedContent) {
                $success = true;
            } else {
                $success = false;
                $message = 'User Generated Content Creation Failed';
            }
        } catch (ValidationException $e) {
            $success = false;
            $message = 'Validation Error';
            $userGeneratedContent = $e->errors();
        }
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $userGeneratedContent,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function getUgcData(Request $request) {
        $lang = isset($request->lang) ? $request->lang : 'ar';
        $catcolumn = $lang == 'ar' ? 'name_arabic' : 'name';
        $sortOrder = $request->sort ?? 'desc';
        $categoryIds = explode(',', $request->category_ids ?? '');
        
        $allCategoryIds = UserGeneratedContentCategories::whereHas('ugcData', function($query) {
            $query->where('status', 1);
        })
        ->pluck('category_id')->unique()->toArray();
        $categories = Productcategory::whereIn('id', $allCategoryIds)
            ->get(['id', $catcolumn]);
        
        $perPage = $request->per_page ?? 12;
        $page = $request->page ?? 1;
        
        $userGeneratedContent = UserGeneratedContent::with('ugcCategory:id,name,name_arabic')
            ->select(['id','facebook_link','twitter_link','tiktok_link','instagram_link','youtube_link', 'video_link'])
            ->when(!empty($categoryIds[0]), function ($query) use ($categoryIds) {
                $query->whereHas('ugcCategory', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                });
            })
            ->where('status', 1)
            ->orderBy('created_at', $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
        
        $success = $userGeneratedContent->isNotEmpty();
        $message = $success ? 'User Generated Content Found' : 'No User Generated Content Found';
        
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $userGeneratedContent,
            'categories' => $categories,
        ];
        
        $responsejson = json_encode($response);
        $data = gzencode($responsejson, 9);
        
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
