<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportErpOrder;
use App\Models\GeneralEmailJobs;
use Mail;

class ErpOrderYesterdayMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:erp-order-yesterday-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emaildata = GeneralEmailJobs::where('type', 'erp-order-email')->first();
        $days = $emaildata->days;
        $currentDate = date('Y-m-d');
        $priorDate = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));
        
        $LastDayAgo = Carbon::now()->subHours(24);
        $currenttime = Carbon::now();
        
        $order = Order::whereNotIn('status',[5,7,8])
        ->whereBetween('created_at', [$priorDate, $currentDate])
        ->whereBetween('created_at', [$LastDayAgo, $currenttime])
        ->orderBy('created_at', 'ASC')
        ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
        ->get();
        
        $orderfetchcount = Order::whereNotIn('status',['5','7','8'])
        ->whereBetween('created_at', [$priorDate, $currentDate])
        ->whereBetween('created_at', [$LastDayAgo, $currenttime])
        ->where('erp_status', 1)
        ->orderBy('created_at', 'ASC')
        ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
        ->count();
        
        $orderscount = $order->count();
        
        $todata = explode(',' ,$emaildata->to);
        $ccdata = explode(',' ,$emaildata->cc);
        $bccdata = explode(',' ,$emaildata->bcc);
        $fromdata = $emaildata->from;
        
        if ($order->count() > 0) {
            
            $export = new ExportErpOrder(['fromdate' => $LastDayAgo,'todate' => $currenttime]);

            $fileName = 'erp_orders.csv';

            Excel::store($export, $fileName);

            Mail::send('email.erpfetchorder', ['order' => $order, 'currenttime' => $currenttime, 'orderscount' => $orderscount, 'orderfetchcount' => $orderfetchcount], function ($message) use ($fileName) {
                $message->to($todata)
                    ->cc($ccdata)
                    ->bcc($bccdata)
                    ->from($fromdata)->subject('ERP Fetch Order Email')->attach(storage_path('app/' . $fileName));
            });

            // Delete the file after sending email
            unlink(storage_path('app/' . $fileName));
        }
    }
}
