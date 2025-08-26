<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnReasons extends Model
{
    use HasFactory;
    protected $table = 'return_reasons';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function question()
    {
        return $this->belongsTo(ReturnQuestions::class, 'question_id', 'id');
    }
    
    public function answer()
    {
        return $this->belongsTo(ReturnAnswers::class, 'answer_id', 'id');
    }
    
    
}
