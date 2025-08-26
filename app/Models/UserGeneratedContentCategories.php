<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGeneratedContentCategories extends Model
{
    use HasFactory;
    protected $table = 'ugc_categories';
    protected $guarded = [];
    
    public function categoryData()
    {
        return $this->belongsTo(Productcategory::class, 'category_id', 'id');
    }
    public function ugcData()
    {
        return $this->belongsTo(UserGeneratedContent::class, 'ugc_id', 'id');
    }
}
