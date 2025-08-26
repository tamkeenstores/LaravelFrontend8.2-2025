<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageSliderImage extends Model
{
    use HasFactory;
    protected $table = 'homepage_slider_images';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function homepagesliderdata(){
        return $this->belongsTo(HomePageSlider::class, 'slider_id', 'id');
    }
    
    public function cityData() {
        return $this->belongsTo(States::class, 'city_id', 'id');
    }
}
