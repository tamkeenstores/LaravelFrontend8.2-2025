<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\AbandonedCart;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;


    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(AbandonedCart $data)
    {
        $this->AbandonedCart = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('رمضان أجمل مع عروضنا')->markdown('email.abandoned-template')->with([
            'Abandoned' => $this->AbandonedCart,
        ]);
    }
}