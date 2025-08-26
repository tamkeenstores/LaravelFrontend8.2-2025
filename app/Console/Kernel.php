<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\GeneralEmailJobs;

class Kernel extends ConsoleKernel
{
    
    protected $commands = [
        Commands\CheckProductQuantity::class,
        Commands\OrderProcessMail::class,
        Commands\NotificationSend::class,
        Commands\AffandLoyaltyStatus::class,
        Commands\CartAbandonedEmail::class,
        Commands\ErpOrderEmail::class,
        Commands\ErpOrderYesterdayMail::class,
        Commands\OrderInvoicesMail::class,
        Commands\OrderInvoicesExcelMail::class
    ];
    
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        
        // ERP
        // $emaildata = GeneralEmailJobs::where('type', 'erp-email-status')->with('emailtimes')->where('status', 1)->first();
        // if($emaildata) {
        //     if(isset($emaildata->emailtimes)) {
        //         foreach ($emaildata->emailtimes as $key => $value) {
        //             $schedule->command('app:erp-order-email')->dailyAt($value->end_time);
        //         }
        //     }
        // }
        
        // Product Stock Update
        // $emaildatapro = GeneralEmailJobs::where('type', 'product-stock-update')->with('emailtimes')->where('status', 1)->first();
        // if($emaildatapro) {
        //     if(isset($emaildatapro->emailtimes)) {
        //         foreach ($emaildatapro->emailtimes as $key => $value) {
        //             $schedule->command('app:product-stock-update')->dailyAt($value->end_time);
        //         }
        //     }
        // }
        
        // // Order Invoice Mail
        // $emaildataord = GeneralEmailJobs::where('type', 'order-invoice-email')->with('emailtimes')->where('status', 1)->first();
        // // $schedule->command('app:order-invoices-mail')->everyMinute();
        // if($emaildataord) {
        //     if(isset($emaildataord->emailtimes)) {
        //         foreach ($emaildataord->emailtimes as $key => $value) {
        //             $schedule->command('app:order-invoices-mail')->dailyAt($value->end_time);
        //         }
        //     }
        // }


        // $schedule->command('app:cart-abandoned-email')->everyMinute();
        // $schedule->command('product:check-quantity')->dailyAt('09:00');
        // $schedule->command('order:processmail')->everyMinute();
        // $schedule->command('app:notification')->everyMinute();
        // $schedule->command('app:affand-loyalty-status')->everyMinute();
        // $schedule->command('app:erp-order-email')->dailyAt('12:00');
        // $schedule->command('app:erp-order-yesterday-mail')->dailyAt('00:00');
        // // $schedule->command('app:order-invoices-mail')->dailyAt('07:59');
        // $schedule->command('app:order-invoices-excel-mail')->dailyAt('07:59');
        // $schedule->command('app:order-invoices-excel-mail')->dailyAt('10:59');
        // $schedule->command('app:order-invoices-excel-mail')->everyMinute();
        // $schedule->command('app:order-invoices-mail')->everyMinute();
        // $schedule->command('app:product-stock-update')->dailyAt('10:20');
        
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application. 
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        // $this->commands([
        //     \App\Console\Commands\CheckProductQuantity::class,
        // ]);

        require base_path('routes/console.php');
    }
}
