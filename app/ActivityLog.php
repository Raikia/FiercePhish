<?php

namespace App;

use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public static function log($msg, $type="General", $error=false)
    {
    	if (empty($msg))
    		return;
    	$a = new ActivityLog();
    	$a->log = $msg;
    	$a->type = $type;
    	$a->is_error = $error;
    	if (Auth::check())
    		$a->user = Auth::user()->name;
    	else
    		$a->user = null;
    	$a->save();
    	return $a;
    }

    public static function fetch()
    {
        return ActivityLog::orderby('id', 'desc')->get();
    }

    public function set_ref_id($id)
    {
    	$this->ref_id = $id;
    	$this->save();
    	return $this;
    }

    public function set_ref_text($text)
    {
    	$this->ref_text = $text;
    	$this->save();
    	return $this;
    }

    public function set_error($bool)
    {
    	$this->is_error = $bool;
    	$this->save();
    	return $this;
    }

    public function read()
    {
    	$ret_text = '';
        $ret_text = '['.\App\Libraries\DateHelper::format($this->created_at, 'm/d/Y - H:i:s').'] ';
    	if ($this->is_error)
    		$ret_text .= '!!!! ERROR !!!! - ';
    	$ret_text .= '{'.$this->type.'} ';
    	$ret_text .= $this->log;
    	$username = '';
    	if ($this->user != null)
    	{
            $username = '  ('.$this->user.')';
    	}
    	$ret_text .= $username;
    	return $ret_text;
    }
    
    public static function getJobList()
    {
        $all_jobs = \DB::table('jobs')->orderby('available_at', 'asc')->where('queue', '!=', 'campaign_email')->get();
        $all_strs = ['html' => ''];
        foreach ($all_jobs as $raw_job)
        {
            $j = unserialize(json_decode($raw_job->payload)->data->command);
            $desc = '';
            if ($j->description != '')
                $desc = '<div style="margin-left: 23px;">'.e($j->description).'</div>';
            $all_strs['html'] .= '<li>
                            <a>
                              <span class="image"><i class="fa fa-'.$j->icon.'"></i></span>
                              <span>
                                <span style="margin-left: 5px;">'.e($j->title).'</span>
                                <span class="time">'.\App\Libraries\DateHelper::relative(Carbon::createFromTimestamp($raw_job->available_at)).'</span>
                              </span>
                              <span class="message">
                               '.$desc.'
                               <div class="progress" style="margin-top: 7px;">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$j->getProgress().'" aria-valuemin="0" aria-valuemax="100" style="background-color: #FF4800; min-width: 2em; width: '.$j->getProgress().'%;">
                                  '.$j->getProgress().'%
                                </div>
                              </div>
                              </span>
                            </a>
                     </li>';
        }
        if ($all_strs['html'] == '')
            $all_strs['html'] = '<li>No running jobs</li>';
        $all_strs['num'] = count($all_jobs);
        return $all_strs;
    }
}
