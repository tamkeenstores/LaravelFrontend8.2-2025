<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    use HasFactory;
    protected $table = 'popups';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function featuredImage(){
        return $this->belongsTo(ProductMedia::class, 'image', 'id');
    }
}
