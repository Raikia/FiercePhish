<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\HostedFileView;
use App\Notifications\HostedFileVisited;

class HostedFile extends Model
{
    const DISABLED = 0;
    const SERVE = 1;
    const PARSE = 2;
    const DOWNLOAD = 3;
    
    const INVALID_ALLOW = 50;
    const INVALID_DENY = 51;
    const INVALID_DISABLE = 52;
    
    public function serve($request)
    {
        $vars = $request->all();
        $path = storage_path('app/'.$this->local_path);
        $continue = $this->logVisit($request);
        if ($this->action == HostedFile::SERVE && $continue)
        {
            header("Content-Type: " . $this->file_mime);
            echo \File::get($path);
        }
        elseif ($this->action == HostedFile::PARSE && $continue)
        {
            $code = \File::get(storage_path('app/'.$this->local_path));
            eval('?> '.$code.' <?php');
        }
        elseif ($this->action == HostedFile::DOWNLOAD && $continue)
        {
            header("Content-Type: application/octet-stream");
            header('Content-Disposition: attachment; filename="'.$this->file_name.'"');
            echo \File::get($path);
        }
        else
        {
            abort(404);
        }
        return;
    }
    
    public function getAction()
    {
        switch ($this->action)
        {
            case HostedFile::DISABLED:
                return "Disabled";
            case HostedFile::SERVE:
                return "Serve";
            case HostedFile::PARSE:
                return "Parse as PHP";
            case HostedFile::DOWNLOAD:
                return "Force Download";
        }
        return "Unknown";
    }
    
    public function getInvalidAction()
    {
        switch ($this->invalid_action)
        {
            case HostedFile::INVALID_ALLOW:
                return "Allow";
            case HostedFile::INVALID_DENY:
                return "Deny";
            case HostedFile::INVALID_DISABLE:
                return "Disable";
        }
        return "Unknown";
    }
    
    public function getPath()
    {
        $str = str_replace('//', '/', '/' . $this->route . '/' . $this->file_name);
        return $str;
    }
    
    public function getPathWithVar()
    {
        $str = $this->getPath();
        if ($this->uidvar != null)
            $str .= '?'.$this->uidvar.'=fiercephishtest';
        return $str;
    }
    
    public function views()
    {
        return $this->hasMany('App\HostedFileView');
    }
    
    public function logVisit(Request $request) 
    {
        // Don't log if its in "emails/templates" or "emails/log" or "emails/simple" because that's us setting up the campaign (this could be done probably more exact)
        if (strpos($request->header('Referer'), 'emails/templates') !== false || strpos($request->header('Referer'), 'emails/log') !== false || strpos($request->header('Referer'), 'emails/simple') !== false)
        {
            return true;
        }
        $visit = new HostedFileView();
        $visit->hosted_file_id = $this->id;
        $visit->ip = $request->ip();
        // Detect browser
        $bc = new \BrowscapPHP\Browscap();
    	$adapter = new \WurflCache\Adapter\File([\WurflCache\Adapter\File::DIR => storage_path('browscap_cache')]);
    	$bc->setCache($adapter);
    	$result = $bc->getBrowser($request->header('User-Agent'));
    	$visit->useragent = $request->header('User-Agent');
    	$visit->referer = $request->header('Referer');
    	$visit->browser = $result->browser;
    	$visit->browser_version = $result->version;
    	$visit->browser_maker = $result->browser_maker;
    	$visit->platform = $result->platform;
    	
    	$invalid_tracker = false;
    	$continue = true;
    	
    	if ($this->uidvar != null) // if uid tracker variable is enabled
    	{
    	    if ($request->has($this->uidvar) && ($email = Email::where('uuid', $request->input($this->uidvar))->first()) !== null) // if it has the uid tracker variable
    	    {
	            $visit->uuid = $email->uuid;
    	    }
    	    else
    	    {
    	        $invalid_tracker = true;
	            if ($this->invalid_action === HostedFile::INVALID_DENY)
	            {
	                $continue = false; // Send 404
	            }
	            elseif ($this->invalid_action === HostedFile::INVALID_DISABLE)
	            {
	                $continue = false; // Send 404 and disable
	                $this->action = HostedFile::DISABLED;
	                $this->save();
	            }
    	    }
    	}
    	if ($this->action !== HostedFile::DISABLED && $this->kill_switch !== null && $this->views()->count() >= $this->kill_switch) // Disable it if max number of requests is reached
    	{
            $continue = false;
            $this->action = HostedFile::DISABLED;
            $this->save();
    	}
    	if ($this->notify_access) // Notify people
    	{
    	    $users = User::getNotifiable();
    	    foreach ($users as $user)
    	    {
    	        $user->notify(new HostedFileVisited($visit, $invalid_tracker));
    	    }
    	}
    	$visit->save();
    	return $continue;
    }
    
    public static function getActions()
    {
        return [
            HostedFile::SERVE       => 'Serve file',
            HostedFile::PARSE       => 'Parse as PHP',
            HostedFile::DOWNLOAD    => 'Force Download',
            HostedFile::DISABLED    => 'Disabled',
        ];
    }
    
    public static function getInvalidActions()
    {
        return [
            HostedFile::INVALID_ALLOW   => 'Allow invalid tracker',
            HostedFile::INVALID_DENY    => '404 on invalid tracker',
            HostedFile::INVALID_DISABLE => 'Disable on invalid tracker',
        ];
    }
    
    public static function path_already_exists($path)
    {
        $all_routes = \Route::getRoutes();
        $matched = $all_routes->match(\Request::create($path));
        if ($matched != null && $matched->uri == '{catchall}')
        {
            if (HostedFile::grab($path) === null)
            {
                if (!file_exists(public_path($path)))
                {
                    return false;
                }
            }
        }
        return true;
    }
    
    public static function grab($path)
    {
        $pathinfo = pathinfo($path);
        $dirname = '';
        if (isset($pathinfo['dirname']) && $pathinfo['dirname'] != '.')
            $dirname = $pathinfo['dirname'];
        return HostedFile::where('route', $dirname)->where('file_name', $pathinfo['basename'])->first();
    }
}

