<?php
namespace App\Libraries;

use Carbon\Carbon;

class DateHelper
{
    public static function print($date)
    {
        return $date->timezone(config('fiercephish.APP_TIMEZONE'))->toDateTimeString();
    }
    
    public static function readable($date)
    {
        return $date->timezone(config('fiercephish.APP_TIMEZONE'))->format('M j, Y @ g:i:s a');
    }
    
    public static function relative($date)
    {
        return $date->timezone(config('fiercephish.APP_TIMEZONE'))->diffForHumans();
    }
    
    public static function format($date, $format)
    {
        return $date->timezone(config('fiercephish.APP_TIMEZONE'))->format($format);
    }
    
    public static function isNull($date)
    {
        return $date===null || property_exists($date, 'year') || $date->year < 5;
    }
}
