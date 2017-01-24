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
    	$rawSendEmailData = DB::table('emails')->select(DB::raw('DATE(CONVERT_TZ(sent_time, "+00:00", "'.\App\Libraries\DateHelper::getOffset().'")) as date'), DB::raw('count(*) as numEmails'))->groupBy('date')->where('status', Email::SENT)->get();
    	// Fill in the gaps of dates where no emails were sent
    	$sendEmailData = [];
    	if (count($rawSendEmailData) > 0)
    	{
	    	$sendEmailData = [$rawSendEmailData[0]];
	    	for ($x=1; $x<count($rawSendEmailData); ++$x)
	    	{
	    		$curDate = Carbon::parse($rawSendEmailData[$x-1]->date)->addDay(1)->format('Y-m-d');
	    		while ($curDate != $rawSendEmailData[$x]->date)
	    		{
	    			$obj = new \stdClass();
	    			$obj->date = $curDate;
	    			$obj->numEmails = "0";
	    			$sendEmailData[] = $obj;
	    			$curDate = Carbon::parse($curDate)->addDay(1)->format('Y-m-d');
	    		}
	    		$sendEmailData[] = $rawSendEmailData[$x];
	    	}
	    }

    	// Get error emails
    	$errorEmailData = DB::table('emails')->select(DB::raw('DATE(CONVERT_TZ(updated_at, "+00:00", "'.\App\Libraries\DateHelper::getOffset().'")) as date'), DB::raw('count(*) as numEmails'))->groupBy('date')->where('status', Email::CANCELLED)->get();

    	// Get Statistics
    	$statistics = [
    		'numSent' => Email::where('status', Email::SENT)->count(),
    		'numCancelled' => Email::where('status', Email::CANCELLED)->count(),
    		'numPending' => Email::where('status', Email::NOT_SENT)->orWhere('status', Email::SENDING)->orWhere('status', Email::PENDING_RESEND)->count(),
    	];

        return view('dashboard.index')->with('activitylog', ActivityLog::fetch())->with('sendEmailData', $sendEmailData)->with('errorEmailData', $errorEmailData)->with('emailStats', $statistics)->with('allActiveCampaigns', Campaign::where('status', Campaign::NOT_STARTED)->orWhere('status', Campaign::SENDING)->orWhere('status', Campaign::WAITING)->get());
    }
}
