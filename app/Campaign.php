<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    const NOT_STARTED = 1;
    const STARTED = 2;
    const SENDING = 3;
    const WAITING = 4;
    const FINISHED = 5;
    
    protected $fillable = ['name', 'notes'];
    
    
    public function email_template()
    {
        return $this->hasOne('App\EmailTemplate');
    }
    
    public function target_list()
    {
        return $this->hasOne('App\TargetList');
    }
}
