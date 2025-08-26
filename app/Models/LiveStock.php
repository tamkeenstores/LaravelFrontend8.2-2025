<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class LiveStock extends Model
// {
//     use HasFactory;
    
//     protected $table = 'livestock';
//     protected $guarded = ['id', 'created_at', 'updated_at'];
//     protected $connection = 'second_db';

//     protected $fillable =[
//     	'id', 'sku', 'ln_sku', 'qty', 'city', 'city_code', 'created_at', 'updated_at',
//     ];

// 	public function product(){
// 	   return $this->setConnection('mysql')->belongsTo(Product::class, 'sku', 'sku');
//     }
// }


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveStock extends Model
{
    use HasFactory;
    protected $table = 'livestock';

    protected $fillable =[
    	'id', 'sku', 'ln_sku', 'qty', 'city', 'city_code'
    ];
    
    public function productData(){
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
    
    public function warehouseData(){
        return $this->belongsTo(Warehouse::class, 'city', 'ln_code');
    }
   
}
