<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HostedFileView extends Model
{
    protected $fillable = ['hosted_file_id', 'ip', 'alert', 'uuid'];
    
    public function hostfile()
    {
        return $this->belongsTo('App\HostedFile');
    }
    
    public function email()
    {
        return $this->hasOne('App\Email', 'uuid', 'uuid');
    }
}
