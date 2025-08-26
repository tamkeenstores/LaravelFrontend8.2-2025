<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Media;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Models\Product;
use App\Models\ProductRelatedCategory;
use App\Models\ProductRelatedBrand;
use App\Models\ProductPriceHistory;
use App\Models\ProductStockHistory;
use App\Models\ProductUpsale;
use App\Models\CategoryProduct;
use App\Models\SubTags;
use App\Models\ProductTag;
use App\Models\ProductMedia;
use App\Models\MarketplaceSales;
use App\Models\ProductSpecifications;
use App\Models\ProductFeatures;
use App\Models\ProductGallery;
use App\Models\States;
use App\Models\RegionalModule;
use App\Models\RegionalCity;
use App\Models\DoorStepDelivery;
use App\Models\DoorStepCategories;
use App\Models\DoorStepBrand;
use App\Models\DoorStepProduct;
use App\Models\Notification;
use App\Models\Region;
use App\Models\NotifyProduct;
use App\Models\PriceAlert;
use App\Models\Role;
use App\Models\User;
use App\Models\ShippingClasses;
use App\Models\ProductReview;
use App\Models\SaveSearch; 
use App\Models\BrandCategory;
use App\Models\BrandLandingPage;
use App\Models\CategoryFilter;
use App\Models\ProductSpecsDetails;
use App\Models\StockAlert;
use App\Models\BrandPageCategories;
use DateTime;
use Carbon\Carbon;

class CSVController extends Controller
{
    
