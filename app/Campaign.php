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

    protected $fillable = ['name', 'from_name', 'from_email', 'description', 'status', 'target_list_id', 'email_template_id'];

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
        switch ($this->status) {
            case self::NOT_STARTED:
                return 'Not started';
            case self::SENDING:
                return 'Sending emails';
            case self::WAITING:
                return 'Running';
            case self::FINISHED:
                return 'Completed';
            case self::CANCELLED:
                return 'Cancelled';
            default:
                return 'Unknown status';
        }
    }

    public function cancel()
    {
        if ($this->status != self::FINISHED && $this->status != self::CANCELLED) {
            $this->status = self::CANCELLED;
            $this->save();
        }
        $this->emails()->where('status', '!=', Email::SENT)->where('status', '!=', Email::CANCELLED)->where('status', '!=', Email::FAILED)->update(['status' => Email::CANCELLED]);
    }
}
