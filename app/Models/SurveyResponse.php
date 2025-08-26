<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'response_data' => 'array', // Ensures JSON is cast to array automatically
    ];
}
