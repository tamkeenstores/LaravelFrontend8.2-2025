<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class companyTags extends Model
{
    use HasFactory;
    protected $table = 'company_tags';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
