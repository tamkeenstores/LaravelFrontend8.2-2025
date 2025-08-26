<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helper\WalletHelper;

class WalletViewJob implements ShouldQueue
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
        WalletHelper::walletData($id);
    }
}