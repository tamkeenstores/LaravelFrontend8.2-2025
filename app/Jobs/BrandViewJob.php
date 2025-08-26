<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Brand;

class BrandViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $id;
    public function __construct($id)
    {
        $this->id  = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $brand = Brand::where('id', $this->id)->first();
        $brand->clicks = $brand->clicks + 1;
        
        $brand->update();
    }
}
