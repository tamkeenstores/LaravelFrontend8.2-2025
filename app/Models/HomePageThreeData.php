<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageThreeData extends Model
{
    use HasFactory;
    protected $table = 'homepage3_extradata';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function homepage3()
    {
        return $this->belongsTo(HomePageThree::class, 'homepage_id');
    }
    
    public function category()
    {
        return $this->belongsTo(Productcategory::class, 'category_id');
    }
}
