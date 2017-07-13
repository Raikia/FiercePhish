<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Mail\NotificationSMS;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        if ($notifiable->notify_pref == User::SMS_NOTIFICATION) {
            return [SmsChannel::class];
        }
        
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
        if ($this->invalid_tracker) {
            $invalid_str = '(invalid tracker!!!)';
        }
        $by_user = '';
        if ($this->visit->email !== null) {
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
        $data .= 'Original Filename: '.$this->visit->hostfile->original_file_name."\n<br>";
        $data .= 'Hosted filename: '.$this->visit->hostfile->getPath()."\n<br>";
        $data .= "\n";
        if ($this->visit->email !== null) {
            $data .= 'User: '.$this->visit->email->targetuser->full_name().' ('.$this->visit->email->targetuser->email.')'."\n<br>";
            $data .= 'Campaign: '.$this->visit->email->campaign->name."\n<br>";
            if ($this->visit->email->targetuser->notes !== null) {
                $data .= 'User note: '.$this->visit->email->targetuser->notes."\n<br>";
            }
            if ($this->visit->email->campaign->target_list->notes !== null) {
                $data .= 'Target List note: '.$this->visit->email->campaign->target_list->notes."\n<br>";
            }
            $data .= "\n";
        }
        if ($this->invalid_tracker) {
            $data .= "INVALID TRACKER!\n\n<br>";
        }
        $data .= 'IP: '.$this->visit->ip."\n<br>";
        $data .= 'System: '.$this->visit->platform.' running '.$this->visit->browser.' v'.$this->visit->browser_version."\n<br>";
        if ($this->visit->credentials !== null) {
            $data .= "<br><br>\n\nGOT CREDENTIALS: <br>Username: ".$this->visit->credentials->username."\n<br>Password: ".$this->visit->credentials->password."\n";
        }
        
        return new NotificationSMS($notifiable, $data);
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
        ];
    }
}
