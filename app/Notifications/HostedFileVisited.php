<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Channels\SmsChannel;
use App\User;
use App\Mail\NotificationSMS;

class HostedFileVisited extends Notification
{
    use Queueable;

    public $visit;
    public $invalid_tracker;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($visit, $invalid)
    {
        $this->visit = $visit;
        $this->invalid_tracker = $invalid;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable->notify_pref == User::SMS_NOTIFICATION)
            return [SmsChannel::class];
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $invalid_str = '';
        if ($this->invalid_tracker)
        {
            $invalid_str = '(invalid tracker!!!)';
        }
        $by_user = '';
        if ($this->visit->email !== null)
        {
            $by_user = ' by '.$this->visit->email->targetuser->full_name();
        }
        $obj = (new MailMessage)
                    ->from(config('fiercephish.NOTIFICATIONS_FROM'))
                    ->subject('FiercePhish: '.$this->visit->hostfile->getPath().' has been viewed'.$by_user.'! '.$invalid_str)
                    ->markdown('notifications.hostedfileviewed.mail', ['visit' => $this->visit, 'invalid' => $this->invalid_tracker]);
        return $obj;
    }

    public function toSms($notifiable)
    {
        $data = '';
        $data .= 'Original Filename: '.$this->visit->hostfile->original_file_name."\n";
        $data .= 'Hosted filename: '.$this->visit->hostfile->getPath()."\n";
        $data .= "\n";
        if ($this->visit->email !== null)
        {
            $data .= "User: ".$this->visit->email->targetuser->full_name()." (".$this->visit->targetuser->email.")"."\n";
            $data .="User note: ".$this->visit->email->targetuser->notes."\n";
            $data .= "\n";
        }
        $data .= "IP: ".$this->visit->ip."\n";
        $data .= "System: ".$this->visit->platform." running ".$this->visit->browser." v".$this->visit->browser_version."\n";
        
        return (new NotificationSMS($notifiable, $data));
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
