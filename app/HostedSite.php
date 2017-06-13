<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HostedSite extends Model
{
    public $fillable = ['name', 'package_name', 'package_author', 'package_email', 'package_url', 'route'];
    
    public static function getConfigPath($config, $name)
    {
        if (! isset($config['paths']) || ! isset($config['path'][$name])) {
            return $name;
        }
        
        return $config['path'][$name];
    }
}
