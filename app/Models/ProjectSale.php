<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSale extends Model
{
    use HasFactory;
    protected $table = 'project_sale';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function citydata(){
        return $this->belongsTo(States::class, 'city', 'id');
    }
}
