<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Productcategory;
use App\Models\HomePageThreeData;
use App\Models\Product;
use App\Models\Brand;
use App\Models\HomePageThree;
use App\Models\HomePageSlider;
use App\Models\States;
use App\Helper\ProductListingHelperNew;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\CacheStores;

class HomePageLatestController extends Controller
{
    public function getHomePagelatestOne(Request $request)
    {
        // Configuration
        $seconds = 86400; // 24 hours cache
        $lang = $request->lang ?? 'ar';
        $deviceType = $request->device_type === 'mobile' ? 'mobile' : 'desktop';
        $cacheKey = "homepage_first_five_v2_{$lang}_{$deviceType}"; // Added version
        $city = $request->city ? $request->city : 'Jeddah';
        
        // Return cached response if available
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                // Select only needed columns
                $columns = [
                    'id',
                    'sec_one_link',
                    $deviceType == 'mobile' ? 'sec_one_image_mobile as sec_one_image' : 'sec_one_image',
                    //new
                    $deviceType == 'mobile' ? 'sec_one_slider_mobile' : 'sec_one_slider',
                    //
                    $deviceType == 'mobile' ? 'sec_two_slider_left_mobile' : 'sec_two_slider_left',
                    $deviceType == 'mobile' ? 'sec_two_slider_top_mobile' : 'sec_two_slider_top',
                    'sec_two_bottom_image_one',
                    'sec_two_bottom_link_one',
                    'sec_two_bottom_image_two',
                    'sec_two_bottom_link_two',
                    'sec_two_bottom_image_three',
                    'sec_two_bottom_link_three',
                    $lang === 'en' ? 'sec_three_title' : 'sec_three_title_ar as sec_three_title',
                    $lang === 'en' ? 'sec_four_title' : 'sec_four_title_ar as sec_four_title',
                    'sec_three_categories',
                    'sec_five_image_one',
                    'sec_five_link_one',
                    'sec_five_image_two',
                    'sec_five_link_two',
                    'sec_five_image_three',
                    'sec_five_link_three',
                    'sec_five_image_four',
                    'sec_five_link_four',
                    $deviceType == 'mobile' ? 'sec_five_slider_mobile' : 'sec_five_slider',
                    //
                    $lang == 'en' ? 'sec_nineteen_title' : 'sec_nineteen_title_ar as sec_nineteen_title',
                    $lang == 'en' ? 'sec_nineteen_image_one' : 'sec_nineteen_image_one_ar as sec_nineteen_image_one',
                    $lang == 'en' ? 'sec_nineteen_image_one_link' : 'sec_nineteen_image_one_link_ar as sec_nineteen_image_one_link',
                    $lang == 'en' ? 'sec_nineteen_image_two' : 'sec_nineteen_image_two_ar as sec_nineteen_image_two',
                    $lang == 'en' ? 'sec_nineteen_image_two_link' : 'sec_nineteen_image_two_link_ar as sec_nineteen_image_two_link',
                    $lang == 'en' ? 'sec_nineteen_image_three' : 'sec_nineteen_image_three_ar as sec_nineteen_image_three',
                    $lang == 'en' ? 'sec_nineteen_image_three_link' : 'sec_nineteen_image_three_link_ar as sec_nineteen_image_three_link',
                    $lang == 'en' ? 'sec_nineteen_image_four' : 'sec_nineteen_image_four_ar as sec_nineteen_image_four',
                    $lang == 'en' ? 'sec_nineteen_image_four_link' : 'sec_nineteen_image_four_link_ar as sec_nineteen_image_four_link',
                    $lang == 'en' ? 'sec_nineteen_image_five' : 'sec_nineteen_image_five_ar as sec_nineteen_image_five',
                    $lang == 'en' ? 'sec_nineteen_image_five_link' : 'sec_nineteen_image_five_link_ar as sec_nineteen_image_five_link',
                    $lang == 'en' ? 'sec_nineteen_image_six' : 'sec_nineteen_image_six_ar as sec_nineteen_image_six',
                    $lang == 'en' ? 'sec_nineteen_image_six_link' : 'sec_nineteen_image_six_link_ar as sec_nineteen_image_six_link',
                    $lang == 'en' ? 'sec_nineteen_image_seven' : 'sec_nineteen_image_seven_ar as sec_nineteen_image_seven',
                    $lang == 'en' ? 'sec_nineteen_image_seven_link' : 'sec_nineteen_image_seven_link_ar as sec_nineteen_image_seven_link',
                    $lang == 'en' ? 'sec_nineteen_image_eight' : 'sec_nineteen_image_eight_ar as sec_nineteen_image_eight',
                    $lang == 'en' ? 'sec_nineteen_image_eight_link' : 'sec_nineteen_image_eight_link_ar as sec_nineteen_image_eight_link',
                ];

                $homeData = HomePageThree::with(['homepagedata' => function($query) use ($lang, $deviceType) {
                    $query->with(['category' => function($q) use ($lang, $deviceType) {
                        $q->select(
                            [
                                'id', 
                                $lang === 'en' ? 'name' : 'name_arabic', 
                                'slug', 
                                $deviceType == 'mobile' ? 'mobile_image_media' : 'web_image_media'
                            ]
                        );
                        // Conditionally load the appropriate media relationship
                        if ($deviceType == 'mobile') {
                            $q->with(['MobileMediaAppImage' => function($mediaQuery) use ($lang) {
                                $mediaQuery->select(['id', 'image', $lang === 'en' ? 'alt' : 'alt_arabic',]);
                            }]);
                        } else {
                            $q->with(['WebMediaImage' => function($mediaQuery) use ($lang) {
                                $mediaQuery->select(['id', 'image', $lang === 'en' ? 'alt' : 'alt_arabic',]);
                            }]);
                        }
                    }])->select(['id', 'homepage_id', 'category_id', 'product_id', 'type']);
                }])
                ->select($columns)
                ->firstOrFail();

                $homeData->makeHidden('homepagedata');

                // section one slider 
                $secOneSliderConfig = [
                    'slide_ids' => $deviceType == 'mobile' ? $homeData->sec_one_slider_mobile : $homeData->sec_one_slider
                ];
                $getSecOneSliderData = $this->getLeftSlider($secOneSliderConfig, $lang, $deviceType, $city);
                // section two left slider 
                $leftSliderConfig = [
                    'slide_ids' => $deviceType == 'mobile' ? $homeData->sec_two_slider_left_mobile : $homeData->sec_two_slider_left
                ];
                $getLeftsliderData = $this->getLeftSlider($leftSliderConfig, $lang, $deviceType, $city);

                // section two top slider 
                $sectionTwoTopSliderConfig = [
                    'slide_ids' => $deviceType == 'mobile' ? $homeData->sec_two_slider_top_mobile : $homeData->sec_two_slider_top
                ];
                $getsectionTwoTopsliderData = $this->getLeftSlider($sectionTwoTopSliderConfig, $lang, $deviceType, $city);


                // section five slider 
                $sectionfiveSliderConfig = [
                    'slide_ids' => $deviceType == 'mobile' ? $homeData->sec_five_slider_mobile : $homeData->sec_five_slider
                ];
                $getsectionfivesliderData = $this->getLeftSlider($sectionfiveSliderConfig, $lang, $deviceType, $city);


                // Process section four data using your ProductListingHelperNew
                $sectionFour = $this->productDataWithHelper(
                    $homeData->homepagedata->where('type', 1),
                    $lang,
                    $deviceType,
                    $city,
                    $take = 8,
                    true // Only load products on first iteration
                );


                // Optimized category loading
                $categoryIds = array_filter(explode(',', $homeData->sec_three_categories ?? ''));
                $categories = !empty($categoryIds) 
                    ? Productcategory::whereIn('id', $categoryIds)
                        ->select(
                            [
                                'id', 
                                $lang === 'en' ? 'name' : 'name_arabic as name', 
                                'slug', 
                                $deviceType == 'mobile' ? 'mobile_image_media' : 'web_image_media'
                            ]
                        )
                        ->orderByRaw("FIELD(id, " . implode(',', $categoryIds) . ")")
                        ->get()
                        ->keyBy('id')
                    : collect();


                // Build response with categories in original order
                $response = [
                    'first_five_sec' => array_merge(
                        $homeData->toArray(),
                        [
                            'section_one_slider_data' => $getSecOneSliderData,
                            'section_two_slider_left' => $getLeftsliderData,
                            'section_two_slider_top' => $getsectionTwoTopsliderData,
                            'sec_three_categories' => array_values(
                                array_filter(
                                    array_map(function($id) use ($categories, $deviceType, $lang) {
                                        if (!isset($categories[$id])) {
                                            return null;
                                        }
                                        
                                        $category = $categories[$id];
                                        return [
                                            'id' => $category['id'],
                                            'name' => $category['name'],
                                            'slug' => $category['slug'],
                                            'image' => $deviceType == 'mobile'
                                            ? ($category->MobileMediaAppImage ? [
                                                'id' => $category->MobileMediaAppImage->id,
                                                'image' => $category->MobileMediaAppImage->image,
                                            ] : null)
                                            : ($category->WebMediaImage ? [
                                                'id' => $category->WebMediaImage->id,
                                                'image' => $category->WebMediaImage->image,
                                            ] : null)
                                        ];
                                    }, $categoryIds)
                                )
                            ),
                            'section_four' => $sectionFour,
                            'section_five_slider' => $getsectionfivesliderData
                        ]
                    ),
                ];

                // Cache the complete response
                Cache::put($cacheKey, $response, $seconds);
                // $this->logCacheCreation($cacheKey, 'homepage');

            } catch (\Exception $e) {
                Log::error("Homepage API Error: " . $e->getMessage());
                
                $response = [
                    'error' => 'Failed to load homepage data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
                return response()->json($response, 500);
            }
        }

