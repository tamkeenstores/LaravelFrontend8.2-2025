<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageThree extends Model
{
    use HasFactory;
    protected $table = 'homepage3';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function homepagedata(){
        return $this->hasMany(HomePageThreeData::class, 'homepage_id', 'id');
    }
}
