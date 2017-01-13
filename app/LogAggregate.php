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
    
    
    
    public static function getSurroundingLogs($carbon_date, $num_secs='20')
    {
        $adate = $carbon_date->modify('+'.$num_secs.' seconds');
        $bdate = $carbon_date->modify('-'.$num_secs.' seconds');
        return LogAggregate::where('log_time', '>=', $bdate)->where('log_time', '<=', $adate)->get();
    }
}
