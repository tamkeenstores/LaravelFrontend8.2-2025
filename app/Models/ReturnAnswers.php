<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnAnswers extends Model
{
    use HasFactory;
    protected $table = 'return_answers';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function questions()
    {
        return $this->belongsTo(ReturnQuestions::class, 'return_questions_id', 'id');
    }
}
