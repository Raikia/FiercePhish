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
            ''                          => '',
            '3 River Wireless'	    	=> 'sms.3rivers.net',
            'ACS Wireless'		        => 'paging.acswireless.com',
            'Alltel'		            => 'message.alltel.com',
            'AT&T'	                	=> 'txt.att.net',
            'Bell Canada'		        => 'txt.bellmobility.ca',
            'Bell Canada'	        	=> 'bellmobility.ca',
            'Bell Mobility (Canada)'    => 'txt.bell.ca',
            'Bell Mobility'		        => 'txt.bellmobility.ca',
            'Blue Sky Frog'		        => 'blueskyfrog.com',
            'Bluegrass Cellular'	    => 'sms.bluecell.com',
            'Boost Mobile'		        => 'myboostmobile.com',
            'BPL Mobile'		        => 'bplmobile.com',
            'Carolina West Wireless'    => 'cwwsms.com',
            'Cellular One'		        => 'mobile.celloneusa.com',
            'Cellular South'		    => 'csouth1.com',
            'Centennial Wireless'	    => 'cwemail.com',
            'CenturyTel'		        => 'messaging.centurytel.net',
            'Cingular (Now AT&T)'	    => 'txt.att.net',
            'Clearnet'		            => 'msg.clearnet.com',
            'Comcast'		            => 'comcastpcs.textmsg.com',
            'Dobson'		            => 'mobile.dobson.net',
            'Edge Wireless'		        => 'sms.edgewireless.com',
            'Fido'		                => 'fido.ca',
            'Golden Telecom'		    => 'sms.goldentele.com',
            'Helio'		                => 'messaging.sprintpcs.com',
            'Houston Cellular'		    => 'text.houstoncellular.net',
            'Idea Cellular'		        => 'ideacellular.net',
            'Illinois Valley Cellular'	=> 'ivctext.com',
            'Inland Cellular Telephone'	=> 'inlandlink.com',
            'MCI'		                => 'pagemci.com',
            'Metrocall'		            => 'page.metrocall.com',
            'Metrocall 2-way'		    => 'my2way.com',
            'Metro PCS'		            => 'mymetropcs.com',
            'Microcell'		            => 'fido.ca',
            'Midwest Wireless'		    => 'clearlydigital.com',
            'Mobilcomm'		            => 'mobilecomm.net',
            'MTS'		                => 'text.mtsmobility.com',
            'Nextel'		            => 'messaging.nextel.com',
            'OnlineBeep'		        => 'onlinebeep.net',
            'PCS One'		            => 'pcsone.net',
            'President\'s Choice'		=> 'txt.bell.ca',
            'Public Service Cellular'	=> 'sms.pscel.com',
            'Qwest'		                => 'qwestmp.com',
            'Rogers AT&T Wireless'		=> 'pcs.rogers.com',
            'Rogers Canada'		        => 'pcs.rogers.com',
            'Satellink'		            => 'satellink.net',
            'Solo Mobile'	        	=> 'txt.bell.ca',
            'Southwestern Bell'		    => 'email.swbw.com',
            'Sprint'		            => 'messaging.sprintpcs.com',
            'Sumcom'		            => 'tms.suncom.com',
            'Surewest Communicaitons'	=> 'mobile.surewest.com',
            'T-Mobile'		            => 'tmomail.net',
            'Telus'		                => 'msg.telus.com',
            'Tracfone'	            	=> 'txt.att.net',
            'Triton'	            	=> 'tms.suncom.com',
            'Unicel'	            	=> 'utext.com',
            'US Cellular'	        	=> 'email.uscc.net',
            'US West'	                => 'uswestdatamail.com',
            'Verizon'	            	=> 'vtext.com',
            'Virgin Mobile'	        	=> 'vmobl.com',
            'Virgin Mobile Canada'		=> 'vmobile.ca',
            'West Central Wireless'		=> 'sms.wcc.net',
            'Western Wireless'	    	=> 'cellularonewest.com',
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
