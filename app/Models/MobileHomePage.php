<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileHomePage extends Model
{
    use HasFactory;
    protected $table = 'mobile_home_page';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function images() {
        return $this->hasMany(MobileHomeImages::class, 'mobile_home_page_id', 'id');
    }
    
    public function services() {
        return $this->hasMany(MobileHomeServices::class, 'mobile_home_page_id', 'id');
    }
}
