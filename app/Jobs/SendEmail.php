<?php

namespace App\Jobs;

use App\Email;
use App\Campaign;
use App\ActivityLog;
use Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\PhishingEmail;

class SendEmail extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($meta, Email $email)
    {
        $this->email = $email;
        parent::__construct($meta);
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
            $this->cleanup();
            return;
        }
        if ($this->email->status == Email::CANCELLED || $this->email->status == Email::SENDING || $this->email->status == Email::SENT || $this->email->status == Email::FAILED)
        {
            $this->cleanup();
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
                Mail::to($this->email->targetuser->email, $this->email->targetuser->full_name())->send(new PhishingEmail($this->email));
            }
            catch (\Exception $e)
            {
                $this->email->status = Email::FAILED;
                $this->email->save();
                Log::error($e);
                echo 'Error: '.$e->getMessage()."\n";
                if ($this->email->campaign != null)
                {
                    ActivityLog::log("Failed to send an email to \"".$this->email->targetuser->email."\" for campaign \"".$this->email->campaign->name."\" (email ID ".$this->email->id.") (try #".$this->attempts().')', "SendEmail", true);
                }
                else
                {
                    ActivityLog::log("Failed to send an email (simple send) to \"".$this->email->targetuser->email."\" (email ID ".$this->email->id.") (try #".$this->attempts().')', "SendEmail", true);
                }
                ActivityLog::log("Cancelling email due to failed sending attempt.  Check the log for the errors!", "SendEmail");
                $this->checkCampaign();
                $this->delete();
                return;
            }
        }
        $this->email->sent_time = Carbon::now();
        $this->email->status = Email::SENT;
        $this->email->save();
        if ($this->email->campaign != null)
        {
            ActivityLog::log("Sent an email to \"".$this->email->targetuser->email."\" for campaign \"".$this->email->campaign->name."\" (email ID ".$this->email->id.")", "SendEmail");
        }
        else
        {
            ActivityLog::log("Sent an email (simple send) to \"".$this->email->targetuser->email."\" (email ID ".$this->email->id.")", "SendEmail");
        }
        
        
        $this->checkCampaign();
        
        $this->cleanup();
    }
    
    public function failed(Exception $exception)
    {
        $this->email->status = Email::FAILED;
        $this->email->save();
        Log::error($e);
        echo $exception->getMessage();
        $this->cleanup();
    }
    
    
    public function checkCampaign()
    {
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