    function csvToArray($filename = '', $delimiter = ',') {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            // print_r(fgetcsv($handle, 1000, $delimiter));die;
            while (($row = fgetcsv($handle, 0, ',')) !== false)
            {
                 // print_r($row[0]);die;
                // echo 'test';
                if (!$header){
                    $header = $row;
                    foreach ($header as $key => $value) {
                        $header[$key] = preg_replace("/[^a-zA-Z0-9_\-.\s]/", "", $value);
                    }
                }
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    function csvToArray2($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            // print_r(fgetcsv($handle, 1000, $delimiter));die;
            while (($row = fgetcsv($handle, 0, ',')) !== false)
            {
                 // print_r($row[0]);die;
                // echo 'test';
                if (!$header){
                    $header = $row;
                    foreach ($header as $key => $value) {
                        $header[$key] = $value;
                    }
                }
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
    
    public function Tags(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $tagsArr = $this->csvToArray($file);
            // print_r($tagsArr);die();
            foreach ($tagsArr as $key => $tagData) {
                $existingTag = Tag::where('name', $tagData['name'])->first();
                if ($existingTag) {
                    if(isset($tagData['sort']) && $tagData['sort'] == null){
                        $tagData['sort'] = null;
                    }
                    $existingTag->update($tagData);
                } else {
                    // $tagData['slug'] = SlugService::createSlug(Tag::class, 'slug', $tagData['name']);
                    if(isset($tagData['sort']) && $tagData['sort'] == null){
                        $tagData['sort'] = null;
                    }
                    $tag = Tag::create($tagData);
                    $tag->save();
                }
            }
            $success = true;
            $message = 'Success! Tags successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }


    // Market Place
    public function MarketPlace(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $mPlaceArr = $this->csvToArray($file);
            foreach ($mPlaceArr as $key => $placeData) {
                if($request->user_id){
                    $placeData['userid'] = $request->user_id ? $request->user_id : null;
                }
                if(isset($placeData['date'])) {
                    $val = str_replace("/", "-", $placeData['date']);
                    $new = new DateTime($val);
                    $final = date_format($new,"Y-m-d");
                    $placeData['date'] = $final;
                }
                $mPlaceSale = MarketplaceSales::create($placeData);
                $mPlaceSale->save();
            }
            $success = true;
            $message = 'Success! Market Place successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function Brands(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $brandArr = $this->csvToArray($file);
            $error = [];
            foreach ($brandArr as $key => $brandData) {
                
                if(isset($brandData['category'])){
                    if (strpos($brandData['category'], ',')) {
                        $category_id = explode(',', $brandData['category']);
                    }
                    else {
                        $category_id = explode(',', $brandData['category']);
                    }
                    unset($brandData['category']);
                }
                // $brandData['slug'] = SlugService::createSlug(Tag::class, 'slug', $brandData['name']);
                if(isset($brandData['brand_image_media'])) {
                    // $trim = str_replace('https://react.tamkeenstores.com.sa/assets/new-media/', '', $brandData['brand_image_media']);
                    $image = ProductMedia::where('image', $brandData['brand_image_media'])->first();
                    if($image) {
                        $brandData['brand_image_media'] = $image->id;   
                    }
                    else {
                        unset($brandData['brand_image_media']);
                    }
                }
                if(isset($brandData['brand_app_image_media'])) {
                    // $trim = str_replace('https://react.tamkeenstores.com.sa/assets/new-media/', '', $brandData['brand_app_image_media']);
                    $image = ProductMedia::where('image', $brandData['brand_app_image_media'])->first();
                    if($image) {
                        $brandData['brand_app_image_media'] = $image->id;   
                    }
                    else {
                        unset($brandData['brand_app_image_media']);
                    }
                }
                // if(isset($brandData['slug']) == null) {
                //     $error[] = $brandData['name'];
                // }
                $existingBrand = Brand::where('name', $brandData['name'])->first();
                if($existingBrand) {
                    $brandUpdate = Brand::where('name', $brandData['name'])->update($brandData);
                    $brand = Brand::orWhere('name', $brandData['name'])->first();
                }
                else {
                    $brand = Brand::create($brandData);
                    $brand->save();
                }
                
                if(isset($category_id)){
                    foreach ($category_id as $key => $cat) {
                        $cate = Productcategory::where('name','=', $cat)->first();
                        if ($cate) {
                            BrandCategory::create([
                                'brand_id' => $brand->id,
                                'category_id' => $cate->id
                            ]);
                        }
                    }
                }
            }
            // $success = true;
            // $message = 'Success! Brands successfully Import';
            
            if($error){
                $success = false;
                $counterrorsku = count($error);
                $error_sku = implode("<br>",$error);
                $message = 'Success! Brands successfully Imported except ('.$counterrorsku.') '.$error_sku ;
            }else{
                $success = true;
                $message = 'Success! Brands successfully Imported!' ;
            }
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);

        // File url base image
        // if($brandData['brand_image_media']) {
        //     $image = Media::where('file_url', $brandData['brand_image_media'])->first();
        //     $brandData['brand_image_media'] = $image->id;
        // }
        // if($brandData['brand_app_image_media']) {
        //     $image = Media::where('file_url', $brandData['brand_app_image_media'])->first();
        //     $brandData['brand_app_image_media'] = $image->id;
        // }
    }
    
    public function Categories(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $CatArr = $this->csvToArray($file);
            $error = [];
            // print_r($brandArr);die();
            foreach ($CatArr as $key => $CatData) {
            if(isset($CatData['parent_id'])) {
                $tagData = Productcategory::where('name', $CatData['parent_id'])->first();
                if($tagData) {
                    $CatData['parent_id'] = $tagData->id;
                }
                // else {
                //     $error = $CatData['name'];
                // }
            }
            if(isset($CatData['web_image_media'])) {
                // $trim = str_replace('https://react.tamkeenstores.com.sa/assets/new-media/', '', $CatData['web_image_media']);
                $image = ProductMedia::where('image', $CatData['web_image_media'])->first();
                if($image) {
                    $CatData['web_image_media'] = $image->id;   
                }
                else {
                    unset($CatData['web_image_media']);
                }
            }
            if(isset($CatData['mobile_image_media'])) {
                // $trim = str_replace('https://react.tamkeenstores.com.sa/assets/new-media/', '', $CatData['mobile_image_media']);
                $image = ProductMedia::where('image', $CatData['mobile_image_media'])->first();
                if($image) {
                    $CatData['mobile_image_media'] = $image->id;   
                }
                else {
                    unset($CatData['mobile_image_media']);
                }
            }
            if(isset($CatData['filter_category'])){
                if (strpos($CatData['filter_category'], ',')) {
                    $category_id = explode(',', $CatData['filter_category']);
                }
                else {
                    $category_id = explode(',', $CatData['filter_category']);
                }
                unset($CatData['filter_category']);
            }
            // if($CatData['slug'] == null) {
            //     $error[] = $CatData['name'];
            // }
            $existingCat = Productcategory::where('name', $CatData['name'])->first();
            if ($existingCat) {
                $existingCat->update($CatData);
                $cat = Productcategory::where('name', $CatData['name'])->first();
            } else {
                $cat = Productcategory::create($CatData);
                $cat->save();
            }
            
            if(isset($category_id)){
                foreach ($category_id as $key => $cated) {
                    $cate = SubTags::where('name','=', $cated)->first();
                    if ($cate) {
                        CategoryFilter::create([
                            'category_id' => $cat->id,
                            'filter_category_id' => $cate->id
                        ]);
                    }
                }
            }
            
            }
            // $success = true;
            // $message = 'Success! Categories successfully Import';
            
            if($error){
                $success = false;
                $counterrorsku = count($error);
                $error_sku = implode("<br>",$error);
                $message = 'Success! Categories successfully Imported except ('.$counterrorsku.') '.$error_sku ;
            }else{
                $success = true;
                $message = 'Success! Categories successfully Imported!' ;
            }
            
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function Products(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $productArr = $this->csvToArray($file);
            $errorsku = [];
            foreach ($productArr as $key => $productData) {
                if($productData['sku']){
                    // for new remove columns
                    unset($productData['erp_sku']);
                    unset($productData['share_count']);
                    
                    // if(isset($productData['status']) && ($productData['status'] == 1 || $productData['status'] == 0)){
                    //     $productData['status'] = $productData['status'] == 1 ? 1 : 0;
                    // }
                    // elseif(isset($productData['status']) && ($productData['status'] == 'disabled' || $productData['status'] == 'enabled')) {
                    //     $productData['status'] = $productData['status'] == 'enabled' ? 1 : 0;
                    // }
                    
                    // if($productData['brand_image_media']) {
                    //     $image = Media::where('file_url', $brandData['brand_image_media'])->first();
                    //     $brandData['brand_image_media'] = $image->id;
                    // }
                    
                    // brands
                    if(isset($productData['brands'])){
                        $brand = Brand::where('name',$productData['brands'])->first();
                        $productData['brands'] = null;
                        if ($brand){
                            $productData['brands'] = $brand->id;
                        }else{
                            $errorsku[] = $productData['sku'];
                        }
                    }
                    
                    if(isset($productData['feature_image'])) {
                        // $trim = str_replace('https://react.tamkeenstores.com.sa/assets/new-media/', '', $productData['feature_image']);
                        $image = ProductMedia::where('image', $productData['feature_image'])->first();
                        if($image) {
                            $productData['feature_image'] = $image->id;   
                        }
                        else {
                            unset($productData['feature_image']);
                        }
                    }

                    // Shipping Classes
                    if(isset($productData['shipping_class'])){
                        $shippingclass = ShippingClasses::where('name','=',$productData['shipping_class'])->first();
                        if ($shippingclass) {
                            $productData['shipping_class'] = $shippingclass->id;
                        }
                    }

                    // Tag unset
                    if(isset($productData['tag'])){
                        if (strpos($productData['tag'], ',')) {
                            $tag_id = explode(',', $productData['tag']);
                        }
                        else {
                            $tag_id = explode(',', $productData['tag']);
                        }
                        unset($productData['tag']);
                    }

                    // Category unset
                    if(isset($productData['category'])){
                        if (strpos($productData['category'], ',')) {
                            $category_id = explode(',', $productData['category']);
                        }
                        else {
                            $category_id = explode(',', $productData['category']);
                        }
                        unset($productData['category']);
                    }

                    // Upsale unset
                    if(isset($productData['upsale'])){
                        if (strpos($productData['upsale'], ',')) {
                            $upsale_id = explode(',', $productData['upsale']);
                        }
                        else {
                            $upsale_id = explode(',', $productData['upsale']);
                        }
                        unset($productData['upsale']);
                    }

                    // Gallery unset
                    if(isset($productData['images'])){
                        if (strpos($productData['images'], ',')) {
                            $images = explode(',', $productData['images']);
                        }
                        else{
                            $images = explode(',', $productData['images']);
                        }
                        unset($productData['images']);
                    }

                    // Related type unset
                    if(isset($productData['related_type']) && $productData['related_type'] != '') {
                        $productData['related_type'] = $productData['related_type'] == 'brands' ? 1 : 0;
                        if(isset($productData['related_brands']) && $productData['related_type'] == 1){
                            if (strpos($productData['related_brands'], ',')) {
                                $related_brands = explode(',', $productData['related_brands']);
                            }
                            else{
                                $related_brands = explode(',', $productData['related_brands']);
                            }
                        }
                        if(isset($productData['related_categories']) && $productData['related_type'] == 0){
                            if (strpos($productData['related_categories'], ',')) {
                                $related_categories = explode(',', $productData['related_categories']);
                            }
                            else{
                                $related_categories = explode(',', $productData['related_categories']);
                            }
                        }
                        unset($productData['related_brands']);
                        unset($productData['related_categories']);
                    }   
                    else {
                        unset($productData['related_brands']);
                        unset($productData['related_categories']);
                    }

                    // sale price
                    if(isset($productData['sale_price'])){
                        if (empty($productData['sale_price'])) {
                            $productData['sale_price'] = 0;
                        }
                    }
                    // price
                    if(isset($productData['price'])){
                        if (empty($productData['price'])) {
                            $productData['price'] = 0;
                        }
                    }

                    // Sale Price History Work
                    $historyProVerify = Product::where('sku', $productData['sku'])->first();
                    if(isset($productData['sale_price']) && $historyProVerify){
                        $historycount = ProductPriceHistory::where('product_id', $historyProVerify->id)->count();
                        if($historycount >= 10){
                            $historydel = ProductPriceHistory::where('product_id', $historyProVerify->id)->orderBy('created_at', 'Asc')->first();
                            $historydel->delete();
                            $saleprice = $productData['sale_price'];
                            $oldprodata = Product::where('id', $historyProVerify->id)->first();
                            $exists = Product::where('id', $historyProVerify->id)->where('sale_price', $saleprice)->count();
                            if($exists >= 1){
                                $salehistory = false;
                            }
                            else {
                                $pricehistory = [
                                    'product_id' => $historyProVerify->id,
                                    'old_sale_price' => $oldprodata->sale_price ? $oldprodata->sale_price : null,
                                    'sale_price' => $productData['sale_price'],
                                ];
                                 ProductPriceHistory::create($pricehistory);
                                 $salehistory = true;
                            }
                        }
                        else {
                            $saleprice = $productData['sale_price'];
                            $oldprodata = Product::where('id', $historyProVerify->id)->first();
                            $exists = Product::where('id', $historyProVerify->id)->where('sale_price', $saleprice)->count();
                            if($exists >= 1){
                                $salehistory = false;
                            }
                            else {
                                $pricehistory = [
                                    'product_id' => $historyProVerify->id,
                                    'old_sale_price' => $oldprodata->sale_price ? $oldprodata->sale_price : null,
                                    'sale_price' => $productData['sale_price'],
                                ];
                                 ProductPriceHistory::create($pricehistory);
                                 $salehistory = true;
                            }
                        }
                    }
                    
                    // Qty History Work
                    if(isset($productData['quantity']) && $historyProVerify){
                        $historycount = ProductStockHistory::where('product_id', $historyProVerify->id)->count();
                        if($historycount >= 10){
                            $historydel = ProductStockHistory::where('product_id', $historyProVerify->id)->orderBy('created_at', 'Asc')->first();
                            $historydel->delete();
                            $saleprice = $productData['quantity'];
                            $oldprodata = Product::where('id', $historyProVerify->id)->first();
                            $exists = Product::where('id', $historyProVerify->id)->where('quantity', $saleprice)->count();
                            if($exists >= 1){
                                $salehistory = false;
                            }
                            else {
                                $pricehistory = [
                                    'product_id' => $historyProVerify->id,
                                    'old_qty' => $oldprodata->quantity ? $oldprodata->quantity : 0,
                                    'qty' => $productData['quantity'],
                                ];
                                 ProductStockHistory::create($pricehistory);
                                 $salehistory = true;
                            }
                        }
                        else {
                            $saleprice = $productData['quantity'];
                            $oldprodata = Product::where('id', $historyProVerify->id)->first();
                            $exists = Product::where('id', $historyProVerify->id)->where('quantity', $saleprice)->count();
                            if($exists >= 1){
                                $salehistory = false;
                            }
                            else {
                                $pricehistory = [
                                    'product_id' => $historyProVerify->id,
                                    'old_qty' => $oldprodata->quantity ? $oldprodata->quantity : 0,
                                    'qty' => $productData['quantity'],
                                ];
                                 ProductStockHistory::create($pricehistory);
                                 $salehistory = true;
                            }
                        }
                    }

                    $product_sku = Product::where('sku','=',$productData['sku'])->first();
                    $update = false;

                    if($product_sku){
                        $update = true;
                        $product = Product::where('id',$product_sku->id)->update($productData);
                        $product = Product::where('id',$product_sku->id)->first();
                    }

                    else{
                        if(!isset($productData['name']) || empty($productData['name'])){
                            $errorsku[] = $productData['sku'];
                        }
                        elseif(!isset($productData['name_arabic']) || empty($productData['name_arabic'])){
                            $errorsku[] = $productData['sku'];
                        }
                        elseif(!isset($productData['sku']) || empty($productData['sku'])){
                            $errorsku[] = isset($productData['name']) ? $productData['name']: $productData['name_arabic'];
                        }
                        elseif(!isset($productData['price']) || empty($productData['price'])){
                            $errorsku[] = $productData['sku'];
                        }
                        elseif(!isset($productData['quantity']) || empty($productData['quantity'])){
                            $errorsku[] = $productData['sku'];
                        }
                        elseif(!isset($productData['sort']) || empty($productData['sort'])){
                            $errorsku[] = $productData['sku'];
                        }
                        else{
                            // $productData['slug'] = SlugService::createSlug(Product::class, 'slug', $productData['sku']);
                            $product = Product::create($productData);
                            $product->save();
                        }

                    }


                    // store tags
                    // print_r($product);die();
                    if(isset($tag_id) && isset($product)){
                        if($update){
                            $tag = ProductTag::where('product_id', '=',$product_sku->id)->get();
                            $tag->each->delete();
                        }
                        foreach ($tag_id as $key => $cat) {
                            // $tagData = explode('_', $cat);
                            $cate = SubTags::where('name','=',$cat)->first();
                            if ($cate) {
                                ProductTag::create([
                                    'product_id' => $product->id,
                                    'sub_tag_id' => $cate->id
                                ]);
                            }
                            // if(isset($tagData[1])) {
                            //     $tagCheck = Tag::where('name', $tagData[1])->first();
                            //     $subTagCheck = SubTags::where('tag_id', $tagCheck->id)->where('name','=',$tagData[0])->first();
                            //     if ($subTagCheck) {
                            //         ProductTag::create([
                            //             'product_id' => $product->id,
                            //             'sub_tag_id' => $subTagCheck->id
                            //         ]);
                            //     }
                            // }   else {
                            //     $cate = SubTags::where('name','=',$cat)->first();
                            //     if ($cate) {
                            //         ProductTag::create([
                            //             'product_id' => $product->id,
                            //             'sub_tag_id' => $cate->id
                            //         ]);
                            //     }
                            // }
                        }
                    }

                    // store category
                    if(isset($category_id) && isset($product)){
                        if($update){
                            Categoryproduct::where('product_id',$product_sku->id)->delete();
                        }
                        foreach ($category_id as $key => $cat) {
                            $cate = Productcategory::where('name','=', $cat)->first();
                            if ($cate) {
                                Categoryproduct::create([
                                    'product_id' => $product->id,
                                    'category_id' => $cate->id
                                ]);
                            }
                        }
                    }

                    // store upsale
                    if(isset($upsale_id) && isset($product)){
                        if($update){
                            ProductUpsale::where('product_id',$product_sku->id)->delete();
                        }
                        foreach ($upsale_id as $key => $cat) {
                            $cate = Product::where('sku','=',$cat)->first();
                            if ($cate) {
                                ProductUpsale::create([
                                    'product_id' => $product->id,
                                    'upsale_id' => $cate->id
                                ]);
                            }
                        }
                    }

                    // store galleries
                    if(isset($images) && isset($product)){
                        if($update){
                            ProductGallery::where('product_id',$product_sku->id)->delete();
                        }
                        foreach ($images as $key => $cat) {
                            // $trim = str_replace('https://react.tamkeenstores.com.sa/assets/new-media/', '', $cat);
                            $cate = ProductMedia::where('image','=', $cat)->first();
                            if ($cate) {
                                ProductGallery::create([
                                    'product_id' => $product->id,
                                    'image' => $cate->id
                                ]);
                            }
                        }
                    }

                    // store related data
                    if(isset($related_brands) && isset($product)) {
                        if($update){
                            ProductRelatedBrand::where('product_id',$product_sku->id)->delete();
                        }
                        $Maindata = Brand::whereIn('name', $related_brands)->get();
                        foreach ($Maindata as $key => $cat) {
                            if ($cat) {
                                ProductRelatedBrand::create([
                                    'product_id' => $product->id,
                                    'brand_id' => $cat->id
                                ]);
                            }
                        }
                    }
                    if(isset($related_categories) && isset($product)) {
                        if($update){
                            ProductRelatedCategory::where('product_id',$product_sku->id)->delete();
                        }
                        $Maindata = Productcategory::whereIn('slug', $related_categories)->get();
                        foreach ($Maindata as $key => $cat) {
                            if ($cat) {
                                ProductRelatedCategory::create([
                                    'product_id' => $product->id,
                                    'category_id' => $cat->id
                                ]);
                            }
                        }
                    }
                }
            }
            if($errorsku){
                $counterrorsku = count($errorsku);
                $error_sku = implode("<br>",$errorsku);
                $message = 'Success! Product successfully Imported except ('.$counterrorsku.') <br>' .$error_sku. ' <br> Please must add Name,Name Arabic, Sku, Slug, Sort, Quantity & Price before upload file';
            }else{
                $message = 'Success! Product successfully Imported!' ;
            }
            $success = true;
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function ProductKeyFeatures(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $keyfeaturesArr = $this->csvToArray($file);
            $errorsku = [];
            foreach ($keyfeaturesArr as $key => $keyFeaturesData) {
                $product_sku = Product::where('sku','=',$keyFeaturesData['sku'])->first();
                // $oldProductFeatures = ProductFeatures::where('product_id', $product_sku->id)->get();
                if($product_sku) {
                    if(isset($keyFeaturesData['feature_en']) && $keyFeaturesData['feature_en'] != '') {
                        $nameen = explode(',', $keyFeaturesData['feature_en']);
                    }   else {
                        $nameen = null;
                    }
                    if (isset($keyFeaturesData['feature_ar']) && $keyFeaturesData['feature_ar'] != '') {
                        $namear = explode(',', $keyFeaturesData['feature_ar']);
                    }   else {
                        $namear = null;
                    }
                    if(isset($keyFeaturesData['feature_image_link']) && $keyFeaturesData['feature_image_link'] != '') {
                        $image = explode(',', $keyFeaturesData['feature_image_link']);
                    }   else {
                        $image = null;
                    }
                    // print_r($nameen);die;
                    if($nameen != null) {
                        $features = ProductFeatures::where('product_id', '=',$product_sku->id)->get();
                        $features->each->delete();
                        foreach ($nameen as $key => $value) {
                            if ($value) {
                                ProductFeatures::create([
                                    'product_id' => $product_sku->id,
                                    'feature_en' => $value,
                                    'feature_ar' => isset($namear[$key]) ? $namear[$key] : null,
                                    'feature_image_link' => isset($image[$key]) ? $image[$key] : null,
                                ]);
                            }
                        }
                    }   
                    elseif($namear != null) {
                        $features = ProductFeatures::where('product_id', '=',$product_sku->id)->get();
                        $features->each->delete();
                        foreach ($namear as $key => $value) {
                            if ($value) {
                                ProductFeatures::create([
                                    'product_id' => $product_sku->id,
                                    'feature_en' => isset($nameen[$key]) ? $nameen[$key] : null,
                                    'feature_ar' => $value,
                                    'feature_image_link' => isset($image[$key]) ? $image[$key] : null,
                                ]);
                            }
                        }
                    }
                    else {
                        $features = ProductFeatures::where('product_id', '=',$product_sku->id)->get();
                        $features->each->delete();
                        foreach ($image as $key => $value) {
                            if ($value) {
                                ProductFeatures::create([
                                    'product_id' => $product_sku->id,
                                    'feature_en' => isset($nameen[$key]) ? $nameen[$key] : null,
                                    'feature_ar' => isset($namear[$key]) ? $namear[$key] : null,
                                    'feature_image_link' => $value,
                                ]);
                            }
                        }
                    }
                }
                else {
                    $errorsku[] = $keyFeaturesData['sku'];
                }
            }
            if($errorsku){
                $counterrorsku = count(array_unique($errorsku));
                $error_sku = implode("<br>",array_unique($errorsku));
                $message = 'Success! Product Key Features successfully Imported except ('.$counterrorsku.') <br>'.$error_sku ;
            }else{
                $message = 'Success! Product Key Features successfully Imported!' ;
            }
            $success = true;
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function ProductSpecifications(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $specificationsArr = $this->csvToArray($file);
            $errorsku = [];
            foreach ($specificationsArr as $key => $specificationsData) {
                // print_r($specificationsData);die();
                $product_sku = Product::where('sku','=',$specificationsData['sku'])->first();
                if($product_sku) {
                    if(isset($specificationsData['specs_en'])){
                        if (strpos($specificationsData['specs_en'], ';')) {
                            $specsen = explode(';', $specificationsData['specs_en']);
                        }
                        else {
                            $specsen = explode(';', $specificationsData['specs_en']);
                        }
                        unset($specificationsData['specs_en']);
                    }
                    if(isset($specificationsData['value_en'])){
                        if (strpos($specificationsData['value_en'], ';')) {
                            $valueen = explode(';', $specificationsData['value_en']);
                        }
                        else {
                            $valueen = explode(';', $specificationsData['value_en']);
                        }
                        unset($specificationsData['value_en']);
                    }
                    if(isset($specificationsData['specs_ar'])){
                        if (strpos($specificationsData['specs_ar'], ';')) {
                            $specsar = explode(';', $specificationsData['specs_ar']);
                        }
                        else {
                            $specsar = explode(',', $specificationsData['specs_ar']);
                        }
                        unset($specificationsData['specs_ar']);
                    }
                    if(isset($specificationsData['value_ar'])){
                        if (strpos($specificationsData['value_ar'], ';')) {
                            $valuear = explode(';', $specificationsData['value_ar']);
                        }
                        else {
                            $valuear = explode(';', $specificationsData['value_ar']);
                        }
                        unset($specificationsData['value_ar']);
                    }
                    $specifications = ProductSpecifications::where('product_id', '=',$product_sku->id)->where('heading_en', $specificationsData['heading_en'])->where('heading_ar', $specificationsData['heading_ar'])->get();
                    $specifications->each->delete();
                    $specs = ProductSpecifications::create([
                        'product_id' => $product_sku->id,
                        'heading_en' => $specificationsData['heading_en'],
                        'heading_ar' => $specificationsData['heading_ar'],
                    ]);
                    
                    if($specsen != null) {
                        $specifications = ProductSpecsDetails::where('specs_id', '=',$specs->id)->get();
                        $specifications->each->delete();
                        foreach ($specsen as $key => $value) {
                            if ($value) {
                                ProductSpecsDetails::create([
                                    'specs_id' => $specs->id,
                                    'specs_en' => $value,
                                    'value_en' => isset($valueen[$key]) ? $valueen[$key] : null,
                                    'specs_ar' => isset($specsar[$key]) ? $specsar[$key] : null,
                                    'value_ar' => isset($valuear[$key]) ? $valuear[$key] : null,
                                ]);
                            }
                        }
                    }   
                    elseif($valueen != null) {
                        $specifications = ProductSpecsDetails::where('specs_id', '=',$specs->id)->get();
                        $specifications->each->delete();
                        foreach ($valueen as $key => $value) {
                            if ($value) {
                                ProductSpecsDetails::create([
                                    'specs_id' => $specs->id,
                                    'specs_en' => isset($specsen[$key]) ? $specsen[$key] : null,
                                    'value_en' => $value,
                                    'specs_ar' => isset($specsar[$key]) ? $specsar[$key] : null,
                                    'value_ar' => isset($valuear[$key]) ? $valuear[$key] : null,
                                ]);
                            }
                        }
                    }
                    elseif($specsar != null) {
                        $specifications = ProductSpecsDetails::where('specs_id', '=',$specs->id)->get();
                        $specifications->each->delete();
                        foreach ($specsar as $key => $value) {
                            if ($value) {
                                ProductSpecsDetails::create([
                                    'specs_id' => $specs->id,
                                    'specs_en' => isset($specsen[$key]) ? $specsen[$key] : null,
                                    'value_en' => isset($valueen[$key]) ? $valueen[$key] : null,
                                    'specs_ar' => $value,
                                    'value_ar' => isset($valuear[$key]) ? $valuear[$key] : null,
                                ]);
                            }
                        }
                    }
                    else {
                        $specifications = ProductSpecsDetails::where('specs_id', '=',$specs->id)->get();
                        $specifications->each->delete();
                        foreach ($valuear as $key => $value) {
                            if ($value) {
                                ProductSpecsDetails::create([
                                    'specs_id' => $specs->id,
                                    'specs_en' => isset($specsen[$key]) ? $specsen[$key] : null,
                                    'value_en' => isset($valueen[$key]) ? $valueen[$key] : null,
                                    'specs_ar' => isset($specsar[$key]) ? $specsar[$key] : null,
                                    'value_ar' => $value,
                                ]);
                            }
                        }
                    }
                    $message = 'Product Specifications Successfully Imported!';
                    $success = true;
                }
                // else {
                //     $message = 'Something Went Wrong!';
                //     $success = false;
                // }
                else {
                    $errorsku[] = $specificationsData['sku'];
                }
                // if($product_sku) {
                //     if(isset($specificationsData['specs_en']) && $specificationsData['specs_en'] != '') {
                //         $specsen = explode(',', $specificationsData['specs_en']);
                //     }   else {
                //         $specsen = null;
                //     }
                //     if (isset($specificationsData['value_en']) && $specificationsData['value_en'] != '') {
                //         $valueen = explode(',', $specificationsData['value_en']);
                //     }   else {
                //         $valueen = null;
                //     }
                //     if(isset($specificationsData['specs_ar']) && $specificationsData['specs_ar'] != '') {
                //         $specsar = explode(',', $specificationsData['specs_ar']);
                //     }   else {
                //         $specsar = null;
                //     }
                //     if(isset($specificationsData['value_ar']) && $specificationsData['value_ar'] != '') {
                //         $valuear = explode(',', $specificationsData['value_ar']);
                //     }   else {
                //         $valuear = null;
                //     }

                //     if($specsen != null) {
                //         $specifications = ProductSpecifications::where('product_id', '=',$product_sku->id)->get();
                //         $specifications->each->delete();
                //         foreach ($specsen as $key => $value) {
                //             if ($value) {
                //                 ProductSpecifications::create([
                //                     'product_id' => $product_sku->id,
                //                     'specs_en' => $value,
                //                     'value_en' => isset($valueen[$key]) ? $valueen[$key] : null,
                //                     'specs_ar' => isset($specsar[$key]) ? $specsar[$key] : null,
                //                     'value_ar' => isset($valuear[$key]) ? $valuear[$key] : null,
                //                 ]);
                //             }
                //         }
                //     }   
                //     elseif($valueen != null) {
                //         $specifications = ProductSpecifications::where('product_id', '=',$product_sku->id)->get();
                //         $specifications->each->delete();
                //         foreach ($valueen as $key => $value) {
                //             if ($value) {
                //                 ProductSpecifications::create([
                //                     'product_id' => $product_sku->id,
                //                     'specs_en' => isset($specsen[$key]) ? $specsen[$key] : null,
                //                     'value_en' => $value,
                //                     'specs_ar' => isset($specsar[$key]) ? $specsar[$key] : null,
                //                     'value_ar' => isset($valuear[$key]) ? $valuear[$key] : null,
                //                 ]);
                //             }
                //         }
                //     }
                //     elseif($specsar != null) {
                //         $specifications = ProductSpecifications::where('product_id', '=',$product_sku->id)->get();
                //         $specifications->each->delete();
                //         foreach ($specsar as $key => $value) {
                //             if ($value) {
                //                 ProductSpecifications::create([
                //                     'product_id' => $product_sku->id,
                //                     'specs_en' => isset($specsen[$key]) ? $specsen[$key] : null,
                //                     'value_en' => isset($valueen[$key]) ? $valueen[$key] : null,
                //                     'specs_ar' => $value,
                //                     'value_ar' => isset($valuear[$key]) ? $valuear[$key] : null,
                //                 ]);
                //             }
                //         }
                //     }
                //     else {
                //         $specifications = ProductSpecifications::where('product_id', '=',$product_sku->id)->get();
                //         $specifications->each->delete();
                //         foreach ($valuear as $key => $value) {
                //             if ($value) {
                //                 ProductSpecifications::create([
                //                     'product_id' => $product_sku->id,
                //                     'specs_en' => isset($specsen[$key]) ? $specsen[$key] : null,
                //                     'value_en' => isset($valueen[$key]) ? $valueen[$key] : null,
                //                     'specs_ar' => isset($specsar[$key]) ? $specsar[$key] : null,
                //                     'value_ar' => $value,
                //                 ]);
                //             }
                //         }
                //     }
                // }
                // else {
                //     $errorsku[] = $specificationsData['sku'];
                // }
            }
            if($errorsku){
                $counterrorsku = count(array_unique($errorsku));
                $error_sku = implode("<br>",array_unique($errorsku));
                $message = 'Success! Product Specifications successfully Imported except ('.$counterrorsku.') '.$error_sku ;
                $success = true;
            }else{
                $message = 'Success! Product Specifications successfully Imported!' ;
                $success = true;
            }
            
        }
        $response = [
            'success'=> $success,
            'message' => $message,
        ];
        $responsejson = json_encode($response);
        $data = gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }


    public function Cities(Request $request) {
        $success = false;
        $message = 'Error! Something went wrong. Please try again';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $citiesArr = $this->csvToArray($file);
            foreach ($citiesArr as $key => $citiesData) {
                if($citiesData['name'] || $citiesData['name_arabic']) {
                    if($citiesData['region']) {
                        $regionData = Region::where('name', $citiesData['region'])->first();
                        if($regionData) {
                            $citiesData['region'] = $regionData->id;
                        }
                        else {
                            $success = false;
                        }
                    }
                    $citiesData['country_id'] = 191;
                    $existingCity = States::orWhere('name', $citiesData['name'])->orWhere('name_arabic', $citiesData['name_arabic'])->first();
                    if ($existingCity) {
                        $existingCity->update($citiesData);
                        $success = true;
                    }
                    else {
                        $city = States::create($citiesData);
                        $city->save();
                        $success = true;
                    }
                }
                if($success == true) {
                    $message = 'Success! Cities successfully Import';
            
                }
                else {
                    // $message = 'Error! Please must add name OR name_arabic data';
                    $message = 'Error! Something went wrong. Please try again';
                }
            }
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function Regions(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $regionsArr = $this->csvToArray($file);
            foreach ($regionsArr as $key => $regionsData) {
                $regionsData['country'] = 1;
                // $status = $regionsData['status'] == 'enabled' ? 1 : 0;
                // $regionsData['status'] = $status;
                
                $existingRegion = Region::orWhere('name', $regionsData['name'])->orWhere('name_arabic', $regionsData['name_arabic'])->first();
                if ($existingRegion) {
                    $existingRegion->update($regionsData);
                }
                else {
                    $region = Region::create($regionsData);
                    $region->save();
                }
            }
            $success = true;
            $message = 'Success! Regions successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function RegionalModule(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $regionalArr = $this->csvToArray($file);
            foreach ($regionalArr as $key => $regionalData) {
                // City unset
                if(isset($regionalData['city'])){
                    if (strpos($regionalData['city'], ',')) {
                        $city_id = explode(',', $regionalData['city']);
                    }
                    else {
                        $city_id = explode(',', $regionalData['city']);
                    }
                    unset($regionalData['city']);
                }

                // $status = $regionalData['status'] == 'enabled' ? 1 : 0;
                // $regionalData['status'] = $status;
                $regional = RegionalModule::create($regionalData);
                $regional->save();

                // store cities
                if(isset($city_id)){
                    RegionalCity::where('regional_id',$regional->id)->delete();
                    foreach ($city_id as $key => $cat) {
                        $name = States::where('name','=', $cat)->first();
                        if ($name) {
                            RegionalCity::create([
                                'regional_id' => $regional->id,
                                'city_id' => $name->id
                            ]);
                        }
                    }
                }
            }
            $success = true;
            $message = 'Success! Regional Module successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function DoorStep(Request $request) {
        // print_r($request->all());die;
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $doorstepArr = $this->csvToArray($file);
            foreach ($doorstepArr as $key => $doorstepData) {
                // Related type unset
                if(isset($doorstepData['type']) && $doorstepData['type'] != '') {
                    if($doorstepData['type'] == 'products') {
                        $type = 1;
                    }
                    elseif($doorstepData['type'] == 'brands') {
                        $type = 2;
                    }
                    elseif($doorstepData['type'] == 'category') {
                        $type = 3;
                    }  
                    else {
                        $type = 0;
                    }
                    $doorstepData['type'] = $type;
                    if(isset($doorstepData['brands'])){
                        if (strpos($doorstepData['brands'], ',')) {
                            $brands = explode(',', $doorstepData['brands']);
                        }
                        else{
                            $brands = explode(',', $doorstepData['brands']);
                        }
                    }
                    if(isset($doorstepData['category'])){
                        if (strpos($doorstepData['category'], ',')) {
                            $category = explode(',', $doorstepData['category']);
                        }
                        else{
                            $category = explode(',', $doorstepData['category']);
                        }
                    }
                    if(isset($doorstepData['products'])){
                        if (strpos($doorstepData['products'], ',')) {
                            $products = explode(',', $doorstepData['products']);
                        }
                        else{
                            $products = explode(',', $doorstepData['products']);
                        }
                    }
                    unset($doorstepData['brands']);
                    unset($doorstepData['category']);
                    unset($doorstepData['products']);
                }   
                else {
                    unset($doorstepData['brands']);
                    unset($doorstepData['category']);
                    unset($doorstepData['products']);
                }

                // $status = $doorstepData['status'] == 'enabled' ? 1 : 0;
                // $doorstepData['status'] = $status;
                $doorstep = DoorStepDelivery::create($doorstepData);
                $doorstep->save();

                // store related data
                if(isset($brands) && $doorstep->type == 2) {
                    DoorStepBrand::where('doorstep_id',$doorstep->id)->delete();
                    $Maindata = Brand::whereIn('name', $brands)->get();
                    foreach ($Maindata as $key => $cat) {
                        if ($cat) {
                            DoorStepBrand::create([
                                'doorstep_id' => $doorstep->id,
                                'brand_id' => $cat->id
                            ]);
                        }
                    }
                }
                if(isset($category) && $doorstep->type == 3) {
                    DoorStepCategories::where('doorstep_id',$doorstep->id)->delete();
                    $Maindata = Productcategory::whereIn('name', $category)->get();
                    foreach ($Maindata as $key => $cat) {
                        if ($cat) {
                            DoorStepCategories::create([
                                'doorstep_id' => $doorstep->id,
                                'category_id' => $cat->id
                            ]);
                        }
                    }
                }
                if(isset($products)  && $doorstep->type == 1) {
                    DoorStepProduct::where('doorstep_id',$doorstep->id)->delete();
                    $Maindata = Product::whereIn('sku', $products)->get();
                    foreach ($Maindata as $key => $cat) {
                        if ($cat) {
                            DoorStepProduct::create([
                                'doorstep_id' => $doorstep->id,
                                'product_id' => $cat->id
                            ]);
                        }
                    }
                }
            }
            $success = true;
            $message = 'Success! Door Step successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }

    public function Notifications(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $notificationsArr = $this->csvToArray($file);
            foreach ($notificationsArr as $key => $notificationsData) {
                // $notificationsData['for_web'] = $notificationsData['for_web'] == 'enabled' ? 1 : 0;
                // $notificationsData['for_app'] = $notificationsData['for_app'] == 'enabled' ? 1 : 0;
                $notification = Notification::create($notificationsData);
                $notification->save();
            }
            $success = true;
            $message = 'Success! Notification successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function SubTags(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $subtagsArr = $this->csvToArray($file);
            $error = [];
            foreach ($subtagsArr as $key => $subtagsData) {
                // print_r($subtagsData);die();
                $existingTag = SubTags::where('name', $subtagsData['name'])->first();
                if ($existingTag) {
                    if($subtagsData['tag_id']) {
                        $tagData = Tag::where('name', $subtagsData['tag_id'])->first();
                        if($tagData) {
                            $subtagsData['tag_id'] = $tagData->id; 
                        }
                        else {
                            $error[] = $subtagsData['name'];
                        }
                        if(isset($subtagsData['sort']) && $subtagsData['sort'] == null){
                            $subtagsData['sort'] = null;
                        }
                    }
                    $existingTag->update($subtagsData);
                } else {
                    if($subtagsData['tag_id']) {
                        $tagData = Tag::where('name', $subtagsData['tag_id'])->first();
                        if($tagData) {
                            $subtagsData['tag_id'] = $tagData->id; 
                        }
                        else {
                            $error[] = $subtagsData['name'];
                        }
                        if(isset($subtagsData['sort']) && $subtagsData['sort'] == null){
                            $subtagsData['sort'] = null;
                        } 
                    }
                    $subtag = SubTags::create($subtagsData);
                    $subtag->save();
                }
            }
            // $success = true;
            // $message = 'Success! Sub Tag successfully Import';
            
            if($error){
                $success = false;
                $counterrorsku = count($error);
                $error_sku = implode("<br>",$error);
                $message = 'Success! Sub Tag successfully Imported except ('.$counterrorsku.') '.$error_sku ;
            }else{
                $success = true;
                $message = 'Success! Sub Tag successfully Imported!' ;
            }
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function NotifyProduct(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $notifyArr = $this->csvToArray($file);
            foreach ($notifyArr as $key => $notifyData) {
                if($notifyData['product_id']) {
                    $proData = Product::where('sku', $notifyData['product_id'])->first();
                    $notifyData['product_id'] = $proData->id;
                }
                $notify = NotifyProduct::create($notifyData);
                $notify->save();
            }
            $success = true;
            $message = 'Success! Notify Product successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function Users(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $userArr = $this->csvToArray($file);
            foreach ($userArr as $key => $userData) {
                if($userData['role_id']) {
                    $roleData = Role::where('name', $userData['role_id'])->first();
                    $userData['role_id'] = $roleData->id;
                }
                $existingUser = User::where('phone_number', $userData['phone_number'])->first();
                if($existingUser) {
                    $existingUser->update($userData);
                }
                else {
                    $user = User::create($userData);
                    $user->save();
                }
            }
            $success = true;
            $message = 'Success! User successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function PriceAlert(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $pricealertArr = $this->csvToArray($file);
            foreach ($pricealertArr as $key => $pricealertData) {
                if($pricealertData['product_id']) {
                    $proData = Product::where('sku', $pricealertData['product_id'])->first();
                    $pricealertData['product_id'] = $proData->id;
                }
                $pricealert = PriceAlert::create($pricealertData);
                $pricealert->save();
            }
            $success = true;
            $message = 'Success! Price Alert successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function StockAlert(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $stockalertArr = $this->csvToArray($file);
            foreach ($stockalertArr as $key => $stockalertData) {
                if($stockalertData['product_id']) {
                    $proData = Product::where('sku', $stockalertData['product_id'])->first();
                    $stockalertData['product_id'] = $proData->id;
                }
                if($stockalertData['user_id']) {
                    $userData = User::where('first_name', $stockalertData['user_id'])->first();
                    $stockalertData['user_id'] = $userData->id;
                }
                $stockalert = StockAlert::create($stockalertData);
                $stockalert->save();
            }
            $success = true;
            $message = 'Success! Stock Alert successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function ProductReview(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $reviewArr = $this->csvToArray($file);
            foreach ($reviewArr as $key => $reviewData) {
                
                $review = ProductReview::create($reviewData);
                $review->save();
            }
            $success = true;
            $message = 'Success! Product Review successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function SaveSearch(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $savesearchArr = $this->csvToArray($file);
            foreach ($savesearchArr as $key => $savesearchData) {
                
                $savesearch = SaveSearch::create($savesearchData);
                $savesearch->save();
            }
            $success = true;
            $message = 'Success! Save Search successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function BrandLandingPage(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $brandlandingArr = $this->csvToArray($file);
            foreach ($brandlandingArr as $key => $brandlandingData) {
                if($brandlandingData['brand_id']) {
                    $braData = Brand::where('name', $brandlandingData['brand_id'])->first();
                    $brandlandingData['brand_id'] = $braData->id;
                }
                
                if(isset($brandlandingData['brand_banner_media'])) {
                    // $imagename = explode('https://react.tamkeenstores.com.sa/assets/new-media/', $brandlandingData['brand_banner_media']);
                    $image = ProductMedia::where('image', $brandlandingData['brand_banner_media'])->first();
                    $brandlandingData['brand_banner_media'] = $image->id;
                }
                
                if(isset($brandlandingData['middle_banner_media'])) {
                    // $middleimagename = explode('https://react.tamkeenstores.com.sa/assets/new-media/', $brandlandingData['middle_banner_media']);
                    $middleimage = ProductMedia::where('image', $brandlandingData['middle_banner_media'])->first();
                    $brandlandingData['middle_banner_media'] = $middleimage->id;
                }
                
                $brandlanding = BrandLandingPage::create($brandlandingData);
                $brandlanding->save();
            }
            $success = true;
            $message = 'Success! Brand Landing Page successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
    
    public function BrandLandingArray(Request $request) {
        $success = false;
        $message = '';
        if (request()->File('file')) {
            $file = request()->File('file');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');
            $file->move($path, $name);
            $file = storage_path('documents/'.$name);
            $brandlandingArr = $this->csvToArray($file);
            foreach ($brandlandingArr as $key => $brandlandingData) {
                if($brandlandingData['brand_landing_id']) {
                    $braData = Brand::where('name', $brandlandingData['brand_landing_id'])->first();
                    $landingpageid = $braData->id;
                    $landingpagedata = BrandLandingPage::where('brand_id', $landingpageid)->first();
                    $brandlandingData['brand_landing_id'] = $landingpagedata->id;
                }
                
                if($brandlandingData['category_id']) {
                    $catData = Productcategory::where('name', $brandlandingData['category_id'])->first();
                    $brandlandingData['category_id'] = $catData->id;
                }
                
                if($brandlandingData['section']) {
                    $brandlandingData['section'] == 'Top' ? 1 : 2;
                }
                
                if(isset($brandlandingData['feature_image'])) {
                    // $imagename = explode('https://react.tamkeenstores.com.sa/assets/new-media/', $brandlandingData['feature_image']);
                    $image = ProductMedia::where('image', $brandlandingData['feature_image'])->first();
                    $brandlandingData['feature_image'] = $image->id;
                }
                
                
                $brandlandingcat = BrandPageCategories::create($brandlandingData);
                $brandlandingcat->save();
            }
            $success = true;
            $message = 'Success! Brand Landing Categories successfully Import';
        }
        $response = [
            'success'=> $success, 'message' => $message
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