<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public const NOT_STARTED = 1;
    public const STARTED = 2;
    public const SENDING = 3;
    public const WAITING = 4;
    public const FINISHED = 5;
    
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
