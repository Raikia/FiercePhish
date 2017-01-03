<?php

namespace App;

use Auth;
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
        $ret_text = '['.$this->created_at->format('m/d/Y - H:i:s').'] ';
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
}
