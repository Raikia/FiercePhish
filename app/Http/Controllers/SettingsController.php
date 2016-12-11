<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use Hash;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('settings.usermanagement.index')->with('users', $users);
    }
    
    public function addUser(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|unique:users',
            'email' => 'required|max:255|email',
            'password' => 'required|min:6'
        ]);
        $newUser = new User();
        $newUser->name = $request->input('name');
        $newUser->email = $request->input('email');
        $newUser->phone_number = $request->input('phone_number');
        $newUser->password = Hash::make($request->input('password'));
        $newUser->save();
        return back()->with('success', 'User created successfully');
    }
    
    public function deleteUser(Request $request)
    {
        $this->validate($request, [
            'user' => 'required|integer'
        ]);
        $user = User::findOrFail($request->input('user'));
        if ($user->id == auth()->user()->id)
            return back()->withErrors('You cannot delete yourself!');
        $user->delete();
        return back()->with('success', 'User has been successfully deleted');
    }
    
    public function get_editprofile($id="")
    {
        $user = auth()->user();
        if ($id != "")
            $user = User::findOrFail($id);
        return view('settings.usermanagement.editprofile')->with('user', $user)->with('self', $id);
    }
    
    public function post_editprofile(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'sometimes|min:6|confirmed',
            'phone_number' => 'min:14|max:14',
            'current_password' => 'sometimes|required',
            'user_id' => 'required|integer',
            ]);
        if ($request->input('user_id') == auth()->user()->id)
        {
            if (!Hash::check($request->input('current_password'), auth()->user()->password))
                return back()->withErrors('Invalid current password');
        }
        $user = User::findOrFail($request->input('user_id'));
        $u = User::where('name', $request->input('name'))->first();
        if ($u != null && $u->id != $request->input('user_id'))
        {
            return back()->withErrors('Username already exists');
        }
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if (!empty($request->input('password')))
            $user->password = Hash::make($request->input('password'));
        $user->phone_number = $request->input('phone_number');
        $user->save();
        if ($request->input('type') == 'diff')
            return redirect()->action('SettingsController@index')->with('success', 'Profile updated successfully');
        return back()->with('success', 'Profile updated successfully');
    }
    
    public function get_config()
    {
        return view('settings.configs.config');
    }
    
    public function post_config(Request $request)
    {
        $all_updates = $request->except('_token');
        //echo nl2br(var_dump($all_updates));
        $path = base_path('.env');
        if (file_exists($path) && is_writable($path)) {
            $file_contents = file_get_contents($path);
            foreach ($all_updates as $key => $value)
            {
                $real_old_value = env($key);
                if ($real_old_value === true)
                    $real_old_value = 'true';
                elseif ($real_old_value === false)
                    $real_old_value = 'false';
                elseif ($real_old_value === null)
                    $real_old_value = 'null';
                
                $real_new_value = $value;
                if ($real_new_value === "")
                    $real_new_value = 'null';
               // echo "<br /><br />SEARCHING FOR: '".$key.'='.$real_old_value."', replacing with '".$key.'='.$real_new_value."'<br /><br />";
                $file_contents = str_replace($key.'='.$real_old_value, $key.'='.$real_new_value, $file_contents);
            }
            //echo nl2br(print_r($file_contents, true));
            file_put_contents($path, $file_contents);
            //die();
            return back()->with('success', 'Settings successfully saved!');
        }
        else
        {
            return back()->withErrors('Settings could not be saved. Check the file permissions on "'.$path.'"!');
        }
    }
}
