<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderSummary;
use DB;
use Carbon\Carbon;

class UserReportApiController extends Controller
{
    public function Userreport($date) {
        $activeusers = User::where('status', 1)
        ->whereBetween('created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('created_at', '>=', explode('_', $date)[0])
        ->where('created_at', '<=', explode('_', $date)[1])
        ->count();
        
        $inactiveusers = User::where('status', 0)
        ->whereBetween('created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('created_at', '>=', explode('_', $date)[0])
        ->where('created_at', '<=', explode('_', $date)[1])
        ->count();
        
        $newusers = User::whereBetween('created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('created_at', '>=', explode('_', $date)[0])
        ->where('created_at', '<=', explode('_', $date)[1])
        ->count();
        
        $newuservalue = Order::select(DB::raw('ROUND(sum(totalorder.price)) as totalamount'))
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->get();
        
        $newuservalueavg = Order::select(DB::raw('ROUND(sum(totalorder.price) / count(order.id))  as totalavg'))
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->get();
        
        $recurringusers = DB::table("order as or")
        ->select('customer_id')
        ->selectRaw('count(customer_id) as customer')
        ->whereBetween('or.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('or.created_at', '>=', explode('_', $date)[0])
        ->where('or.created_at', '<=', explode('_', $date)[1])
        ->count('customer_id');
        
        // $result = DB::table("order as or")
        // ->select('customer_id')
        // ->selectRaw('count(customer_id) as customer')
        // ->groupBy('customer_id')
        // ->orderBy('customer', 'DESC')
        // ->whereBetween('or.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        // ->first();
        
        
        $result = User::select(DB::raw('count(order.id) as totalorders'),'order.customer_id as customer_id')
        ->leftJoin('order', function($join) {
            $join->on('users.id', '=', 'order.customer_id');
        })
        ->groupBy('users.id')
        ->orderBy('totalorders', 'DESC')
        ->whereBetween('users.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('users.created_at', '>=', explode('_', $date)[0])
        ->where('users.created_at', '<=', explode('_', $date)[1])
        ->first();
        
        
        
       $avgordervalue = Order::select([DB::raw('ROUND(sum(totalorder.price) / count(order.id))  as totalavg')])
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->first();
        
        
        
        $topusers = User::select(DB::raw('ROUND(sum(totalorder.price)) as totalrevenue'),DB::raw('count(order.id) as totalorders'),DB::raw('group_concat(Distinct states.name) as city'),'users.first_name', 'users.last_name',
        'users.phone_number','users.email','users.date_of_birth','users.user_device', 'users.gender','order.customer_id as customer_id')
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
        ->limit(15)
        ->whereBetween('users.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('users.created_at', '>=', explode('_', $date)[0])
        ->where('users.created_at', '<=', explode('_', $date)[1])
        ->get();
        
        // print_r($topusers);die();
        
        
        return json_encode(['activeusers' => $activeusers, 'inactiveusers' => $inactiveusers, 'recurringusers' => $recurringusers, 'newusers' => $newusers, 'newuservalue' => $newuservalue, 'uservalueavg' => $newuservalueavg, 'highestpaiduser' => $result, 'averageordervalue' => $avgordervalue
       , 'topusers' => $topusers]);
        
        
        
    }
}
