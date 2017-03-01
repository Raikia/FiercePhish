<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Mail;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);
        $number = preg_replace("/[^0-9]/", "", $notifiable->phone_number);
        if ($notifiable->phone_isp === '')
            return;
        echo "SENDING THING: " . $message->data;
        Mail::to($number.'@'.$notifiable->phone_isp)->send($message);
    }
}