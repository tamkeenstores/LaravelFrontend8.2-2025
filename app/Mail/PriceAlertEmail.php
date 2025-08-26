<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PriceAlert;

class PriceAlertEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PriceAlert $data)
    {
        $this->PriceAlert = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Price Alert')->markdown('email.notify-product-template')->with([
            'PriceAlert' => $this->PriceAlert,
        ]);
    }
}
