<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteCreds extends Model
{
    public $fillable = ['username', 'password', 'hosted_file_view_id'];

    public function view()
    {
        return $this->belongsTo('App\HostedFileView', 'hosted_file_view_id');
    }
}
