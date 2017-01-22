<?php
namespace App\Libraries;

use Carbon\Carbon;

class DateHelper
{
    public static function print($date)
    {
        return $date->timezone(config('fiercephish.APP_TIMEZONE'))->toDateTimeString();
    }
    
    public static function relative($date)
    {
        return $date->timezone(config('fiercephish.APP_TIMEZONE'))->diffForHumans();
    }
    
    public static function isNull($date)
    {
        return $date===null || property_exists($date, 'year') || $date->year < 5;
    }
}
