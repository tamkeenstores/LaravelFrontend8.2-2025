<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

// use App\Exports\ProductCsvEmail;
// use App\Models\Product;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Models\GeneralEmailJobs;
// use Mail;
// use Carbon\Carbon;

class ProductStockUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-stock-update';
    protected $description = 'Backup the MySQL database using mysqldump';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {   
        
        // $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        // $path = storage_path("app/backups/{$filename}");
        // \Log::info("Backup file created: {$path}");

        // $user = env('DB_USERNAME');
        // $pass = env('DB_PASSWORD');
        // $host = env('DB_HOST');
        // $db   = env('DB_DATABASE');
        // $command = "mysqldump -u{$user} -p{$pass} -h{$host} {$db} > {$path}";

        // $this->info("Running backup command...");
        // $result = null;
        // system($command, $result);

        // if ($result === 0) {
        //     \Log::info('Running mysqldump backup command');
        //     $this->info("Backup saved to: {$path}");
        // } else {
        //     $this->error("Backup failed.");
        //     \Log::info("Backup file created: {$path}");
        // }
        
        // $emaildatapro = GeneralEmailJobs::where('type', 'product-stock-update')->with('emailtimes')->where('status', 1)->first();
        // if($emaildatapro) {
        //     if(isset($emaildatapro->emailtimes)) {
        //         foreach ($emaildatapro->emailtimes as $key => $value) {
        //             $days = isset($value->days) ?  $value->days : 0;
        //         }
        //     }
        //     $todata = isset($emaildatapro->to) ? explode(',' ,$emaildatapro->to) : ['usman@tamkeen-ksa.com'];
        //     $ccdata = isset($emaildatapro->cc) ? explode(',' ,$emaildatapro->cc) : [];
        //     $bccdata = isset($emaildatapro->bcc) ? explode(',' ,$emaildatapro->bcc) : [];
        //     $fromdata = isset($emaildatapro->from) ? $emaildatapro->from : ['adminpanel@tamkeenstores.com.sa'];
        //     // print_r($emaildata->cc);die;
        // }
        
        // $currentDate = date('Y-m-d');
        // $priorDate = date('Y-m-d', strtotime("-$days days", strtotime($currentDate)));
        // $currenttime = Carbon::now();
        
        
        // $products = Product::when($days !== 0, function ($q) use ($priorDate, $currentDate) {
        //     return $q->whereBetween('created_at', [$priorDate, $currentDate]);
        // })->get();
        
        // // these are the headers for the csv file.
        // $headers = array(
        //     'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        //     'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        //     'Content-Disposition' => 'attachment; filename=download.csv',
        //     'Expires' => '0',
        //     'Pragma' => 'public',
        // );

        // //creating the download file
        // $export = new ProductCsvEmail($products);

        // // $fileName = "'Stock-Update_' . date('Y-m-d') . '.csv'";
        // $fileName = 'Stock-Update_' . date('Y-m-d') . '.csv';
        // // print_r($fileName);die; 

        // Excel::store($export, $fileName);
    
        // Mail::send('email.stockskuupdate', ['product' => $products], function ($message) use ($fileName, $todata, $ccdata, $bccdata, $fromdata) {
        //     $message->to($todata)->cc($ccdata)->bcc($bccdata)->from($fromdata)->subject('Product Stock Update')->attach(storage_path('app/' . $fileName));
        // });

        // unlink(storage_path('app/' . $fileName));
    }
}