        // Compress response if client supports it
        if (str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            $compressed = gzencode(json_encode($response), 9); // Level 9 for balance
            return response($compressed)->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Encoding' => 'gzip',
                'Vary' => 'Accept-Encoding'
            ]);
        }

        return response()->json($response);
    }

    public function getHomePagelatestTwo(Request $request)
    {
        // Configuration
        $seconds = 86400; // 24 hours cache
        $lang = $request->lang ?? 'ar';
        $deviceType = $request->device_type === 'mobile' ? 'mobile' : 'desktop';
        $cacheKey = "homepage_six_eleven_v2_{$lang}_{$deviceType}"; // Added version
        $city = $request->city ? $request->city : 'Jeddah';
        $columns = [];
        // Return cached response if available
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                // Select only needed columns
                $columns = [
                    'id',
                    $lang === 'en' ? 'sec_six_title' : 'sec_six_title_ar as sec_six_title',
                    'sec_seven_brands',
                    $deviceType == 'mobile' ? 'sec_eight_mobile_link as sec_eight_link' : 'sec_eight_link',
                    $deviceType == 'mobile' ? 'sec_eight_mobile_image as sec_eight_image' : 'sec_eight_image',
                    $deviceType == 'mobile' ? 'sec_eight_second_mobile_image as sec_eight_video' : 'sec_eight_video',
                    $lang === 'en' ? 'sec_eight_heading' : 'sec_eight_heading_ar as sec_eight_heading',
                    $lang === 'en' ? 'sec_eight_paragraph' : 'sec_eight_paragraph_ar as sec_eight_paragraph',
                    $lang === 'en' ? 'sec_eight_button_title' : 'sec_eight_button_title_ar as sec_eight_button_title',
                    'sec_eight_redirection_link',
                    $lang === 'en' ? 'sec_nine_title' : 'sec_nine_title_ar as sec_nine_title',
                    $lang === 'en' ? 'sec_nine_button_title' : 'sec_nine_button_title_ar as sec_nine_button_title',
                    'sec_nine_button_link',
                    'sec_nine_products',
                    $lang === 'en' ? 'sec_ten_title' : 'sec_ten_title_ar as sec_ten_title',
                    $lang === 'en' ? 'sec_ten_button_title' : 'sec_ten_button_title_ar as sec_ten_button_title',
                    'sec_ten_button_link',
                    'sec_ten_products',
                    'sec_eleven_slider',
                    $deviceType == 'mobile' ? 'sec_eleven_slider_mobile' : 'sec_eleven_slider',
                ];
                if ($deviceType == 'mobile') {
                    $columns[] = 'sec_eight_second_mobile_link';
                } 
                $homeData = HomePageThree::
                select($columns)
                ->firstOrFail();

                $homeData->makeHidden('homepagedata');

                // Process section four data using your ProductListingHelperNew
                $sectionSix = $this->productDataWithHelper(
                    $homeData->homepagedata->where('type', 2),
                    $lang,
                    $deviceType,
                    $city,
                    4,
                    true // Only load products on first iteration
                );

                // sec seven brands
                $brandIds = [];
                if(!empty($homeData->sec_seven_brands)) {
                    $brandIds = explode(',', $homeData->sec_seven_brands); 
                }

                $getBrands = Brand::whereIn('id', $brandIds)
                ->orderByRaw('FIELD(id, ' . implode(',', $brandIds) . ')')
                ->select([
                    'id', 
                    // $lang == 'ar' ? 'name_arabic as name' : 'name', 
                    'name', 
                    'name_arabic', 
                    'slug', 
                    'status', 
                    $deviceType == 'mobile' ? 'brand_app_image_media' : 'brand_image_media'
                ])
                // ->with(['category:id,icon,name',
                 ->with([
                    'category' => function ($q) use ($lang) {
                        $q->select([
                            'productcategories.id', 
                            'productcategories.image_link_app',
                            'productcategories.slug',
                            $lang == 'ar' ? 'productcategories.name_arabic as name' : 'productcategories.name',
                        ]);
                    },
                    $deviceType == 'mobile' ? 'BrandMediaAppImage' : 'BrandMediaImage' => function($query) use ($lang) {
                        $query->select([
                            'id', 
                            'image',
                            $lang === 'en' ? 'alt' : 'alt_arabic'
                        ]);
                    }
                ])
                ->get();


                // section nine products 
                $sectionNineProducts = [];
                if(!empty($homeData->sec_nine_products)) {
                    $filters = [
                        'productbyid' => explode(',', $homeData->sec_nine_products),
                        'lang' => $lang,
                        'device_type' => $deviceType,
                        'take' => 8,
                    ];
                    $sectionNineProducts = ProductListingHelperNew::productData($filters, false, false, $city);
                }


                // section ten products 
                $sectionTenProducts = [];
                if(!empty($homeData->sec_ten_products)) {
                    $filters = [
                        'productbyid' => explode(',', $homeData->sec_ten_products),
                        'lang' => $lang,
                        'device_type' => $deviceType,
                        'take' => 8,
                    ];
                    $sectionTenProducts = ProductListingHelperNew::productData($filters, false, false, $city);
                }

                // section eleven slider
                $sectionElevenSliderConfig = [
                    'slide_ids' => $deviceType == 'mobile' ? $homeData->sec_eleven_slider_mobile : $homeData->sec_eleven_slider
                ];
                $getsectionElevensliderData = $this->getLeftSlider($sectionElevenSliderConfig, $lang, $deviceType, $city);
                        

                // Build response with categories in original order
                $response = [
                    'six_eleven_sec' => array_merge(
                        $homeData->toArray(),
                        [
                            'section_six' => $sectionSix,
                            'section_seven' => $getBrands,
                            'section_nine' => $sectionNineProducts,
                            'section_ten' => $sectionTenProducts,
                            'section_eleven' => $getsectionElevensliderData
                        ]
                    ),
                ];

                // Cache the complete response
                Cache::put($cacheKey, $response, $seconds);
                // $this->logCacheCreation($cacheKey, 'homepage');

            } catch (\Exception $e) {
                Log::error("Homepage Second API Error: " . $e->getMessage());
                
                $response = [
                    'error' => 'Failed to load homepage second data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
                return response()->json($response, 500);
            }
        }

        // Compress response if client supports it
        if (str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            $compressed = gzencode(json_encode($response), 9); // Level 9 for balance
            return response($compressed)->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Encoding' => 'gzip',
                'Vary' => 'Accept-Encoding'
            ]);
        }
        return response()->json($response);
    }

    public function getHomePagelatestThree(Request $request)
    {
        // Configuration
        $seconds = 86400; // 24 hours cache
        $lang = $request->lang ?? 'ar';
        $deviceType = $request->device_type === 'mobile' ? 'mobile' : 'desktop';
        $cacheKey = "homepage_twelve_seventeen_v2_{$lang}_{$deviceType}"; // Added version
        $city = $request->city ? $request->city : 'Jeddah';
        
        // Return cached response if available
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                // Select only needed columns
                $columns = [
                    'id',
                    $lang === 'en' ? 'sec_twelve_title' : 'sec_twelve_title_ar as sec_twelve_title',
                    $lang === 'en' ? 'sec_twelve_button_title' : 'sec_twelve_button_title_ar as sec_twelve_button_title',
                    'sec_twelve_button_link',
                    'sec_twelve_products',
                    $deviceType == 'mobile' ? 'sec_thirteen_bg_mobile_image as sec_thirteen_bg_image' : 'sec_thirteen_bg_image',
                    $deviceType == 'mobile' ? 'sec_thirteen_bg_mobile_link as sec_thirteen_bg_link' : 'sec_thirteen_bg_link',
                    'sec_thirteen_image_one',
                    'sec_thirteen_link_one',
                    'sec_thirteen_image_two',
                    'sec_thirteen_link_two',
                    'sec_thirteen_image_three',
                    'sec_thirteen_link_three',
                    $deviceType == 'mobile' ? 'sec_fourteen_slider_mobile' : 'sec_fourteen_slider',
                    $lang == 'ar' ? 'sec_thirteen_button_title_ar as sec_thirteen_button_title' : 'sec_thirteen_button_title',
                    'sec_thirteen_button_link',
                    $lang === 'en' ? 'sec_fifteen_title' : 'sec_fifteen_title_ar as sec_fifteen_title',
                    $lang === 'en' ? 'sec_fifteen_button_title' : 'sec_fifteen_button_title_ar as sec_fifteen_button_title',
                    'sec_fifteen_button_link',
                    'sec_fifteen_products',
                    $lang === 'en' ? 'sec_sixteen_title' : 'sec_sixteen_title_ar as sec_sixteen_title',
                    $lang === 'en' ? 'sec_sixteen_button_title' : 'sec_sixteen_button_title_ar as sec_sixteen_button_title',
                    'sec_sixteen_button_link',
                    'sec_sixteen_products',
                    $lang === 'en' ? 'sec_seventeen_title' : 'sec_seventeen_title_ar as sec_seventeen_title',
                    $lang === 'en' ? 'sec_seventeen_button_title' : 'sec_seventeen_button_title_ar as sec_seventeen_button_title',
                    'sec_seventeen_button_link',
                    'sec_seventeen_products',
                    $lang === 'en' ? 'sec_eighteen_heading' : 'sec_eighteen_heading_ar as sec_eighteen_heading',
                    $lang === 'en' ? 'sec_eighteen_sub_heading' : 'sec_eighteen_sub_heading_ar as sec_eighteen_sub_heading',
                    'sec_eighteen_button_link',
                    'sec_eighteen_button_link',
                    'sec_eighteen_image_one',
                    'sec_eighteen_image_two',
                    'sec_eighteen_image_three',
                    'sec_eighteen_link_one',
                    'sec_eighteen_link_two',
                    'sec_eighteen_link_three'
                ];

                $homeData = HomePageThree::
                select($columns)
                ->firstOrFail();

                $homeData->makeHidden('homepagedata');

                // section twelve products 
                $sectionTwelveProducts = [];
                if(!empty($homeData->sec_twelve_products)) {
                    $filters = [
                        'productbyid' => explode(',', $homeData->sec_twelve_products),
                        'lang' => $lang,
                        'device_type' => $deviceType,
                        'take' => 8,
                    ];
                    $sectionTwelveProducts = ProductListingHelperNew::productData($filters, false, false, $city);
                }
                        

                // section fourteen slider
                $sectionFourteenSliderConfig = [
                    'slide_ids' => $deviceType == 'mobile' ? $homeData->sec_fourteen_slider_mobile : $homeData->sec_fourteen_slider
                ];
                $getsectionFourteensliderData = $this->getLeftSlider($sectionFourteenSliderConfig, $lang, $deviceType, $city);


                // section fifteen products 
                $sectionFifteenProducts = [];
                if(!empty($homeData->sec_fifteen_products)) {
                    $filters = [
                        'productbyid' => explode(',', $homeData->sec_fifteen_products),
                        'lang' => $lang,
                        'device_type' => $deviceType,
                        'take' => 8,
                    ];
                    $sectionFifteenProducts = ProductListingHelperNew::productData($filters, false, false, $city);
                }

                // section sixteen products 
                $sectionSixteenProducts = [];
                if(!empty($homeData->sec_sixteen_products)) {
                    $filters = [
                        'productbyid' => explode(',', $homeData->sec_sixteen_products),
                        'lang' => $lang,
                        'device_type' => $deviceType,
                        'take' => 8,
                    ];
                    $sectionSixteenProducts = ProductListingHelperNew::productData($filters, false, false, $city);
                }

                // section seventeen products 
                $sectionSeventeenProducts = [];
                if(!empty($homeData->sec_seventeen_products)) {
                    $filters = [
                        'productbyid' => explode(',', $homeData->sec_seventeen_products),
                        'lang' => $lang,
                        'device_type' => $deviceType,
                        'take' => 8,
                    ];
                    $sectionSeventeenProducts = ProductListingHelperNew::productData($filters, false, false, $city);
                }

                // Build response with categories in original order
                $response = [
                    'twelve_seventeen_sec' => array_merge(
                        $homeData->toArray(),
                        [
                            'sec_twelve_products' => $sectionTwelveProducts,
                            'section_fourteen' => $getsectionFourteensliderData,
                            'sec_fifteen_products' => $sectionFifteenProducts,
                            'sec_sixteen_products' => $sectionSixteenProducts,
                            'sec_seventeen_products' => $sectionSeventeenProducts
                        ]
                    ),
                ];

                // Cache the complete response
                Cache::put($cacheKey, $response, $seconds);
                // $this->logCacheCreation($cacheKey, 'homepage');

            } catch (\Exception $e) {
                Log::error("Homepage Second API Error: " . $e->getMessage());
                
                $response = [
                    'error' => 'Failed to load homepage second data',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ];
                
                return response()->json($response, 500);
            }
        }

        // Compress response if client supports it
        if (str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            $compressed = gzencode(json_encode($response), 9); // Level 9 for balance
            return response($compressed)->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Encoding' => 'gzip',
                'Vary' => 'Accept-Encoding'
            ]);
        }
        return response()->json($response);
    }

    protected function logCacheCreation($key, $type)
    {
        try {
            CacheStores::create([
                'key' => $key,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::error("Cache logging failed: " . $e->getMessage());
        }
    }

    protected function productDataWithHelper($homepagedata, $lang, $deviceType, $city, $take = 20,$loadProductsOnFirstOnly = false)
    {
        $productsLoaded = false;

        return $homepagedata->map(function($item) use ($lang, $deviceType, $city, $loadProductsOnFirstOnly, $take, &$productsLoaded) {
            // Notice the & before $productsLoaded - this makes it pass by reference
            if (!$item->category) return null;

            $responseItem = [
                'type' => $item->type,
                'row_id' => $item->id,
                'category' => [
                    'id' => $item->category->id,
                    'name' => $lang == 'ar' ? $item->category->name_arabic : $item->category->name,
                ],
                'products' => []
            ];

            // Determine if we should load products for this item
            $shouldLoadProducts = !$loadProductsOnFirstOnly || ($loadProductsOnFirstOnly && !$productsLoaded);

            if ($shouldLoadProducts && !empty($item->product_id)) {
                $filters = [
                    'productbyid' => explode(',', $item->product_id),
                    'take' => $take,
                    'lang' => $lang,
                    'device_type' => $deviceType
                ];
                
                $products = ProductListingHelperNew::productData($filters, false, false, $city);
                $responseItem['products'] = $products['products']['data'] ?? [];
                
                if ($loadProductsOnFirstOnly) {
                    $productsLoaded = true; // This will now persist between iterations
                }
            }

            return $responseItem;
        })->filter()->values()->toArray();
    }

    private function getLeftSlider($sliderConfig, $lang, $deviceType,$city) {
        if (!isset($sliderConfig['slide_ids']) && count($sliderConfig) == 0) return null;

        $getsliderids = explode(',', $sliderConfig['slide_ids']);
        try {
            $query = HomePageSlider::where('status', 1)
            ->with(['sliderImage' => function($query) use ($lang, $deviceType, $city) {
                $query->select(['id', 'slider_id', 'redirection_link', 'timer', 'image', 'status', 'city_id'])->where('status',1)->orderBy('sorting')
                ->when($city != null, function($q) use ($city) {
                    $q->where(function($q) use ($city) {
                        $q->whereNull('city_id')
                          ->orWhereHas('cityData', function($q) use ($city) {
                              $q->where('name', $city)
                                ->orWhere('name_arabic', $city);
                          });
                    });
                });
            }])
            ->whereIn('id', $getsliderids)
            ->select(
                [
                    'id',
                    'status'
                ]
            );
            return  $query->first();
        } 
        catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function getLatestCategoryProducts(Request $request, $type, $rowId) {
        $seconds = 86400;
        $lang = $request->lang ?? 'ar';
        $deviceType = $request->device_type === 'mobile' ? 'mobile' : 'desktop';
        $city = $request->city ? $request->city : 'Jeddah';
        $cacheKey = "homepage_get_latest_category_products_{$type}_{$rowId}_{$lang}_{$deviceType}"; // Added version
        
        if (Cache::has($cacheKey)) {
            $response = Cache::get($cacheKey);
        } else {
            try {
                $catProducts = HomePageThreeData::where('type', $type)
                ->with('category:id,name')
                // ->whereId($rowId)
                ->where('category_id', $rowId)
                ->select([
                    'type',
                    'id',
                    'category_id',
                    'product_id'
                ])
                ->get();
    
                $response = $catProducts->map(function($item) use ($lang, $city, $type,$deviceType) {
                    if (!$item->category) return null;
    
                    $responseItem = [
                        'type' => $item->type,
                        'row_id' => $item->id,
                        'category' => [
                            'id' => $item->category->id,
                            'name' => $lang == 'ar' ? $item->category->name_arabic : $item->category->name,
                        ],
                        'products' => []
                    ];
    
                    // Determine if we should load products for this item
                    $filters = [
                        'productbyid' => explode(',', $item->product_id),
                        'lang' => $lang,
                        'take' => $type == 2 ? 4 : 8,
                        'device_type' => $deviceType
                    ];
                    
                    $products = ProductListingHelperNew::productData($filters, false, false, $city);
                    $responseItem['products'] = $products['products']['data'] ?? [];
    
                    return $responseItem;
                })->filter()->values()->toArray();
    
                Cache::put($cacheKey, $response, $seconds);
                // $this->logCacheCreation($cacheKey, 'homepage');
                return response()->json($response);
            } catch (\Exception $e) {
                Log::error("Getting Product Failed: " . $e->getMessage());
            }
        }
        // Compress response if client supports it
        if (str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            $compressed = gzencode(json_encode($response), 9); // Level 9 for balance
            return response($compressed)->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Encoding' => 'gzip',
                'Vary' => 'Accept-Encoding'
            ]);
        }
    }

}