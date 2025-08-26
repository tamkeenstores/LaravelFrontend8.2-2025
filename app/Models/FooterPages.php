<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterPages extends Model
{
    use HasFactory;
    protected $table = 'footer_pages';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
