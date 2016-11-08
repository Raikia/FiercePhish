<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function get_editprofile($id="")
    {
        $user = auth()->user();
        if ($id != "")
            $user = User::findOrFail($id);
        return view('user.editprofile')->with('user', $user);
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
        $user->password = Hash::make($request->input('password'));
        $user->phone_number = $request->input('phone_number');
        $user->save();
        return back()->with('success', 'Profile updated successfully');
    }
}
