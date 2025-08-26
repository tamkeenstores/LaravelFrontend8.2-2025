<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class companyTypes extends Model
{
    use HasFactory;
    protected $table = 'companytypes';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
