<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\GeneralSetting;
use Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductEmailExport;

class CheckProductQuantity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:check-quantity';

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
        // \Log::info(date('Y-m-d H:i:00').'proquantitydata');
        $setting = GeneralSetting::with('productsetting:generalsetting_id,low_stock_status,low_stock_quantity,low_stock_email,low_stock_category_id')->first();
        // \Log::info(explode(',' ,$setting->productsetting->low_stock_category_id));
        if($setting->productsetting->low_stock_status == 1) {
            
        $quantity = $setting->productsetting->low_stock_quantity;
        $email = explode(',' ,$setting->productsetting->low_stock_email);
        $category = $setting->productsetting->low_stock_category_id ? explode(',' ,$setting->productsetting->low_stock_category_id) : null;
        $products = Product::where('quantity', '<', $quantity)
        ->when($category, function ($q) use ($category) {
            return $q->whereHas('productcategory', function ($query) use ($category) {
                return $query->whereIn('productcategories.id', $category);
            });
        })->get();

        if ($products->count() > 0) {
            
            $export = new ProductEmailExport($products);

            $fileName = 'low_quantity_products.xlsx';

            Excel::store($export, $fileName);

            Mail::send('email.ProductQuantity', ['product' => $products], function ($message) use ($fileName, $email) {
                $message->to($email)->subject('Low Product Quantity Alert')->attach(storage_path('app/' . $fileName));
            });

            // Delete the file after sending email
            unlink(storage_path('app/' . $fileName));
        }
        
      }
    }
}
