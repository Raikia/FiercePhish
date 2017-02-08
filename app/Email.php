<?php

namespace App;

use App\Jobs\SendEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\LogAggregate;
use Carbon\Carbon;

class Email extends Model
{
	use DispatchesJobs;
	
	const NOT_SENT = 1;
	const SENDING = 2;
	const SENT = 3;
	const PENDING_RESEND = 4;
	const CANCELLED = 8;
	const FAILED = 9;
	
    protected $fillable = ['sender_name', 'sender_email', 'target_user_id', 'subject', 'message', 'tls', 'has_attachment', 'attachment', 'status', 'uuid', 'related_logs', 'sent_time', 'planned_time'];
    
    protected $dates = ['sent_time', 'planned_time'];
    
    public function campaign()
    {
    	return $this->belongsTo('App\Campaign');
    }
    
    public function send($delay=-1, $queue="email")
    {
        if ($delay === -1)
            $delay = Carbon::now()->addSeconds(1);
        if ($this->status == Email::SENT)
            $this->status = Email::PENDING_RESEND;
        else
    	   $this->status = Email::NOT_SENT;
    	$this->planned_time = $delay;
    	$this->save();
    	$job = (new SendEmail(['title' => 'Send Email', 'description' => 'To: '.$this->receiver_name, 'icon' => 'envelope'], $this))->onQueue($queue)->delay($delay);
    	dispatch($job);
    }
    
    public function targetuser()
    {
        return $this->belongsTo('App\TargetUser', 'target_user_id');
    }
    
    public function getStatus()
    {
    	switch ($this->status)
    	{
    		case Email::NOT_SENT:
    			return "Not sent";
    		case Email::SENDING:
    			return "Sending";
    		case Email::SENT:
    			return "Sent";
    		case Email::PENDING_RESEND:
    			return "Pending resend";
    		case Email::CANCELLED:
    		    return "Cancelled";
    		case Email::FAILED:
    			return "Failed sending";
    		default:
    			return "Unknown status";
    	}
    }
    
    public function cancel()
    {
        if ($this->status != Email::SENT && $this->status != Email::CANCELLED && $this->status != Email::FAILED)
        {
            $this->status = Email::CANCELLED;
            $this->save();
        }
    }
}
