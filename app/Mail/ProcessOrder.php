<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\PriceAlert;

class ProcessOrder extends Mailable
{
    use Queueable, SerializesModels;


    public $emailtemplate;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $emailtemplate)
    {
        $this->emailtemplate = $emailtemplate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Processing Order')->markdown('email.process_order')->with([
            'emailtemplate' => $this->emailtemplate,
        ]);
        
        // return $this->subject('Your Order Received')->html($this->emailContent);
        
        // $emailtemplate = $this->emailtemplate;
        // $order = $this->order;

        // $renderedEmailTemplate = view('email.process_order', compact('emailtemplate', 'order'))->render();
        // print_r($emailtemplate);die;
        
        // try {
        //     // View rendering logic
        //     $renderedEmailTemplate = view('email.process_order', compact('emailtemplate', 'order'))->render();
        // } catch (Exception $e) {
        //     Log::error('Error rendering email template: ' . $e->getMessage());
        //     // You can handle the error as needed, such as displaying a generic error message to the user
        //     // or redirecting them to an error page.
        // }

        // return $this->html($renderedEmailTemplate);
    }
}
