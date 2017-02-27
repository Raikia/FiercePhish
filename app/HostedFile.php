<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HostedFile extends Model
{
    const DISABLED = 0;
    const SERVE = 1;
    const PARSE = 2;
    const DOWNLOAD = 3;
    
    const INVALID_ALLOW = 50;
    const INVALID_DENY = 51;
    const INVALID_DISABLE = 52;
    
    public function serve($vars)
    {
        $path = storage_path('app/'.$this->local_path);
        if ($this->action == HostedFile::DISABLED)
            abort(404);
        elseif ($this->action == HostedFile::SERVE)
        {
            header("Content-Type: " . $this->file_mime);
            echo \File::get($path);
            return;
        }
        elseif ($this->action == HostedFile::PARSE)
        {
            $code = \File::get(storage_path('app/'.$this->local_path));
            eval('?> '.$code.' <?php');
            return;
        }
        elseif ($this->action == HostedFile::DOWNLOAD)
        {
            header("Content-Type: application/octet-stream");
            header('Content-Disposition: attachment; filename="'.$this->file_name.'"');
            echo \File::get($path);
            return;
        }
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
        return $this->route . '/' . $this->file_name;
    }
    
    public function views()
    {
        return $this->hasMany('App\HostedFileView');
    }
    
    public function logVisit(Request $request)
    {
        $visit = new HostedFileView();
        $visit->hosted_file_id = $this->id;
        $visit->ip = $request->ip();
        $visit->alert = $this->notify_access;
        $disable = false;
        if ($this->uidvar != null) // if uid tracker variable enabled
        {
            if ($request->has($this->uidvar)) // if it has the uid tracker variable
            {
                $email = Email::where('uuid', $request->input($this->uidvar))->first();
                if ($email !== null) // if the uid is valid
                {
                    $visit->uuid = $email->uuid;
                }
                else
                {
                    if ($this->alert_invalid)
                    {
                        $visit->alert = true; // Invalid uuid
                    }
                    if ($this->disable_invalid)
                    {
                        $disable = true;
                    }
                }
            }
            else
            {
                if ($this->alert_invalid)
                {
                    $visit->alert = true; // no uuid
                }
                if ($this->disable_invalid)
                {
                    $disable = true;
                }
            }
        }
        if ($this->kill_switch !== null && $this->views()->count() >= $this->kill_switch)
            $disable = true;
        if ($disable)
        {
            $this->action = HostedFile::DISABLED;
            $this->save();
        }
        $visit->save();
    }
    
    public static function getActions()
    {
        return [
            HostedFile::DISABLED    => 'Disabled',
            HostedFile::SERVE       => 'Serve file',
            HostedFile::PARSE       => 'Parse as PHP',
            HostedFile::DOWNLOAD    => 'Force Download',
        ];
    }
    
    public static function getInvalidActions()
    {
        return [
            
        ];
    }
    
    public static function path_already_exists($path)
    {
        $all_routes = \Route::getRoutes();
        $matched = $all_routes->match(\Request::create($path));
        if ($matched != null && $matched->uri == '{catchall}')
        {
            if (HostedFile::grab($path) === null)
                return false;
            return true;
        }
        else
        {
            return true;
        }
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
