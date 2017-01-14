<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use \Response;
use \ZipArchive;

class LogController extends Controller
{
    
    private $logs_to_check;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->logs_to_check = [
            'apache-access' => '/var/log/apache2/access.log', 
            'apache-error' => '/var/log/apache2/error.log',
            'mail' => '/var/log/mail.log', 
            'dovecot' => '/var/log/dovecot.log',
            'laravel' => base_path('storage/logs/laravel.log'),
        ];
    }
    
    public function index()
    {
        return view('logs.index')->with('logs', $this->logs_to_check);
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
            $zip->close();
            $resp = Response::make(file_get_contents($file), '200', [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="fiercephish_logbundle.zip"'
            ]);
            unlink($file);
            return $resp;
        }
        else 
        {
            return abort(404);
        }
    }
}
