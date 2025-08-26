<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Mail;
use Carbon\Carbon;
use App\Models\GeneralEmailJobs;
use DateTime;
use App\Exports\OrderInvoiceExcelEmail;
use DateTimeZone;
use PDF;
ini_set('max_execution_time', '500');
ini_set("pcre.backtrack_limit", "5000000");

class OrderInvoicesMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:order-invoices-mail';

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
        $emaildata = GeneralEmailJobs::where('type', 'order-invoice-email')->where('status', 1)->with('emailtimes')->first();
        if($emaildata) {
            $days = isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->days : 1;
            $currentDate = date('Y-m-d');
            $priorDate = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));
            // print_r($priorDate);die;
            
            
            $today = new DateTime("today " . isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->end_time . ':00' : '08:00:00');
            $todaydate = $today->format('Y-m-d H:i:s');
            // print_r($todaydate);die;
            
            // Yesterday
            $yesterday = isset($emaildata->emailtimes[0]) ? $priorDate . ' ' .  $emaildata->emailtimes[0]->start_time . ':00' :  new Datetime('yesterday 08:00:00');
            $yesterdaydate = isset($emaildata->emailtimes[0]) ? $yesterday : $yesterday->format('Y-m-d H:i:s');
            
    
            $orders = Order::whereIn('status', [0, 2])->whereBetween('created_at', [$yesterdaydate, $todaydate])->pluck('id')->toArray();
            // print_r($orders);die;
    
            $thankyous = Order::whereIn('id', $orders)->get();
            $html = (string)view('pdf-backend', compact('thankyous'));
            $pdf = PDF::loadHtml($html);
    
    
            // Today
            $emailtoday = new DateTime("today " . isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->end_time . ':00' : '08:00:00');
            $emailtodaydate = $emailtoday->format('d M, Y');
            
    
            // Yesterday
            $emailyesterday = isset($emaildata->emailtimes[0]) ? new Datetime($priorDate . ' ' .  $emaildata->emailtimes[0]->start_time . ':00') :  new Datetime('yesterday 08:00:00');
            $emailyesterdaydate = $emailyesterday->format('d M, Y');
            
    
            // Today
            $filenametoday = new DateTime("today " . isset($emaildata->emailtimes[0]) ? $emaildata->emailtimes[0]->end_time . ':00' : '08:00:00');
            $filenametodaydate = $filenametoday->format('dM_');
            
    
            // Yesterday
            $filenameyesterday = isset($emaildata->emailtimes[0]) ? new Datetime($priorDate . ' ' .  $emaildata->emailtimes[0]->start_time . ':00') :  new Datetime('yesterday 08:00:00');
            $filenameyesterdaydate = $filenameyesterday->format('dM_Y');
            $bccemails = ['mohammed.saied@tamkeen-ksa.com', 'fawad@tamkeen-ksa.com', 'usman@tamkeen-ksa.com', 'ali.hassan@tamkeen-ksa.com', 'g.elzahaby@tamkeen-ksa.com', 'sameeriqbal1200@gmail.com'];
            // $todata = isset($emaildata->to) ? explode(',' ,$emaildata->to) : ['usman@tamkeen-ksa.com'];
            // $ccdata = isset($emaildata->cc) ? explode(',' ,$emaildata->cc) : ['usman@tamkeen-ksa.com'];
             $todata = ['sameeriqbal1200@gmail.com'];
             $ccdata = ['sameeriqbal1200@gmail.com'];
            // $bccdata = isset($emaildata->bcc) ? explode(',' ,$emaildata->bcc) : [];
            $fromdata = isset($emaildata->from) ? $emaildata->from : ['sales@tamkeenstores.com.sa'];
                        
            if(count($orders) >= 1) {
                $success = true;
                $message = 'email send';
                Mail::send('email.invoices', $orders, function($message) use ($pdf, $emailyesterdaydate, $emailtodaydate, $filenameyesterdaydate, $filenametodaydate, $fromdata, $bccemails, $todata, $ccdata){
                    $message->to($todata);
                    // $message->cc($ccdata);
                    $message->from($fromdata);
                    $message->subject('Tamkeen Stores Invoices ' . $emailyesterdaydate . ' to ' . $emailtodaydate . '.');
                    $message->attachData($pdf->output(),'Invoices_'.$filenameyesterdaydate.$filenametodaydate.'.pdf');
                });
                return response()->json(['success' => $success, 'message' => $message]);
            }
            else {
                $success = false;
                $message = 'email not send';
                return response()->json(['success' => $success, 'message' => $message]);
            }
                // die('yes');
                
            // } catch (Exception $e) {
            //     // die('no');
            //     $success = false;
            //     $message = 'email not send';
            //     return response()->json(['success' => $success, 'message' => $message]);
            // }
        }
    }
}