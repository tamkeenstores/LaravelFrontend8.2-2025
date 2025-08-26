<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadgeCategories extends Model
{
    use HasFactory;
    protected $table = 'badge_categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
