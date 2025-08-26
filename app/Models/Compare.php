<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compare extends Model
{
    use HasFactory;
    
    protected $table = 'compare';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable =[
    	'id', 'product_id', 'user_id', 'created_at', 'updated_at',
    ];

    public function user(){
	   return $this->belongsTo(User::class);
	}

	public function product(){
	   return $this->belongsTo(Product::class);
	}
}
