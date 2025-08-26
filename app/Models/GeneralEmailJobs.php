<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralEmailJobs extends Model
{
    use HasFactory;
    protected $table = 'general_email_jobs';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function emailtimes() {
        return $this->hasMany(GeneralEmailTimes::class, 'email_job_id', 'id');
    }
    
    // public function emailtimes()
    // {
    //     return $this->belongsTo(GeneralEmailTimes::class, 'id', 'email_job_id');
    // }
}
