<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\NotificationToken;
use App\Helper\NotificationHelper;
use App\Models\Product;
use App\Models\Productcategory;
use App\Models\Brand;

class NotificationSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification';

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
        $notifications = Notification::where('date', date('Y-m-d H:i'))->where('for_app', 1)->where('for_web', 1)->get();
        // print_r($notifications);die;
        
        $totalblogs = NotificationToken::count();
        $totalxml = $totalblogs / 1000;
        for ($i=0; $i < $totalxml; $i++) {
            // $products = NotificationToken::select('token')->orderBy('id', 'asc')->paginate('1000', ['*'], 'page', ($i+1));
            // $tokenData = $products->pluck('token')->toArray();
            foreach($notifications as $notification){
                $product = '';
                $brand = '';
                $cat = '';
                // if($notification->type == 1) {
                //     $type = 'Products';
                //     $product = Product::where('id', $notification->product_id)->first();
                // }
                // elseif($notification->type == 2) {
                //     $type = 'Brands';
                //     $brand = Brand::where('id', $notification->brand_id)->first();
                // }
                // elseif($notification->type == 3) {
                //     $type = 'Product Categories';
                //     $cat = Productcategory::where('id', $notification->category_id)->first();
                // }
                // else{
                    $type = '';
                // }
                
                // if($notification->type == 1) {
                //     $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type, isset($product->slug) ? $product->slug : null);
                // }
                // elseif($notification->type == 2) {
                //     $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type, isset($brand->slug) ? $brand->slug : null);
                // }
                // elseif($notification->type == 3) {
                //     $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type, isset($cat->slug) ? $cat->slug : null);
                // }
                // else {
                //     $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, $notification->for_web == 1 && $notification->for_app == 0 ? $notification->link : '', $type);
                // }
                
                
                if($notification->for_web == 1 && $notification->for_app == 1) {
                    // for web
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, 'https://tamkeenstores.com.sa/ar/' . $notification->link, $type);
                    
                    // for app
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', '!=', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $notification->link, $type);
                    
                }
                elseif($request->for_web == 1) {
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', 'Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray();   
                    $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, 'https://tamkeenstores.com.sa/ar/' . $notification->link, $type);
                }
                elseif($request->for_app == 1) {
                    $tokenData = NotificationToken::orderBy('id', 'asc')->where('device', '!=','Website')->paginate('1000', ['*'], 'page', ($i+1))->pluck('token')->toArray(); 
                    $data = NotificationHelper::global_notification($tokenData, $notification->title_arabic, $notification->message_arabic, $notification->image, 'https://deeplink-app.tamkeenstores.com.sa/ar/' . $notification->link, $type);
                }
            }
        }
    }
}
