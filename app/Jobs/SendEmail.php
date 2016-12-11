<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Email;
use App\Campaign;
use Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->email->campaign != null && $this->email->campaign->status == Campaign::CANCELLED)
        {
            $this->email->status = Email::CANCELLED;
            $this->email->save();
            return;
        }
        if ($this->email->status == Email::CANCELLED)
        {
            return;
        }
        if ($this->email->campaign != null)
        {
            $this->email->campaign->status = Campaign::SENDING;
            $this->email->campaign->save();
        }
        $this->email->status = Email::SENDING;
        $this->email->save();
        
       /* Mail::send('layouts.email', ['data' => $this->email->message], function ($message) {
            $message->from($this->email->sender_email, $this->email->sender_name);
            $message->to($this->email->receiver_email, $this->email->receiver_name);
            $message->subject($this->email->subject);
            if ($this->email->has_attachment)
            {
                $message->attachData(base64_decode($this->email->attachment), $this->email->attachment_name, ['mime' => $this->email->attachment_mime]);
            }
            if (env('MAIL_BCC_ALL') !== null)
                $message->bcc(env('MAIL_BCC_ALL'));
        });*/
        
        $this->email->status = Email::SENT;
        $this->email->save();
        
        if ($this->email->campaign != null)
        {
            if ($this->email->campaign->emails()->where('status', Email::NOT_SENT)->count() == 0)
            {
                $this->email->campaign->status = Campaign::FINISHED;
            }
            else
            {
                $this->email->campaign->status = Campaign::WAITING;
            }
            $this->email->campaign->save();
        }
    }
}
