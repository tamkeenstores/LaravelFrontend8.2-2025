<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Models\BrandLandingPage;
use App\Jobs\BrandViewJob;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function BrandPage($slug) {
        $seconds = 86400;
        $cacheKey = "brand_slug_data_{$slug}"; // Added version
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
             try {
                $categoriesSection1 = collect(); // default empty collection
                $categoriesSection2 = collect();
                $getbrand = Brand::where('slug', $slug)->first('id', 'name');
        
                $landingdata = BrandLandingPage::with(['branddata'
                => function ($q) {
                    return $q->select('id','name','name_arabic','slug','brand_image_media','meta_title_en','meta_title_ar','meta_tag_en','meta_tag_ar',
                    'meta_description_en','meta_description_ar')->withCount('productname');   
                }
        
                , 'branddata.BrandMediaImage:id,image',
                'categories' => function ($q) use ($getbrand) {
                    $q->whereHas('Category.productname', function ($b) use ($getbrand) {
                        $b->where('brands', $getbrand->id)->where('status', 1)->where('quantity', '>=',1);
                    });
                },
                'categories' => function ($q) use ($getbrand) {
                    $q->where('status', 0)
                      ->whereHas('Category.productname', function ($b) use ($getbrand) {
                          $b->where('brands', $getbrand->id)
                            ->where('status', 1)
                            ->where('quantity', '>=', 1);
                      });
                },
                'BrandBannerImage:id,image', 'MiddleBannerImage:id,image','categories.category:id,name,name_arabic,slug,description,description_arabic',
                'categories.FeaturedImage:id,image'])->select('id', 'brand_id', 'brand_banner_link', 'brand_banner_media', 'middle_banner_link', 'middle_banner_media', 'status')
                ->where('brand_id', '=', function($query) use ($slug) {
                $query->select('id')
                    ->from('brands')
                    ->where('slug', '=', $slug)
                    ->limit(1);
            })
            ->first();
            
            $data = BrandLandingPage::with(['branddata'
                => function ($q) {
                    return $q->select('id','name','name_arabic','slug','brand_image_media','meta_title_en','meta_title_ar','meta_tag_en','meta_tag_ar',
                    'meta_description_en','meta_description_ar')->withCount('productname');   
                } 
                , 'branddata.BrandMediaImage:id,image',
                'categories' => function ($q) use ($getbrand) {
                    $q->whereHas('Category.productname', function ($b) use ($getbrand) {
                        $b->where('brands', $getbrand->id)->where('status', 1)->where('quantity', '>=',1);
                    });
                },
                'BrandBannerImage:id,image', 'MiddleBannerImage:id,image',
                'categories.FeaturedImage:id,image'])->select('id', 'brand_id', 'brand_banner_link', 'brand_banner_media', 'middle_banner_link', 'middle_banner_media', 'status')
                ->where('brand_id', '=', function($query) use ($slug) {
                $query->select('id')
                    ->from('brands')
                    ->where('slug', '=', $slug)
                    ->limit(1);
            })
            ->first();
            
            if ($landingdata) {
                $categoriesSection1 = $landingdata->categories->where('section', '1')->sortBy('sorting')->values();
                $categoriesSection2 = $landingdata->categories->where('section', '2')->sortBy('sorting')->values();
            }
            
            
            $brandata = Brand::where('slug', $slug)->first();
            if ($brandata) {
                BrandViewJob::dispatch($brandata->id);
                
            }
            $response = [
                'data' => $data,
                'categoriessec1' => $categoriesSection1,
                'categoriessec2' => $categoriesSection2,
            ];
            // Cache the complete response
            Cache::put($cacheKey, $response, $seconds);
            
            } catch (\Exception $e) {
                Log::error("brand slug API Error: " . $e->getMessage());
                
                $response = [
                    'error' => 'Failed to load brand slug data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
                return response()->json($response, 500);
            }
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    // All Brands data
    public function getBrandsData() {
        $seconds = 86400;
        $lang = $request->lang ?? 'ar';
        $cacheKey = "brand_listing_data_{$lang}"; // Added version
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
             try {
                $brands = Brand::with(['BrandMediaImage:id,image', 'BrandMediaAppImage:id,image'])->where('show_in_front', 1)->where('status',1)->select(['id', 'show_in_front', 'sorting','name','name_arabic','slug','brand_image_media', 'brand_app_image_media','status'])
                ->orderBy('sorting', 'asc')
                ->whereHas('productname', function ($b) {
                    $b->where('status', 1)->where('quantity', '>=',1);
                })
                ->get();
                foreach ($brands as $brand) {
                    $categories = Productcategory::select('name', 'name_arabic', 'slug', 'image_link_app')
                        ->leftJoin('brand_category', 'productcategories.id', '=', 'brand_category.category_id')
                        ->where('brand_category.brand_id', $brand->id)
                        // ->withCount('getProductCount')
                        ->whereHas('productname', function ($b) use ($brand) {
                            $b->where('brands', $brand->id)->where('status', 1)->where('quantity', '>=',1);
                        })
                        ->get();
                    $brand->categories = $categories;
                }
                
                $response = [
                    'brands' =>  $brands,
                    'count' => $brands->sortByDesc('category_count')->pluck('category_count')->first(),
                    'brand_count' => count($brands)
                ];
                // Cache the complete response
                Cache::put($cacheKey, $response, $seconds);
            } catch (\Exception $e) {
                Log::error("brand listing API Error: " . $e->getMessage());
                
                $response = [
                    'error' => 'Failed to load homepage data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
                return response()->json($response, 500);
            }
        }
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}
