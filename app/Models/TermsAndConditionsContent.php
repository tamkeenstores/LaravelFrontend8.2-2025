<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAndConditionsContent extends Model
{
    use HasFactory;
    protected $table = 'terms_and_conditions_content';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function terms()
    {
        return $this->hasMany(TermsAndConditions::class, 'terms_id', 'id');
    }
}
