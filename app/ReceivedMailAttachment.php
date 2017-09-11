<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceivedMailAttachment extends Model
{
    protected $fillable = ['received_mail_id', 'name', 'content'];
    
    protected $hidden = ['content'];
    
    public function received_mail()
    {
        return $this->belongsTo('App\ReceivedMail');
    }
}
