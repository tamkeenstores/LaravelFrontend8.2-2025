<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxClasses extends Model
{
    use HasFactory;
     protected $table = 'taxclasses';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = ['name','name_arabic'];

    // public function productsCount() {
    //     return $this->hasMany(Product::class, 'tax_class', 'id');
    // }
}
