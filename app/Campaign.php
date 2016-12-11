<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
        return $this->hasOne('App\EmailTemplate');
    }
    
    public function target_list()
    {
        return $this->hasOne('App\TargetList');
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
}
