<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\EmailTemplate;
use Mail;
use Carbon\Carbon;
use App\Models\GeneralEmailJobs;
use App\Helper\NotificationHelper;

class OrderProcessMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:processmail';

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
        $emaildata = GeneralEmailJobs::where('type', 'pending-order-email')->where('status', 1)->with('emailtimes')->first();
        if($emaildata) {
            $days = isset($emaildata->hours) ? $emaildata->hours : 3;
            
            $threeHoursAgo = Carbon::now()->subHours($days);
            $currentDate = Carbon::now();
            $orders = Order::
            where('status', 8)
            ->where('created_at', '<=' ,$threeHoursAgo)
            ->where('created_at', '>=', Carbon::now()->subDays(2))
            ->with('Address', 'UserDetail')
            ->whereNull('pendingemailstatus')
            // ->whereMonth('created_at', $currentDate->month)
            ->get(['id', 'order_no', 'customer_id', 'shipping_id', 'status', 'paymentmethod', 'lang', 'pendingemailstatus', 'pending_email_count', 'created_at']);
           
            $todata = isset($emaildata->to) ? explode(',' ,$emaildata->to) : [];
            // $ccdata = isset($emaildata->cc) ? explode(',' ,$emaildata->cc) : ['usman@tamkeen-ksa.com'];
            $bccdata = isset($emaildata->bcc) ? explode(',' ,$emaildata->bcc) : [];
            $fromdata = isset($emaildata->from) ? $emaildata->from : ['adminpanel@tamkeenstores.com.sa'];
           
            foreach($orders as $order) {
                // $lang = $order->lang != null ? $order->lang : 'en';
                $lang = 'ar';
                if($order->Address && $order->Address->phone_number != null) {
                    $response = NotificationHelper::whatsappmessageImage('+966' . str_replace('+966', '', $order->Address->phone_number),'pending_orders',$lang, 'https://images.tamkeenstores.com.sa/assets/new-media/Whatsapp_newPromo_17June.jpg');   
                }
                if($order->UserDetail && $order->UserDetail->email != null) {
                    try {
                        Mail::send('email.pending-order-email-template', ['order' => $order], function ($message) use ($todata, $bccdata, $fromdata, $order) {
                            $message->to($order->UserDetail->email)
                            // ->cc($ccdata)
                            // ->bcc($bccdata)
                            ->from('sales@tamkeenstores.com.sa')->subject('Pending Order');
                        }); 
                      } catch(\Exception $e) {
                            // echo "erro";
                    }  
                }
                
                $order->pendingemailstatus = 1;
                $order->pending_email_count = $order->pending_email_count + 1;
                $order->pending_whatsapp_count = $order->pending_whatsapp_count + 1;
                $order->update();
            }
        }
    }
}
