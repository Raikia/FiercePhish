<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
	const NOT_SENT = 1;
	const SENDING = 2;
	const SENT = 3;
	const FAILED = 9;

    protected $fillable = ['sender_name', 'sender_email', 'receiver_name', 'receiver_email', 'subject', 'message', 'tls', 'has_attachment', 'attachment', 'status'];
}
