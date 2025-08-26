<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTags extends Model
{
    use HasFactory;
    
    protected $table = 'sub_tags';
    // protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        'id', 'tag_id','name', 'name_arabic','sort', 'status','created_at','updated_at', 'icon', 'image_link_app',
    ];
    
    public function parentData() {
        return $this->belongsTo(Tag::class, 'tag_id', 'id')->select(['id','name','name_arabic','slug','sort','status']);
    }
    
    public function tagProducts() {
        return $this->belongsToMany(Product::class, 'product_tag', 'sub_tag_id', 'product_id');
    }
}
