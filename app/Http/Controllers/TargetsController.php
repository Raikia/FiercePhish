<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\TargetUser;
use App\TargetList;

class TargetsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $targetUsers = TargetUser::with('lists')->orderBy('last_name')->get();
        return view('targets.index')->with('targetUsers', $targetUsers);
    }
}
