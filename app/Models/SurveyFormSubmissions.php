<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyFormSubmissions extends Model
{
    protected $table = 'survey_form_submissions';
    protected $guarded = [];
    public function surveyForm(){
        return $this->belongsTo(SurveyForm::class,'survey_form_id','id');
    }
}
