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

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($visit)
    {
        $this->visit = $visit;
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
        return (new MailMessage)
                    ->from('fiercephish@raikia.com')
                    ->greeting('Hosted File View Notification!')
                    ->subject('FiercePhish Notification: ')
                    ->line('Some notificaiton here')
                    ->line('To disable these notifications, go to xyz');
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
