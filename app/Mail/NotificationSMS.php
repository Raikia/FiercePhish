<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationSMS extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $data;
    public $to;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('layouts.email_plaintext')
                    ->with('data', $this->data)
                    ->subject('FiercePhish Notification')
                    ->from(config('fiercephish.NOTIFICATIONS_FROM'));
    }
}
