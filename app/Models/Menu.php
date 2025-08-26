<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menu';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        'id', 'name', 'name_arabic', 'slug', 'sort', 'status', 'image', 'svg_icon'
    ];
    
    public function ImageData(){
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
}
