<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportTag;
use App\Exports\ExportBrand;
use App\Exports\ExportCategory;
use App\Exports\ExportProduct;
use App\Exports\ExportMarketSales;
use App\Exports\ExportCities;
use App\Exports\ExportRegion;
use App\Exports\ExportProductSpecs;
use App\Exports\ExportProductFeatures;
use App\Exports\ExportRegional;
use App\Exports\ExportDoorStep;
use App\Exports\ExportNotifications;
use App\Exports\ExportSubTags;
use App\Exports\ExportNotifyProduct;
use App\Exports\ExportUser;
use App\Exports\ExportOrder;
use App\Exports\ExportPriceAlert;
use App\Exports\ExportProductReview;
use App\Exports\ExportSaveSearch;
use App\Exports\ExportPimAnalyBrands;
use App\Exports\ExportPimAnalyProducts;
use App\Exports\ExportSalesProduct;
use App\Exports\ExportSalesBrand;
use App\Exports\ExportSalesCategory;
use App\Exports\ExportSalesPMethod;
use App\Exports\ExportPimAnalyCategory;
use App\Exports\ExportSalesCities;
use App\Exports\ExportUsersAnalysis;
use App\Exports\ExportStockAlert;
use App\Exports\ExportProductHistory;
use App\Exports\ExportErpOrder;
use App\Exports\ExportAbandonedCart;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Models\Product;
use App\Models\ProductTag;
use Response;


