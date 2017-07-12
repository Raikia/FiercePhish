<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HostedSite extends Model
{
    public $fillable = ['name', 'package_name', 'package_author', 'package_email', 'package_url', 'route', 'entry_file_id'];
    
    public function files()
    {
        return $this->hasMany('App\HostedFile');
    }
    
    public function entry_file()
    {
        return $this->hasOne('App\HostedFile', 'id', 'entry_file_id');
    }
    
    public function credentials()
    {
        $allcreds = collect();
        foreach ($this->files()->with('credentials')->get() as $file) {
            foreach ($file->credentials as $cred) {
                $cred->fileReference = $file->original_file_name;
                $allcreds->push($cred);
            }
        }
        
        return $allcreds;
    }
    
    public static function getConfigValue($config, $key, $default)
    {
        $recursive_path = explode('|', $key);
        $current_search = $config;
        for ($x = 0; $x < count($recursive_path); ++$x) {
            if (is_array($current_search) && isset($current_search[$recursive_path[$x]])) {
                $current_search = $current_search[$recursive_path[$x]];
            } else {
                return $default;
            }
        }
        
        return $current_search;
    }
}
