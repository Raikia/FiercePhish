<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ReceivedMail extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['message_id', 'sender_name', 'sender_email', 'replyto_name', 'replyto_email', 'receiver_name', 'receiver_email', 'subject', 'received_date', 'message', 'seen', 'replied', 'forwarded'];
    
    protected $dates = ['deleted_at', 'received_date'];
    
    
    public function attachments()
    {
        return $this->hasMany('App\ReceivedMailAttachment');
    }
    
    public function attachment_count()
    {
        return $this->attachments()->selectRaw('received_mail_id, count(*) as aggregate')->groupBy('received_mail_id');
    }
}
