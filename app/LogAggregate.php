<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogAggregate extends Model
{
    protected $fillable = ['log_time', 'log_type', 'data'];
    
    protected $dates = ['log_time'];
    
    public static function hash(LogAggregate $log)
    {
        return md5($log->log_time.'-'.$log->log_type.'-'.$log->data);
    }
    
    
    
    public static function getSurroundingLogs($carbon_date, $before_num_secs='20', $after_num_secs='20', $type = null)
    {
        $adate = $carbon_date->copy()->subSeconds($before_num_secs);
        $bdate = $carbon_date->copy()->addSeconds($after_num_secs);
        $query = LogAggregate::where('log_time', '>=', $adate)->where('log_time', '<=', $bdate);
        if ($type !== null)
            $query = $query->where('log_type', $type);
        $query = $query->orderby('log_time', 'asc')->orderby('id', 'desc');
        return $query->get();
    }
}
