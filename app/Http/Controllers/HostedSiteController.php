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
        $site->route = '';
        if ($request->has('path')) {
            $site->route = $request->input('path');
        }
        $site->entry_file_id = null;
        // We do a loop first to check if any files have routes already taken before we save anything
        $errors = [];
        for ($i = 0; $i < $za->numFiles; ++$i) {
            $fileinfo = $za->statIndex($i);
            if (substr($fileinfo['name'], -1) === '/' || $fileinfo['name'] === 'package.fiercephish') {  // Skip if its just a folder
                continue;
            }
            $path = '';
            if ($request->has('path')) {
                $path = $request->input('path');
            }
            $path = trim(str_replace('//','/', $path.'/'.HostedSite::getConfigValue($config, 'paths|'.$fileinfo['name'], $fileinfo['name'])), '/');
            if (HostedFile::path_already_exists($path)) {
                $errors[] = 'Route for "'.$path.'" already exists';
            }
        }
        $configPaths = HostedSite::getConfigValue($config, 'paths', []);
        $notUnique = array_unique(array_diff_assoc($configPaths, array_unique(array_map('strtolower', $configPaths))));
        foreach ($notUnique as $route) {
            $errors[] = 'Configured route of "'.$route.'" is will exist twice. Check the config file!';
        }
        if (count($errors) > 0) {
            return back()->withErrors($errors);
        }
        $site->save();
        for ($i = 0; $i < $za->numFiles; ++$i) {
            $fileinfo = $za->statIndex($i);
            if (substr($fileinfo['name'], -1) === '/' || $fileinfo['name'] === 'package.fiercephish') {  // Skip if its just a folder
                continue;
            }
            $file = new HostedFile();
            $filename = 'hosted/'.HostedFile::generateFilename();
            $fo = fopen(storage_path('app/'.$filename), 'w');
            fwrite($fo, $za->getFromIndex($i));
            fclose($fo);
            $file->local_path = $filename;
            $path = '';
            if ($request->has('path')) {
                $path = $request->input('path');
            }
            $path = trim(str_replace('//','/', $path.'/'.HostedSite::getConfigValue($config, 'paths|'.$fileinfo['name'], $fileinfo['name'])), '/');
            $pathinfo = pathinfo($path);
            $dirname = '';
            if (isset($pathinfo['dirname']) && $pathinfo['dirname'] != '.') {
                $dirname = $pathinfo['dirname'];
            }
            $file->route = $dirname;
            $file->file_name = $pathinfo['basename'];
            $file->original_file_name = $fileinfo['name'];
            $file->file_mime = mime_content_type(storage_path('app/'.$filename));
            $file->kill_switch = null;
            if (stripos($fileinfo['name'],'.php') === false) {
                $file->action = HostedFile::SERVE;
            } else {
                $file->action = HostedFile::PARSE;
            }
            $file->uidvar = HostedSite::getConfigValue($config, 'track|'.$fileinfo['name'], null);
            $file->invalid_action = HostedFile::INVALID_ALLOW;
            $file->notify_access = HostedSite::getConfigValue($config, 'notify|'.$fileinfo['name'], HostedSite::getConfigValue($config, 'defaults|notify', false));
            $file->hosted_site_id = $site->id;
            $file->save();
            if ($file->original_file_name === HostedSite::getConfigValue($config, 'entry', null)) {
                $site->entry_file_id = $file->id;
                $site->save();
            }
        }
        return back()->with('success', 'Added site successfully');
    }
    
    public function deletesite(Request $request)
    {
        
    }
}
