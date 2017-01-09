<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\TargetUser;
use App\TargetList;
use App\EmailTemplate;
use App\ActivityLog;
use App\Libraries\DomainTools;
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
    
    public function targetuser_list(Request $request)
    {
        $query = TargetUser::query();
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
            $lists = '<ul>';
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
            $ret['data'][] = [$user->first_name, $user->last_name, $user->email, $lists, $notes];
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
    

}
