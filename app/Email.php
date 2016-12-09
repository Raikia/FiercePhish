<?php

namespace App;

use App\Jobs\SendEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Email extends Model
{
	use DispatchesJobs;
	
	const NOT_SENT = 1;
	const SENDING = 2;
	const SENT = 3;
	const PENDING_RESENT = 4;
	const FAILED = 9;
	
    protected $fillable = ['sender_name', 'sender_email', 'receiver_name', 'receiver_email', 'subject', 'message', 'tls', 'has_attachment', 'attachment', 'status'];
    
    public function campaign()
    {
    	return $this->belongsTo('App\Campaign');
    }
    
    public function send($delay=1, $queue="high")
    {
    	$this->status = Email::NOT_SENT;
    	$this->save();
    	$job = (new SendEmail($this))->onQueue($queue)->delay($delay);
    	dispatch($job);
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
    		case Email::PENDING_RESENT:
    			return "Pending resend";
    		case Email::FAILED:
    			return "Failed sending";
    		default:
    			return "Unknown status";
    	}
    }
}
