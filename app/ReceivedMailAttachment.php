<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceivedMailAttachment extends Model
{
    protected $fillable = ['name', 'content'];
    
    protected $hidden = ['content'];
    
    public function received_mail()
    {
        return $this->belongsTo('App\ReceivedMail');
    }
}
