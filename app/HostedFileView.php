<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HostedFileView extends Model
{
    protected $fillable = ['hosted_file_id', 'browser', 'browser_version', 'platform', 'useragent', 'uuid'];
    
    public function hostfile()
    {
        return $this->belongsTo('App\HostedFile');
    }
    
    public function email()
    {
        return $this->hasOne('App\Email', 'uuid', 'uuid');
    }
    
    // Add plugin detection as well
    
    public function browserDetection($useragent)
    {
        $this->useragent = $useragent;
    }
}
