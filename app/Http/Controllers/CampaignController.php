<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\EmailTemplate;
use App\TargetList;
use App\Campaign;
use App\Email;
use App\ActivityLog;
use App\Jobs\StartCampaign;
use Carbon\Carbon;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $all_campaigns = Campaign::all();
        return view('campaigns.index')->with('all_campaigns', $all_campaigns);
    }
    
    public function create()
    {
        $all_templates = EmailTemplate::orderby('name', 'asc')->get();
        $all_lists = TargetList::orderby('name', 'asc')->get();
        return view('campaigns.create')->with('templates', $all_templates)->with('lists', $all_lists);
    }
    
    public function create_post(Request $request)
    {
        
        $this->validate($request, [
            'campaign_name' => 'required',
            'campaign_description' => 'required',
            'email_template' => 'required|integer',
            'target_list' => 'required|integer',
            'sender_name' => 'required',
            'sender_email' => 'required|email',
        ]);
        
        $template = EmailTemplate::findOrFail($request->input('email_template'));
        $list = TargetList::findOrFail($request->input('target_list'));
        $campaign = new Campaign();
        $campaign->name = $request->input('campaign_name');
        $campaign->from_name = $request->input('sender_name');
        $campaign->from_email = $request->input('sender_email');
        $campaign->description = $request->input('campaign_description');
        $campaign->status = Campaign::NOT_STARTED;
        $campaign->target_list_id = $request->input('target_list');
        $campaign->email_template_id = $request->input('email_template');
        $campaign->save();
        $start_date = $request->input('starting_date') ?: \App\Libraries\DateHelper::now()->format('m/d/Y');
        $start_time = $request->input('starting_time') ?: \App\Libraries\DateHelper::now()->format('g:ia');
        $start_date = Carbon::parse($start_date . ' ' . $start_time, config('fiercephish.APP_TIMEZONE'))->addSeconds(1)->timezone('UTC');
        $send_num_emails = min((int)$request->input('send_num'),1000);
        $send_every_minutes = min((int)$request->input('send_every_x_minutes'), 1000);
        if ($request->input('sending_schedule') == 'all' || empty($request->input('send_num')) || empty($request->input('send_every_x_minutes')))
        {
            $send_num_emails = -1; // Send all emails at once
        }
        $job = (new StartCampaign(['title' => 'Create campaign', 'description' => 'Campaign: "' . $campaign->name.'"', 'icon' => 'play'], $campaign, $list, $template, $send_num_emails, $send_every_minutes, $start_date))->onQueue('operation')->delay(1);
        $this->dispatch($job);
        ActivityLog::log("Created a create campaign job named \"".$campaign->name."\" to queue ".$list->users()->count()." emails for sending", "Campaign");
        return redirect()->action('CampaignController@campaign_details', ['id' => $campaign->id])->with('success', 'Job to create campaign has been launched successfully');
    }
    
    
    public function campaign_details($id)
    {
        $campaign = Campaign::findOrFail($id);
        return view('campaigns.details')->with('campaign', $campaign);
    }
    
    public function campaign_cancel($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->cancel();
        ActivityLog::log("Cancelled the \"".$campaign->name."\" campaign", "Campaign");
        return back()->with('success', 'Campaign was cancelled successfully');
    }
}
