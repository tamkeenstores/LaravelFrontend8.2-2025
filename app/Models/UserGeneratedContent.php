<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGeneratedContent extends Model
{
    use HasFactory;
    protected $table = 'user_generated_content';
    protected $guarded = [];
    
    public function userData()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function ugcCategory() 
    {
        return $this->belongsToMany(Productcategory::class, 'ugc_categories', 'ugc_id', 'category_id');
    }
}
