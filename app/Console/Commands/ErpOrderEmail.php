<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportErpOrder;
use App\Models\GeneralEmailJobs;
use Mail;

class ErpOrderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:erp-order-email';

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
        $emaildata = GeneralEmailJobs::where('type', 'erp-email-status')->with('emailtimes')->where('status', 1)->first();
        if($emaildata) {
            if(isset($emaildata->emailtimes)) {
                foreach ($emaildata->emailtimes as $key => $value) {
                    $endtime = $value->end_time;
                    if($endtime == date('H:i')) {
                        $days = $value->days ? $value->days : 0;
                        $currentDate = date('Y-m-d');
                        $minuDays = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));

                        $from = $minuDays . ' ' . $value->start_time . ':00';
                        $to = $minuDays . ' ' . $value->end_time . ':00';
                        $currenttime = Carbon::now();

                        $order = Order::whereNotIn('status',['5','7','8'])
                        ->whereDate('created_at', $minuDays)
                        ->whereTime('created_at', '>=', $value->start_time)
                        ->whereTime('created_at', '<=', $value->end_time)
                        ->orderBy('created_at', 'ASC')
                        ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
                        ->get();

                        // $orderfetchcount = Order::whereNotIn('status',['5','7','8'])
                        // ->whereDate('created_at', $minuDays)
                        // ->whereTime('created_at', '>=', $value->start_time)
                        // ->whereTime('created_at', '<=', $value->end_time)
                        // ->where('erp_status', 1)
                        // ->orderBy('created_at', 'ASC')
                        // ->select(['id','order_no','erp_status','erp_fetch_date','erp_fetch_time','created_at','status'])
                        // ->count();
                        
                        $orderfetchcount = $order->where('erp_status', 1)->count();

                        $orderscount = $order->count();
            
                        $todata = isset($emaildata->to) ? explode(',' ,$emaildata->to) : ['mubashirasif1@gmail.com'];
                        $ccdata = isset($emaildata->cc) ? explode(',' ,$emaildata->cc) : [];
                        $bccdata = isset($emaildata->bcc) ? explode(',' ,$emaildata->bcc) : [];
                        $fromdata = isset($emaildata->from) ? $emaildata->from : ['adminpanel@tamkeenstores.com.sa'];

                        if ($order->count() > 0) {
                            $export = new ExportErpOrder(['fromdate' => $from,'todate' => $to]);
                            $fileName = 'erp_orders.csv';
                            Excel::store($export, $fileName);
                            Mail::send('email.erpfetchorder', ['order' => $order, 'currenttime' => $currenttime, 'orderscount' => $orderscount, 'orderfetchcount' => $orderfetchcount], function ($message) use ($fileName, $todata, $ccdata, $bccdata, $fromdata) {
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
            }
        }
    }
}
