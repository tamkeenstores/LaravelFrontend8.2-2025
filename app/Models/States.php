<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    use HasFactory;
    protected $table = 'states';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function region() {
        return $this->belongsTo(Region::class, 'region', 'id');
    }
}
