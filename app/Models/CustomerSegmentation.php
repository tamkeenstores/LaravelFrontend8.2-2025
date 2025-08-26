<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSegmentation extends Model
{
    use HasFactory;
    protected $table = 'customer_segmentation';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function conditions(){
        return $this->belongsTo(SegmentationCondition::class, 'id', 'segment_id');
    }
}
