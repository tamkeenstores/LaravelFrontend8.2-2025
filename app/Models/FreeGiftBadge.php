<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeGiftBadge extends Model
{
    use HasFactory;
    protected $table = 'free_gift_badge';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
        'id', 'end_date', 'start_date', 'text', 'text_arabic', 'status', 'badge_type', 'for_web', 'for_app'
    ];

    public function Badgeproducts() {
        return $this->hasMany(FreeGiftBadgeProduct::class, 'badge_id', 'id');
    }

    public function BadgeStore() {
        return $this->belongsToMany(FreeGiftBadgeProduct::class, 'free_gift_badge_product', 'badge_id', 'product_id');
    }

    public function Badgecategories() {
        return $this->hasMany(FreeGiftBadgeCategories::class, 'badge_id', 'id');
    }

    public function categoriesStore() {
        return $this->belongsToMany(FreeGiftBadgeCategories::class, 'free_gift_badge_categories', 'badge_id', 'category_id');
    }

    public function BadgeBrands() {
        return $this->hasMany(FreeGiftBadgeBrands::class, 'badge_id', 'id');
    }

    public function brandsStore() {
        return $this->belongsToMany(FreeGiftBadgeBrands::class, 'free_gift_badge_categories', 'badge_id', 'category_id');
    }
}
