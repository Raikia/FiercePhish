<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use \Response;
use \ZipArchive;
use App\ActivityLog;

class LogController extends Controller
{
    
    private $logs_to_check;
    
    // Add activitylog here as well!
    public function __construct()
    {
        $this->middleware('auth');
        $this->logs_to_check = [
            'apache-access' => '/var/log/apache2/access_fiercephish.log', 
            'apache-error' => '/var/log/apache2/error_fiercephish.log',
            'mail' => '/var/log/mail.log', 
            'dovecot' => '/var/log/dovecot.log',
            'laravel' => base_path('storage/logs/laravel.log'),
        ];
    }
    
    public function index()
    {
        $logs = ActivityLog::orderby('id', 'desc')->take(200)->get();
        $activitylog_arr = [];
        foreach ($logs as $log)
        {
            $activitylog_arr[] = $log->read();
        }
        $activitylog = implode("\n", $activitylog_arr);
        return view('logs.index')->with('logs', $this->logs_to_check)->with('activitylog', $activitylog);
    }
    
    public function download($type)
    {
        $file_to_download = '';
        if (array_key_exists($type, $this->logs_to_check))
        {
            if (!is_readable($this->logs_to_check[$type]))
                return back()->withErrors('"'.$this->logs_to_check[$type].'" does not exist or has invalid permissions');
            return Response::download($this->logs_to_check[$type], $type.'.log');
        }
        elseif ($type == 'all')
        {
            $zip = new ZipArchive();
            $file = '/tmp/log_download.zip';
            if ($zip->open($file, ZipArchive::CREATE) != true)
            {
                return back()->withErrors("Unable to create ZIP file");
            }
            foreach ($this->logs_to_check as $name => $log)
            {
                if (is_readable($log))
                    $zip->addFile($log, $name.'.log');
            }
            // ActivityLog
            $logs = ActivityLog::orderby('id', 'desc')->get();
            $activitylog_arr = [];
            foreach ($logs as $log)
            {
                $activitylog_arr[] = $log->read();
            }
            $activitylog = implode("\n", $activitylog_arr);
            $zip->addFromString('activitylog.log', $activitylog);
            $zip->close();
            $resp = Response::make(file_get_contents($file), '200', [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="fiercephish_logs.bundle.zip"',
                'Content-Length' => filesize($file),
            ]);
            unlink($file);
            return $resp;
        }
        elseif ($type == 'activitylog')
        {
            $logs = ActivityLog::orderby('id', 'desc')->get();
            $activitylog_arr = [];
            foreach ($logs as $log)
            {
                $activitylog_arr[] = $log->read();
            }
            $activitylog = implode("\n", $activitylog_arr);
            return Response::make($activitylog, '200', [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="activitylog.log"',
            ]);
        }
        else 
        {
            return abort(404);
        }
    }
}
