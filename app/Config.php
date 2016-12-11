<?php

namespace App;

use Cache;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['key', 'value', 'description'];
    
    public static function get($key, $default='')
    {
        if (Cache::has('config_'.$key))
            return Cache::get('config_'.$key);
        $config = Config::where('key', $key)->first();
        if ($config == null)
        {
            $config = new Config();
            $config->key = $key;
            $config->value = $default;
            $config->save();
        }
        Cache::put('config_'.$key, $config->value, 60);
        return $config->value;
    }
    
    public static function set($key, $value)
    {
        $config = Config::where('key', $key)->first();
        if ($config == null)
        {
            $config = new Config();
            $config->key = $key;
        }
        $config->value = $value;
        $config->save();
        Cache::put('config_'.$key, $config->value, 60);
    }
}
