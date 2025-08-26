<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BrandLandingPage;
use App\Models\BrandPageCategories;
use App\Models\Brand;
use App\Models\Productcategory;
use App\Traits\CrudTrait;

class BrandLandingPageApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'brand_landing_page';
    protected $relationKey = 'brand_landing_page_id';


    public function model() {
        $data = ['limit' => -1, 'model' => BrandLandingPage::class, 'sort' => ['id','desc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        return ['categories_id' => 'categories','BrandBannerImage_id' => 'BrandBannerImage:id,image','MiddleBannerImage_id' => 'MiddleBannerImage:id,image'
        ,'categories_image' => 'categories.FeaturedImage:id,image', 'brand_data' => 'branddata:id,name', 'categories_data' => 'categories.Category:id,name'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['category' => Productcategory::where('status','=',1)->get(['id as value', 'name as label']),
         'brands' => Brand::where('status','=',1)->get(['id as value', 'name as label']),
         ];
    }
    
    public function store(Request $request) 
    {
        // print_r($request->all());die();
        
        $brandlanding = BrandLandingPage::create([
            'brand_id' => isset($request->brand_id) ? $request->brand_id : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'brand_banner_link' => isset($request->brand_banner_link) ? $request->brand_banner_link : null,
            'brand_banner_media' => isset($request->brand_banner_media) ? $request->brand_banner_media : null,
            'middle_banner_link' => isset($request->middle_banner_link) ? $request->middle_banner_link : null,
            'middle_banner_media' => isset($request->middle_banner_media) ? $request->middle_banner_media : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
        if (isset($request->categories_id)) {
             foreach ($request->categories_id as $k => $value) {
                //   print_r($value['ImageId']);
                $data = [
                    'brand_landing_id' => $brandlanding->id,
                    'category_id' => isset($value['category']) ? $value['category']['value'] : null,
                    'section' => !empty($value['section']) ? $value['section']['value'] : null,
                    'sorting' => isset($value['sorting']) ? $value['sorting'] : null,
                    'link' => isset($value['link']) ? $value['link'] : null,
                    'feature_image' => isset($value['ImageId']) ? $value['ImageId'] : null,
                ];
                
                BrandPageCategories::create($data);
             }
         }
         return response()->json(['success' => true, 'message' => 'Brand Landing Page Has been created!']);
    }
    
     public function update(Request $request, $id) {
         if (isset($request->categories_id)) {
            $categories_data = BrandPageCategories::where('brand_landing_id', '=',$id)->get();
            $categories_data->each->delete();
            
            foreach ($request->categories_id as $k => $value) {
                //   print_r($value['ImageId']);
                $data = [
                    'brand_landing_id' => $id,
                    'category_id' => isset($value['category']) ? $value['category']['value'] : null,
                    'section' => !empty($value['section']) ? $value['section']['value'] : null,
                    'sorting' => isset($value['sorting']) ? $value['sorting'] : null,
                    'link' => isset($value['link']) ? $value['link'] : null,
                    'feature_image' => isset($value['ImageId']) ? $value['ImageId'] : null,
                ];
                
                BrandPageCategories::create($data);
             }
         }
         
         $brandlanding = BrandLandingPage::whereId($id)->update([
            'brand_id' => isset($request->brand_id) ? $request->brand_id : null,
            'notes' => isset($request->notes) ? $request->notes : null,
            'brand_banner_link' => isset($request->brand_banner_link) ? $request->brand_banner_link : null,
            'brand_banner_media' => isset($request->brand_banner_media) ? $request->brand_banner_media : null,
            'middle_banner_link' => isset($request->middle_banner_link) ? $request->middle_banner_link : null,
            'middle_banner_media' => isset($request->middle_banner_media) ? $request->middle_banner_media : null,
            'status' => isset($request->status) ? $request->status : 0,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Brand Landing Page Has been updated!']);
     }
     
     public function destroy($id)
    {
        $categories_data = BrandPageCategories::where('brand_landing_id', '=',$id)->get();
        $categories_data->each->delete();
        
        $brands = BrandLandingPage::findorFail($id);
        $brands->delete();
        return response()->json(['success' => true, 'message' =>'Brand Landing Page Has been deleted!']);
    }
    
    public function multidelete(Request $request) {
        $success = false;
        if(isset($request->id)) {
            $ids = $request->id;
            $categories_data = BrandPageCategories::where('brand_landing_id', '=',$ids)->get();
            $categories_data->each->delete();
            $deletebrandslanding = BrandLandingPage::whereIn('id',$ids)->get();
            $deletebrandslanding->each->delete();
            $success = true;
        }
        return response()->json(['success' => $success, 'message' => 'Selected Brand Landing Pages Has been deleted!']);
            
    }
}
