<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\ActivityLog;
use DB;
use App\Email;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
    	// Get sent email stats
    	$sendEmailData = \App\Libraries\GlobalHelper::generateGraphData(Email::where('status', Email::SENT), 'sent_time');
    	// Get error emails
    	$errorEmailData = \App\Libraries\GlobalHelper::generateGraphData(Email::where('status', Email::CANCELLED), 'updated_at');

    	// Get Statistics
    	$statistics = [
    		'numSent' => Email::where('status', Email::SENT)->count(),
    		'numCancelled' => Email::where('status', Email::CANCELLED)->count(),
    		'numPending' => Email::where('status', Email::NOT_SENT)->orWhere('status', Email::SENDING)->orWhere('status', Email::PENDING_RESEND)->count(),
    	];

        return view('dashboard.index')->with('activitylog', ActivityLog::fetch())->with('sendEmailData', $sendEmailData)->with('errorEmailData', $errorEmailData)->with('emailStats', $statistics)->with('allActiveCampaigns', Campaign::where('status', Campaign::NOT_STARTED)->orWhere('status', Campaign::SENDING)->orWhere('status', Campaign::WAITING)->get());
    }
    
    public function test()
    {
    	$request = \Request::instance();
    	dd($request->input('test'));
    }
}
