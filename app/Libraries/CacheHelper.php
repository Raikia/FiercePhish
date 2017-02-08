<?php
namespace App\Libraries;

use Cache;

class CacheHelper
{
    public static function getLatestVersion()
    {
        return Cache::remember('latest_fiercephish_version', 7200, function () {
            return trim(file_get_contents('https://raw.githubusercontent.com/Raikia/FiercePhish/master/VERSION?nocache'));
        });
    }
    
    public static function getCurrentVersion()
    {
        return trim(file_get_contents(base_path('VERSION')));
    }
}
