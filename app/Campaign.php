<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Email;

class Campaign extends Model
{
    const NOT_STARTED = 1;
    const SENDING = 3;
    const WAITING = 4;
    const FINISHED = 5;
    const CANCELLED = 6;
    
    protected $fillable = ['name', 'notes'];
    
    
    public function email_template()
    {
        return $this->belongsTo('App\EmailTemplate');
    }
    
    public function target_list()
    {
        return $this->belongsTo('App\TargetList');
    }
    
    public function emails()
    {
        return $this->hasMany('App\Email');
    }
    
    public function getStatus()
    {
    	switch ($this->status)
    	{
    		case Campaign::NOT_STARTED:
    			return "Not started";
    		case Campaign::SENDING:
    			return "Sending emails";
    		case Campaign::WAITING:
    			return "Running";
    		case Campaign::FINISHED:
    			return "Completed";
    		case Campaign::CANCELLED:
    			return "Cancelled";
    		default:
    			return "Unknown status";
    	}
    }
    
    public function cancel()
    {
        if ($this->status != Campaign::FINISHED && $this->status != Campaign::CANCELLED)
        {
            $this->status = Campaign::CANCELLED;
            $this->save();
        }
        $this->emails()->where('status', '!=', Email::SENT)->where('status', '!=', Email::CANCELLED)->where('status', '!=', Email::FAILED)->update(['status' => Email::CANCELLED]);
    }
}
