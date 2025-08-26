<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryFilter extends Model
{
    use HasFactory;
    protected $table = 'filter_category';

    protected $fillable =[
    	'id', 'category_id', 'filter_category_id'
    ];
}
