<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Email;

class PhishingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = $this->view('layouts.email_html')
                    ->text('layouts.email_plaintext')
                    ->with(['data' => $this->email->message])
                    ->from($this->email->sender_email, $this->email->sender_name)
                    ->to($this->email->targetuser->email, $this->email->targetuser->full_name())
                    ->subject($this->email->subject);
        if ($this->email->has_attachment)
            $message = $message->attachData(base64_decode($this->email->attachment), $this->email->attachment_name, ['mime' => $this->email->attachment_mime]);
        if (strpos(config('fiercephish.MAIL_BCC_ALL'), '@') !== false)
            $message = $message->bcc(config('fiercephish.MAIL_BCC_ALL'));
        $message = $message->withSwiftMessage(function ($swiftmessage) {
            if (strstr(config('fiercephish.APP_URL'), '.') !== false)
            {
                $id = explode('@',$swiftmessage->getId());
                $domain = explode(':', str_replace(['http://','https://'],'', config('fiercephish.APP_URL')))[0];
                $swiftmessage->setId($id[0].'@'.$domain);
               // $swiftmessage->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:admin@'.$domain.'>');
            }
            return $swiftmessage;
        });
        return $message;
    }
}
