<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\ActivityLog;
use File;
use Hash;
use DB;

class SettingsController extends Controller
{

    private $backup_parts = ['activitylogs' => '\App\ActivityLog', 
                            'campaigns' => '\App\Campaign', 
                            'emails' => '\App\Email', 
                            'emailtemplates' => '\App\EmailTemplate', 
                            'targetlists' => '\App\TargetList', 
                            'targetusers' => '\App\TargetUser', 
                            'users' => '\App\User'];

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
        ActivityLog::log("Added a new user named \"".$newUser->name."\"", "Settings");
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
        ActivityLog::log("Deleted a user named \"".$user->name."\"", "Settings");
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
            ActivityLog::log("Application configuration has been edited", "Settings");
            return back()->with('success', 'Settings successfully saved!');
        }
        else
        {
            return back()->withErrors('Settings could not be saved. Check the file permissions on "'.$path.'"!');
        }
    }

    public function get_import_export()
    {
        return view('settings.configs.import_export');
    }
    public function post_export_data()
    {
        ActivityLog::log('FirePhish Settings exported', 'Settings');
        $storage_class = new \stdClass();
        foreach ($this->backup_parts as $attr => $class)
        {
            $storage_class->$attr = serialize($class::all());
        }
        $storage_class->env = file_get_contents(base_path('.env'));
        return response(serialize($storage_class))->header('Content-Type', 'application/octet-stream')->header('Content-Disposition','attachment; filename="backup.firephish.dat"');
    }
    public function post_import_data(Request $request)
    {
        $this->validate($request, [
            'attachment' => 'required|file',
        ]);
        $content = File::get($request->file('attachment')->getRealPath());
        $storage_class = false;
        try
        {
            $storage_class = @unserialize($content);
            if ($storage_class === false)
                return back()->withErrors('Data import failed!  This is not a proper FirePhish backup file!');
        }
        catch (Exception $e)
        {
            return back()->withErrors('Data import failed!  This is not a proper FirePhish backup file!');
        }
        foreach ($this->backup_parts as $attr => $class)
        {
            $data = false;
            try
            {
                $data = @unserialize($storage_class->$attr);
                if ($data === false)
                    return back()->withErrors('Data import failed!  Data import is incomplete');
            }
            catch (Exception $e)
            {
                return back()->withErrors('Data import failed!  Data import is incomplete');
            }
            $class::truncate();
            foreach ($data as $d)
            {
                $new_d = $d->replicate();
                $new_d->updated_at = $d->updated_at;
                $new_d->created_at = $d->created_at;
                $new_d->save();
            }
        }
        file_put_contents(base_path('.env'), $storage_class->env);
        ActivityLog::log('Imported settings from a previous FirePhish install', 'Settings');
        return back()->with('success', 'Successfully imported settings');
    }
}
