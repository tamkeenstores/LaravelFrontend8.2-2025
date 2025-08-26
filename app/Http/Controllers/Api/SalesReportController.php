<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\OrderSummary;
use App\Models\Product;
use App\Models\Brand;
use App\Models\OrderDetail;
use App\Models\Productcategory;
use App\Models\MarketplaceSales;
use DB;

class SalesReportController extends Controller
{
    public function reportIndex($date) {
        $month = date('m', strtotime(explode('_', $date)[1]));
        // $filter = false;
        // if($month > 6) {
            $filter = true;
        // }
        // print_r(explode('_', $date)[0]);
        $count = Order::select([DB::raw('count(order.id) as totalorder'), DB::raw('ROUND(sum(totalorder.price)) as totalamount'), DB::raw('ROUND(sum(totalorder.price) / count(order.id))  as totalavg'), 'status'])
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        // ->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        // ->where( DB::raw('YEAR(order.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->groupBy('order.status')
        ->get();

        $pendingChart = Order::select(DB::raw('count(order.id) as totalorder'), DB::raw('ROUND(sum(totalorder.price)) as totalamount'), DB::raw("MONTH(order.created_at) as ordermonth"))
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->where('status', 8)
        ->orderBy('ordermonth', 'ASC')
        ->groupByRaw('MONTH(order.created_at)')
        ->where( DB::raw('YEAR(order.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->get();

        $allorderChart = Order::select(DB::raw('count(order.id) as totalorder'), DB::raw('ROUND(sum(totalorder.price)) as totalamount'), DB::raw("MONTH(order.created_at) as ordermonth"))
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        // ->where('status', '!=', 8)
        // ->where('status', '!=', 7)
        // ->where('status', '!=', 6)
        // ->where('status', '!=', 5)
        ->orderBy('ordermonth', 'ASC')
        ->groupByRaw('MONTH(order.created_at)')
        ->where( DB::raw('YEAR(order.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->get();


        $paymentmethod = Order::select(
            DB::raw('ROUND(sum(totalorder.price)) as totalamount'), 
            DB::raw("order.paymentmethod as paymentmethod"), 
            DB::raw("COUNT(order.id) as torder"),
            DB::raw("MONTH(order.created_at) as ordermonth")
        )
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->groupBy('order.paymentmethod', 'ordermonth')
        ->where('status', '!=', 8)
        ->where('status', '!=', 7)
        ->where('status', '!=', 6)
        ->where('status', '!=', 5)
        ->orderBy('ordermonth', 'ASC')
        ->whereNotNull('paymentmethod')
        ->where( DB::raw('YEAR(order.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->get();
        
        // market place
        $markets = MarketplaceSales::
        when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('marketplacesales.date', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('marketplacesales.date', '>=', explode('_', $date)[0])
        ->where('marketplacesales.date', '<=', explode('_', $date)[1])
        ->get();

        $paymentData = [
            'tabbysaale' => $paymentmethod->where('paymentmethod', 'tabby')->sum('totalamount'), 
            'tabbychart' => $paymentmethod->where('paymentmethod', 'tabby')->toArray(),
            'tamarasaale' => $paymentmethod->where('paymentmethod', 'tamara')->sum('totalamount'), 
            'tamarachart' => $paymentmethod->where('paymentmethod', 'tamara'),
            'hyperpaysaale' => $paymentmethod->where('paymentmethod', 'hyperpay')->sum('totalamount'), 
            'hyperpaychart' => $paymentmethod->where('paymentmethod', 'hyperpay'),
            'applepaysaale' => $paymentmethod->where('paymentmethod', 'applepay')->sum('totalamount'), 
            'applepaychart' => $paymentmethod->where('paymentmethod', 'applepay'),
            'madapaysaale' => $paymentmethod->where('paymentmethod', 'madapay')->sum('totalamount'), 
            'madapaychart' => $paymentmethod->where('paymentmethod', 'madapay'),
            'tasheelsaale' => $paymentmethod->where('paymentmethod', 'tasheel')->sum('totalamount'), 
            'tasheelchart' => $paymentmethod->where('paymentmethod', 'tasheel'),
        ];


        // City chart Please don't remove this code
        // $currentmonth = date('m');
        // if(date('Y', strtotime(explode('_', $date )[1]) < date('Y'))) {
        //     $currentmonth = 12;
        // }

        // $selects = [
        //     'states.name as city',
        //     DB::raw('SUM(totalqty.qty) as totalqty'),
        //     DB::raw('ROUND(sum(totalorder.price)) as totalamount'), 
        //     'states.id as cities_id',
        //     DB::raw('count(order.id) as totalorder')
        // ];
        // for ($i=0; $i < $currentmonth; $i++) { 
        //     $selects[] =  DB::raw('CONVERT(ROUND(SUM(IF(MONTH(order.created_at) = '.($i+1).', totalorder.price, 0))), char) as '.($i+1).'_total');
        // }
        // if ($currentmonth < 12) {
        //     for ($i=$currentmonth; $i < 12; $i++) { 
        //         $selects[] =  DB::raw('round(sum(totalorder.price) / '.$currentmonth.') as '.($i+1).'_total');
        //     }
        // }

        // $cityChart = Order::select($selects)
        // ->Join('order_summary as totalorder', function($join) {
        //     $join->on('order.id', '=', 'totalorder.order_id');
        //     $join->on('totalorder.type', '=', DB::raw("'total'"));
        // })
        // ->Join('shipping_address', function($join) {
        //     $join->on('order.shipping_id', '=', 'shipping_address.id');
        // })
        // ->Join('states', function($join) {
        //     $join->on('states.id', '=', 'shipping_address.state_id');
        // })
        // ->Join(DB::raw("(select sum(quantity) as qty, order_id from `order` join order_detail on order.id = order_detail.order_id group by order.id) totalqty"), function($join) {
        //     $join->on('order.id', '=', 'totalqty.order_id');
        // })
        // ->where('order.status', '!=', 8)
        // ->where('order.status', '!=', 7)
        // ->where('order.status', '!=', 6)
        // ->where('order.status', '!=', 5)
        // ->orderBy('totalamount', 'DESC')
        // ->groupBy('states.id')
        // ->where( DB::raw('YEAR(totalorder.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])))
        // ->limit(10)
        // ->get();
        // City chart Please don't remove this code
        
        // Top selling brands
        $sellingbrand = OrderDetail::
        select('brands.name as selling_name', DB::raw('sum(order_detail.quantity) as selling_qty'), DB::raw('ROUND(sum(order_detail.total)) as selling_price'))
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('sellingpro.id', '=', 'order_detail.product_id');
        })
        ->leftJoin('brands', function($join) {
            $join->on('brands.id', '=', 'sellingpro.brands');
        })
        ->leftJoin('order', function($join) {
            $join->on('order.id', '=', 'order_detail.order_id');
        })
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        ->groupBy('brands.id')
        ->orderBy('selling_qty', 'desc')
        ->where('order_detail.created_at', '>=', explode('_', $date)[0])
        ->where('order_detail.created_at', '<=', explode('_', $date)[1])
        ->limit('15')
        ->get();
        // Top selling brands

        // Top selling categories
        $sellingcategory = OrderDetail::
        select('productcategories.name as selling_cat', DB::raw('sum(order_detail.quantity) as selling_qty'), DB::raw('ROUND(sum(order_detail.total)) as selling_price'))
        // ->where('total', '>', 1)
        ->groupBy('productcategories.id')
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('order_detail.product_id', '=', 'sellingpro.id');
        })
        ->leftJoin('product_categories', function($join) {
            $join->on('sellingpro.id', '=', 'product_categories.product_id');
        })
        ->leftJoin('productcategories', function($join) {
            $join->on('product_categories.category_id', '=', 'productcategories.id');
        })
        ->leftJoin('order', function($join) {
            $join->on('order.id', '=', 'order_detail.order_id');
        })
        ->where('productcategories.menu', 1)
        ->where('productcategories.status', 1)
        ->where('productcategories.parent_id', '!=', null)
        ->orderByRaw('COUNT(*) DESC')
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        // ->whereBetween('order_detail.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('order_detail.created_at', '>=', explode('_', $date)[0])
        ->where('order_detail.created_at', '<=', explode('_', $date)[1])
        ->limit('15')
        ->get();
        // Top selling categories

        // Top selling cities
        $Topselects = [
            'states.name as city',
            DB::raw('SUM(totalqty.qty) as totalqty'),
            DB::raw('ROUND(sum(totalorder.price)) as totalamount'), 
            'states.id as cities_id',
            DB::raw('count(order.id) as totalorder')
        ];

        $TopcityChart = Order::select($Topselects)
        ->Join('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->Join('shipping_address', function($join) {
            $join->on('order.shipping_id', '=', 'shipping_address.id');
        })
        ->Join('states', function($join) {
            $join->on('states.id', '=', 'shipping_address.state_id');
        })
        ->Join(DB::raw("(select sum(quantity) as qty, order_id from `order` join order_detail on order.id = order_detail.order_id group by order.id) totalqty"), function($join) {
            $join->on('order.id', '=', 'totalqty.order_id');
        })
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        ->orderBy('totalamount', 'DESC')
        ->groupBy('states.id')
        ->orderByRaw('COUNT(*) DESC')
        // ->where( DB::raw('YEAR(totalorder.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])))
        ->whereBetween('totalorder.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('totalorder.created_at', '>=', explode('_', $date)[0])
        ->where('totalorder.created_at', '<=', explode('_', $date)[1])
        ->limit(30)
        ->get();
        // Top selling cities

        // sale box
        $totalorderchart = Order::select(DB::raw('count(order.id) as totalorder'), DB::raw("DATE_FORMAT(order.created_at, '%m/%d/%Y') AS date"))
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        // ->where('status', '!=', 8)
        // ->where('status', '!=', 7)
        // ->where('status', '!=', 6)
        // ->where('status', '!=', 5)
        ->orderBy('date', 'ASC')
        ->groupBy('date')
        ->where( DB::raw('YEAR(order.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->get();
        // sale box

        // recieved order sale box
        $receivedorderchart = Order::select(DB::raw('count(order.id) as totalorder'), DB::raw("DATE_FORMAT(order.created_at, '%m/%d/%Y') AS date"))
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->where('status', '!=', 8)
        ->where('status', '!=', 7)
        ->where('status', '!=', 6)
        ->where('status', '!=', 5)
        ->orderBy('date', 'ASC')
        ->groupBy('date')
        ->where( DB::raw('YEAR(order.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->get();
        // recieved order sale box


        // Payment chart
        $paymentmethodChart = Order::select(
            // DB::raw('ROUND(sum(totalorder.price)) as totalamount'), 
            DB::raw("order.paymentmethod as paymentmethod"), 
            DB::raw("COUNT(order.id) as torder")
            // DB::raw("MONTH(order.created_at) as ordermonth")
        )
        ->leftJoin('order_summary as totalorder', function($join) {
            $join->on('order.id', '=', 'totalorder.order_id');
            $join->on('totalorder.type', '=', DB::raw("'total'"));
        })
        // ->when($filter, function ($q) use ($filter, $date) {
        //     return $q->whereBetween('order.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        // })
        ->where('order.created_at', '>=', explode('_', $date)[0])
        ->where('order.created_at', '<=', explode('_', $date)[1])
        ->groupBy('order.paymentmethod')
        ->where('status', '!=', 8)
        ->where('status', '!=', 7)
        ->where('status', '!=', 6)
        ->where('status', '!=', 5)
        ->orderBy('torder', 'DESC')
        ->whereNotNull('paymentmethod')
        ->get();
        // Payment chart

        $response = [
            'data' => $count, 
            'orderCount' => $count->sum('totalorder'), 
            'orderPrice' => $count->sum('totalamount'), 
            'pendingChart' => $pendingChart, 
            'allorderChart' => $allorderChart, 
            'paymentmethod' => $paymentData, 
            'cityChart' => [], 
            'sellingbrand' => $sellingbrand, 
            'brandsale' => array_sum($sellingbrand->pluck('selling_price')->toArray()), 
            'sellingcategory' => $sellingcategory, 
            'TopcityChart' => $TopcityChart, 
            'totalorderchart' => $totalorderchart, 
            'receivedorderchart' => $receivedorderchart, 
            'paymentmethodChart' => $paymentmethodChart,
            'amazonesale' => $markets->sum('amazon'),
            'carefoursale' => $markets->sum('carefour'),
            'homzmartsale' => $markets->sum('homzmart'),
            'noonsale' => $markets->sum('noon'),
            'centerpointsale' => $markets->sum('centerpoint')
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }


    // pim analytics
    public function pimReport($date, Request $request) {
        // print_r($request->filter);die;
        $filter = true;
        // DB::enableQueryLog();

        $sellingproduct = OrderDetail::
        select('products.sku as selling_sku', 'products.sale_price as selling_pro_price', 'products.quantity as pro_qty')
        ->leftJoin('products', function($join) {
            $join->on('order_detail.product_id', '=', 'products.id');
        })        
        ->leftJoin('order', function($join) {
            $join->on('order.id', '=', 'order_detail.order_id');
        })
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order_detail.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order_detail.created_at', '>=', explode('_', $date)[0])
        ->where('order_detail.created_at', '<=', explode('_', $date)[1])
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        // ->where( DB::raw('YEAR(order_detail.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->groupBy('products.id')
        ->where('total', '>', 1)
        ->orderByRaw('COUNT(*) DESC')
        ->first();

        // selling category
        $sellingcategory = OrderDetail::
        select('productcategories.name as selling_cat', DB::raw('sum(order_detail.quantity) as selling_qty'), DB::raw('ROUND(sum(order_detail.total)) as selling_price'))
        ->where('total', '>', 1)
        ->groupBy('productcategories.id')
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('order_detail.product_id', '=', 'sellingpro.id');
        })
        ->leftJoin('product_categories', function($join) {
            $join->on('sellingpro.id', '=', 'product_categories.product_id');
        })
        ->leftJoin('productcategories', function($join) {
            $join->on('product_categories.category_id', '=', 'productcategories.id');
        })
        ->leftJoin('order', function($join) {
            $join->on('order.id', '=', 'order_detail.order_id');
        })
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        ->where('productcategories.menu', 1)
        ->where('productcategories.status', 1)
        ->orderByRaw('COUNT(*) DESC')
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order_detail.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order_detail.created_at', '>=', explode('_', $date)[0])
        ->where('order_detail.created_at', '<=', explode('_', $date)[1])
        // ->where( DB::raw('YEAR(order_detail.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        // ->limit('5')
        ->first();


        // selling brand
        // $sellingbrand = Brand::
        // select('brands.name as selling_name', DB::raw('sum(order_detail.quantity) as selling_qty'), DB::raw('ROUND(sum(order_detail.total)) as selling_price'))
        // // ->where('total', '>', 1)
        // ->groupBy('brands.id')
        // ->leftJoin('products as sellingpro', function($join) {
        //     $join->on('brands.id', '=', 'sellingpro.brands');
        // })
        // ->leftJoin('order_detail', function($join) {
        //     $join->on('sellingpro.id', '=', 'order_detail.product_id');
        // })
        // ->where('brands.status', 1)
        // ->orderBy('selling_price', 'DESC')
        // ->whereBetween('order_detail.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        // ->where('order_detail.created_at', '>=', explode('_', $date)[0])
        // ->where('order_detail.created_at', '<=', explode('_', $date)[1])
        // // ->where( DB::raw('YEAR(order_detail.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        // //->limit('5')
        // ->first();
        $sellingbrand = OrderDetail::
        select('brands.name as selling_name', DB::raw('sum(order_detail.quantity) as selling_qty'), DB::raw('sum(order_detail.unit_price) as selling_price'))
        ->leftJoin('products as sellingpro', function($join) {
            $join->on('sellingpro.id', '=', 'order_detail.product_id');
        })
        ->leftJoin('brands', function($join) {
            $join->on('brands.id', '=', 'sellingpro.brands');
        })
        ->leftJoin('order', function($join) {
            $join->on('order.id', '=', 'order_detail.order_id');
        })
        ->where('order.status', '!=', 8)
        ->where('order.status', '!=', 7)
        ->where('order.status', '!=', 6)
        ->where('order.status', '!=', 5)
        ->groupBy('brands.id')
        ->where('brands.status', 1)
        ->orderBy('selling_price', 'DESC')
        ->when($filter, function ($q) use ($filter, $date) {
            return $q->whereBetween('order_detail.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
        })
        ->where('order_detail.created_at', '>=', explode('_', $date)[0])
        ->where('order_detail.created_at', '<=', explode('_', $date)[1])
        // ->limit('15')
        ->first();

        // reviews
        $reviews = ProductReview::
        select('product_review.product_sku as high_sku', DB::raw('sum(product_review.rating) as high_review'))
        ->groupBy('product_sku')
        ->orderByRaw('COUNT(*) DESC')
        ->whereBetween('product_review.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('product_review.created_at', '>=', explode('_', $date)[0])
        ->where('product_review.created_at', '<=', explode('_', $date)[1])
        // ->where( DB::raw('YEAR(product_review.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        ->first();

        $totalrev = ProductReview::distinct()
        ->whereBetween('product_review.created_at', [explode('_', $date)[0], explode('_', $date)[1]])
        ->where('product_review.created_at', '>=', explode('_', $date)[0])
        ->where('product_review.created_at', '<=', explode('_', $date)[1])
        ->count('product_sku');

        $currentmonth = date('m');
        if(date('Y', strtotime(explode('_', $date )[1]) < date('Y'))) {
            $currentmonth = 12;
        }

        // Products
        $selects = [
            'products.name', 'products.sku', 
            DB::raw('sum(order_detail.unit_price) as t_revenue'), 
            'products.sale_price as selling_pro_price', 
            DB::raw('sum(order_detail.quantity) as s_qty'), 
            DB::raw('CONVERT(products.quantity, char) as r_qty'),
            'brands.name as brand',
            DB::raw('product_media.image as image'),
            'products.id as products_id',
            DB::raw('(SELECT productcategories.name FROM productcategories INNER JOIN product_categories ON product_categories.category_id = productcategories.id WHERE product_categories.product_id = products.id AND productcategories.parent_id IS NOT NULL ORDER BY productcategories.sort ASC LIMIT 1) AS last_category')
        ];

        // Month wise calculation work
        // for ($i=0; $i < $currentmonth; $i++) { 
        //     $selects[] =  DB::raw('CONVERT(SUM(IF(MONTH(order_detail.created_at) = '.($i+1).', order_detail.quantity, 0)), char) as '.($i+1).'_qty');
        // }
        // if ($currentmonth < 12) {
        //     for ($i=$currentmonth; $i < 12; $i++) { 
        //         $selects[] =  DB::raw('round(sum(order_detail.quantity) / '.$currentmonth.') as '.($i+1).'_qty');
        //     }
        // }

        $topsellingproduct = OrderDetail::select($selects)
            ->groupBy('products.id')
            ->where('total', '>', 1)
            ->leftJoin('products', function($join) {
                $join->on('order_detail.product_id', '=', 'products.id');
            })
            // ->leftJoin('product_categories', function($join) {
            //     $join->on('products.id', '=', 'product_categories.product_id');
            // })
            // ->leftJoin('productcategories', function($join) {
            //     $join->on('product_categories.category_id', '=', 'productcategories.id');
            // })
            ->leftJoin('product_media', function($join) {
                $join->on('products.feature_image', '=', 'product_media.id');
            })
            ->leftJoin('brands', function($join) {
                $join->on('products.brands', '=', 'brands.id');
            })
            ->leftJoin('order', function($join) {
                $join->on('order.id', '=', 'order_detail.order_id');
            })
            ->when($filter, function ($q) use ($filter, $date) {
                return $q->whereBetween('order_detail.created_at', [explode('_', $date)[0], explode('_', $date)[1]]);
            })
            ->where('order_detail.created_at', '>=', explode('_', $date)[0])
            ->where('order_detail.created_at', '<=', explode('_', $date)[1])
            ->where('order.status', '!=', 8)
            ->where('order.status', '!=', 7)
            ->where('order.status', '!=', 6)
            ->where('order.status', '!=', 5)
            ->orderBy('s_qty', 'desc')
            ->having('s_qty', '>=', 1)
            ->limit(50)
            ->get();


        // // brands
        // $selects = [
        //     'brands.name as name',
        //     DB::raw('CONVERT(brands.clicks, char) as clicks'),
        //     DB::raw('CONVERT(brands.status, char) as status'),
        //     DB::raw('ROUND(sum(order_detail.total)) as sales'),
        //     'brands.id as brands_id',
        //     DB::raw('media.small as image'),
        // ];

        // for ($i=0; $i < $currentmonth; $i++) { 
        //     $selects[] =  DB::raw('CONVERT(SUM(IF(MONTH(order_detail.created_at) = '.($i+1).', order_detail.total, 0)), char) as '.($i+1).'_totalamount');
        // }
        // if ($currentmonth < 12) {
        //     for ($i=$currentmonth; $i < 12; $i++) { 
        //         $selects[] =  DB::raw('round(sum(order_detail.total) / '.$currentmonth.') as '.($i+1).'_totalamount');
        //     }
        // }

        // $topsellingbrands = Brand::
        // select($selects)
        // ->groupBy('brands.id')
        // ->where('total', '>', 1)
        // ->leftJoin('products as sellingpro', function($join) {
        //     $join->on('brands.id', '=', 'sellingpro.brands');
        // })
        // ->leftJoin('order_detail', function($join) {
        //     $join->on('sellingpro.id', '=', 'order_detail.product_id');
        // })
        // ->leftJoin('media', function($join) {
        //     $join->on('brands.brand_image_media', '=', 'media.id');
        // })
        // ->where('brands.status', 1)
        // ->orderBy('sales', 'DESC')
        // ->where( DB::raw('YEAR(order_detail.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        // ->limit(10)
        // ->get();


        // // Category
        // $selects = [
        //     'productcategories.name as name',
        //     DB::raw('CONVERT(productcategories.clicks, char) as clicks'),
        //     'productcategories.slug as slug',
        //     'productcategories.id as cat_id',
        //     DB::raw('CONVERT(productcategories.status, char) as status'),
        //     DB::raw('ROUND(sum(order_detail.total)) as sales'),
        //     DB::raw('media.small as image'),
        // ];

        // for ($i=0; $i < $currentmonth; $i++) { 
        //     $selects[] =  DB::raw('CONVERT(SUM(IF(MONTH(order_detail.created_at) = '.($i+1).', order_detail.total, 0)), char) as '.($i+1).'_totalamount');
        // }
        // if ($currentmonth < 12) {
        //     for ($i=$currentmonth; $i < 12; $i++) { 
        //         $selects[] =  DB::raw('round(sum(order_detail.total) / '.$currentmonth.') as '.($i+1).'_totalamount');
        //     }
        // }

        // $topsellingcats = Productcategory::
        // select($selects)
        // ->leftJoin('product_categories', function($join) {
        //     $join->on('product_categories.category_id', '=', 'productcategories.id');
        // })
        // ->leftJoin('products as sellingpro', function($join) {
        //     $join->on('product_categories.product_id', '=', 'sellingpro.id');
        // })
        // ->leftJoin('order_detail', function($join) {
        //     $join->on('order_detail.product_id', '=', 'sellingpro.id');
        // })
        // ->leftJoin('media', function($join) {
        //     $join->on('productcategories.web_image_media', '=', 'media.id');
        // })
        // ->groupBy('productcategories.id')
        // ->where('productcategories.menu', 1)
        // ->where('productcategories.status', 1)
        // ->where( DB::raw('YEAR(order_detail.created_at)'), '=', date('Y', strtotime(explode('_', $date )[1])) )
        // ->limit(10)
        // ->get();

        $response = [
            'sellingproduct' => $sellingproduct, 
            'sellingcategory' => $sellingcategory, 
            'sellingbrand' => $sellingbrand, 
            'reviews' => $reviews, 
            'total_reviews' => $totalrev, 
            'monthlyProData' => $topsellingproduct, 
            'pro_counts' => $topsellingproduct->count()
            // 'monthlyBrandData' => $topsellingbrands, 
            // 'monthlyCatData' => $topsellingcats
        ];
        $responsejson=json_encode($response);
        $data=gzencode($responsejson,9);
        return response($data)->withHeaders([
            'Content-type' => 'application/json; charset=utf-8',
            'Content-Length'=> strlen($data),
            'Content-Encoding' => 'gzip'
        ]);
    }
}