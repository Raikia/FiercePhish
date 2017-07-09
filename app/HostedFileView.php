<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HostedFileView extends Model
{
    protected $fillable = ['hosted_file_id', 'browser', 'browser_version', 'platform', 'useragent', 'uuid', 'referer', 'ip'];
    
    public function hostfile()
    {
        return $this->belongsTo('App\HostedFile', 'hosted_file_id', 'id');
    }
    
    public function email()
    {
        return $this->hasOne('App\Email', 'uuid', 'uuid');
    }
    
    public function geolocate()
    {
        return $this->hasOne('App\Geolocation', 'ip', 'ip');
    }
    
    public function credentials()
    {
        return $this->hasOne('App\SiteCreds');
    }
    
    // Add plugin detection as well
    
    public function detectBrowser($useragent)
    {
        $this->useragent = $useragent;
        // Detect browser
        $bc = new \BrowscapPHP\Browscap();
        $adapter = new \WurflCache\Adapter\File([\WurflCache\Adapter\File::DIR => storage_path('browscap_cache/cache')]);
        $bc->setCache($adapter);
        $result = $bc->getBrowser($useragent);
        $this->browser = $result->browser;
        $this->browser_version = $result->version;
        $this->browser_maker = $result->browser_maker;
        $this->platform = $result->platform;
    }
}
