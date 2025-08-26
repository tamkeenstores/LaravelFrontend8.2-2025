<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\SubTags;
use App\Models\Brand;
use App\Models\TaxClasses;
use App\Models\ShippingClasses;
use App\Models\CategoryProduct;
use App\Models\ProductFeatures;
use App\Models\ProductSpecifications;
use App\Models\ProductSpecsDetails;
// use App\Models\Categoryproduct;
use App\Models\ProductTag;
use App\Models\ProductUpsale;
use App\Models\ProductGallery;
use App\Models\ProductRelatedBrand;
use App\Models\ProductRelatedCategory;
use App\Models\ProductPriceHistory;
use App\Models\ProductStockHistory;
use App\Models\ProductFaqs;
use App\Models\ProductQuestions;
use App\Traits\CrudTrait;
use Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductEmailExport;

class ProductApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'products';
    protected $relationKey = 'products_id';

    public function index(Request $request){
        // if($request->all()) {
        //     $stock_status = $request['status'];
        //     $brands = $request['brands'];
        //     $cat_filter = $request['cats'];
        //     if (count($cat_filter) >= 1) {
        //         $cat_pro = CategoryProduct::where('category_id', $cat_filter)->pluck('product_id')->toArray();
        //     } else {
        //         $cat_pro = CategoryProduct::pluck('product_id')->toArray();
        //     }
        //     $data = Product::with('productcategory:id,name,name_arabic','featuredImage:id,image,title')
        //     ->select(['id', 'name', 'sku', 'notes', 'quantity', 'price', 'sale_price', 'status', 'in_stock', 'brands', 'clicks', 'feature_image'])
        //     ->when($stock_status, function ($q) use ($stock_status) {
        //       if($stock_status == 0){
        //         return $q->where('status', 0);
        //       } elseif($stock_status == 1) {
        //         return $q->where('status', '=', 1);
        //       }
        //       elseif($stock_status == 2) {
        //         return $q->where('quantity', '<=', '0');
        //       }
        //       elseif($stock_status == 3){
        //         return $q->where('quantity', '>=', '0');
        //       }
        //     })
        //     ->when($brands, function ($q) use ($brands) {
        //       return $q->whereIn('brands', $brands);
        //     })
        //     ->when($cat_filter, function ($q) use ($cat_pro) {
        //       return $q->whereIn('id', $cat_pro);
        //     })
        //     ->limit(500)->get();
        // }
        // else
        // {
        //     $data = Product::with('productcategory:id,name,name_arabic','featuredImage:id,image,title')->select(['id', 'name', 'sku', 'notes', 'quantity', 'price', 'sale_price', 'status', 'clicks', 'feature_image'])->limit(500)->get();
        // }
        $search = $request['search'];
        $order = $request['sort'];
        $take = isset($request['page_size']) ? $request['page_size'] : 100;
        $pageNumber = isset($request['page']) ? $request['page'] : 1;
        $stock_status = $request['status'] ?? false;
        $brands = $request['brands'];
        $cat_filter = $request['cats'];
        if (count($cat_filter) >= 1) {
            $cat_pro = CategoryProduct::where('category_id', $cat_filter)->pluck('product_id')->toArray();
        } else {
            $cat_pro = CategoryProduct::pluck('product_id')->toArray();
        }
        $data = Product::with('productcategory:id,name,name_arabic','featuredImage:id,image,title')
        ->select(['id', 'name', 'sku','ln_sku', 'notes', 'quantity','amazon_stock', 'price', 'sale_price', 'status', 'clicks', 'feature_image', 'pre_order'])
        ->orderBy('id', 'desc')
        ->when($search, function ($q) use ($search) {
            return $q->where(function($query) use($search){
                return $query->where("name","LIKE","%{$search}%")
                    ->orWhere("name_arabic","LIKE","%{$search}%")
                    ->orWhere("sku","LIKE","%{$search}%")
                    ->orWhere("notes","LIKE","%{$search}%");
            });
        })
        ->when($order, function ($q) use ($order) {
            return $q->orderBy($order[0], $order[1]);
        })
        ->when($stock_status !== false, function ($q) use ($stock_status) {
            if($stock_status == 0){
                return $q->where('status', 0);
            } elseif($stock_status == 1) {
                return $q->where('status', '=', 1);
            }
            elseif($stock_status == 2) {
                return $q->where('quantity', '<=', '0');
            }
            elseif($stock_status == 3){
                return $q->where('quantity', '>', '0');
            }
        })
        ->when($brands, function ($q) use ($brands) {
            return $q->whereIn('brands', $brands);
        })
        ->when($cat_filter, function ($q) use ($cat_pro) {
            return $q->whereIn('id', $cat_pro);
        })
        ->orderBy('id', 'desc')
        ->paginate($take, ['*'], 'page', $pageNumber);
        //->limit(500)
        //->get();
        // return response()->json(['data' => $data]);
        
        $response = [
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
    
    public function model() {
        $data = ['limit' => 500, 'model' => Product::class, 'sort' => ['id','desc']];
        return $data;
    }
    
    public function store(Request $request) 
    {
        $products = Product::create([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'short_description' => isset($request->short_description) ? $request->short_description : null,
            'short_description_arabic' => isset($request->short_description_arabic) ? $request->short_description_arabic : null,
            'description' => isset($request->description) ? $request->description : null,
            'description_arabic' => isset($request->description_arabic) ? $request->description_arabic : null,
            'slug' => isset($request->slug) ? $request->slug : null,
            'price' => isset($request->price) ? $request->price : 0,
            'sale_price' => isset($request->sale_price) ? $request->sale_price : 0,
            'sort' => isset($request->sort) ? $request->sort : 0,
            'notes' => isset($request->notes) ? $request->notes : null,
            'sku' => isset($request->sku) ? $request->sku : null,
            'ln_sku' => isset($request->ln_sku) ? $request->ln_sku : null,
            'quantity' => isset($request->quantity) ? $request->quantity : null,
            'amazon_stock' => isset($request->amazon_stock) ? $request->amazon_stock : 0,
            'in_stock' => isset($request->in_stock) ? $request->in_stock : 0,
            'shipping_class' => isset($request->shipping_class) ? $request->shipping_class : null,
            'custom_badge_en' => isset($request->custom_badge_en) ? $request->custom_badge_en : null,
            'custom_badge_ar' => isset($request->custom_badge_ar) ? $request->custom_badge_ar : null,
            'meta_title_en' => isset($request->meta_title_en) ? $request->meta_title_en : null,
            'meta_title_ar' => isset($request->meta_title_ar) ? $request->meta_title_ar : null,
            'meta_tag_en' => isset($request->meta_tag_en) ? $request->meta_tag_en : null,
            'meta_tag_ar' => isset($request->meta_tag_ar) ? $request->meta_tag_ar : null,
            'meta_canonical_en' => isset($request->meta_canonical_en) ? $request->meta_canonical_en : null,
            'meta_canonical_ar' => isset($request->meta_canonical_ar) ? $request->meta_canonical_ar : null,
            'meta_description_en' => isset($request->meta_description_en) ? $request->meta_description_en : null,
            'meta_description_ar' => isset($request->meta_description_ar) ? $request->meta_description_ar : null,
            'status' => isset($request->status) ? $request->status : 0,
            'best_seller' => isset($request->best_seller) ? $request->best_seller : 0,
            'free_gift' => isset($request->free_gift) ? $request->free_gift : 0,
            'low_in_stock' => isset($request->low_in_stock) ? $request->low_in_stock : 0,
            'top_selling' => isset($request->top_selling) ? $request->top_selling : 0,
            'brands' => isset($request->brands) ? $request->brands : null,
            'feature_image' => isset($request->feature_image) ? $request->feature_image : null,
            'pre_order' => isset($request->pre_order) ? $request->pre_order : 0,
            'no_of_days' => isset($request->no_of_days) ? $request->no_of_days : null,
            'related_type' => isset($request->related_type) ? $request->related_type : 0,
            'warranty' => isset($request->warranty) ? $request->warranty : null,
        ]);
        
        if (isset($request->features_id)) {
             foreach ($request->features_id as $k => $value) {
                //   print_r($value['feature_ar']);die();
                $featuresdata = [
                     'product_id' => $products->id,
                    'feature_en' => isset($value['feature_en']) ? $value['feature_en'] : null,
                    'feature_ar' => isset($value['feature_ar']) ? $value['feature_ar'] : null,
                    'feature_image_link' => isset($value['feature_image_link']) ? $value['feature_image_link'] : null,
                ];
                // $products->featuresStore()->attach($featuresdata);
                ProductFeatures::create($featuresdata);
             }
         }
        
        if (isset($request->specs_id)) {
             foreach ($request->specs_id as $k => $value) {
                $specsdata = [
                    'product_id' => $products->id,
                    'heading_en' => isset($value['heading_en']) ? $value['heading_en'] : null,
                    'heading_ar' => isset($value['heading_ar']) ? $value['heading_ar'] : null,
                ];
                $specs = ProductSpecifications::create($specsdata);
                
                foreach ($request->specs_inner_id[$k] as $key => $val) {
                    // print_r($request->specs_inner_id[$k]);die();
                    $specsinnerdatadata = [
                        'specs_id' => $specs->id,
                        'specs_en' => isset($val['specs_en']) ? $val['specs_en'] : null,
                        'specs_ar' => isset($val['specs_ar']) ? $val['specs_ar'] : null,
                        'value_en' => isset($val['value_en']) ? $val['value_en'] : null,
                        'value_ar' => isset($val['value_ar']) ? $val['value_ar'] : null,
                    ];
                 ProductSpecsDetails::create($specsinnerdatadata);
                }
                // print_r($request->specs_inner_id[$k]);die();
                
                // $products->specsStore()->attach($specsdata);
                    
                
             }
        }
         
         if (isset($request->upsale_id)) {
             foreach ($request->upsale_id as $k => $value) {
                //   print_r($value['upsale_id']);die();
                $upsaledata = [
                    'product_id' => $products->id,
                    'upsale_id' => isset($value['upsale_id']) ? $value['upsale_id'] : null,
                ];
                $products->upsale()->attach($upsaledata);
                // ProductFeatures::create($featuresdata);
             }
         }
        
        if($request->related_type == 1) {
             if (isset($request->related_brands)) {
                 foreach ($request->related_brands as $k => $value) {
                    //   print_r($value['brand_id']);die();
                    $relatedbranddata = [
                        'product_id' => $products->id,
                        'brand_id' => isset($value['brand_id']) ? $value['brand_id'] : null,
                    ];
                    $products->productrelatedbrand()->attach($relatedbranddata);
                    // ProductFeatures::create($featuresdata);
                 }
             }
        }
        if($request->related_type == 2) {
            if (isset($request->related_category)) {
                 foreach ($request->related_category as $k => $value) {
                    $relatedcatdata = [
                        'product_id' => $products->id,
                        'category_id' => isset($value['category_id']) ? $value['category_id'] : null,
                    ];
                    $products->productrelatedcategory()->attach($relatedcatdata);
                    // ProductFeatures::create($featuresdata);
                 }
             }
        }
        
         if (isset($request->tag_id)) {
             foreach ($request->tag_id as $k => $value) {
                $tagdata = [
                    'product_id' => $products->id,
                    'sub_tag_id' => isset($value['tag_id']) ? $value['tag_id'] : null,
                ];
                $products->tags()->attach($tagdata);
                // ProductFeatures::create($featuresdata);
             }
         }
         
        if (isset($request->productcategory_id)) {
             foreach ($request->productcategory_id as $k => $value) {
                $catdata = [
                    'product_id' => $products->id,
                    'category_id' => isset($value['category_id']) ? $value['category_id'] : null,
                ];
                $products->productcategory()->attach($catdata);
                // ProductFeatures::create($featuresdata);
             }
         }
         
         if (isset($request->image_gallery)) {
             foreach ($request->image_gallery as $k => $value) {
                $gallerydata = [
                    'product_id' => $products->id,
                    'image' => isset($value['image']) ? $value['image'] : null,
                ];
                // $products->galleryStore()->attach($gallerydata);
                ProductGallery::create($gallerydata);
             }
         }
         
         if (isset($request->questions_id)) {
             foreach ($request->questions_id as $k => $value) {
                $questionsdata = [
                    'product_id' => $products->id,
                    'questions_id' => isset($value['questions_id']) ? $value['questions_id'] : null,
                ];
                $products->questions()->attach($questionsdata);
                // ProductFeatures::create($featuresdata);
             }
         }
         
        return response()->json(['success' => true, 'message' => 'Product Has been Created Successfully']);        
    }

    // product copy
    public function productCopy($id) {
        $success = false;
        $message = 'Error! Product not copied.';
        $productdata = Product::where('id', $id)->first();
        $productCount = Product::where('name', $productdata->name)->where('name_arabic', $productdata->name_arabic)->count();
        $products = Product::create([
            'name' => $productdata->name,
            'name_arabic' => $productdata->name_arabic,
            'short_description' => isset($productdata->short_description) ? $productdata->short_description : null,
            'short_description_arabic' => isset($productdata->short_description_arabic) ? $productdata->short_description_arabic : null,
            'description' => isset($productdata->description) ? $productdata->description : null,
            'description_arabic' => isset($productdata->description_arabic) ? $productdata->description_arabic : null,
            'slug' => isset($productdata->slug) ? $productdata->slug . '-' . $productCount : null,
            'price' => isset($productdata->price) ? $productdata->price : 0,
            'sale_price' => isset($productdata->sale_price) ? $productdata->sale_price : 0,
            'sort' => isset($productdata->sort) ? $productdata->sort : 0,
            'notes' => isset($productdata->notes) ? $productdata->notes : null,
            'sku' => isset($productdata->sku) ? $productdata->sku . $productCount : null,
            'ln_sku' => isset($productdata->ln_sku) ? $productdata->ln_sku : null,
            'quantity' => isset($productdata->quantity) ? $productdata->quantity : null,
            'amazon_stock' => isset($productdata->amazon_stock) ? $productdata->amazon_stock : 0,
            'in_stock' => isset($productdata->in_stock) ? $productdata->in_stock : 0,
            'shipping_class' => isset($productdata->shipping_class) ? $productdata->shipping_class : null,
            'custom_badge_en' => isset($productdata->custom_badge_en) ? $productdata->custom_badge_en : null,
            'custom_badge_ar' => isset($productdata->custom_badge_ar) ? $productdata->custom_badge_ar : null,
            'meta_title_en' => isset($productdata->meta_title_en) ? $productdata->meta_title_en : null,
            'meta_title_ar' => isset($productdata->meta_title_ar) ? $productdata->meta_title_ar : null,
            'meta_tag_en' => isset($productdata->meta_tag_en) ? $productdata->meta_tag_en : null,
            'meta_tag_ar' => isset($productdata->meta_tag_ar) ? $productdata->meta_tag_ar : null,
            'meta_canonical_en' => isset($productdata->meta_canonical_en) ? $productdata->meta_canonical_en : null,
            'meta_canonical_ar' => isset($productdata->meta_canonical_ar) ? $productdata->meta_canonical_ar : null,
            'meta_description_en' => isset($productdata->meta_description_en) ? $productdata->meta_description_en : null,
            'meta_description_ar' => isset($productdata->meta_description_ar) ? $productdata->meta_description_ar : null,
            'status' => 0,
            'best_seller' => isset($productdata->best_seller) ? $productdata->best_seller : 0,
            'free_gift' => isset($productdata->free_gift) ? $productdata->free_gift : 0,
            'low_in_stock' => isset($productdata->low_in_stock) ? $productdata->low_in_stock : 0,
            'top_selling' => isset($productdata->top_selling) ? $productdata->top_selling : 0,
            'brands' => isset($productdata->brands) ? $productdata->brands : null,
            'feature_image' => isset($productdata->feature_image) ? $productdata->feature_image : null,
            'pre_order' => isset($productdata->pre_order) ? $productdata->pre_order : 0,
            'no_of_days' => isset($productdata->no_of_days) ? $productdata->no_of_days : null,
            'related_type' => isset($productdata->related_type) ? $productdata->related_type : 0,
            'warranty' => isset($productdata->warranty) ? $productdata->warranty : null,
        ]);

        $feature_data = ProductFeatures::where('product_id', '=',$id)->get();
        if(isset($feature_data) && count($feature_data) >= 1) {
            foreach ($feature_data as $k => $value) {
                $featuresdata = [
                    'product_id' => $products->id,
                    'feature_en' => isset($value->feature_en) ? $value->feature_en : null,
                    'feature_ar' => isset($value->feature_ar) ? $value->feature_ar : null,
                    'feature_image_link' => isset($value->feature_image_link) ? $value->feature_image_link : null,
                ];
                ProductFeatures::create($featuresdata);
            }
        }

        $specs_data = ProductSpecifications::where('product_id', '=',$id)->get();
        if(isset($specs_data) && count($specs_data) >= 1) {
            foreach ($specs_data as $k => $value) {
                $specsdata = [
                    'product_id' => $products->id,
                    'heading_en' => isset($value->heading_en) ? $value->heading_en : null,
                    'heading_ar' => isset($value->heading_ar) ? $value->heading_ar : null,
                ];
                $specs = ProductSpecifications::create($specsdata);
                $specsdetail_data = ProductSpecsDetails::where('specs_id', '=',$value->id)->get();
                foreach ($specsdetail_data as $key => $val) {
                    $specsinnerdatadata = [
                        'specs_id' => $specs->id,
                        'specs_en' => isset($val->specs_en) ? $val->specs_en : null,
                        'specs_ar' => isset($val->specs_ar) ? $val->specs_ar : null,
                        'value_en' => isset($val->value_en) ? $val->value_en : null,
                        'value_ar' => isset($val->value_ar) ? $val->value_ar : null,
                    ];
                    ProductSpecsDetails::create($specsinnerdatadata);
                }  
            }
        }
        
        $upsale = ProductUpsale::where('product_id', $id)->get();
        if (isset($upsale) && count($upsale) >= 1) {
             foreach ($upsale as $k => $value) {
                $upsaledata = [
                    'product_id' => $products->id,
                    'upsale_id' => isset($value->upsale_id) ? $value->upsale_id : null,
                ];
                ProductUpsale::create($upsaledata);
             }
         }
         
        if($productdata->related_type == 1) {
            $relatedbrand_data = ProductRelatedBrand::where('product_id', '=',$id)->get();
            if(isset($relatedbrand_data) && count($relatedbrand_data) >= 1) {
                foreach ($relatedbrand_data as $k => $value) {
                    $relatedbranddata = [
                        'product_id' => $products->id,
                        'brand_id' => isset($value->brand_id) ? $value->brand_id : null,
                    ];
                    ProductRelatedBrand::create($relatedbranddata);
                }
            }
        }
        if($productdata->related_type == 2) {
            $relatedcat_data = ProductRelatedCategory::where('product_id', '=',$id)->get();
            if(isset($relatedcat_data) && count($relatedcat_data) >= 1) {
                foreach ($relatedcat_data as $k => $value) {
                    $relatedcatdata = [
                        'product_id' => $products->id,
                        'category_id' => isset($value->category_id) ? $value->category_id : null,
                    ];
                    ProductRelatedCategory::create($relatedcatdata);
                }
            }
        }
        
        $tag_data = ProductTag::where('product_id', '=',$id)->get();
        if(isset($tag_data) && count($tag_data) >= 1) {
            foreach ($tag_data as $k => $value) {
                $tagdata = [
                    'product_id' => $products->id,
                    'sub_tag_id' => isset($value->sub_tag_id) ? $value->sub_tag_id : null,
                ];
                ProductTag::create($tagdata);
            }
        }
         
        $category_data = Categoryproduct::where('product_id', '=',$id)->get();
        if(isset($category_data) && count($category_data) >= 1) {
            foreach ($category_data as $k => $value) {
                $catdata = [
                    'product_id' => $products->id,
                    'category_id' => isset($value->category_id) ? $value->category_id : null,
                ];
                Categoryproduct::create($catdata);
            }
        }
         
        $gallery_data = ProductGallery::where('product_id', '=',$id)->get();
        if(isset($gallery_data) && count($gallery_data) >= 1) {
            foreach ($gallery_data as $k => $value) {
                $gallerydata = [
                    'product_id' => $products->id,
                    'image' => isset($value->image) ? $value->image : null,
                ];
                ProductGallery::create($gallerydata);
            }
        }
        
        $category_data = Categoryproduct::where('product_id', '=',$id)->get();
        if(isset($category_data) && count($category_data) >= 1) {
            foreach ($category_data as $k => $value) {
                $catdata = [
                    'product_id' => $products->id,
                    'category_id' => isset($value->category_id) ? $value->category_id : null,
                ];
                Categoryproduct::create($catdata);
            }
        }
        
        $ques_data = ProductQuestions::where('product_id', '=',$id)->get();
        if(isset($ques_data) && count($ques_data) >= 1) {
            foreach ($ques_data as $k => $value) {
                $catdata = [
                    'product_id' => $products->id,
                    'questions_id' => isset($value->questions_id) ? $value->questions_id : null,
                ];
                ProductQuestions::create($catdata);
            }
        }
        $success = true;
        $message = 'Success! Product Has been Copied Successfully';

        return response()->json(['success' => $success, 'message' => $message]);
    }
    
    public function edit($resource_id) {
        $editdata = Product::with('specs:id,product_id,heading_en,heading_ar', 'specs.specdetails', 'features:id,product_id,feature_en,feature_ar,feature_image_link',
        'upsale', 'tags:id,tag_id,name', 'brand:id,name', 'featuredImage:id,image', 'shippingclass:id,name', 'gallery.galleryImage', 'productrelatedcategory', 'productrelatedbrand', 'productcategory:id,name'
        , 'questions')->findOrFail($resource_id);
        $data = [];
        $data['category'] = Productcategory::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']);
        $data['tags'] = SubTags::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']);
        $data['brands'] = Brand::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'name as label']);
        $data['upsells_products'] = Product::where('status','=',1)->orderby('id', 'DESC')->get(['id as value', 'sku as label']);
        $data['tax_classes'] = TaxClasses::get(['id as value', 'name as label']);
        $data['shipping_classes'] = ShippingClasses::get(['id as value', 'name as label']);
        $data['faq_questions'] =  ProductFaqs::get(['id as value', 'question as label']);
        return response()->json(['editdata' => $editdata, 'data' => $data]);
    }
    
    public function update(Request $request, $id) {
        // print_r($request->related_type);die();
        
        // Sale Price History Work
        if($request->sale_price){
            $historycount = ProductPriceHistory::where('product_id', $id)->count();
            if($historycount >= 10){
                $historydel = ProductPriceHistory::where('product_id', $id)->orderBy('created_at', 'Asc')->first();
                $historydel->delete();
                $saleprice = $request->sale_price;
                $oldprodata = Product::where('id', $id)->first();
                $exists = Product::where('id', $id)->where('sale_price', $saleprice)->count();
                if($exists >= 1){
                    $salehistory = false;
                }
                else {
                    $pricehistory = [
                        'product_id' => $id,
                        'old_sale_price' => $oldprodata->sale_price ? $oldprodata->sale_price : null,
                        'sale_price' => $request->sale_price,
                    ];
                     ProductPriceHistory::create($pricehistory);
                     $salehistory = true;
                }
            }
            else {
                $saleprice = $request->sale_price;
                $oldprodata = Product::where('id', $id)->first();
                $exists = Product::where('id', $id)->where('sale_price', $saleprice)->count();
                if($exists >= 1){
                    $salehistory = false;
                }
                else {
                    $pricehistory = [
                        'product_id' => $id,
                        'old_sale_price' => $oldprodata->sale_price ? $oldprodata->sale_price : null,
                        'sale_price' => $request->sale_price,
                    ];
                     ProductPriceHistory::create($pricehistory);
                     $salehistory = true;
                }
            }
        }
        
        // Qty History Work
        if($request->quantity){
            $historycount = ProductStockHistory::where('product_id', $id)->count();
            if($historycount >= 10){
                $historydel = ProductStockHistory::where('product_id', $id)->orderBy('created_at', 'Asc')->first();
                $historydel->delete();
                $saleprice = $request->quantity;
                $oldprodata = Product::where('id', $id)->first();
                $exists = Product::where('id', $id)->where('quantity', $saleprice)->count();
                if($exists >= 1){
                    $salehistory = false;
                }
                else {
                    $pricehistory = [
                        'product_id' => $id,
                        'old_qty' => $oldprodata->quantity ? $oldprodata->quantity : 0,
                        'qty' => $request->quantity,
                    ];
                     ProductStockHistory::create($pricehistory);
                     $salehistory = true;
                }
            }
            else {
                $saleprice = $request->quantity;
                $oldprodata = Product::where('id', $id)->first();
                $exists = Product::where('id', $id)->where('quantity', $saleprice)->count();
                if($exists >= 1){
                    $salehistory = false;
                }
                else {
                    $pricehistory = [
                        'product_id' => $id,
                        'old_qty' => $oldprodata->quantity ? $oldprodata->quantity : 0,
                        'qty' => $request->quantity,
                    ];
                     ProductStockHistory::create($pricehistory);
                     $salehistory = true;
                }
            }
        }
        
        
        if (isset($request->features_id)) {
            $feature_data = ProductFeatures::where('product_id', '=',$id)->get();
            $feature_data->each->delete();
            
             foreach ($request->features_id as $k => $value) {
                //   print_r($value['feature_ar']);die();
                $featuresdata = [
                     'product_id' => $id,
                    'feature_en' => isset($value['feature_en']) ? $value['feature_en'] : null,
                    'feature_ar' => isset($value['feature_ar']) ? $value['feature_ar'] : null,
                    'feature_image_link' => isset($value['feature_image_link']) ? $value['feature_image_link'] : null,
                ];
                // $products->featuresStore()->attach($featuresdata);
                ProductFeatures::create($featuresdata);
             }
         }
         
        if (isset($request->specs_id)) {
            $specs_data = ProductSpecifications::where('product_id', '=',$id)->get();
            $specs_data->each->delete();
             foreach ($request->specs_id as $k => $value) {
                $specsdata = [
                    'product_id' => $id,
                    'heading_en' => isset($value['heading_en']) ? $value['heading_en'] : null,
                    'heading_ar' => isset($value['heading_ar']) ? $value['heading_ar'] : null,
                ];
                $specs = ProductSpecifications::create($specsdata);
                // print_r($request->specs_inner_id);die();
                $specsdetail_data = ProductSpecsDetails::where('specs_id', '=',$specs->id)->get();
                $specsdetail_data->each->delete();
                
                foreach ($request->specs_inner_id[$k] as $key => $val) {
                    // print_r($request->specs_inner_id[$k]);die();
                    $specsinnerdatadata = [
                        'specs_id' => $specs->id,
                        'specs_en' => isset($val['specs_en']) ? $val['specs_en'] : null,
                        'specs_ar' => isset($val['specs_ar']) ? $val['specs_ar'] : null,
                        'value_en' => isset($val['value_en']) ? $val['value_en'] : null,
                        'value_ar' => isset($val['value_ar']) ? $val['value_ar'] : null,
                    ];
                 ProductSpecsDetails::create($specsinnerdatadata);
                }
                // print_r($request->specs_inner_id[$k]);die();
                
                // $products->specsStore()->attach($specsdata);
                    
                
             }
        }
        $row = Product::whereId($id)->first();
        
        if (isset($request->upsale_id)) {
             foreach ($request->upsale_id as $k => $value) {
                //   print_r($value['upsale_id']);die();
                $upsaledata = [
                    'product_id' => $id,
                    'upsale_id' => isset($value['upsale_id']) ? $value['upsale_id'] : null,
                ];
                // $row->upsale()->sync($upsaledata);
                // $products->upsale()->attach($upsaledata);
                ProductUpsale::create($upsaledata);
             }
         }
         
        if($request->related_type == 1) {
             if (isset($request->related_brands)) {
                 $relatedbrand_data = ProductRelatedBrand::where('product_id', '=',$id)->get();
                 $relatedbrand_data->each->delete();
                 $relatedcat_data = ProductRelatedCategory::where('product_id', '=',$id)->get();
                $relatedcat_data->each->delete();
                 foreach ($request->related_brands as $k => $value) {
                    //   print_r($value['brand_id']);die();
                    $relatedbranddata = [
                        'product_id' => $id,
                        'brand_id' => isset($value['brand_id']) ? $value['brand_id'] : null,
                    ];
                    // $row->productrelatedbrand()->sync($relatedbranddata);
                    // $products->productrelatedbrand()->attach($relatedbranddata);
                    ProductRelatedBrand::create($relatedbranddata);
                 }
             }
        }
        if($request->related_type == 0) {
            if (isset($request->related_category)) {
                $relatedbrand_data = ProductRelatedBrand::where('product_id', '=',$id)->get();
                 $relatedbrand_data->each->delete();
                $relatedcat_data = ProductRelatedCategory::where('product_id', '=',$id)->get();
                $relatedcat_data->each->delete();
                 foreach ($request->related_category as $k => $value) {
                    $relatedcatdata = [
                        'product_id' => $id,
                        'category_id' => isset($value['category_id']) ? $value['category_id'] : null,
                    ];
                    // $row->productrelatedcategory()->sync($relatedcatdata);
                    // $products->productrelatedcategory()->attach($relatedcatdata);
                    ProductRelatedCategory::create($relatedcatdata);
                 }
             }
        }
        
        if (isset($request->tag_id)) {
            $tag_data = ProductTag::where('product_id', '=',$id)->get();
            $tag_data->each->delete();
             foreach ($request->tag_id as $k => $value) {
                $tagdata = [
                    'product_id' => $id,
                    'sub_tag_id' => isset($value['sub_tag_id']) ? $value['sub_tag_id'] : null,
                ];
                // $row->tags()->sync($tagdata);
                // $products->tags()->attach($tagdata);
                ProductTag::create($tagdata);
             }
         }
         
        if (isset($request->productcategory_id)) {
            $category_data = Categoryproduct::where('product_id', '=',$id)->get();
            $category_data->each->delete();
             foreach ($request->productcategory_id as $k => $value) {
                $catdata = [
                    'product_id' => $id,
                    'category_id' => isset($value['category_id']) ? $value['category_id'] : null,
                ];
                // $row->productcategory()->sync($catdata);
                // $products->productcategory()->attach($catdata);
                Categoryproduct::create($catdata);
             }
         }
         
         if (isset($request->questions_id)) {
            $que_data = ProductQuestions::where('product_id', '=',$id)->get();
            $que_data->each->delete();
             foreach ($request->questions_id as $k => $value) {
                $quedata = [
                    'product_id' => $id,
                    'questions_id' => isset($value['questions_id']) ? $value['questions_id'] : null,
                ];
                // $row->tags()->sync($tagdata);
                // $products->tags()->attach($tagdata);
                ProductQuestions::create($quedata);
             }
         }
         
         if (isset($request->image_gallery)) {
             $gallery_data = ProductGallery::where('product_id', '=',$id)->get();
            $gallery_data->each->delete();
             foreach ($request->image_gallery as $k => $value) {
                $gallerydata = [
                    'product_id' => $id,
                    'image' => isset($value['image']) ? $value['image'] : null,
                ];
                // $products->galleryStore()->attach($gallerydata);
                ProductGallery::create($gallerydata);
             }
         }
         
        $products = Product::whereId($id)->update([
            'name' => $request->get('name'),
            'name_arabic' => $request->get('name_arabic'),
            'short_description' => isset($request->short_description) ? $request->short_description : null,
            'short_description_arabic' => isset($request->short_description_arabic) ? $request->short_description_arabic : null,
            'description' => isset($request->description) ? $request->description : null,
            'description_arabic' => isset($request->description_arabic) ? $request->description_arabic : null,
            'slug' => isset($request->slug) ? $request->slug : null,
            'price' => isset($request->price) ? $request->price : 0,
            'sale_price' => isset($request->sale_price) ? $request->sale_price : 0,
            'sort' => isset($request->sort) ? $request->sort : 0,
            'notes' => isset($request->notes) ? $request->notes : null,
            'sku' => isset($request->sku) ? $request->sku : null,
            'ln_sku' => isset($request->ln_sku) ? $request->ln_sku : null,
            'quantity' => isset($request->quantity) ? $request->quantity : null,
            'amazon_stock' => isset($request->amazon_stock) ? $request->amazon_stock : 0,
            'in_stock' => isset($request->in_stock) ? $request->in_stock : 0,
            'shipping_class' => isset($request->shipping_class) ? $request->shipping_class : null,
            'custom_badge_en' => isset($request->custom_badge_en) ? $request->custom_badge_en : null,
            'custom_badge_ar' => isset($request->custom_badge_ar) ? $request->custom_badge_ar : null,
            'meta_title_en' => isset($request->meta_title_en) ? $request->meta_title_en : null,
            'meta_title_ar' => isset($request->meta_title_ar) ? $request->meta_title_ar : null,
            'meta_tag_en' => isset($request->meta_tag_en) ? $request->meta_tag_en : null,
            'meta_tag_ar' => isset($request->meta_tag_ar) ? $request->meta_tag_ar : null,
            'meta_canonical_en' => isset($request->meta_canonical_en) ? $request->meta_canonical_en : null,
            'meta_canonical_ar' => isset($request->meta_canonical_ar) ? $request->meta_canonical_ar : null,
            'meta_description_en' => isset($request->meta_description_en) ? $request->meta_description_en : null,
            'meta_description_ar' => isset($request->meta_description_ar) ? $request->meta_description_ar : null,
            'status' => isset($request->status) ? $request->status : 0,
            'best_seller' => isset($request->best_seller) ? $request->best_seller : 0,
            'free_gift' => isset($request->free_gift) ? $request->free_gift : 0,
            'low_in_stock' => isset($request->low_in_stock) ? $request->low_in_stock : 0,
            'top_selling' => isset($request->top_selling) ? $request->top_selling : 0,
            'brands' => isset($request->brands) ? $request->brands : null,
            'feature_image' => isset($request->feature_image) ? $request->feature_image : null,
            'pre_order' => isset($request->pre_order) ? $request->pre_order : 0,
            'no_of_days' => isset($request->no_of_days) ? $request->no_of_days : null,
            'related_type' => isset($request->related_type) ? $request->related_type : 0,
            'warranty' => isset($request->warranty) ? $request->warranty : null,
        ]);
        
        return response()->json(['success' => true,'sale_history' => $salehistory, 'message' => 'Products Has been updated!']);
    }
    
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        return ['tag_id' => 'tags:id,name',
        'features_id' => 'features:id,product_id,feature_en,feature_ar,feature_image_link', 'productcategory_id' => 'productcategory:id,name,name_arabic',
        'brand_id' =>'brand:id,name,name_arabic','shipping_class' =>'shippingclass:id,name,name_arabic,description,description_arabic',
        'image_gallery' => 'gallery','related_brands' => 'productrelatedbrand:id,name,name_arabic', 'upsale_id' => 'upsale:id,name,sku',
        'related_category' => 'productrelatedcategory:id,name,name_arabic', 'featured_image' => 'featuredImage'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
         return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'tags' => SubTags::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         'upsells_products' => Product::where('status','=',1)->get(['id as value', 'sku as label']),
         'tax_classes' => TaxClasses::get(['id as value', 'name as label']),
         'shipping_classes' => ShippingClasses::get(['id as value', 'name as label']),
         'faq_questions' => ProductFaqs::get(['id as value', 'question as label'])];
    }
    
    // public function MultipleMediaImageUpload(Request $request) {
    //     //print_r($_FILES);die;
    //     $success = false;
    //     $upload = false;
    //     $path = null;
    //     if($request->gallery) {
    //         $path = [];
    //         // print_r('request- <'.$request->gallery);
    //         // print_r($request->File('gallery'));die();
    //         foreach(request()->File('gallery') as $image) {
    //             //print_r($image);die();
    //             $fileName = md5($image->getClientOriginalName()) . time() . "." . $image->getClientOriginalExtension();
    //             $image->move(public_path('/assets/images'), $fileName);
    //             $path[] = $fileName;
    //         }
    //         $success = true;
    //         return response()->json(['success' => $success, 'path' => $path]);
    //     }
    //     else {
    //         $file = request()->File('file');
    //         $fileName = md5($file->getClientOriginalName()) . time() . "." . $file->getClientOriginalExtension();
    //         $path = $file->move(public_path('/assets/images'), $fileName);
    //         $success = true;
    //         return response()->json(['success' => $success, 'path' => $fileName]);
    //     }
    // }
    
    
    public function prodata() {
        $product = Product::get(['id', 'name', 'quantity', 'status', 'price', 'sale_price']);
        $procount = $product->count();
        
        $proinstock = Product::where('quantity', '>', '0')->get();
        $instockpros = $proinstock->count();
        
        $proenable = Product::where('status', 1)->get();
        $enabled = $proenable->count();
        
        $prodisable = Product::where('status', 0)->get();
        $disabled = $prodisable->count();
        
        $prooutstock = Product::where('quantity', '<=', '0')->get();
        $outstockpros = $prooutstock->count();
        
        $proregularprice = Product::where('sale_price', 0)->orWhereNull('sale_price')->get();
        $regularpricepros = $proregularprice->count();
        
        $prosaleprice = Product::where('sale_price', '>=', '1')->get();
        $salepricepros = $prosaleprice->count();
        
        return response()->json(['count' => $procount, 'product' => $product, 'enablepros' => $enabled, 'instockpros' => $instockpros,
        'disablepros' => $disabled, 'outstockpros' => $outstockpros, 'regularpricepros' => $regularpricepros, 'salepricepros' => $salepricepros]);
    }
    
    public function checksku(Request $request) {
        $sku = $request->sku;
        $exists = Product::where('sku', $sku)->count();
        // print_r($exists);die();
        if($exists >= 1){
            $success = false;
            $product = true;
            return response()->json(['success' => $success, 'product' => $product, 'message' => 'Sku is already used!']);
        }
        $product = false;
        return response()->json(['success' => true, 'product' => $product, 'message' => 'Sku is Perfect!']);
    }
    
    public function quantityemail() {
        $products = Product::where('quantity', '<', 5)->get();

        if ($products->count() > 0) {
            $export = new ProductEmailExport($products);

            $fileName = 'low_quantity_products.xlsx';

            Excel::store($export, $fileName);

            Mail::send('email.ProductQuantity', ['product' => $products], function ($message) use ($fileName) {
                $message->to('mubashirasif1@gmail.com')->subject('Low Product Quantity Alert')->attach(storage_path('app/' . $fileName));
            });

            // Delete the file after sending email
            // unlink(storage_path('app/' . $fileName));
        }
    }
}