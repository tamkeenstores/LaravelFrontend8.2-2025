<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogSliders extends Model
{
    use HasFactory;
    protected $table = 'blog_sliders';
    protected $fillable = [
        'id','sliders', 'slider_image_one', 'url_one', 'slider_image_two', 'url_two', 'slider_image_three', 'url_three', 'slider_image_four', 'url_four', 'slider_image_five', 'url_five', 'slider_image_six', 'url_six','status','created_at','updated_at',
    ];
    
    // public function SliderImageOne() {
    //     return $this->belongsTo(MediaCdn::class, 'slider_image_one', 'id')->select(['id','file_url']);
    // }
    // public function SliderImageTwo() {
    //     return $this->belongsTo(MediaCdn::class, 'slider_image_two', 'id')->select(['id','file_url']);
    // }
    // public function SliderImageThree() {
    //     return $this->belongsTo(MediaCdn::class, 'slider_image_three', 'id')->select(['id','file_url']);
    // }
    // public function SliderImageFour() {
    //     return $this->belongsTo(MediaCdn::class, 'slider_image_four', 'id')->select(['id','file_url']);
    // }
    // public function SliderImageFive() {
    //     return $this->belongsTo(MediaCdn::class, 'slider_image_five', 'id')->select(['id','file_url']);
    // }
    // public function SliderImageSix() {
    //     return $this->belongsTo(MediaCdn::class, 'slider_image_six', 'id')->select(['id','file_url']);
    // }
}
