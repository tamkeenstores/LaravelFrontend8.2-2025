<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCenterCity extends Model
{
    use HasFactory;
    protected $table = 'maintenance_center_city';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function cityData() 
    {
        return $this->belongsTo(States::class, 'city_id', 'id');
    }
}
