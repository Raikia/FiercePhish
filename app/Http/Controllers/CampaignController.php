<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\EmailTemplate;
use App\TargetList;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('campaigns.index');
    }
    
    public function create()
    {
        $all_templates = EmailTemplate::orderby('name', 'asc')->get();
        $all_lists = TargetList::orderby('name', 'asc')->get();
        return view('campaigns.create')->with('templates', $all_templates)->with('lists', $all_lists);
    }
}
