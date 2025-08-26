<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentSections extends Model
{
    use HasFactory;
    protected $table = 'department_sections';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
