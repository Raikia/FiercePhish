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
        $query = TargetUser::query();
        if ($id != null)
            $query = TargetList::findOrFail($id)->availableUsers();
        $query = $query->with('lists');
        if ($request->input('search')['value'] != '')
        {
            $sqa = array_filter(explode(' ', $request->input('search')['value']));
            foreach ($sqa as $sq)
            {
                $query = $query->where(function ($query) use ($sq) {
                    $query->where('first_name', 'like', '%'.$sq.'%')->orWhere('last_name', 'like', '%'.$sq.'%')->orWhere('email', 'like', '%'.$sq.'%')->orWhere('notes', 'like', '%'.$sq.'%')->orWhereHas('lists', function($q) {
                        $q->where('name', 'like', '%'.request()->search['value'].'%');
                    });
                });
            }
        }
        $orders = $request->input('order') ?: [];
        foreach ($orders as $sort)
        {
            switch ($sort['column'])
            {
                case 0:
                    $query = $query->orderby('first_name', $sort['dir']);
                    break;
                case 1:
                    $query = $query->orderby('last_name', $sort['dir']);
                    break;
                case 2:
                    $query = $query->orderby('email', $sort['dir']);
                    break;
                case 4:
                    $query = $query->orderby('notes', $sort['dir']);
                    break;
            }
        }
        $filteredLength = $query->count();
        $start = (int)$request->input('start') ?: 0;
        $length = (int)$request->input('length') ?: 10;
        $query = $query->skip($start)->take($length);
        $data = $query->get();
        $ret = ['draw' => (int)$request->input('draw'),
                'recordsTotal' => TargetUser::count(),
                'recordsFiltered' => $filteredLength,
                'data' => [],
        ];
        foreach ($data as $user)
        {
            $lists = '<ul style="margin-bottom: 0px;">';
            foreach ($user->lists as $list)
            {
                $lists .= '<li>'.$list->name.'</li>';
            }
            if (count($user->lists) != 0)
            {
                $lists .= '</ul>';
            }
            else
            {
                $lists = 'None';
            }
            if ($user->notes == '')
            {
                $notes = '<a href="#" class="editnotes editable-empty" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">Empty</a>';
            }
            else
            {
                $notes = '<a href="#" class="editnotes" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">'.$user->notes.'</a>';
            }
            $ret['data'][] = ['0' => $user->first_name, '1' => $user->last_name, '2' => $user->email, '3' => $lists, '4' => $notes, 'DT_RowId' => 'row_'.$user->id];
        }
        return Response::json($ret, 200);
    }
    
    public function targetuser_membership(Request $request, $id)
    {
        $query = TargetList::findOrFail($id)->users();
        if ($request->input('search')['value'] != '')
        {
            $sqa = array_filter(explode(' ', $request->input('search')['value']));
            foreach ($sqa as $sq)
            {
                $query = $query->where(function ($query) use ($sq) {
                    $query->where('first_name', 'like', '%'.$sq.'%')->orWhere('last_name', 'like', '%'.$sq.'%')->orWhere('email', 'like', '%'.$sq.'%')->orWhere('notes', 'like', '%'.$sq.'%');
                });
            }
        }
        $orders = $request->input('order') ?: [];
        foreach ($orders as $sort)
        {
            switch ($sort['column'])
            {
                case 0:
                    $query = $query->orderby('first_name', $sort['dir']);
                    break;
                case 1:
                    $query = $query->orderby('last_name', $sort['dir']);
                    break;
                case 2:
                    $query = $query->orderby('email', $sort['dir']);
                    break;
                case 4:
                    $query = $query->orderby('notes', $sort['dir']);
                    break;
            }
        }
        $filteredLength = $query->count();
        $start = (int)$request->input('start') ?: 0;
        $length = (int)$request->input('length') ?: 10;
        $query = $query->skip($start)->take($length);
        $data = $query->get();
        $ret = ['draw' => (int)$request->input('draw'),
                'recordsTotal' => TargetList::findOrFail($id)->users()->count(),
                'recordsFiltered' => $filteredLength,
                'data' => [],
        ];
        foreach ($data as $user)
        {
            if ($user->notes == '')
            {
                $notes = '<a href="#" class="editnotes editable-empty" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">Empty</a>';
            }
            else
            {
                $notes = '<a href="#" class="editnotes" data-type="text" data-pk="'.$user->id.'" data-url="'.action('AjaxController@edit_targetuser_notes').'" data-title="Enter note">'.$user->notes.'</a>';
            }
            $ret['data'][] = ['0' => $user->first_name, '1' => $user->last_name, '2' => $user->email, '3' => $notes, 'DT_RowId' => 'row_'.$user->id];
        }
        return Response::json($ret, 200);
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
                                <span class="time">'.\Carbon\Carbon::createFromTimeStamp(strtotime($j->created_at))->diffForHumans().'</span>
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
        $campaign = Campaign::findOrFail($id);
        $query = $campaign->emails();
        if ($request->input('search')['value'] != '')
        {
            $sqa = array_filter(explode(' ', $request->input('search')['value']));
            foreach ($sqa as $sq)
            {
                $query = $query->where(function ($query) use ($sq) {
                    $query->where('receiver_name', 'like', '%'.$sq.'%')->orWhere('receiver_email', 'like', '%'.$sq.'%')->orWhere('uuid', 'like', '%'.$sq.'%');
                });
            }
        }
        $orders = $request->input('order') ?: [];
        foreach ($orders as $sort)
        {
            switch ($sort['column'])
            {
                case 0:
                    $query = $query->orderby('receiver_name', $sort['dir']);
                    break;
                case 1:
                    $query = $query->orderby('receiver_email', $sort['dir']);
                    break;
                case 2:
                    $query = $query->orderby('uuid', $sort['dir']);
                    break;
                case 3:
                    $query = $query->orderby('status', $sort['dir']);
                    break;
                case 4:
                    $query = $query->orderby('updated_at', $sort['dir']);
            }
        }
        $filteredLength = $query->count();
        $start = (int)$request->input('start') ?: 0;
        $length = (int)$request->input('length') ?: 10;
        $query = $query->skip($start)->take($length);
        $data = $query->get();
        $ret = ['draw' => (int)$request->input('draw'),
                'recordsTotal' => $campaign->emails()->count(),
                'recordsFiltered' => $filteredLength,
                'data' => [],
        ];
        foreach ($data as $email)
        {
            $ret['data'][] = ['0' => $email->receiver_name, '1' => $email->receiver_email, '2' => $email->uuid, '3' => $email->getStatus(), '4' => $email->updated_at.'', 'DT_RowId' => 'row_'.$email->id];
        }
        return Response::json($ret, 200);
    }
    
    
    public function email_log(Request $request)
    {
        $query = Email::with('campaign');
        if ($request->input('search')['value'] != '')
        {
            $sqa = array_filter(explode(' ', $request->input('search')['value']));
            foreach ($sqa as $sq)
            {
                $query = $query->where(function ($query) use ($sq) {
                    $query->where('receiver_name', 'like', '%'.$sq.'%')->orWhere('receiver_email', 'like', '%'.$sq.'%')->orWhere('sender_name', 'like', '%'.$sq.'%')->orWhere('sender_email', 'like', '%'.$sq.'%')->orWhere('subject', 'like', '%'.$sq.'%')->orWhere('uuid', 'like', '%'.$sq.'%')->orWhereHas('campaign', function($q) {
                        $q->where('name', 'like', '%'.request()->search['value'].'%');
                    });
                });
            }
        }
        $orders = $request->input('order') ?: [];
        foreach ($orders as $sort)
        {
            switch ($sort['column'])
            {
                case 0:
                    $query = $query->orderby('receiver_name', $sort['dir']);
                    break;
                case 1:
                    $query = $query->orderby('receiver_email', $sort['dir']);
                    break;
                case 2:
                    $query = $query->orderby('sender_name', $sort['dir']);
                    break;
                case 3:
                    $query = $query->orderby('sender_email', $sort['dir']);
                    break;
                case 4:
                    $query = $query->orderby('subject', $sort['dir']);
                    break;
                case 5:
                    $query = $query->orderby('uuid', $sort['dir']);
                    break;
                case 6:
                    $query = $query->orderby('status', $sort['dir']);
                    break;
                case 8:
                    $query = $query->orderby('created_at', $sort['dir']);
                    break;
                case 9:
                    $query = $query->orderby('updated_at', $sort['dir']);
                    break;
            }
        }
        $filteredLength = $query->count();
        $start = (int)$request->input('start') ?: 0;
        $length = (int)$request->input('length') ?: 10;
        $query = $query->skip($start)->take($length);
        $data = $query->get();
        $ret = ['draw' => (int)$request->input('draw'),
                'recordsTotal' => Email::count(),
                'recordsFiltered' => $filteredLength,
                'data' => [],
        ];
        foreach ($data as $email)
        {
            $camp = 'None';
            if ($email->campaign != null)
            {
                $camp = '<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>';
            }
            $ret['data'][] = ['0' => $email->receiver_name, '1' => $email->receiver_email, '2' => $email->sender_name, '3' => $email->sender_email, '4' => $email->subject, '5' => $email->uuid, '6' => $email->getStatus(), '7' => $camp, '8' => $email->created_at.'', '9' => $email->updated_at.'', 'DT_RowId' => 'row_'.$email->id];
        }
        return Response::json($ret, 200);
    }
    
}
