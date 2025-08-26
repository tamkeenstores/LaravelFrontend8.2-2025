<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplaceTag extends Model
{
    use HasFactory;
    protected $table = 'replace_tag';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
