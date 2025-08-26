<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnQuestions extends Model
{
    use HasFactory;
    protected $table = 'return_questions';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function answers() {
        return $this->hasMany(ReturnAnswers::class, 'return_questions_id', 'id');
    }
    
}
