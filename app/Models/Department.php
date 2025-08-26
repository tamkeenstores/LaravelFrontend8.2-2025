<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table = 'department';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function teamsData() {
        return $this->belongsToMany(User::class, 'department_teams', 'department_id', 'team_id');
    }
    
    public function sectionData() {
        return $this->belongsToMany(Section::class, 'department_sections', 'department_id', 'section_id');
    }
    
    public function ManagerData() {
        return $this->belongsTo(User::class, 'manager', 'id');
    }
    
    public function SupervisorData() {
        return $this->belongsTo(User::class, 'supervisor', 'id');
    }
}
