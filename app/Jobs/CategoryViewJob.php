<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Productcategory;

class CategoryViewJob implements ShouldQueue
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
        $category = Productcategory::where('id', $this->id)->first();
        $category->clicks = $category->clicks + 1;
        $category->views = $category->views + 1;
        $category->update();
    }
}
