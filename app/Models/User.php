<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
Use App\Models\Role;
Use DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'phone_number',
        'role_id',
        'email',
        'date_of_birth',
        'notes',
        'status',
        'blacklist',
        'password',
        'user_device',
        'lang',
        'gender',
        'profile_img',
        'amount',
        'emailstatus',
        'remember_token',
        'browser',
        'promotionemail',
        'new_pending_email_count',
        'deleted_at',
        'created_at', 
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->hasMany(RolePermission::class, 'role_id','role_id');
    }

    // public function getmodules()
    // {

    //     $perms = $this->permission()->select(DB::raw("GROUP_CONCAT(module_id SEPARATOR ',') as `modules`"))->where('pview', '=', '1')->first();
    //     $modules = Module::with(['childs' => function($query) use($perms)
    //             {
    //                 $query->whereIn('id', explode(',', $perms->modules));
    //             }
    //         ])->whereIn('id', explode(',', $perms->modules))->where('parent_id', 0)->get();
    //     //print_r($modules);die();
    //     return $modules;
    // }

    // public function hasPer($perm = null)
    // {
    //     if(is_null($perm)) return false;
    //     $module = Module::where('name',$perm)->first();
    //     //print_r($module);die();
    //     $perms = $this->permission()->select('pview','pedit','pcreate','pdelete')->where('module_id', '=', $module->id)->first();
    //     return ($perms) ? $perms->toArray() : [];
    //     // if ($perms) {
    //     //     return $perms->toArray();
    //     // }
    //     // else{
    //     //     return [];
    //     // }
    // }

    public function shippingAddressData()
    {
        return $this->hasMany(shippingAddress::class, 'customer_id', 'id');
    }
    public function shippingAddressDataDefault()
    {
        return $this->belongsTo(shippingAddress::class, 'id', 'customer_id')->where('make_default', 1);
    }
    public function OrdersData()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
    
    public function DeliveredOrdersData()
    {
        return $this->hasMany(DeliveredOrder::class, 'customer_id', 'id');
    }
    
    public function ConfirmedOrdersData()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id')->whereIn('status', [1, 2, 4]);
    }
    
    public function lastOrder()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id')->latest();
    }
    
    public function OrdersDataDiscountRule()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
    
    public function wishlists()
    {
        return $this->hasMany(Wishlists::class, 'user_id', 'id');
    }
    
    public function compares()
    {
        return $this->hasMany(Compare::class, 'user_id', 'id');
    }
    
    public function loyaltypointsdata()
    {
        return $this->hasMany(LoyaltyHistory::class, 'user_id', 'id');
    }
    
    public function commissions()
    {
        return $this->hasMany(UserComissions::class, 'user_id', 'id');
    }
    
    public function returnData()
    {
        return $this->hasMany(ReturnRefund::class, 'user_id', 'id');
    }
    
    public function WalletHistory()
    {
        return $this->hasMany(WalletHistory::class, 'user_id', 'id')->orderBy('id','desc');
    }
    
    public function loyaltyPointsDataNew(){
       return $this->setConnection('mysql')->belongsTo(LoyaltyPoints::class, 'phone_number', 'mobile_number');
    }
    
    public function loyaltyTransactionsDataNew(){
       return $this->setConnection('mysql')->belongsTo(LoyaltyTransactions::class, 'phone_number', 'mobile_number');
    }
}
