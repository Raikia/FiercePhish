<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogAggregate extends Model
{
    protected $fillable = ['log_time', 'log_type', 'data'];
    
    
    
    public static function hash(LogAggregate $log)
    {
        return md5($log->log_time.'-'.$log->log_type.'-'.$log->data);
    }
}
