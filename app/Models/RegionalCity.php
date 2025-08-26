<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionalCity extends Model
{
    use HasFactory;
    protected $table = 'regional_city';

    protected $fillable =[
     
     'id', 'city_id', 'regional_id',

    ];
}
