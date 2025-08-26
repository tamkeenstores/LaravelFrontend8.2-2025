<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageSlider extends Model
{
    use HasFactory;
    protected $table = 'homepage_sliders';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function sliderImage(){
        return $this->hasMany(HomePageSliderImage::class, 'slider_id', 'id');
    }
}
