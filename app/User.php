<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const NO_NOTIFICATION = 0;
    const EMAIL_NOTIFICATION = 1;
    const SMS_NOTIFICATION = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','phone_number','phone_isp', 'notify_pref'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'google2fa_secret',
    ];
    
    
    public static function getPhoneISPs()
    {
        return [
            ''              => '',
            'T-Mobile'      => 'tmomail.net',
        ];
    }
    
    public static function getNotifications()
    {
        return [
            User::NO_NOTIFICATION => 'No notifications',
            User::EMAIL_NOTIFICATION => 'Email notifications',
            User::SMS_NOTIFICATION => 'SMS notifications',
        ];
    }
    
    public static function getNotifiable()
    {
        return User::where('notify_pref', User::EMAIL_NOTIFICATION)->orWhere('notify_pref', User::SMS_NOTIFICATION)->get();
    }
}
