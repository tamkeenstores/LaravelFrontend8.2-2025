<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BrandLandingPage;
use App\Models\BlogSetting;
use DB;
use App\Jobs\BlogViewJob;

class BlogController extends Controller
{
    public function BlogListing() {
        
        $blogs = Blog::with('BlogMediaImage:id,image')->select('id', 'name', 'name_arabic','slug','image_media','file_media',
        DB::raw('SUBSTRING(description, 1, 200) as description'), DB::raw('SUBSTRING(description_arabic, 1, 200) as description_arabic'),
        'created_at', 'views', 'status')->where('status', 1)->orderBy('id','DESC')->get();
        $blogsettings = BlogSetting::with('SliderImage:id,image')->select('id','meta_title_en','meta_title_ar',
        'meta_description_en', 'meta_description_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_canonical_en', 'meta_canonical_ar', 'slider_image')->first();
        $response = [
            'data' => $blogs,
            'blogsettings' => $blogsettings,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function BlogDetail($slug) {
        
        $blogs = Blog::where('slug',$slug)->with('subTagsData:id,name,name_arabic','BrandsData:id,name,name_arabic'
        , 'BlogMediaImage:id,image','categoriesData:id,name,name_arabic')
        ->select('id', 'name', 'name_arabic','slug', 'description', 'description_arabic', 'meta_title_en','meta_title_ar','file_media',
        'meta_description_en', 'meta_description_ar', 'meta_tag_en', 'meta_tag_ar', 'status','image_media')->first();
        $latestblogs = Blog::with('BlogMediaImage:id,image')->select('id', 'name', 'name_arabic','slug','image_media','file_media',
        DB::raw('SUBSTRING(description, 1, 75) as description'),DB::raw('SUBSTRING(description_arabic, 1, 75) as description_arabic'),
        'created_at', 'views', 'status')->where('status', 1)->limit(5)->latest()->get();
        if ($blogs) {
            $id = $blogs->id;
            BlogViewJob::dispatch($id);
        }
        $response = [
            'data' => $blogs,
            'latestblogs' => $latestblogs,
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
