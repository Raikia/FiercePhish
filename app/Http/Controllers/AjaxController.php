<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\TargetUser;
use App\TargetList;
use App\EmailTemplate;
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
        return Response::json("Success", 200);
    }
    
    public function edit_targetlist_notes(Request $request)
    {
        if (!$request->has('pk'))
            return Response::json("Invalid list: ", 400);
        $t = TargetList::findOrFail($request->input('pk'));
        $t->notes = $request->input('value');
        $t->save();
        return Response::json("Success", 200);
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
        $response = [$command => 'Failed'];
        if (empty($command) || empty($domain)) {
            return Response::json($response, 200);
        }
        $server_ip = Cache::remember('server_ip', 120, function() {
            return trim(file_get_contents('http://icanhazip.com/'));
        });
        
        // TEST CHANGE
        $server_ip = '70.114.211.123';
        // END TEST CHANGE
        
        if ($command == "a_record_primary")
        {
            $records = AjaxController::get_A_records($domain);
            if (is_array($records))
            {
                $response[$command] = 'An A record for "'.$domain.'" does not exist pointing to the IP '.$server_ip;
                foreach ($records as $host => $ips)
                {
                    if ($host == $domain && in_array($server_ip,$ips))
                    {
                        $response[$command] = 'Success';
                        break;
                    }
                }
            }
            else
            {
                $response[$command] = $records;
            }
        }
        elseif ($command == 'a_record_mail')
        {
            $records = AjaxController::get_A_records($domain);
            if (is_array($records))
            {
                $response[$command] = 'An A record for "mail.'.$domain.'" does not exist pointing to the IP '.$server_ip;
                foreach ($records as $host => $ips)
                {
                    if ($host == 'mail.'.$domain && in_array($server_ip, $ips))
                    {
                        $response[$command] = 'Success';
                        break;
                    }
                }
            }
            else
            {
                $response[$command] = $records;
            }
            
        }
        elseif ($command == 'mx_record')
        {
            $results = dns_get_record($domain, DNS_ALL);
            if (count($results) == 0)
                $response[$command] = 'Invalid domain';
            foreach ($results as $record)
            {
                if ($record['type'] == 'MX' && $record['host'] == $domain)
                {
                    $system = $record['target'];
                    $mx_lookup = AjaxController::get_A_records($system);
                    foreach ($mx_lookup as $host => $ips)
                    {
                        if ($host == $system && in_array($server_ip,$ips))
                        {
                            $response[$command] = 'Success';
                            break;
                        }
                    }
                    if ($response[$command] == 'Success')
                        break;
                }
            }
            if ($response[$command] == 'Failed')
            {
                $response[$command] = 'An MX record for "'.$domain.'" does not exist pointing to the IP '.$server_ip;
            }
        }
        elseif ($command == 'spf_record')
        {
            $results = AjaxController::get_TXT_records($domain);
            dd($results);
        }
        elseif ($command == 'dkim_record')
        {
        }
        return Response::json($response, 200);
    }
    
    
    
    
    
    
    // THESE FUNCTIONS SHOULD GET MOVED TO A LIBRARY!
    static function get_A_records($domain)
    {
        $results = dns_get_record($domain, DNS_ALL);
        if (count($results) == 0)
            return 'Invalid domain';
        $return = [];
        foreach ($results as $record)
        {
            if ($record['type'] == 'A')
            {
                if (!isset($return[$record['host']]))
                    $return[$record['host']] = [];
                $return[$record['host']][] = $record['ip'];
            }
        }
        return $return;
    }
    static function get_TXT_records($domain)
    {
        $results = dns_get_record($domain, DNS_ALL);
        if (count($results) == 0)
            return 'Invalid domain';
        $return = [];
        foreach ($results as $record)
        {
            if ($record['type'] == 'TXT')
            {
                if (!isset($return[$record['host']]))
                    $return[$record['host']] = [];
                $return[$record['host']][] = $record['txt'];
            }
        }
        return $return;
    }
    
    static function cidr_match($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
    
        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1) ) == ip2long($subnet))
        { 
            return true;
        }
    
        return false;
    }
}
