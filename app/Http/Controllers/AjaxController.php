<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\TargetUser;
use Response;

class AjaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function edit_targetuser_notes(Request $request)
    {
        if (!$request->has('pk'))
            return Response::json("Invalid target user: ", 400);
        $t = TargetUser::findOrFail($request->input('pk'));
        $t->notes = $request->input('value');
        $t->save();
        return Response::json("Success", 200);
    }
}
