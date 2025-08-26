<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;
    protected $table = 'general_setting';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public function productsetting()
    {
        return $this->belongsTo(GeneralSettingProduct::class, 'id', 'generalsetting_id');
    }
    
    public function paymentsetting()
    {
        return $this->belongsTo(GeneralSettingPayment::class, 'id', 'generalsetting_id');
    }
    
    public function faviconimage() {
        return $this->belongsTo(ProductMedia::class, 'favicon_image', 'id');
    }
    
    public function logowebimage() {
        return $this->belongsTo(ProductMedia::class, 'logo_web_image', 'id');
    }
    
    public function logomobileimage() {
        return $this->belongsTo(ProductMedia::class, 'logo_mob_image', 'id');
    }
}
