<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplaceBrand extends Model
{
    use HasFactory;
    protected $table = 'replace_cat';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
