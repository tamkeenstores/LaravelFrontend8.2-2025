<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrowserAnalytics extends Model
{
    use HasFactory;
    protected $table = 'browser_analytics';
    protected $guarded = [];
}
