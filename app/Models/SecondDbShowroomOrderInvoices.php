<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDbShowroomOrderInvoices extends Model
{
    use HasFactory;
    protected $table = 'showroom_order_invoices';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $connection = 'second_db';
}
