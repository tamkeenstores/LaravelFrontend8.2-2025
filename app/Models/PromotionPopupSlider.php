<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionPopupSlider extends Model
{
    use HasFactory;
    protected $table ='promotion_popup_slider';
    protected $guarded =['id','created_at','updated_at'];
}
