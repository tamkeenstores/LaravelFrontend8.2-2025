<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;

class ProductViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
     
     protected $id;
    public function __construct($id)
    {
        $this->id  = $id;
        // print_r($this->id);die();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        $product = Product::where('id', $this->id)->first();
        $product->clicks = $product->clicks + 1;
        $product->view_product = $product->view_product + 1;
        
        $product->update();
    }
}
