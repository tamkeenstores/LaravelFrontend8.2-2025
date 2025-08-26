<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Blog;

class BlogViewJob implements ShouldQueue
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
        $blog = Blog::where('id', $this->id)->first();
        $blog->views = $blog->views + 1;
        $blog->update();
    }
}
