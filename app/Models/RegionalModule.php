<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionalModule extends Model
{
    use HasFactory;
    protected $table = 'regional_modules';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['id','title','title_arabic','priority','status','created_at','updated_at'];
    
    public function citydata() 
    {
        return $this->belongsToMany(States::class, 'regional_city', 'regional_id', 'city_id');
    }
}
