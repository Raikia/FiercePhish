<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\TargetUser;
use App\TargetList;
use App\EmailTemplate;
use App\ActivityLog;
use App\Libraries\DomainTools;
use App\Campaign;
use App\Email;
use Response;
use Cache;
use App\ReceivedMail;
use App\ReceivedMailAttachment;
use Datatables;

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
        ActivityLog::log('Edited the note of "'.$t->email.'"', 'Target User');
        return Response::json("Success", 200);
    }
    
    public function edit_targetlist_notes(Request $request)
    {
        if (!$request->has('pk'))
            return Response::json("Invalid list: ", 400);
        $t = TargetList::findOrFail($request->input('pk'));
        $t->notes = $request->input('value');
        $t->save();
        ActivityLog::log('Edited the note of "'.$t->name.'"', 'Target List');
        return Response::json("Success", 200);
    }
    
    public function targetuser_list(Request $request, $id=null)
    {
        if ($id === null)
        {
            return Datatables::of(TargetUser::where('hidden', false))->addColumn('list_of_membership', function ($user) {
                    if (count($user->lists) == 0)
                        return 'None';
                    $lists = '<ul style="margin-bottom: 0px;">';
                    foreach ($user->lists as $list)
                        $lists .= '<li>'.$list->name.'</li>';
                    $lists .= '</ul>';
                    return $lists;
                })->editColumn('notes', function($user) {
                    if ($user->notes == '')
                    {
                        $notes = '<a href="#" class="editnotes editable-empty" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">Empty</a>';
                    }
                    else
                    {
                        $notes = '<a href="#" class="editnotes" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">'.$user->notes.'</a>';
                    }
                    return $notes;
                })->make(true);
        }
        else
        {
            return Datatables::of(TargetList::findOrFail($id)->availableUsers())->setRowId('row_{{ $id }}')->addColumn('list_of_membership', function ($user) {
                    if (count($user->lists) == 0)
                        return 'None';
                    $lists = '<ul style="margin-bottom: 0px;">';
                    foreach ($user->lists as $list)
                        $lists .= '<li>'.$list->name.'</li>';
                    $lists .= '</ul>';
                    return $lists;
                })->editColumn('notes', function($user) {
                    if ($user->notes == '')
                    {
                        $notes = '<a href="#" class="editnotes editable-empty" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">Empty</a>';
                    }
                    else
                    {
                        $notes = '<a href="#" class="editnotes" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">'.$user->notes.'</a>';
                    }
                    return $notes;
                })->make(true);
        }
    }
    
    public function targetuser_membership(Request $request, $id)
    {
        return Datatables::of(TargetList::findOrFail($id)->users())->editColumn('notes', function($user) {
                    if ($user->notes == '')
                    {
                        $notes = '<a href="#" class="editnotes editable-empty" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">Empty</a>';
                    }
                    else
                    {
                        $notes = '<a href="#" class="editnotes" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">'.$user->notes.'</a>';
                    }
                    return $notes;
                })->make(true);
    }
    
    public function get_emailtemplate_info(Request $request, $id='')
    {
        if ($id === '')
            return Response::json("Invalid ID", 400);
        $template = EmailTemplate::findOrFail($id);
        return Response::json($template, 200);
    }
    
    
    
    
    public function email_check_commands (Request $request, $command='', $domain='')
    {
        $response = [$command => false, 'command' => $command, 'message' => ''];
        if (empty($command) || empty($domain)) {
            return Response::json($response, 200);
        }
        $server_ip = DomainTools::getServerIP();
        
        // TEST CHANGE
        //$server_ip = '162.243.4.171'; // '70.114.211.123';
        // END TEST CHANGE
        
        if ($command == "a_record_primary")
        {
            $resp = '';
            $response[$command] = DomainTools::is_IP_an_A_record($domain, $server_ip, $domain, $resp);
            $response['message'] = $resp;
            
        }
        elseif ($command == 'a_record_mail')
        {
            $resp = '';
            $response[$command] = DomainTools::is_IP_an_A_record('mail.'.$domain, $server_ip, 'mail.'.$domain, $resp);
            $response['message'] = $resp;
        }
        elseif ($command == 'mx_record')
        {
            $resp = '';
            $response[$command] = DomainTools::is_IP_an_MX_record($domain, $server_ip, $resp);
            $response['message'] = $resp;
        }
        elseif ($command == 'spf_record')
        {
            $resp = '';
            $response[$command] = DomainTools::is_IP_in_SPF_record($domain, $server_ip, $resp);
            $response['message'] = $resp;
        }
        return Response::json($response, 200);
    }
    
    
    
    public function get_activitylog($id="-1")
    {
        $logs = ActivityLog::orderby('id', 'desc')->where('id', '>', $id)->get();
        $strings = [];
        foreach ($logs as $log)
        {
            $strings[] = $log->read();
        }
        $ret = ['latest_id' => $id, 'data' => $strings];
        if ($logs->count() > 0)
            $ret['latest_id'] = $logs[0]->id;
        return Response::json($ret, 200);
    }
    
    
    public function get_jobs()
    {
        $all_jobs = \App\ProcessingJob::orderby('created_at', 'asc')->get();
        $all_strs = ['html' => ''];
        foreach ($all_jobs as $j)
        {
            $desc = '';
            if ($j->description != '')
                $desc = '<div style="margin-left: 23px;">'.$j->description.'</div>';
            $all_strs['html'] .= '<li>
                            <a>
                              <span class="image"><i class="fa fa-'.$j->icon.'"></i></span>
                              <span>
                                <span style="margin-left: 5px;">'.$j->name.'</span>
                                <span class="time">'.\App\Libraries\DateHelper::relative($j->created_at).'</span>
                              </span>
                              <span class="message">
                               '.$desc.'
                               <div class="progress" style="margin-top: 7px;">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$j->progress.'" aria-valuemin="0" aria-valuemax="100" style="background-color: #FF4800; min-width: 2em; width: '.$j->progress.'%;">
                                  '.$j->progress.'%
                                </div>
                              </div>
                              </span>
                            </a>
                     </li>';
        }
        if ($all_strs['html'] == '')
            $all_strs['html'] = '<li>No running jobs</li>';
        $all_strs['num'] = count($all_jobs);
        return Response::json($all_strs, 200);
    }
    
    public function campaign_emails_get(Request $request, $id)
    {
        return Datatables::of(Campaign::findorfail($id)->emails()->with('targetuser'))->setRowId('row_{{ $id }}')->editColumn('status', function ($email) {
                return $email->getStatus();
            })->make(true);
    }
    
    
    public function email_log(Request $request)
    {
        return Datatables::of(Email::with('campaign', 'targetuser'))->setRowId('row_{{ $id }}')->editColumn('status', function ($email) {
            return $email->getStatus();
        })->editColumn('campaign.name', function($email) {
            if ($email->campaign !== null)
                return '<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>';
            else
                return 'None';
        })->editColumn('targetuser.full_name', function ($email) {
            return $email->targetuser->full_name();
        })->editColumn('sent_time', function ($email) {
            return \App\Libraries\DateHelper::print($email->sent_time);
        })->editColumn('created_at', function($email) {
            return \App\Libraries\DateHelper::print($email->created_at);
        })->filterColumn('sent_time', function($query, $keyword) {
            $query->whereRaw('CAST(CONVERT_TZ(sent_time, "+00:00", "'.\App\Libraries\DateHelper::getOffset(config('fiercephish.APP_TIMEZONE')).'") as char) like ?', ["%{$keyword}%"]);
        })->filterColumn('created_at', function($query, $keyword) {
            $query->whereRaw('CAST(CONVERT_TZ(created_at, "+00:00", "'.\App\Libraries\DateHelper::getOffset(config('fiercephish.APP_TIMEZONE')).'") as char) like ?', ["%{$keyword}%"]);
        })->filterColumn('targetuser.full_name', function ($query, $keyword) {
            $query->whereRaw("(select count(1) from target_users where target_users.id = target_user_id and CONCAT(target_users.first_name,' ',target_users.last_name) like ?) >= 1", ["%{$keyword}%"]);
        })->filterColumn('campaign.name', function ($query, $keyword) {
            $query->whereRaw("(select count(1) from campaigns where campaigns.id = campaign_id and campaigns.name like ?) >= 1", ["%{$keyword}%"]);
        })->orderColumn('targetuser.full_name', 'target_users.first_name $1')->make(true);
    }
    
    
    public function get_inbox_messages($id='')
    {
        $ret = [];
        if ($id === '')
        {
            $all_mails = ReceivedMail::with('attachment_count')->orderby('received_date', 'desc')->select(['id', 'subject', 'received_date', 'sender_name', 'seen', 'replied', 'forwarded', \DB::raw("SUBSTRING(`message`,1,80) as sub_msg")])->get();
            for ($x=0; $x < count($all_mails); ++$x)
            {
                $all_mails[$x]->subject = e($all_mails[$x]->subject);
                $all_mails[$x]->sender_name = e($all_mails[$x]->sender_name);
                $all_mails[$x]->sub_msg = e($all_mails[$x]->sub_msg);
            }
            $ret['data'] = $all_mails;
        }
        else
        {
            $message = ReceivedMail::with('attachments')->findOrFail($id);
            if (!$message->seen)
            {
                $message->seen = true;
                $message->save();
            }
            $message->sender_name = e($message->sender_name);
            $message->sender_email = e($message->sender_email);
            $message->replyto_name = e($message->replyto_name);
            $message->replyto_email = e($message->replyto_email);
            $message->receiver_name = e($message->receiver_name);
            $message->receiver_email = e($message->receiver_email);
            $message->subject = e($message->subject);
            $message->message = e($message->message);
            
            for ($x=0; $x < $message->attachments()->count(); ++$x)
            {
                $message->attachments[$x]->name = e($message->attachments[$x]->name);
            }
            
            $ret['data'] = $message;
        }
        
        
        return Response::json($ret, 200);
    }
    
    public function get_num_new_messages()
    {
        $ret = ['data' => ReceivedMail::where('seen', false)->count()];
        return Response::json($ret, 200);
    }
    
    public function delete_inbox_message($id='')
    {
        $ret = ['data' => true];
        $mail = ReceivedMail::findOrFail($id);
        $mail->delete();
        return Response::json($ret, 200);
    }
    
}
