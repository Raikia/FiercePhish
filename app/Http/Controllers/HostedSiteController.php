<?php

namespace App\Http\Controllers;

use App\HostedFile;
use App\HostedSite;
use Illuminate\Http\Request;

class HostedSiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $allsites = HostedSite::all();
        
        return view('sites.index', ['allsites' => $allsites]);
    }
    
    public function addsite(Request $request)
    {
        $this->validate($request, [
            'attachment' => 'required|file|mimes:zip',
            'name' => 'required|unique:hosted_sites',
        ]);
        $file = $request->file('attachment');
        $za = new \ZipArchive();
        $za->open($file->getRealPath());
        $config_contents = $za->getFromName('package.fiercephish');
        if ($config_contents === false) {
            return back()->withErrors('No "package.fiercephish" file found! Make sure it is in the root of the ZIP file');
        }
        $config = @yaml_parse($config_contents);
        if ($config === false) {
            return back()->withErrors('Invalid yaml format for "package.fiercephish"!');
        }
        $site = new HostedSite();
        $site->name = $request->input('name');
        $site->package_name = $config['name'] ?? 'N/A';
        $site->package_author = $config['author'] ?? 'N/A';
        $site->package_email = $config['email'] ?? 'N/A';
        $site->package_url = $config['url'] ?? 'N/A';
        $site->package_tracker = $config['uid tracker'] ?? '';
        $site->route = $request->input('path');
        $site->save();
        for ($i = 0; $i < $za->numFiles; $i++) {
                /*$table->string('local_path');
                $table->string('route');
                $table->string('original_file_name');
                $table->string('file_name');
                $table->string('file_mime');
                $table->tinyInteger('action')->default(0);
                $table->integer('kill_switch')->nullable();
                $table->string('uidvar')->nullable();
                $table->tinyInteger('invalid_action')->default(50);
                $table->boolean('notify_access')->default(false);
                $table->integer('hosted_site_id')->nullable(); */
            dd("break");
            $fileinfo = $za->statIndex($i);
            if (substr($fileinfo['name'], -1) === '/') {  // Skip if its just a folder
                continue;
            }
            $file = new HostedFile();
            $filename = 'hosted/'.sha1(time().''.rand()).'.dat';
            $fo = fopen(storage_path('app/'.$filename), 'w');
            fwrite($fo, $za->getFromIndex($i));
            fclose($fo);
            $file->local_path = $filename;
            $pathinfo = pathinfo(HostedSite::getConfigPath($config, $fileinfo['name']));
            dd($pathinfo);
            $dirname = '';
            if (isset($pathinfo['dirname']) && $pathinfo['dirname'] != '.') {
                $dirname = $pathinfo['dirname'];
            }
            $file->route = $dirname;
            $file->file_name = $pathinfo['basename'];
            $file->original_file_name = $fileinfo['name'];
            $file->file_mime = mime_content_type(storage_path('app/'.$filename));
            $file->kill_switch = null;
            $file->action = 0;
            
            echo "index: $i\n";
            $fileinfo = $za->statIndex($i);
            print_r($fileinfo);
            print_r($za->getFromName($fileinfo['name']));
            
        }
        echo "numFile:" . $za->numFiles . "\n";
        
    }
    
    public function deletesite(Request $request)
    {
        
    }
}
