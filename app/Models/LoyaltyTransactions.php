<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransactions extends Model
{
    use HasFactory;
    protected $table = 'loyalty_transactions';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
}
