<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Mail;
use Carbon\Carbon;
use App\Models\GeneralEmailJobs;
use DateTime;
use App\Exports\OrderInvoiceExcelEmail;
use Maatwebsite\Excel\Facades\Excel;
use DateTimeZone;

class OrderInvoicesExcelMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order-invoices-excel-mail';

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
        $nowtodaydate = Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d H:i:s');
        
        $nowtodaydateolny = Carbon::now(new DateTimeZone('Asia/Riyadh'))->format('Y-m-d');
        // print_r($nowtodaydate);
        $todaysixam = new DateTime("today 08:00:01");
        $todaysixamdate = $todaysixam->format('Y-m-d H:i:s');
        
        $todaytwopm = new DateTime("today 11:00:00");
        $todaytwopmdate = $todaytwopm->format('Y-m-d H:i:s');
        
        
        

        // $ordersData = Order::whereIn('status', [0, 1, 2])->whereDate('created_at', $nowtodaydate)->pluck('id')->toArray();
        
        if($nowtodaydate >= $todaysixamdate && $nowtodaydate <= $todaytwopmdate){
            $orders = Order::with('details','UserDetail','Address.stateData')
            ->whereIn('status', [0,1,2])
            // ->whereIn('id', $ordersData)
            ->whereBetween('created_at', [$todaysixamdate, $todaytwopmdate])
            // ->where('created_at', $nowtodaydate)
            ->get();
            // print_r($orders);die;
            if($orders){
                $export = new OrderInvoiceExcelEmail($orders);

                $fileName = "OrderData-8-11" . $nowtodaydate . ".csv";
    
                Excel::store($export, $fileName);
                
                $toemails = ['kadel@tamkeen-ksa.com','ahmad.milhem@tamkeen-ksa.com','b.gharbi@tamkeen-ksa.com','shassan@tamkeen-ksa.com','amr.ali@tamkeen-ksa.com'];
                // $toemails = ['sameeriqbal1200@gmail.com'];
                // $bccemails = ['usman@tamkeen-ksa.com'];
                $bccemails = ['mohammed.saied@tamkeen-ksa.com', 'fawad@tamkeen-ksa.com', 'usman@tamkeen-ksa.com', 'ali.hassan@tamkeen-ksa.com', 'qaiser@tamkeen-ksa.com','sameeriqbal1200@gmail.com'];
                try {
                    $success = true;
                    $message = 'email send';
                    Mail::send('email.exportorderemail-8-11', ['todaysixamdate' => $todaysixamdate, 'todaytwopmdate' => $todaytwopmdate], function($message) use ($fileName,$nowtodaydateolny,$toemails,$bccemails){
                        //   $message->to('qaiserabbas613@gmail.com')->subject('Tamkeen Stores Order Email '.$nowtodaydateolny.' 08:00 AM to 11:00 PM');
                        $message->to($toemails);
                        $message->cc($bccemails);
                        $message->subject('Tamkeen Stores Order Email '.$nowtodaydateolny.' 08:00 AM to 11:00 AM');
                        $message->attach(storage_path('app/' . $fileName));
                    });
                    unlink(storage_path('app/' . $fileName));
                    return response()->json(['success' => $success, 'message' => $message]);
                } catch (Exception $e) {
                    $success = false;
                    $message = 'email not send';
                    return response()->json(['success' => $success, 'message' => $message]);
                }
            }
        }
        
        
        // Yesterday
        $yesterday = new DateTime("yesterday 11:00:00");
        $yesterdaydate = $yesterday->format('Y-m-d H:i:s');
        $yesterdaydateonly = $yesterday->format('Y-m-d');
        
        
        $todaysixamold = new DateTime("today 08:00:00");
        $todaytwopmdateold = $todaysixamold->format('Y-m-d H:i:s');
        
        if($nowtodaydate <= $todaytwopmdateold && $nowtodaydate >= $yesterdaydate){
            $orders = Order::with('details','UserDetail','Address.stateData')->whereIn('status', [0,1,2])
            ->whereBetween('created_at', [$yesterday, $todaytwopmdateold])
            ->get();
            if($orders){
                
                $export = new OrderInvoiceExcelEmail($orders);

                $fileName = "OrderData-11-8" . $yesterdaydateonly . ".csv";
    
                Excel::store($export, $fileName);
                
                $toemails = ['kadel@tamkeen-ksa.com','ahmad.milhem@tamkeen-ksa.com','b.gharbi@tamkeen-ksa.com','shassan@tamkeen-ksa.com','amr.ali@tamkeen-ksa.com'];
                // $toemails = ['sameeriqbal1200@gmail.com'];
                // $bccemails = ['usman@tamkeen-ksa.com'];
                $bccemails = ['mohammed.saied@tamkeen-ksa.com', 'fawad@tamkeen-ksa.com', 'usman@tamkeen-ksa.com', 'ali.hassan@tamkeen-ksa.com', 'qaiser@tamkeen-ksa.com','sameeriqbal1200@gmail.com'];
                try {
                    $success = true;
                    $message = 'email send';
                    Mail::send('email.exportorderemail-11-8', [], function($message) use ($fileName,$yesterdaydateonly,$toemails,$bccemails){
                        //  $message->to('qaiserabbas613@gmail.com')->subject('Tamkeen Stores Order Email '. $yesterdaydateonly.' 11:00 PM to 06:00 AM');
                        $message->to($toemails);
                        $message->cc($bccemails);
                        $message->subject('Tamkeen Stores Order Email '. $yesterdaydateonly.'11:00 AM to 08:00 AM');
                        $message->attach(storage_path('app/' . $fileName));
                    });
                    unlink(storage_path('app/' . $fileName));
                    return response()->json(['success' => $success, 'message' => $message]);
                } catch (Exception $e) {
                    $success = false;
                    $message = 'email not send';
                    return response()->json(['success' => $success, 'message' => $message]);
                }
            }
        }
        
        die();
    }
}
