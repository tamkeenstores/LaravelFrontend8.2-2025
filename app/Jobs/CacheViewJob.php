<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CacheStores;
use Illuminate\Support\Facades\Cache;


class CacheViewJob implements ShouldQueue
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
        $id = $this->id;
        $success = false;
        $CacheStores = CacheStores::where('type',$id)->pluck('key')->toArray();
        // print_r($CacheStores);die();
        if($CacheStores){
            foreach($CacheStores as $key => $CacheStore){
                // print_r($CacheStore);die();
                Cache::forget($CacheStore);
                CacheStores::where('key',$CacheStore)->delete();
            }
            // CacheStores::whereIn('key',$CacheStores)->delete();
            // $success = true;
        }
        
    }
}