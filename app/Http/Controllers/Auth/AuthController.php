<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Http\Requests\ValidateSecretRequest;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $username = 'name';
    
    protected $redirectAfterLogout = '/login';
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectTo = action('DashboardController@index');
        $this->redirectAfterLogout = action('Auth\AuthController@showLoginForm');
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
    
    private function authenticated(Request $request, Authenticatable $user)
    {
        if ($user->google2fa_secret)
        {
            Auth::logout();
            $request->session()->put('2fa:user:id', $user->id);
            return redirect()->action('Auth\AuthController@getValidateToken');
        }
        return redirect()->intended($this->redirectTo);
    }
    
    public function getValidateToken()
    {
        if (session('2fa:user:id'))
        {
            return view('auth.2fa');
        }
        return redirect()->action('Auth\AuthController@showLoginForm');
    }
    
    public function postValidateToken(ValidateSecretRequest $request)
    {
        $userId = $request->session()->pull('2fa:user:id');
        $key = $userId.':'.$request->totp;
        Cache::add($key, true, 4);
        Auth::loginUsingId($userId);
        return redirect()->intended($this->redirectTo);
    }
}
