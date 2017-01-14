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
    
    
    
    public static function getSurroundingLogs($carbon_date, $num_secs='20', $type = null)
    {
        $adate = $carbon_date->copy()->addSeconds($num_secs);
        $bdate = $carbon_date->copy()->subSeconds($num_secs);
        $query = LogAggregate::where('log_time', '>=', $bdate)->where('log_time', '<=', $adate);
        if ($type !== null)
            $query = $query->where('log_type', $type);
        $query = $query->orderby('log_time', 'asc')->orderby('id', 'desc');
        return $query->get();
    }
}
