<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Newslatter;

class NewslatterMail extends Mailable
{
    use Queueable, SerializesModels;


    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Newslatter $data)
    {
        $this->Newslatter = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Newslatter activation')->markdown('email.newslatter-template')->with([
            'newslatter' => $this->Newslatter,
        ]);
    }
}