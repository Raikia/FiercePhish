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
                    ->from('fiercephish@raikia.com')
                    ->subject('FiercePhish: '.$this->visit->hostfile->getPath().' has been viewed'.$by_user.'! '.$invalid_str)
                    ->greeting('Hosted File View Notification for "'.$this->visit->hostfile->file_name.'"'.$invalid_str.'!')
                    ->line('Original filename: ' . $this->visit->hostfile->original_file_name)
                    ->line('Hosted filename: ' . $this->visit->hostfile->getPath());
        if ($this->visit->email !== null)
        {
            $user_notes = '';
            if ($this->visit->email->targetuser->notes !== null)
                $user_notes = ' (Note: '.$this->visit->email->targetuser->notes.')';
            $obj = $obj->line('Related email: ' . $this->visit->email->subject)
                        ->line('Related user: ' . $this->visit->email->targetuser->full_name() . ' ('.$this->visit->email->targetuser->email.')'.$user_notes);
            
        }
        if ($this->visit->referer !== null)
        {
            $obj = $obj->line('Referer: ' . $this->visit->referer);
        }
        $obj = $obj->line('IP: ' . $this->visit->ip)
                    ->line('Browser: ' . $this->visit->browser . ' v'.$this->visit->browser_version . ' (by ' . $this->visit->browser_maker . ')')
                    ->line('Platform: ' . $this->visit->platform)
                    ->line('Raw Useragent: ' . $this->visit->useragent)
                    ->line('')
                    ->line('To disable these notifications, go to '.action('SettingsController@get_editprofile'));
        return $obj;
    }

    public function toSms($notifiable)
    {
        return (new NotificationSMS($notifiable, 'test data'));
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
