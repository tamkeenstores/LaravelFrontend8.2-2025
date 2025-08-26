<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $table = 'tags';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        
        'id','name', 'name_arabic','slug', 'sort','icon','image_link_app','status','created_at','updated_at',
    ];

    //  public function blog() {
    //     return $this->belongsToMany('blog')->withTimestamps();
    // }
    public function childs() {
        return $this->hasMany(SubTags::class, 'tag_id', 'id')->orderBy('sort', 'ASC');
    }
    public function FeatureImage() {
        return $this->belongsTo(ProductMedia::class, 'icon', 'id');
    }

    // public function tag() {
    //     return $this->belongsToMany(Tag::class, 'product_tag', 'tag_id', 'product_id');
    // }

    //  public function sluggable(): array
    // {
    //     return [
    //         'slug' => [
    //             'source' => 'name'
    //         ]
    //     ];
    // }
}
