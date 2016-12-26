<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public static function log($msg, $type="General")
    {
    	if (empty($msg))
    		return;
    	$a = new ActivityLog();
    	$a->log = $msg;
    	$a->type = $type;
    	$a->is_error = false;
    	if (Auth::check())
    		$a->user_id = Auth::user()->id;
    	else
    		$a->user_id = null;
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
    	if ($this->is_error)
    		$ret_text .= '!!!! ERROR !!!! - ';
    	$ret_text .= '['.$this->type.'] ';
    	$ret_text .= $this->log;
    	$username = '  (Unknown User)';
    	if ($this->user_id == null)
    		$username = '';
    	else
    	{
    		$usr = User::find($this->user_id);
    		if ($usr != null)
    			$username = '  ('.$usr->name.')';
    	}
    	$ret_text .= $username;
    	return $ret_text;
    }
}
