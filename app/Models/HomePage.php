<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    use HasFactory;
    protected $table = 'home_pages';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'meta_title_en', 'meta_title_ar','meta_description_en', 'meta_description_ar', 'meta_keyword_en', 'meta_keyword_ar','categories_top','categories_top_status','brands_middle','brands_middle_status','products_first','products_first_status','products_second','products_second_status','products_third','products_third_status', 'products_fourth','products_fourth_status', 'products_fifth','products_fifth_status',
    	'cat_view_all',
    	'cat_heading',
    	'cat_heading_arabic',
    	'brand_view_all', 
    	'pro_first_view_all', 
    	'pro_first_heading',
    	'pro_first_heading_arabic', 
    	'pro_second_view_all',
    	'pro_second_heading', 
    	'pro_second_heading_arabic', 
    	'pro_third_view_all', 
    	'pro_third_heading',
    	'pro_third_heading_arabic',
    	'pro_fourth_view_all', 
    	'pro_fourth_heading',
    	'pro_fourth_heading_arabic',
    	'pro_fifth_view_all', 
    	'pro_fifth_heading',
    	'pro_fifth_heading_arabic',

        'products_sixth',
        'products_sixth_status',
        'pro_sixth_view_all', 
        'pro_sixth_heading',
        'pro_sixth_heading_arabic',

        'products_seventh',
        'products_seventh_status',
        'pro_seventh_view_all', 
        'pro_seventh_heading',
        'pro_seventh_heading_arabic',

        'products_eigth',
        'products_eigth_status',
        'pro_eigth_view_all', 
        'pro_eigth_heading',
        'pro_eigth_heading_arabic',

        'products_nineth',
        'products_nineth_status',
        'pro_nineth_view_all', 
        'pro_nineth_heading',
        'pro_nineth_heading_arabic',

        'products_tenth',
        'products_tenth_status',
        'pro_tenth_view_all', 
        'pro_tenth_heading',
        'pro_tenth_heading_arabic',

        'products_eleventh',
        'products_eleventh_status',
        'pro_eleventh_view_all', 
        'pro_eleventh_heading',
        'pro_eleventh_heading_arabic',
        
        'products_twelveth',
        'products_twelveth_status',
        'pro_twelveth_view_all', 
        'pro_twelveth_heading',
        'pro_twelveth_heading_arabic',
        
        'banner_image1',
        'banner_image2',
        'banner_image3',
        'banner_image4',
        'banner_image1_link',
        'banner_image2_link',
        'banner_image3_link',
        'banner_image4_link',
        'banner_first_status',
        'banner_second_status',
        'banner_first_heading',
        'banner_first_heading_arabic',
        'banner_second_heading',
        'banner_second_heading_arabic',
    ];
    public function BannerImageOne(){
        return $this->belongsTo(ProductMedia::class, 'banner_image1', 'id');
    }
    
    public function BannerImageTwo(){
        return $this->belongsTo(ProductMedia::class, 'banner_image2', 'id');
    }
    
    public function BannerImageThird(){
        return $this->belongsTo(ProductMedia::class, 'banner_image3', 'id');
    }
    
    public function BannerImageFourth(){
        return $this->belongsTo(ProductMedia::class, 'banner_image4', 'id');
    }
}
