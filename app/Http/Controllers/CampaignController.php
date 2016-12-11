<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\EmailTemplate;
use App\TargetList;
use App\Campaign;

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
    
    public function create_post(Request $request)
    {
        // Array ( [campaign_name] => efWEF [campaign_description] => wefWEF [email_template] => 1 [target_list] => 2 [sender_name] => wef [sender_email] => WEF [sending_schedule] => all [send_num] => 231234 [send_every_x_minutes] => 32523 [starting_date] => 12/10/2016 [starting_time] => 12:48am [_token] => a9e7Gf79PBAdkOV30CeViAM0fe9b95OcZGHu9roR )
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
        $campaign->state = Campaign::NOT_STARTED;
        $campaign->target_list_id = $request->input('target_list');
        $campaign->email_template_id = $request->input('email_template_id');
        //$campaign->save();
        $start_date = $request->input('starting_date') ?: date('m/d/Y');
        $start_time = $request->input('starting_time') ?: date('g:ia');
        $seconds_offset_start = max(strtotime($start_date . " " . $start_time) - time(), 1);
        echo "STARTING OFFSET: " . $seconds_offset_start . "<br />";
        $send_all_immediately = false;
        if ($request->input('sending_schedule') == 'all' || empty($request->input('send_num')) || empty($request->input('send_every_x_minutes')))
        {
            $send_all_immediately = true;
        }
        $send_num_emails = min((int)$request->input('send_num'),1000);
        $send_every_minutes = min((int)$request->input('send_every_x_minutes'), 1000);
        $counter = 0;
        $original_send_num_emails = $send_num_emails;
        foreach ($list->users as $user)
        {
            $new_email = $template->craft_email($campaign, $user);
            if ($send_all_immediately)
            {
                $new_email->send($seconds_offset_start);
            }
            else 
            {
                
                $new_email->send($seconds_offset_start + ($counter * ($send_every_minutes*60)), 'medium');
                --$send_num_emails;
                if ($send_num_emails == 0)
                {
                    $send_num_emails = $original_send_num_emails;
                    ++$counter;
                }
            }
        }
        // Change this to be redirect to the running campaign once that page is functional
        return back()->with('success', 'Campaign has been launched successfully');
    }
}
