<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Crypt;
use Google2FA;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use \ParagonIE\ConstantTime\Base32;

class Google2FAController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        $this->middleware(['web', 'auth']);
    }
    
    public function enableTwoFactor(Request $request)
    {
        $secret = $this->generateSecret();
        $user = $request->user();
        $user->google2fa_secret = Crypt::encrypt($secret);
        $user->save();
        
        return redirect()->action('SettingsController@get_editprofile')->with('success', 'Enabled Google 2FA');
    }
    
    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();
        $user->google2fa_secret = null;
        $user->save();
        return redirect()->action('SettingsController@get_editprofile')->with('success', 'Google 2FA has been disabled');
    }
    
    private function generateSecret()
    {
        $randomBytes = random_bytes(10);
        return Base32::encodeUpper($randomBytes);
    }
}
