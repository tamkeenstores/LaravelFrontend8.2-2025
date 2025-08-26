<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RulesDiscount extends Model
{
    use HasFactory;
    protected $table = 'rule_discount';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
