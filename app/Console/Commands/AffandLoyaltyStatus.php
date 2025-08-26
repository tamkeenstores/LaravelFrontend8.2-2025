<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\GeneralEmailJobs;

class AffandLoyaltyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:affand-loyalty-status';

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
        $emaildata = GeneralEmailJobs::where('type', 'aff-loyalty-update')->where('status', 1)->with('emailtimes')->first();
        if($emaildata) {
            $hours = isset($emaildata->hours) ? $emaildata->hours : 4;
            $currentDate = Carbon::now();
            
            
            // $FourHoursAgo = Carbon::now()->subHours($hours);
            $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
            
            
            $orders = Order::where('status', 4)
            ->whereDate('created_at', '<=', $sevenDaysAgo)
            ->with(['usercommission' => function ($query) {
                $query->where('status', 0);
            }, 'orderloyaltypoints' => function ($query) {
                $query->where('status', 0);
            }])
           ->get();
           
           
           
          foreach($orders as $order) {
            foreach($order->usercommission as $commissiondata) {
                $commissiondata->status = 1;
                $commissiondata->update();
            }
            foreach($order->orderloyaltypoints as $loyaltydata) {
                $loyaltydata->status = 1;
                $loyaltydata->update();
            }
          }
        }
    }
}
