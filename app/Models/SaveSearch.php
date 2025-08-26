<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaveSearch extends Model
{
    use HasFactory;
    protected $table = 'save_search';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function userData()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
