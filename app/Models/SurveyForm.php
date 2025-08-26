<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyForm extends Model
{
    protected $guarded = [];
    protected $table = 'survey_form';
    public function surveyFormSubmissions(){
        return $this->hasMany(SurveyFormSubmissions::class,'survey_form_id');
    }
    public function users(){
        return $this->belongsTo(User::class, 'creator', 'id');
    }
}
