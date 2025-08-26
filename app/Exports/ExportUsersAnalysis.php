<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\User;
use DB;

class ExportUsersAnalysis implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public $r = [];
    
    public function __construct($date)
    {
        $this->r = $date;
        // print_r($this->r);die();
    }
    
    public function collection()
    {
        $topusers = User::select('users.first_name', 'users.last_name','users.email', 'users.phone_number','users.gender','users.date_of_birth',
        DB::raw('group_concat(Distinct states.name) as city'),DB::raw('count(order.id) as totalorders'),
        DB::raw('ROUND(sum(totalorder.price)) as totalrevenue'),
      'users.user_device')
        ->leftJoin('order', function($join) {
            $join->on('users.id', '=', 'order.customer_id');
        })
        ->leftJoin('shipping_address', function($join) {
            $join->on('shipping_id', '=', 'shipping_address.id');
        })
        ->leftJoin('states', function($join) {
            $join->on('states.id', '=', 'shipping_address.state_id');
        })
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        // ->selectRaw('count(order.customer_id) as totalorders')
        ->groupBy('users.id')
        ->orderBy('totalorders', 'DESC')
        // ->limit(1000)
        ->whereBetween('users.created_at', [explode('_', $this->r)[0], explode('_', $this->r)[1]])
        ->get();
        
        return $topusers;
    }
    
    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Email',
            'Phone Number',
            'Gender',
            'D.O.B',
            'City',
            'T.Orders',
            'T.Revenue	',
            'Device',
        ];
    }
}
