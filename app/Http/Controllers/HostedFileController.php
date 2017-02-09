<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HostedFileController extends Controller
{
    public function index()
    {
        echo "index";
        return;
    }
    
    public function checkfile(Request $request)
    {
        echo $request->url() . "<br />";
        echo $request->root() . "<br />";
        echo $request->fullUrl() . "<br />";
        echo $request->path() . "<br />";
        echo $request->decodedPath() . "<br />";
        echo var_dump($request->segments()) . "<br />";
        echo var_dump(\Route::has('ajax/targetlist/note')) . "<br />";
        echo var_dump($request->all()) . "<br />";
        return;
    }
}
