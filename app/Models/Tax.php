<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;
    protected $table = 'tax';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['class_id','country_code','state_code','postcode_zip','city','rate','tax_name','tax_name_arabic','priority'];
}
