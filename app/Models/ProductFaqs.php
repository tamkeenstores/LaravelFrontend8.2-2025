<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFaqs extends Model
{
    use HasFactory;
    protected $table = 'product_faqs';

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
