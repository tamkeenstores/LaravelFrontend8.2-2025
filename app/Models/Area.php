<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $table = 'area';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function city() {
        return $this->belongsTo(States::class, 'city', 'id');
    }
}
