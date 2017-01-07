<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Email;
use App\Campaign;
use App\ActivityLog;
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
        if (config('fiercephish.TEST_EMAIL_JOB') === false)
        {
            try
            {
                Mail::send(['layouts.email_html', 'layouts.email_plaintext'], ['data' => $this->email->message], function ($message) {
                    $message->from($this->email->sender_email, $this->email->sender_name);
                    $message->to($this->email->receiver_email, $this->email->receiver_name);
                    $message->subject($this->email->subject);
                    if (strstr(config('fiercephish.APP_URL'), '.') !== false)
                    {
                        $id = explode('@',$message->getSwiftMessage()->getId());
                        $domain = str_replace(['http://','https://'],'', config('fiercephish.APP_URL'));
                        $message->getSwiftMessage()->setId($id[0].'@'.$domain);
                       // $message->getSwiftMessage()->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:admin@'.$domain.'>');
                    }
                    if ($this->email->has_attachment)
                    {
                        $message->attachData(base64_decode($this->email->attachment), $this->email->attachment_name, ['mime' => $this->email->attachment_mime]);
                    }
                    if (config('fiercephish.MAIL_BCC_ALL') !== null)
                        $message->bcc(config('fiercephish.MAIL_BCC_ALL'));
                });
            }
            catch (\Exception $e)
            {
                $this->email->status = Email::FAILED;
                $this->email->save();
                echo 'Error: '.$e->getMessage()."\n";
                if ($this->email->campaign != null)
                {
                    ActivityLog::log("Failed to send an email to \"".$this->email->receiver_email."\" for campaign \"".$this->email->campaign->name."\" (email ID ".$this->email->id.") (try #".$this->attempts().')', "SendEmail", true);
                }
                else
                {
                    ActivityLog::log("Failed to send an email (simple send) to \"".$this->email->receiver_email."\" (email ID ".$this->email->id.") (try #".$this->attempts().')', "SendEmail", true);
                }
                if ($this->attempts() > 5)
                {
                    ActivityLog::log("Cancelling email due to too many failed attempts.  Check the log for the errors!", "SendEmail");
                    $this->delete();
                }
                throw $e;
            }
        }
        
        $this->email->status = Email::SENT;
        if ($this->email->campaign != null)
        {
            ActivityLog::log("Sent an email to \"".$this->email->receiver_email."\" for campaign \"".$this->email->campaign->name."\" (email ID ".$this->email->id.")", "SendEmail");
        }
        else
        {
            ActivityLog::log("Sent an email (simple send) to \"".$this->email->receiver_email."\" (email ID ".$this->email->id.")", "SendEmail");
        }
        
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
