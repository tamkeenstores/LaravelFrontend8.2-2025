<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AbandonedCart;
use App\Models\GeneralEmailJobs;
use Mail;
use Carbon\Carbon;
use App\Helper\NotificationHelper;

class CartAbandonedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cart-abandoned-email';

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
        $emaildata = GeneralEmailJobs::where('type', 'abandoned-cart-email')->first();
       
        if($emaildata->status == 1) { 
            $days = isset($emaildata->hours) ? $emaildata->hours : 2;
            $twelveHoursAgo = Carbon::now()->subHours($days);
            $cartdata = AbandonedCart::
            with('userData')
            ->where('created_at', '<=', $twelveHoursAgo)
            ->where('firstemail', 0)
            // ->limit(1)
            ->get();
            
            $ccdata = explode(',' ,$emaildata->cc);
            $bccdata = explode(',' ,$emaildata->bcc);
            $fromdata = isset($emaildata->from) ? $emaildata->from : ['sales@tamkeenstores.com.sa'];
       
            foreach($cartdata as $cart) {
                // $lang = $cart->userData->lang != null ? $cart->userData->lang : 'en';
                $lang = 'ar';
                if($cart->userData && $cart->userData->phone_number != null) {
                    $response = NotificationHelper::whatsappmessageContent('+966' . str_replace('+966', '', $cart->userData->phone_number),'cart_abandonment',$lang, 'https://images.tamkeenstores.com.sa/assets/new-media/Whatsapp_newPromo_17June.jpg');  
                } 
                
                if($cart->userData && $cart->userData->email) {
                    try {
                        Mail::send('email.abandoned-template', ['cartdata' => $cart], function ($message) use ($cart, $ccdata, $bccdata, $fromdata) {
                            $message->to($cart->userData->email)
                            // ->bcc($bccdata)
                            ->from($fromdata)
                            ->subject('عرض الضريبة علينا من تمكين');
                        });
                      } catch(\Exception $e) {
                            // echo "erro";
                    } 
                }
                
                $cart->firstemail = 1;
                $cart->update();
            }
       }
    }
}
