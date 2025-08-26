<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    use HasFactory;
    protected $table = 'product_gallery';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['product_id','image'];
    
    public function galleryImage(){
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
}
