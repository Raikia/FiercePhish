<?php

namespace App;

use App\Notifications\HostedFileVisited;
use Illuminate\Database\Eloquent\Model;

class HostedFile extends Model
{
    protected $fillable = ['path', 'file_data', 'file_name', 'file_mime', 'action', 'hosted_site_id', 'uidvar'];
    
    const DISABLED = 0;
    const SERVE = 1;
    const PARSE = 2;
    const DOWNLOAD = 3;
    const INVALID_ALLOW = 50;
    const INVALID_DENY = 51;
    const INVALID_DISABLE = 52;
    
    public function site()
    {
        return $this->belongsTo('App\HostedSite');
    }
    
    public function serve($request)
    {
        $vars = $request->all();
        $path = storage_path('app/'.$this->local_path);
        $continue = $this->logVisit($request);
        if ($this->action == self::SERVE && $continue) {
            header('Content-Type: '.$this->file_mime);
            echo \File::get($path);
        } elseif ($this->action == self::PARSE && $continue) {
            $code = \File::get(storage_path('app/'.$this->local_path));
            eval('?> '.$code.' <?php');
        } elseif ($this->action == self::DOWNLOAD && $continue) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$this->file_name.'"');
            echo \File::get($path);
        } else {
            abort(404);
        }
    }
    
    public function getAction()
    {
        switch ($this->action) {
            case self::DISABLED:
                return 'Disabled';
            case self::SERVE:
                return 'Serve';
            case self::PARSE:
                return 'Parse as PHP';
            case self::DOWNLOAD:
                return 'Force Download';
        }
        
        return 'Unknown';
    }
    
    public function getInvalidAction()
    {
        switch ($this->invalid_action) {
            case self::INVALID_ALLOW:
                return 'Allow';
            case self::INVALID_DENY:
                return 'Deny';
            case self::INVALID_DISABLE:
                return 'Disable';
        }
        
        return 'Unknown';
    }
    
    public function getPath()
    {
        $str = str_replace('//', '/', '/'.$this->route.'/'.$this->file_name);
        
        return $str;
    }
    
    public function getPathWithVar()
    {
        $str = $this->getPath();
        if ($this->uidvar != null) {
            $str .= '?'.$this->uidvar.'=[uid]';
        }
        
        return $str;
    }
    
    public function getFullPath()
    {
        $str = $this->getPathWithVar();
        
        return \Request::root().$str;
    }
    
    public function views()
    {
        return $this->hasMany('App\HostedFileView');
    }
    
    public function logVisit()
    {
        $request = \Request::instance(); // Not type hinting in the arguments for later user scripting purposes
        if (\Auth::check()) { // Don't log the view if the user is logged in to FiercePhish
            return true;
        }
        $visit = new HostedFileView();
        $visit->hosted_file_id = $this->id;
        $visit->ip = $request->ip();
        $visit->referer = $request->header('Referer');
        $visit->detectBrowser($request->header('User-Agent'));
        $invalid_tracker = false;
        $continue = true;
        if ($this->uidvar != null) { // if uid tracker variable is enabled
            if ($request->has($this->uidvar) && ($email = Email::where('uuid', $request->input($this->uidvar))->first()) !== null) { // if it has the uid tracker variable
                $visit->uuid = $email->uuid;
            } else {
                $invalid_tracker = true;
                if ($this->invalid_action === self::INVALID_DENY) {
                    $continue = false; // Send 404
                } elseif ($this->invalid_action === self::INVALID_DISABLE) {
                    $continue = false; // Send 404 and disable
                    $this->action = self::DISABLED;
                    $this->save();
                }
            }
        }
        if ($this->action !== self::DISABLED && $this->kill_switch !== null && $this->views()->count() >= $this->kill_switch) { // Disable it if max number of requests is reached
            $continue = false;
            $this->action = self::DISABLED;
            $this->save();
        }
        if ($this->notify_access) { // Notify people
            $users = User::getNotifiable();
            foreach ($users as $user) {
                $user->notify(new HostedFileVisited($visit, $invalid_tracker));
            }
        }
        $visit->save();
        
        return $continue;
    }
    
    public function deleteFile() {
        unlink(storage_path('app/'.$this->local_path));
        foreach ($this->views as $view) {
            $view->delete();
        }
        $this->delete();
    }
    
    public static function getActions()
    {
        return [
            self::SERVE       => 'Serve file',
            self::PARSE       => 'Parse as PHP',
            self::DOWNLOAD    => 'Force Download',
            self::DISABLED    => 'Disabled',
        ];
    }
    
    public static function getInvalidActions()
    {
        return [
            self::INVALID_ALLOW   => 'Allow invalid tracker',
            self::INVALID_DENY    => '404 on invalid tracker',
            self::INVALID_DISABLE => 'Disable on invalid tracker',
        ];
    }
    
    public static function path_already_exists($path)
    {
        $all_routes = \Route::getRoutes();
        $matched = $all_routes->match(\Request::create($path));
        if ($matched != null && $matched->uri == '{catchall}') {
            if (self::grab($path) === null) {
                if (! file_exists(public_path($path))) {
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
        if (isset($pathinfo['dirname']) && $pathinfo['dirname'] != '.') {
            $dirname = $pathinfo['dirname'];
        }
        
        return self::where('route', $dirname)->where('file_name', $pathinfo['basename'])->first();
    }
    
    public static function generateFilename()
    {
        return sha1(time().''.rand()).'.dat';
    }
}
