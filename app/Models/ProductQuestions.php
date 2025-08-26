<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQuestions extends Model
{
    use HasFactory;
    protected $table = 'product_questions';

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
