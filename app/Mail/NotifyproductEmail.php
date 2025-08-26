<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\NotifyProduct;

class NotifyproductEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(NotifyProduct $data)
    {
        $this->NotifyProduct = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Notify Products')->markdown('email.notify-product-template')->with([
            'NotifyProduct' => $this->NotifyProduct,
        ]);
    }
}