class ExportCsvController extends Controller
{
    // Tags Export
    public function TagExport(Request $request) { 

        $tags = Tag::with('childs')->get();
        $fields = array();
        $linedata = array();
        
        $object = new \stdClass();
        $myArray = [];
        $filename = "tags_data_" . date('Y-m-d') . ".csv"; 
        $body = [];
        $header = ['id', 'name', 'name_arabic', 'sorting', 'sub_tags', 'status'];

        foreach ($tags as $key => $tag) {
            $subtags = [];
            if($tag->childs){
                foreach ($tag->childs as $sub) {
                    $subtags[] = $sub->name;
                }
            }
            if($request['id'] == 1){
                $body['id'] = $tag->id;
            }
            if($request['name'] == 1){
                $body['name'] = $tag->name;
            }
            if($request['name_arabic'] == 1){
                $body['name_arabic'] = $tag->name_arabic;
            }
            if($request['sorting'] == 1){
                $body['sorting'] = $tag->sorting;
            }
            if($request['sub_tags'] == 1){
                $body['sub_tags'] = count($subtags) >= 1 ? implode(',', $subtags) : '';
            }
            if($request['status'] == 1){
                $body['status'] = $tag->status == 1 ? 'enabled' : 'disabled';
            }
            $myArray[] = $body;
        }
        $response = [
            'success' => true,'data' => $myArray, 'header' => $header, 'filename' => $filename
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    // Tags Export

    // Brands Export
    public function BrandExport(Request $request) {
        $brands = Brand::with('BrandMediaImage', 'BrandMediaAppImage')->get();
        $fields = array();
        $linedata = array();
        
        $object = new \stdClass();
        $myArray = [];
        $filename = "brands_data_" . date('Y-m-d') . ".csv"; 
        $body = [];
        // $header = ['id', 'name', 'name_arabic', 'sorting', 'status'];

        foreach ($brands as $key => $brand) {
            if($request['id'] == 1){
                $body['id'] = $brand->id;
            }
            if($request['name'] == 1){
                $body['name'] = $brand->name;
            }
            if($request['name_arabic'] == 1){
                $body['name_arabic'] = $brand->name_arabic;
            }
            if($request['slug'] == 1){
                $body['slug'] = $brand->slug;
            }
            if($request['image'] == 1){
                $body['image'] = $brand->BrandMediaImage ? $brand->BrandMediaImage->file_url . 'h=100' : '';
            }
            if($request['app_image'] == 1){
                $body['app_image'] = $brand->BrandMediaAppImage ? $brand->BrandMediaAppImage->file_url . 'h=100' : '';
            }
            if($request['sorting'] == 1){
                $body['sorting'] = $brand->sorting;
            }
            if($request['status'] == 1){
                $body['status'] = $brand->status == 1 ? 'enabled' : 'disabled';
            }
            if($request['show_as_popular'] == 1){
                $body['show_as_popular'] = $brand->show_as_popular;
            }
            if($request['meta_title'] == 1){
                $body['meta_title'] = $brand->meta_title_en;
            }
            if($request['meta_title_arabic'] == 1){
                $body['meta_title_arabic'] = $brand->meta_title_ar;
            }
            if($request['meta_description'] == 1){
                $body['meta_description'] = $brand->meta_description_en;
            }
            if($request['meta_description_arabic'] == 1){
                $body['meta_description_arabic'] = $brand->meta_description_ar;
            }
            if($request['meta_tag'] == 1){
                $body['meta_tag'] = $brand->meta_tag_en;
            }
            if($request['meta_tag_arabic'] == 1){
                $body['meta_tag_arabic'] = $brand->meta_tag_ar;
            }
            if($request['meta_canonical'] == 1){
                $body['meta_canonical'] = $brand->meta_canonical_en;
            }
            if($request['meta_canonical'] == 1){
                $body['meta_canonical_ar'] = $brand->meta_canonical_ar;
            }
            $myArray[] = $body;
        }
        $response = [
            'success' => true,'data' => $myArray, 'filename' => $filename
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]); 
    }
    // Brands Export

    // Product Export
    public function ProductExport(Request $request) {
        $instock = $request['in_stock'] == true ? 1 : 0;
        $enabled = $request['enabled'] == true ? 1 : 0;
        if($request['filter_pro']){
            $products = Product::whereIn('id',$request['filter_pro'])
            ->when($instock, function ($q) use ($instock) {
              return $q->where('instock', 1);
            })
            ->when($enabled, function ($q) use ($enabled) {
              return $q->where('status', 1);
            })
            ->get();
        }   
        elseif($request['filter_brands']){
            $products = Product::whereIn('brands',$request['filter_brands'])
            ->when($instock, function ($q) use ($instock) {
              return $q->where('instock', 1);
            })
            ->when($enabled, function ($q) use ($enabled) {
              return $q->where('status', 1);
            })
            ->get();
        }   
        elseif($request['filter_cats']){
            $Productcategory = Productcategory::whereIn('category_id', $request['filter_cats'])->pluck('product_id')->toArray();
            $products = Product::whereIn('id',$Productcategory)
            ->when($instock, function ($q) use ($instock) {
              return $q->where('instock', 1);
            })
            ->when($enabled, function ($q) use ($enabled) {
              return $q->where('status', 1);
            })
            ->get();
        }
        elseif($request['filter_tags']){
            $tags = ProductTag::whereIn('sub_tag_id', $request['filter_tags'])->pluck('product_id')->toArray();
            $products = Product::whereIn('id',$tags)
            ->when($instock, function ($q) use ($instock) {
              return $q->where('instock', 1);
            })
            ->when($enabled, function ($q) use ($enabled) {
              return $q->where('status', 1);
            })
            ->get();
        }   
        else {
            $products = Product::
            when($instock, function ($q) use ($instock) {
              return $q->where('instock', 1);
            })
            ->when($enabled, function ($q) use ($enabled) {
              return $q->where('status', 1);
            })
            ->get();
        }

        $fields = array();
        $linedata = array();
        
        $object = new \stdClass();
        $myArray = [];
        $filename = "products_data_" . date('Y-m-d') . ".csv"; 
        $body = [];

        foreach ($products as $key => $product) {
            if($request['id'] == 1){
                $body['id'] = $product->id;
            }
            if($request['name'] == 1){
                $body['name'] = $product->name;
            }
            if($request['name_arabic'] == 1){
                $body['name_arabic'] = $product->name_arabic;
            }
            if($request['description'] == 1){
                $body['description'] = $product->description;
            }
            if($request['description_arabic'] == 1){
                $body['description_arabic'] = $product->description_arabic;
            }
            if($request['short_description'] == 1){
                $body['short_description'] = $product->short_description;
            }
            if($request['short_description_arabic'] == 1){
                $body['short_description_arabic'] = $product->short_description_arabic;
            }
            // if($request['promotion_data'] == 1){
            //     $body['promotion_data'] = $product->promotion_data;
            // }
            if($request['image'] == 1){
                $body['feature_image'] = $product->featuredImage ? $product->featuredImage->id : '';
            }
            if($request['gallery'] == 1){
                $promultiImagesName = [];
                if($product->gallery){
                    foreach ($product->gallery as $key => $promultiImages) {
                    $promultiImagesName[] = $promultiImages->galleryImage ? $promultiImages->galleryImage->id : '' ;
                    }
                }
                $imp_promultiImagesName = implode(',', $promultiImagesName);
                $body['gallery'] = $imp_promultiImagesName;
            }
            if($request['specification'] == 1){
                $proSpecs = [];
                if($product->specs) {
                    foreach ($product->specs as $key => $prospecdata) {
                        $proSpecs[] = $prospecdata ? $prospecdata->specs_en .' ; '. $prospecdata->value_en : '';
                    }
                }
                $imp_proSpecs = implode(',', $proSpecs);
                $body['specification'] = $imp_proSpecs;
            }
            if($request['specification_arabic'] == 1){
                $proSpecs = [];
                if($product->specs) {
                    foreach ($product->specs as $key => $prospecdata) {
                        $proSpecs[] = $prospecdata ? $prospecdata->specs_ar .' ; '. $prospecdata->value_ar : '';
                    }
                }
                $imp_proSpecs = implode(',', $proSpecs);
                $body['specification_arabic'] = $imp_proSpecs;
            }
            if($request['shipping_class'] == 1){
                $body['shipping_class'] = $product->shippingclass ? $product->shippingclass->name : '';
            }
            if($request['note'] == 1){
                $body['note'] = $product->note;
            }
            if($request['price'] == 1){
                $body['price'] = $product->price;
            }
            if($request['category'] == 1){
                $proCats = [];
                if($product->productcategory){
                    foreach ($product->productcategory as $key => $productcat) {
                        $proCats[] = $productcat ? $productcat->name : '' ;
                    }
                }
                $imp_proCats = implode(',', $proCats);
                $body['category'] = $imp_proCats;
            }
            if($request['sale_price'] == 1){
                $body['sale_price'] = $product->sale_price;
            }
            if($request['brands'] == 1){
                $body['brands'] = $product->brand ? $product->brand->name : '';
            }
            if($request['quantity'] == 1){
                $body['quantity'] = $product->quantity;
            }
            if($request['tag'] == 1){
                $proTags = [];
                if($product->tags){
                    foreach ($product->tags as $key => $producttag) {
                        $proTags[] = $producttag ? $producttag->name : '' ;
                    }
                }
                $imp_proTags = implode(',', $proTags);
                $body['tag'] = $imp_proTags;
            }
            if($request['status'] == 1){
                $body['status'] = $product->status == 1 ? 'enabled' : 'disabled';
            }
            if($request['upsale_product'] == 1){
                $proUpsale = [];
                if($product->upsale){
                    foreach ($product->upsale as $key => $productupsale) {
                        $proUpsale[] = $productupsale ? $productupsale->sku : '' ;
                    }
                }
                $imp_proUpsale = implode(',', $proUpsale);
                $body['upsale_product'] = $proUpsale;
            }

            if($request['sku'] == 1){
                $body['sku'] = $product->sku;
            }
            if($request['slug'] == 1){
                $body['slug'] = $product->slug;
            }
            if($request['best_seller'] == 1){
                $body['best_seller'] = $product->best_seller == 1 ? 'Yes' : 'No';
            }
            if($request['link'] == 1){
                $body['link'] = 'https://tamkeenstores.com.sa/en/product/' . $product->slug;
            }
            if($request['key_feature'] == 1){
                $proKeyFeatures = [];
                if($product->features) {
                    foreach ($product->features as $key => $prospecdata) {
                        $proKeyFeatures[] = $prospecdata ? 'feature_en: ' . $prospecdata->feature_en .'; feature_ar: '. $prospecdata->feature_ar . '; feature_image_link: ' . $prospecdata->feature_image_link : '';
                    }
                }
                $imp_proKeyFeatures = implode(',', $proKeyFeatures);
                $body['key_feature'] = $imp_proKeyFeatures;
            }
            if($request['sorting'] == 1){
                $body['sorting'] = $product->sorting;
            }
            if($request['custom_badge'] == 1){
                $body['custom_badge'] = $product->custom_badge_en;
            }
            if($request['view_product'] == 1){
                $body['view_product'] = 0;
                // $product->view_product
            }
            if($request['sale_product_count'] == 1){
                $body['sale_product_count'] = 0;
            }
            if($request['shared_product_count'] == 1){
                $body['shared_product_count'] = 0;
            }
            if($request['image_title'] == 1){
                $body['image_title'] = $product->featuredImage ? $product->featuredImage->title : '';
            }
            if($request['erp_sku'] == 1){
                $body['erp_sku'] = '';
                // $product->erp_sku
            }
            if($request['meta_title_arabic'] == 1){
                $body['meta_title_arabic'] = $product->meta_title_ar;
            }
            if($request['meta_description'] == 1){
                $body['meta_description'] = $product->meta_description_en;
            }
            if($request['meta_description_arabic'] == 1){
                $body['meta_description_arabic'] = $product->meta_description_ar;
            }
            if($request['meta_tag'] == 1){
                $body['meta_tag'] = $product->meta_tag_en;
            }
            if($request['meta_tag_arabic'] == 1){
                $body['meta_tag_arabic'] = $product->meta_tag_ar;
            }
            if($request['meta_canonical'] == 1){
                $body['meta_canonical'] = $product->meta_canonical_en;
            }
            if($request['meta_canonical'] == 1){
                $body['meta_canonical_ar'] = $product->meta_canonical_ar;
            }
            $myArray[] = $body;
        }
        $response = [
            'success' => true,'data' => $myArray, 'filename' => $filename
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    // Product Export
    
    // Category Export
    public function CategoryExport(Request $request) { 

        $cats = Productcategory::with('child')->get();
        $fields = array();
        $linedata = array();
        
        $object = new \stdClass();
        $myArray = [];
        $filename = "categories_data_" . date('Y-m-d') . ".csv"; 
        $body = [];
        // $header = ['id', 'name', 'name_arabic', 'slug', 'web_image_media','mobile_image_media', 'description','description_arabic','status',
        // 'menu','parent_id','tproducts','clicks','meta_title_en','meta_title_ar','meta_tag_en','meta_tag_ar','meta_canonical_en','meta_description_en'
        // ,'meta_description_ar'];

        foreach ($cats as $key => $cat) {
            // print_r();die();
            if($request['id'] == 1){
                $body['id'] = $cat->id;
            }
            if($request['name'] == 1){
                $body['name'] = $cat->name;
            }
            if($request['name_arabic'] == 1){
                $body['name_arabic'] = $cat->name_arabic;
            }
            if($request['slug'] == 1){
                $body['slug'] = $cat->slug;
            }
            if($request['web_image_media'] == 1){
                $body['web_image_media'] = $cat->WebMediaImage ? $cat->WebMediaImage->id : '';
            }
            if($request['mobile_image_media'] == 1){
                $body['mobile_image_media'] = $cat->MobileMediaAppImage ? $cat->MobileMediaAppImage->id : '';
            }
            if($request['description'] == 1){
                $body['description'] = "'".$cat->description."'";
            }
            if($request['description_arabic'] == 1){
                $body['description_arabic'] = $cat->description_arabic;
            }
            if($request['status'] == 1){
                $body['status'] = $cat->status == 1 ? 'enabled' : 'disabled';
            }
            if($request['menu'] == 1){
                $body['menu'] = $cat->menu == 1 ? 'enabled' : 'disabled';
            }
            if($request['parent_id'] == 1){
                $body['parent_id'] = $cat->category ? $cat->category->name : '';
            }
            if($request['tproducts'] == 1){
                $body['tproducts'] = $cat->productname ? $cat->productname->count() : '';
            }
            if($request['clicks'] == 1){
                $body['clicks'] = $cat->clicks;
            }
            if($request['meta_title_en'] == 1){
                $body['meta_title_en'] = $cat->meta_title_en;
            }
            if($request['meta_title_ar'] == 1){
                $body['meta_title_ar'] = $cat->meta_title_ar;
            }
            if($request['meta_tag_en'] == 1){
                $body['meta_tag_en'] = $cat->meta_tag_en;
            }
            if($request['meta_tag_ar'] == 1){
                $body['meta_tag_ar'] = $cat->meta_tag_ar;
            }
            if($request['meta_canonical_en'] == 1){
                $body['meta_canonical_en'] = $cat->meta_canonical_en;
            }
            if($request['meta_description_en'] == 1){
                $body['meta_description_en'] = $cat->meta_description_en;
            }
            if($request['meta_description_ar'] == 1){
                $body['meta_description_ar'] = $cat->meta_description_ar;
            }
            
            $myArray[] = $body;
            // print_r($body);die();
        }
        $response = [
            'success' => true,'data' => $myArray, 'filename' => $filename
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    // Category Export
    
    public function TagExportCsv(Request $request){
        return Excel::download(new ExportTag($request->all()), 'tags.csv');
    }
    
    public function exportTags(Request $request){
        return Excel::download(new ExportTag($request->all()), 'tags.xlsx');
    }
    
    public function BrandExportCsv(Request $request){
        return Excel::download(new ExportBrand($request->all()), 'brands.csv');
    }
    
    public function exportBrands(Request $request){
        return Excel::download(new ExportBrand($request->all()), 'brands.xlsx');
    }
    
    public function CategoryExportCsv(Request $request){
        return Excel::download(new ExportCategory($request->all()), 'categories.csv');
    }
    
    public function exportCategories(Request $request){
        return Excel::download(new ExportCategory($request->all()), 'categories.xlsx');
    }
    
    public function exportProducts(Request $request){
        return Excel::download(new ExportProduct($request->all()), 'products.xlsx');
    }
    
    public function exportCities(Request $request){
        return Excel::download(new ExportCities($request->all()), 'cities.xlsx');
    }
    
    public function exportCitiesCsv(Request $request){
        return Excel::download(new ExportCities($request->all()), 'cities.csv');
    }
    
    public function exportRegion(Request $request){
        return Excel::download(new ExportRegion($request->all()), 'region.xlsx');
    }
    
    public function exportRegionCsv(Request $request){
        return Excel::download(new ExportRegion($request->all()), 'region.csv');
    }
    
    public function exportProductSpecs(Request $request){
        return Excel::download(new ExportProductSpecs($request->all()), 'product-specs.xlsx');
    }
    
    public function exportProductFeatures(Request $request){
        return Excel::download(new ExportProductFeatures($request->all()), 'product-features.xlsx');
    }
    
    public function exportRegional(Request $request){
        return Excel::download(new ExportRegional($request->all()), 'regional.xlsx');
    }
    
    public function exportRegionalCsv(Request $request){
        return Excel::download(new ExportRegional($request->all()), 'regional.csv');
    }
    
    public function exportDoorStep(Request $request){
        return Excel::download(new ExportDoorStep($request->all()), 'doorstepdelivery.xlsx');
    }
    
    public function exportDoorStepCsv(Request $request){
        return Excel::download(new ExportDoorStep($request->all()), 'doorstepdelivery.csv');
    }
    
    public function exportNotifications(Request $request){
        return Excel::download(new ExportNotifications($request->all()), 'notifications.xlsx');
    }
    
    public function exportNotificationCsv(Request $request){
        return Excel::download(new ExportNotifications($request->all()), 'notifications.csv');
    }
    
    public function exportSubTags(Request $request){
        return Excel::download(new ExportSubTags($request->all()), 'sub-tags.xlsx');
    }
    
    public function exportSubTagCsv(Request $request){
        return Excel::download(new ExportSubTags($request->all()), 'sub-tags.csv');
    }
    
    public function exportNotifyProduct(Request $request){
        return Excel::download(new ExportNotifyProduct($request->all()), 'notify-product.xlsx');
    }
    
    public function exportNotifyProductCsv(Request $request){
        return Excel::download(new ExportNotifyProduct($request->all()), 'notify-product.csv');
    }
    
    public function exportUsers(Request $request){
        return Excel::download(new ExportUser($request->all()), 'users.xlsx');
    }
    
    public function UserExportCsv(Request $request){
        return Excel::download(new ExportUser($request->all()), 'users.csv');
    }
    
    public function ProductExportCsv(Request $request){
        return Excel::download(new ExportProduct($request->all()), 'products.csv');
    }
    
    public function MarketSalesCsv(Request $request){
        return Excel::download(new ExportMarketSales($request->all()), 'market-sales.csv');
    }
    public function MarketSalesXlsx(Request $request){
        return Excel::download(new ExportMarketSales($request->all()), 'market-sales.xlsx');
    }
    
    public function exportProductSpecsCsv(Request $request){
        return Excel::download(new ExportProductSpecs($request->all()), 'product-specs.csv');
    }
    
    public function exportProductFeaturesCsv(Request $request){
        return Excel::download(new ExportProductFeatures($request->all()), 'product-features.csv');
    }
    
    public function exportPriceAlert(Request $request){
        return Excel::download(new ExportPriceAlert($request->all()), 'price-alert.xlsx');
    }
    
    public function exportPriceAlertCsv(Request $request){
        return Excel::download(new ExportPriceAlert($request->all()), 'price-alert.csv');
    }
    
    public function exportStockAlert(Request $request){
        return Excel::download(new ExportStockAlert($request->all()), 'stock-alert.xlsx');
    }
    
    public function exportStockAlertCsv(Request $request){
        return Excel::download(new ExportStockAlert($request->all()), 'stock-alert.csv');
    }
    
    public function exportOrder(Request $request){
        return Excel::download(new ExportOrder($request->all()), 'order.xlsx');
    }
    
    public function exportOrderCSv(Request $request){
        return Excel::download(new ExportOrder($request->all()), 'order.csv');
    }
    
    public function exportProductReview(Request $request){
        return Excel::download(new ExportProductReview($request->all()), 'product-review.xlsx');
    }
    public function exportProductReviewCsv(Request $request){
        return Excel::download(new ExportProductReview($request->all()), 'product-review.csv');
    }
    
    public function exportSaveSearch(Request $request){
        return Excel::download(new ExportSaveSearch($request->all()), 'save-search.xlsx');
    }
    
    public function exportSaveSearchCsv(Request $request){
        return Excel::download(new ExportSaveSearch($request->all()), 'save-search.csv');
    }
    
    public function exportPimAnalyProducts(Request $request, $date){
        return Excel::download(new ExportPimAnalyProducts($date), 'pim-analytics-product.xlsx');
    }
    
    public function exportPimAnalyBrands(Request $request, $date){
        return Excel::download(new ExportPimAnalyBrands($date), 'pim-analytics-brand.xlsx');
    }
    
    public function exportPimAnalyCategory(Request $request, $date){
        return Excel::download(new ExportPimAnalyCategory($date), 'pim-analytics-category.xlsx');
    }
    
    public function exportSalesCities(Request $request, $date){
        return Excel::download(new ExportSalesCities($date), 'sales-cities.xlsx');
    }
    
    public function salesReportExportProduct(Request $request){
        return Excel::download(new ExportSalesProduct($request->all()), 'sales-report-product.csv');
    }
    
    public function salesReportExportBrand(Request $request){
        return Excel::download(new ExportSalesBrand($request->all()), 'sales-report-brand.csv');
    }
    
    public function salesReportExportCategory(Request $request){
        return Excel::download(new ExportSalesCategory($request->all()), 'sales-report-category.csv');
    }
    
    public function salesReportExportPMethod(Request $request){
        return Excel::download(new ExportSalesPMethod($request->all()), 'sales-report-payment-method.csv');
    }
    
    public function exportUsersAnalysis($date){
        return Excel::download(new ExportUsersAnalysis($date), 'user-analysis.xlsx');
    }
    
    public function exportProductHistory(Request $request){
        return Excel::download(new ExportProductHistory($request->all()), 'product-history.xlsx');
    }
    
    public function exportProductHistoryCsv(Request $request){
        return Excel::download(new ExportProductHistory($request->all()), 'product-history.csv');
    }
    
    public function exportErpOrder(Request $request){
        return Excel::download(new ExportErpOrder($request->all()), 'erp-order-status.xlsx');
    }
    
    public function exportErpOrderCsv(Request $request){
        return Excel::download(new ExportErpOrder($request->all()), 'erp-order-status.csv');
    }
    
    public function exportAbandonedCart(Request $request){
        return Excel::download(new ExportAbandonedCart($request->all()), 'abandoned-cart.xlsx');
    }
    
    public function exportAbandonedCartCsv(Request $request){
        return Excel::download(new ExportAbandonedCart($request->all()), 'abandoned-cart.csv');
    }
}
