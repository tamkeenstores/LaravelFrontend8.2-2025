<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Helper\ProductListingHelper;
use App\Helper\ProductListingHelperNew;
use App\Jobs\CategoryViewJob;
use App\Models\ProductMedia;
use App\Models\Brand;
use App\Models\Slider;
use App\Models\CacheStores;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    
    public function CatProductsRegional($slug, Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        $category = Productcategory
        ::with(['filtercategory' => function ($q) {
            $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id','name', 'name_arabic', 'icon');
        }, 'filtercategory.parentData:id,name,name_arabic' , 'child:name,name_arabic,id,slug,parent_id','WebMediaImage:id,image'])
        ->where('slug', $slug)->first(['id', 'slug', 'name', 'name_arabic', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media',
                                        'content_title','content_desc','content_title_arabic','content_desc_arabic','no_follow' , 'no_index','redirection_link']);
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'cat_id' => $category->id, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['filter_min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['filter_max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productDataRegional($filters);
        $id = $category->id;

        // Breadcrumbs
        $breads = [];
        $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
        if(isset($breadcrumb) && $breadcrumb != null) {
            $breads['breadcrumb'] = $breadcrumb;
            $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
            if(isset($childcat) && $childcat != null) {
                $breads['childcat'] = $childcat;
                $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                if(isset($parentcat) && $parentcat != null) {
                    $breads['parentcat'] = $parentcat;
                }
            }
        }
        
        
        $productchema = [];
        $productchemaar = [];
        $products = $productData['products'];
        if($productData && isset($productData['products'])){
                
                for($x = 0; $x < count($productData['products']); $x++)
                {
                    if(isset($productData['products'][$x]))
                    {   
                        $imageid = $productData['products'][$x]->feature_image;
                        $ProductImageName = ProductMedia::where('id',$imageid)->first(['id','image']);
                        $productschemaitem = array (
                            "@type" => "ListItem",
                            "position" => $x+'1',
                            "item" => array(
                                "@type"=> "Product",
                                "name"=> $productData['products'][$x]->name,
                                "image"=> 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.$ProductImageName->image,
                                "offers" => array(
                                    "@type"=> "Offer",
                                    "priceCurrency"=> "SAR",
                                    "price"=> $productData['products'][$x]->sale_price,
                                    "itemCondition"=> "https://schema.org/NewCondition",
                                    "availability"=> "https://schema.org/InStock"
                                ),
                                "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
                            ),
                        );
                        $productschemaitemar = array (
                            "@type" => "ListItem",
                            "position" => $x+'1',
                            "item" => array(
                                "@type"=> "Product",
                                "name"=> $productData['products'][$x]->name_arabic,
                                // 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.
                                // "image"=> $productData['products'][$x]->featured_image,
                                "offers" => array(
                                    "@type"=> "Offer",
                                    "priceCurrency"=> "SAR",
                                    "price"=> $productData['products'][$x]->sale_price,
                                    "itemCondition"=> "https://schema.org/NewCondition",
                                    "availability"=> "https://schema.org/InStock"
                                ),
                                "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
                            ),
                        );
                    } else {
                      echo "No Data Found";
                    }
                    $productchema[] = $productschemaitem;
                    $productchemaar[] = $productschemaitemar;
                }
        }
        

        CategoryViewJob::dispatch($category->id);
        $response = [
            'category' => $category,
            'productData' => $productData,
            'breadcrumb' => $breads,
            'productchema' => $productchema,
            'productchemaar' => $productchemaar,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function CatProductsRegionalNew($slug, $city, Request $request) {
        $requestdata = $request->all();
        $seconds = 86400;
        // print_r(ProductListingHelper::productData());die;
        
        $lang = isset($request->lang) ? $request->lang : 'ar';
        
        $name = $lang == 'ar' ? 'name_arabic' : 'name';
        $metaTitle = $lang == 'ar' ? 'meta_title_ar' : 'meta_title_en';
        $metaTag = $lang == 'ar' ? 'meta_tag_ar' : 'meta_tag_en';
        $metaDesc = $lang == 'ar' ? 'meta_description_ar' : 'meta_description_en';
        $metaCan = $lang == 'ar' ? 'meta_canonical_ar' : 'meta_canonical_en';
        $contentTitle = $lang == 'ar' ? 'content_title_arabic' : 'content_title';
        $contentDesc = $lang == 'ar' ? 'content_desc_arabic' : 'content_desc';
         
        //$category = Productcategory
        //::with(['filtercategory' => function ($q) {
        //    $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id','name', 'name_arabic', 'icon');
        //}, 'filtercategory.parentData:id,name,name_arabic' , 'child:name,name_arabic,id,slug,parent_id','WebMediaImage:id,image'])
        //->where('slug', $slug)->first(['id', 'slug', 'name','name_arabic', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media',
        //                               'content_title','content_desc','content_title_arabic','content_desc_arabic','no_follow' , 'no_index','redirection_link']);
      	//$filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'cat_id' => $category->id, 'filters' => true];  

        if(Cache::has('categorydetail_'.$slug.'_'.$lang)){
            $category = Cache::get('categorydetail_'.$slug.'_'.$lang);
        }
        else {
            //CacheStores::create([
                //'key' => 'categorydetail_'.$slug.'_'.$lang,
                //'type' => 'catdata'
            //]);
            $category = Cache::remember('categorydetail_'.$slug.'_'.$lang, $seconds, function () use ($slug,$name,$metaTitle,$metaTag,$metaDesc,$metaCan,$contentTitle,$contentDesc) {
                return Productcategory::with([
                    'filtercategory' => function ($q) {
                        $q->orderBy('tag_id', 'asc')
                          ->orderBy('sort', 'asc')
                          ->select('sub_tags.id', 'tag_id', 'name', 'name_arabic', 'icon');
                    },
                    'filtercategory.parentData:id,name,name_arabic',
                    'child:name,name_arabic,id,slug,parent_id',
                    'WebMediaImage:id,image'
                ])
                ->where('slug', $slug)
                ->first([
                    'id', 'slug', 
                    $name,
                    $metaTitle,
                    $metaTag,
                    $metaDesc,
                    $metaCan,
                    'web_image_media',
                    $contentTitle,
                    $contentDesc,
                    'no_follow', 'no_index', 'redirection_link'
                ]);
            });

        }

		$filters = [
            'take'    => 20,
            'page'    => $requestdata['page'] ?? 1,
            'cat_id'  => $category ? $category->id : null,
            'filters' => true,
        ];
      
      	
        if(isset($requestdata['min']))
        $filters['filter_min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['filter_max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);

        $cacheKey = 'cat_products_data_' . md5(json_encode($filters));

        if (Cache::has($cacheKey)) {
            $productData = Cache::get($cacheKey);
        } else {
            // CacheStores::create([
                // 'key' => $cacheKey,
                // 'type' => 'categoryproducts'
            // ]);

            $productData = Cache::remember($cacheKey, $seconds, function () use ($filters, $city) {
                return ProductListingHelperNew::productDataUpdated($filters, false, false, $city);
            });
        }

        // $productData = ProductListingHelperNew::productData($filters, false, false, $city);
        $id = $category ? $category->id : null;

        // Breadcrumbs
        $breads = [];
        if (Cache::has('categorybreadcrumbs_'.$slug)) {
            $breads = Cache::get('categorybreadcrumbs_'.$slug);
        } else {
            // CacheStores::create([
                // 'key' => 'categorybreadcrumbs_'.$slug,
                // 'type' => 'catbreads'
            // ]);

            $breads = Cache::remember('categorybreadcrumbs_'.$slug, $seconds, function () use ($slug) {
                $breads = [];
                $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                if(isset($breadcrumb) && $breadcrumb != null) {
                    $breads['breadcrumb'] = $breadcrumb;
                    $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                    if(isset($childcat) && $childcat != null) {
                        $breads['childcat'] = $childcat;
                        $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                        if(isset($parentcat) && $parentcat != null) {
                            $breads['parentcat'] = $parentcat;
                        }
                    }
                }
                return $breads;
            });
        }
        
        $productchema = [];
        $productchemaar = [];
        $products = $productData['products'];

        // Generate a unique cache key based on product data
        $cacheKey = 'product_schema_' . md5(serialize($productData['products']));

        if (Cache::has($cacheKey)) {
            $cachedSchema = Cache::get($cacheKey);
            $productchema = $cachedSchema['en'];
            $productchemaar = $cachedSchema['ar'];
        } else {
            // Store the cache key reference
            // CacheStores::create([
                // 'key' => $cacheKey,
               // 'type' => 'product_schema'
            // ]);

            // Cache for 1 hour (3600 seconds) - adjust as needed
            $cachedData = Cache::remember($cacheKey, $seconds, function () use ($productData) {
                $productchema = [];
                $productchemaar = [];

                if($productData && isset($productData['products'])) {
                    foreach ($productData['products'] as $x => $product) {
                        if(isset($product)) {   
                            $imageid = $product->feature_image;
                            $ProductImageName = ProductMedia::where('id', $imageid)->first(['id','image']);
                            
                            // English schema
                            $productschemaitem = [
                                "@type" => "ListItem",
                                "position" => $x + 1,
                                "item" => [
                                    "@type" => "Product",
                                    "name" => $product->name,
                                    "image" => 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.$ProductImageName->image,
                                    "offers" => [
                                        "@type" => "Offer",
                                        "priceCurrency" => "SAR",
                                        "price" => $product->sale_price,
                                        "itemCondition" => "https://schema.org/NewCondition",
                                        "availability" => "https://schema.org/InStock"
                                    ],
                                    "url" => 'https://tamkeenstores.com.sa/en/product/'.$product->slug,
                                ],
                            ];
                            
                            // Arabic schema
                            $productschemaitemar = [
                                "@type" => "ListItem",
                                "position" => $x + 1,
                                "item" => [
                                    "@type" => "Product",
                                    "name" => $product->name_arabic,
                                    "image" => 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.$ProductImageName->image,
                                    "offers" => [
                                        "@type" => "Offer",
                                        "priceCurrency" => "SAR",
                                        "price" => $product->sale_price,
                                        "itemCondition" => "https://schema.org/NewCondition",
                                        "availability" => "https://schema.org/InStock"
                                    ],
                                    "url" => 'https://tamkeenstores.com.sa/en/product/'.$product->slug,
                                ],
                            ];
                            
                            $productchema[] = $productschemaitem;
                            $productchemaar[] = $productschemaitemar;
                        }
                    }
                }
                
                return [
                    'en' => $productchema,
                    'ar' => $productchemaar
                ];
            });
            
            $productchema = $cachedData['en'];
            $productchemaar = $cachedData['ar'];
        }

        if($category){
            CategoryViewJob::dispatch($category->id);
        }
        $response = [
            'category' => $category,
            'productData' => $productData,
            'breadcrumb' => $breads,
            'productchema' => $productchema,
            'productchemaar' => $productchemaar,
        ];
        
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    
    // public function CatProductsRegionalNewTesting($slug, $city, Request $request) {
    //     $requestdata = $request->all();
    //     // print_r(ProductListingHelper::productData());die;
        
    //     $category = Productcategory
    //     ::with(['filtercategory' => function ($q) {
    //         $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id','name', 'name_arabic', 'icon');
    //     }, 'filtercategory.parentData:id,name,name_arabic' , 'child:name,name_arabic,id,slug,parent_id','WebMediaImage:id,image'])
    //     ->where('slug', $slug)->first(['id', 'slug', 'name','name_arabic', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media',
    //                                     'content_title','content_desc','content_title_arabic','content_desc_arabic','no_follow' , 'no_index','redirection_link']);
    //     $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'cat_id' => $category->id, 'filters' => true];
    //     if(isset($requestdata['min']))
    //     $filters['filter_min'] = $requestdata['min'];
    //     if(isset($requestdata['max']))
    //     $filters['filter_max'] = $requestdata['max'];
    //     if(isset($requestdata['sort']))
    //     $filters['sort'] = $requestdata['sort'];
    //     if(isset($requestdata['brand']))
    //     $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
    //     if(isset($requestdata['tags']))
    //     $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
    //     if(isset($requestdata['rating']))
    //     $filters['filter_review'] = explode(',', $requestdata['rating']);
    //     if(isset($requestdata['cats']))
    //     $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
    //     $productData = ProductListingHelper::productDataRegionalNewTesting($filters, false, false, $city);
    //     $id = $category->id;

    //     // Breadcrumbs
    //     $breads = [];
    //     $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
    //     if(isset($breadcrumb) && $breadcrumb != null) {
    //         $breads['breadcrumb'] = $breadcrumb;
    //         $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
    //         if(isset($childcat) && $childcat != null) {
    //             $breads['childcat'] = $childcat;
    //             $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
    //             if(isset($parentcat) && $parentcat != null) {
    //                 $breads['parentcat'] = $parentcat;
    //             }
    //         }
    //     }
        
        
    //     $productchema = [];
    //     $productchemaar = [];
    //     $products = $productData['products'];
    //     if($productData && isset($productData['products'])){
                
    //             for($x = 0; $x < count($productData['products']); $x++)
    //             {
    //                 if(isset($productData['products'][$x]))
    //                 {   
    //                     $imageid = $productData['products'][$x]->feature_image;
    //                     $ProductImageName = ProductMedia::where('id',$imageid)->first(['id','image']);
    //                     $productschemaitem = array (
    //                         "@type" => "ListItem",
    //                         "position" => $x+'1',
    //                         "item" => array(
    //                             "@type"=> "Product",
    //                             "name"=> $productData['products'][$x]->name,
    //                             "image"=> 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.$ProductImageName->image,
    //                             "offers" => array(
    //                                 "@type"=> "Offer",
    //                                 "priceCurrency"=> "SAR",
    //                                 "price"=> $productData['products'][$x]->sale_price,
    //                                 "itemCondition"=> "https://schema.org/NewCondition",
    //                                 "availability"=> "https://schema.org/InStock"
    //                             ),
    //                             "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
    //                         ),
    //                     );
    //                     $productschemaitemar = array (
    //                         "@type" => "ListItem",
    //                         "position" => $x+'1',
    //                         "item" => array(
    //                             "@type"=> "Product",
    //                             "name"=> $productData['products'][$x]->name_arabic,
    //                             // 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.
    //                             // "image"=> $productData['products'][$x]->featured_image,
    //                             "offers" => array(
    //                                 "@type"=> "Offer",
    //                                 "priceCurrency"=> "SAR",
    //                                 "price"=> $productData['products'][$x]->sale_price,
    //                                 "itemCondition"=> "https://schema.org/NewCondition",
    //                                 "availability"=> "https://schema.org/InStock"
    //                             ),
    //                             "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
    //                         ),
    //                     );
    //                 } else {
    //                   echo "No Data Found";
    //                 }
    //                 $productchema[] = $productschemaitem;
    //                 $productchemaar[] = $productschemaitemar;
    //             }
    //     }
        

    //     CategoryViewJob::dispatch($category->id);
    //     $response = [
    //         'category' => $category,
    //         'productData' => $productData,
    //         'breadcrumb' => $breads,
    //         'productchema' => $productchema,
    //         'productchemaar' => $productchemaar,
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    public function CatProductsRegionalNewTesting($slug, $city, Request $request) {
        $requestdata = $request->all();
    
        // Get Category with related data
        $category = Productcategory::with([
                'filtercategory' => function ($q) {
                    $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id', 'name', 'name_arabic', 'icon');
                },
                'filtercategory.parentData:id,name,name_arabic', 
                'child:id,name,name_arabic,slug,parent_id',
                'WebMediaImage:id,image'
            ])
            ->where('slug', $slug)
            ->first(['id', 'slug', 'name', 'name_arabic', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media', 'content_title', 'content_desc', 'content_title_arabic', 'content_desc_arabic', 'no_follow', 'no_index', 'redirection_link']);
        
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
    
        // Set filters for product data
        $filters = [
            'take' => 20, 
            'page' => $requestdata['page'] ?? 1, 
            'cat_id' => $category->id, 
            'filters' => true
        ];
    
        $filters = array_merge($filters, array_filter([
            'filter_min' => $requestdata['min'] ?? null,
            'filter_max' => $requestdata['max'] ?? null,
            'sort' => isset($requestdata['sort']) ? $requestdata['sort'] : null, // Make sure it gets passed if present
            'filter_brand_id' => isset($requestdata['brand']) ? explode(',', $requestdata['brand']) : null,
            'filter_tag_id' => isset($requestdata['tags']) ? explode(',', $requestdata['tags']) : null,
            'filter_review' => isset($requestdata['rating']) ? explode(',', $requestdata['rating']) : null,
            'filter_cat_id' => isset($requestdata['cats']) ? explode(',', $requestdata['cats']) : null
        ]));

    
        // Fetch product data using the helper function
        $productData = ProductListingHelper::productDataRegionalNewTesting($filters, false, false, $city);
        $id = $category->id;
    
        // Breadcrumbs
        $breads = $this->generateBreadcrumbs($slug);
    
        // Process product schema for SEO
        $productchema = [];
        $productchemaar = [];
        if (isset($productData['products'])) {
            foreach ($productData['products'] as $x => $product) {
                $ProductImageName = ProductMedia::find($product->feature_image, ['id', 'image']);
                if ($ProductImageName) {
                    $productchema[] = $this->generateProductSchema($product, $ProductImageName->image, $x);
                    $productchemaar[] = $this->generateProductSchema($product, $ProductImageName->image, $x, true);
                }
            }
        }
    
        // Dispatch category view job
        CategoryViewJob::dispatch($category->id);
    
        // Prepare the response
        $response = [
            'category' => $category,
            'productData' => $productData,
            'breadcrumb' => $breads,
            'productchema' => $productchema,
            'productchemaar' => $productchemaar,
        ];
    
        // Compress and return the response
        $responsejson = json_encode($response);
        $data = gzencode($responsejson, 9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    private function generateBreadcrumbs($slug) {
        $breads = [];
        $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
        if ($breadcrumb) {
            $breads['breadcrumb'] = $breadcrumb;
            $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
            if ($childcat) {
                $breads['childcat'] = $childcat;
                $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                if ($parentcat) {
                    $breads['parentcat'] = $parentcat;
                }
            }
        }
        return $breads;
    }
    
    private function generateProductSchema($product, $image, $position, $isArabic = false) {
        $name = $isArabic ? $product->name_arabic : $product->name;
        return [
            "@type" => "ListItem",
            "position" => $position + 1,
            "item" => [
                "@type" => "Product",
                "name" => $name,
                "image" => 'https://images.tamkeenstores.com.sa/public/assets/new-media/' . $image,
                "offers" => [
                    "@type" => "Offer",
                    "priceCurrency" => "SAR",
                    "price" => $product->sale_price,
                    "itemCondition" => "https://schema.org/NewCondition",
                    "availability" => "https://schema.org/InStock"
                ],
                "url" => 'https://tamkeenstores.com.sa/en/product/' . $product->slug,
            ],
        ];
    }


    public function CatProducts($slug, Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        $category = Productcategory
        ::with(['filtercategory' => function ($q) {
            $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id','name', 'name_arabic', 'icon');
        }, 'filtercategory.parentData:id,name,name_arabic' , 'child:name,name_arabic,id,slug,parent_id','WebMediaImage:id,image'])
        ->where('slug', $slug)->first(['id', 'slug', 'name', 'name_arabic', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media',
                                        'content_title','content_desc','content_title_arabic','content_desc_arabic','no_follow' , 'no_index','redirection_link','name_app','name_arabic_app']);
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'filters' => true];
        if(isset($category))
        $filters['cat_id'] = $category->id;
        if(isset($requestdata['min']))
        $filters['filter_min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['filter_max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productData($filters);
        $id = $category->id;

        // Breadcrumbs
        $breads = [];
        $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
        if(isset($breadcrumb) && $breadcrumb != null) {
            $breads['breadcrumb'] = $breadcrumb;
            $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
            if(isset($childcat) && $childcat != null) {
                $breads['childcat'] = $childcat;
                $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                if(isset($parentcat) && $parentcat != null) {
                    $breads['parentcat'] = $parentcat;
                }
            }
        }
        
        $productchema = [];
        $productchemaar = [];
        $products = $productData['products'];
        if($productData && isset($productData['products'])){
                
                for($x = 0; $x < count($productData['products']); $x++)
                {
                    if(isset($productData['products'][$x]))
                    {   
                        $imageid = $productData['products'][$x]->feature_image;
                        $ProductImageName = ProductMedia::where('id',$imageid)->first(['id','image']);
                        $productschemaitem = array (
                            "@type" => "ListItem",
                            "position" => $x+'1',
                            "item" => array(
                                "@type"=> "Product",
                                "name"=> $productData['products'][$x]->name,
                                "image"=> 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.$ProductImageName->image,
                                "offers" => array(
                                    "@type"=> "Offer",
                                    "priceCurrency"=> "SAR",
                                    "price"=> $productData['products'][$x]->sale_price,
                                    "itemCondition"=> "https://schema.org/NewCondition",
                                    "availability"=> "https://schema.org/InStock"
                                ),
                                "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
                            ),
                        );
                        $productschemaitemar = array (
                            "@type" => "ListItem",
                            "position" => $x+'1',
                            "item" => array(
                                "@type"=> "Product",
                                "name"=> $productData['products'][$x]->name_arabic,
                                // 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.
                                // "image"=> $productData['products'][$x]->featured_image,
                                "offers" => array(
                                    "@type"=> "Offer",
                                    "priceCurrency"=> "SAR",
                                    "price"=> $productData['products'][$x]->sale_price,
                                    "itemCondition"=> "https://schema.org/NewCondition",
                                    "availability"=> "https://schema.org/InStock"
                                ),
                                "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
                            ),
                        );
                    } else {
                      echo "No Data Found";
                    }
                    $productchema[] = $productschemaitem;
                    $productchemaar[] = $productschemaitemar;
                }
        }

        CategoryViewJob::dispatch($category->id);
        $response = [
            'category' => $category,
            'productData' => $productData,
            'breadcrumb' => $breads,
            'productchema' => $productchema,
            'productchemaar' => $productchemaar,
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function CatSliders($id) {
        $success = false;
        $data = Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
        ->orderBy('sorting', 'asc') 
        ->where('position', 1)
        ->where('status', 1)
        ->where('slider_category_id', $id)
        ->select('id', 'name', 'name_ar', 'alt', 'alt_ar', 'sorting'
        , 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id')->get();
        
        if($data){
            $success = true;
        }
        
        $response = [
            'success' => $success,
            'data' => $data
        ];
        
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function NewlyArrived(Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'new' =>true, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productData($filters);
        //$id = $category->id;

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function NewlyArrivedRegional(Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'new' =>true, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productDataRegional($filters);
        //$id = $category->id;

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function NewlyArrivedRegionalNew($city, Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'new' =>true, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
        //$id = $category->id;

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function searchpageRegional(Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'search' =>isset($requestdata['text']) ? $requestdata['text'] : false, 'filters' => false];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        
        //print_r($filters);die;
        $productData = ProductListingHelper::productDataRegional($filters);
        //$id = $category->id;
        $query = $requestdata['text'];
        
        $cats = Productcategory::
            // whereIn('id', $catids)
        where(function ($queryBuilder) use ($query) {
            $queryBuilder
            // ->where('name', 'like', "%$query%")
            // ->orWhere('name_arabic', 'like', "%$query%");
            ->where('meta_tag_en', 'like', "%$query%")
            ->orWhere('meta_tag_ar', 'like', "%$query%");
        })
        ->where('menu', 1)
        ->where('status', 1)
        ->whereNotNull('parent_id')
        ->select('id', 'name', 'name_arabic', 'slug','icon', 'image_link_app')
        //->with('BrandMediaImage:id,image')
        //->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
        //->limit(8)
        ->get();
        
        
        $brands = Brand::
            //whereIn('id', $brandNames)
        where(function ($queryBuilder) use ($query) {
            $queryBuilder
            // ->where('name', 'like', "%$query%")
            // ->orWhere('name_arabic', 'like', "%$query%");
            ->where('meta_tag_en', 'like', "%$query%")
            ->orWhere('meta_tag_ar', 'like', "%$query%");
        })
        ->where('status', 1)
        ->where('show_in_front', 1)
        //->select('id', 'name', 'name_arabic', 'slug','brand_image_media')
        ->with('BrandMediaImage:id,image')
        ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
        ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
        //->limit(8)
        ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'brand_image_media', 'brand_app_image_media']);

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            'cats' => $cats,
            'brands' => $brands
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function searchpageRegionalNew($city, Request $request) {
        $requestdata = $request->all();
        $seconds = 86400; // Cache duration (24 hours)
        
        
        // Build filters array
        $filters = [
            'take' => 20, 
            'page' => $requestdata['page'] ?? 1, 
            'search' => $requestdata['text'] ?? false, 
            'filters' => false
        ];
        
        // Add optional filters
        if(isset($requestdata['min'])) $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max'])) $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort'])) $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand'])) $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags'])) $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating'])) $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats'])) $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        
        // Cache key for product data
        $productCacheKey = 'search_page_products_' . md5(json_encode($filters) . '_' . $city);
        
        if (Cache::has($productCacheKey)) {
            $productData = Cache::get($productCacheKey);
        } else {
            // CacheStores::create([
                // 'key' => $productCacheKey,
                // 'type' => 'search_page_products'
            // ]);
            
            $productData = Cache::remember($productCacheKey, $seconds, function () use ($filters, $city) {
                return ProductListingHelperNew::productData($filters, false, false, $city);
            });
        }
        
        $query = $requestdata['text'] ?? '';
        
        // Cache key for categories
        $catsCacheKey = 'search_page_cats_' . md5($query);
        
        if (Cache::has($catsCacheKey)) {
            $cats = Cache::get($catsCacheKey);
        } else {
            // CacheStores::create([
                // 'key' => $catsCacheKey,
                // 'type' => 'search_page_cats'
            // ]);
            
            $cats = Cache::remember($catsCacheKey, $seconds, function () use ($query) {
                return Productcategory::where(function ($queryBuilder) use ($query) {
                        $queryBuilder
                        ->where('meta_tag_en', 'like', "%$query%")
                        ->orWhere('meta_tag_ar', 'like', "%$query%");
                    })
                    ->where('menu', 1)
                    ->where('status', 1)
                    ->whereNotNull('parent_id')
                    ->select('id', 'name', 'name_arabic', 'slug','icon', 'image_link_app')
                    ->get();
            });
        }
        
        // Cache key for brands
        $brandsCacheKey = 'search_page_brands_' . md5($query);
        
        if (Cache::has($brandsCacheKey)) {
            $brands = Cache::get($brandsCacheKey);
        } else {
            // CacheStores::create([
                // 'key' => $brandsCacheKey,
                // 'type' => 'search_page_brands'
            // ]);
            
            $brands = Cache::remember($brandsCacheKey, $seconds, function () use ($query) {
                return Brand::where(function ($queryBuilder) use ($query) {
                        $queryBuilder
                        ->where('meta_tag_en', 'like', "%$query%")
                        ->orWhere('meta_tag_ar', 'like', "%$query%");
                    })
                    ->where('status', 1)
                    ->where('show_in_front', 1)
                    ->with('BrandMediaImage:id,image')
                    ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
                    ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
                    ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'brand_image_media', 'brand_app_image_media']);
            });
        }
        
        $response = [
            'productData' => $productData,
            'cats' => $cats,
            'brands' => $brands
        ];
        
        $responsejson = json_encode($response);
        $data = gzencode($responsejson, 9);
        
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function searchpage(Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'search' =>isset($requestdata['text']) ? $requestdata['text'] : false, 'filters' => false];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        
        //print_r($filters);die;
        $productData = ProductListingHelper::productData($filters);
        //$id = $category->id;
        $query = isset($requestdata['text']) ? $requestdata['text'] : '' ;
        
        $cats = Productcategory::
            // whereIn('id', $catids)
        where(function ($queryBuilder) use ($query) {
            $queryBuilder
            // ->where('name', 'like', "%$query%")
            // ->orWhere('name_arabic', 'like', "%$query%");
            ->where('meta_tag_en', 'like', "%$query%")
            ->orWhere('meta_tag_ar', 'like', "%$query%");
        })
        ->where('menu', 1)
        ->where('status', 1)
        ->whereNotNull('parent_id')
        ->select('id', 'name', 'name_arabic', 'slug','icon', 'image_link_app')
        //->with('BrandMediaImage:id,image')
        //->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
        //->limit(8)
        ->get();
        
        
        $brands = Brand::
            //whereIn('id', $brandNames)
        where(function ($queryBuilder) use ($query) {
            $queryBuilder
            // ->where('name', 'like', "%$query%")
            // ->orWhere('name_arabic', 'like', "%$query%");
            ->where('meta_tag_en', 'like', "%$query%")
            ->orWhere('meta_tag_ar', 'like', "%$query%");
        })
        ->where('status', 1)
        ->where('show_in_front', 1)
        //->select('id', 'name', 'name_arabic', 'slug','brand_image_media')
        ->with('BrandMediaImage:id,image')
        ->orderByRaw("CASE WHEN id = '22' THEN 0 ELSE 1 END")
        ->with('BrandMediaImage:id,image,title,title_arabic,alt,alt_arabic,details')
        //->limit(8)
        ->get(['id', 'name', 'name_arabic', 'slug', 'status', 'brand_image_media', 'brand_app_image_media']);

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            'cats' => $cats,
            'brands' => $brands
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function MostRated(Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'rating' =>true, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productData($filters);
        //$id = $category->id;

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function MostRatedRegional(Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'rating' =>true, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::productDataRegional($filters);
        //$id = $category->id;

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function MostRatedRegionalNew($city, Request $request) {
        $requestdata = $request->all();
        // print_r(ProductListingHelper::productData());die;
        
        
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'rating' =>true, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        // $productData = ProductListingHelper::productDataRegionalNew($filters);
        $productData = ProductListingHelper::productDataRegionalNew($filters, false, false, $city);
        //$id = $category->id;

        
        $response = [
            //'category' => $category,
            'productData' => $productData,
            //'breadcrumb' => $breads
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    //filter
    public function TestFilterBrandCategory($brandslug = null, $slug = null, $city = null, Request $request) {
        $requestdata = $request->all();
        
        $category = Productcategory
        ::with(['filtercategory' => function ($q) {
            $q->orderBy('tag_id', 'asc')->orderBy('sort', 'asc')->select('sub_tags.id', 'tag_id','name', 'name_arabic', 'icon');
        }, 'filtercategory.parentData:id,name,name_arabic' , 'child:name,name_arabic,id,slug,parent_id','WebMediaImage:id,image'])
        ->where('slug', $slug)->first(['id', 'slug', 'name', 'name_arabic', 'meta_title_en', 'meta_title_ar', 'meta_tag_en', 'meta_tag_ar', 'meta_description_en', 'meta_description_ar', 'meta_canonical_en', 'meta_canonical_ar', 'web_image_media',
                                        'content_title','content_desc','content_title_arabic','content_desc_arabic','no_follow' , 'no_index','redirection_link']);
        
        //brand
        $brand = Brand::where('slug',$brandslug)->first(['id','slug','name','name_arabic']);
        //
        $filters = ['take' => 20, 'page' => isset($requestdata['page']) ? $requestdata['page'] : 1, 'cat_id' => $category->id, 'b_id' => $brand->id, 'filters' => true];
        if(isset($requestdata['min']))
        $filters['filter_min'] = $requestdata['min'];
        if(isset($requestdata['max']))
        $filters['filter_max'] = $requestdata['max'];
        if(isset($requestdata['sort']))
        $filters['sort'] = $requestdata['sort'];
        if(isset($requestdata['brand']))
        $filters['filter_brand_id'] = explode(',', $requestdata['brand']);
        if(isset($requestdata['tags']))
        $filters['filter_tag_id'] = explode(',', $requestdata['tags']);
        if(isset($requestdata['rating']))
        $filters['filter_review'] = explode(',', $requestdata['rating']);
        if(isset($requestdata['cats']))
        $filters['filter_cat_id'] = explode(',', $requestdata['cats']);
        $productData = ProductListingHelper::testBrandCatData($filters, false, false, $city);
        $id = $category->id;

        // Breadcrumbs
        $breads = [];
        $breadcrumb = Productcategory::where('slug', $slug)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
        if(isset($breadcrumb) && $breadcrumb != null) {
            $breads['breadcrumb'] = $breadcrumb;
            $childcat = Productcategory::where('id', $breadcrumb->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
            if(isset($childcat) && $childcat != null) {
                $breads['childcat'] = $childcat;
                $parentcat = Productcategory::where('id', $childcat->parent_id)->where('status', 1)->select(['id', 'name', 'name_arabic', 'slug', 'parent_id'])->first();
                if(isset($parentcat) && $parentcat != null) {
                    $breads['parentcat'] = $parentcat;
                }
            }
        }
        
        
        $productchema = [];
        $productchemaar = [];
        $products = $productData['products'];
        if($productData && isset($productData['products'])){
                
                for($x = 0; $x < count($productData['products']); $x++)
                {
                    if(isset($productData['products'][$x]))
                    {   
                        $imageid = $productData['products'][$x]->feature_image;
                        $ProductImageName = ProductMedia::where('id',$imageid)->first(['id','image']);
                        $productschemaitem = array (
                            "@type" => "ListItem",
                            "position" => $x+'1',
                            "item" => array(
                                "@type"=> "Product",
                                "name"=> $productData['products'][$x]->name,
                                "image"=> 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.$ProductImageName->image,
                                "offers" => array(
                                    "@type"=> "Offer",
                                    "priceCurrency"=> "SAR",
                                    "price"=> $productData['products'][$x]->sale_price,
                                    "itemCondition"=> "https://schema.org/NewCondition",
                                    "availability"=> "https://schema.org/InStock"
                                ),
                                "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
                            ),
                        );
                        $productschemaitemar = array (
                            "@type" => "ListItem",
                            "position" => $x+'1',
                            "item" => array(
                                "@type"=> "Product",
                                "name"=> $productData['products'][$x]->name_arabic,
                                // 'https://images.tamkeenstores.com.sa/public/assets/new-media/'.
                                // "image"=> $productData['products'][$x]->featured_image,
                                "offers" => array(
                                    "@type"=> "Offer",
                                    "priceCurrency"=> "SAR",
                                    "price"=> $productData['products'][$x]->sale_price,
                                    "itemCondition"=> "https://schema.org/NewCondition",
                                    "availability"=> "https://schema.org/InStock"
                                ),
                                "url"=> 'https://tamkeenstores.com.sa/en/product/'.$productData['products'][$x]->slug,
                            ),
                        );
                    } else {
                      echo "No Data Found";
                    }
                    $productchema[] = $productschemaitem;
                    $productchemaar[] = $productschemaitemar;
                }
        }
        
        CategoryViewJob::dispatch($category->id);
        $response = [
            'category' => $category,
            'productData' => $productData,
            'breadcrumb' => $breads,
            'productchema' => $productchema,
            'productchemaar' => $productchemaar,
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