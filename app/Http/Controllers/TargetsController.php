<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\TargetUser;
use App\TargetList;

use File;

class TargetsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $targetUsers = TargetUser::with('lists')->orderBy('last_name')->get();
        return view('targets.index')->with('targetUsers', $targetUsers);
    }
    
    public function addTarget(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email'
        ]);
        $checkUser = TargetUser::where('first_name', $request->input('first_name'))->where('last_name', $request->input('last_name'))->where('email', $request->input('email'))->get();
        if (count($checkUser) == 0)
        {
            TargetUser::create($request->all());
            return back()->with('success', 'Target added successfully');
        }
        else
        {
            return back()->withErrors('Target already exists');
        }
    }
    
    public function importTargets(Request $request)
    {
        $this->validate($request, [
            'import_file' => 'required|mimes:csv,txt|max:15000'
        ]);
        $content = File::get($request->file('import_file')->getRealPath());
        $lines = explode("\n", $content);
        $errors = [];
        $success = 0;
        $warn = 0;
        $lineNum = 0;
        foreach ($lines as $line)
        {
            ++$lineNum;
            $line = trim($line);
            if ($line == "")
                continue;
            $parts = str_getcsv($line, ",", '"');
            for ($x=0; $x<count($parts); ++$x)
            {
                $parts[$x] = trim(trim($parts[$x]),'"');
            }
            if (count($parts) < 3)
                $errors[] = $lineNum;
            else
            {
                if (strpos($parts[2],'@') === false)
                    $errors[] = $lineNum;
                else
                {
                    $t = new TargetUser();
                    $t->first_name = $parts[0];
                    $t->last_name = $parts[1];
                    $t->email = $parts[2];
                    if (count($parts) > 3)
                        $t->notes = $parts[3];
                    try
                    {
                        $t->save();
                        ++$success;
                    }
                    catch (\Illuminate\Database\QueryException $e)
                    {
                        ++$warn;
                    }
                }
            }
        }
        $ret_obj = back();
        if ($success != 0)
            $ret_obj = $ret_obj->with('success', 'Successfully added ' . $success . ' target'.(($success==1)?'':'s'));
        if ($warn != 0)
            $ret_obj = $ret_obj->with('warn', 'Did not add '.$warn.' duplicate entr'.(($warn==1)?'y':'ies'));
        if (count($errors) > 0)
            $ret_obj = $ret_obj->withErrors('Unable to add targets from line'.((count($errors)==1)?'':'s').' ' . implode(', ', $errors));
        return $ret_obj;
    }
    
    
    public function targetlists_index()
    {
        $targetLists = TargetList::with('users')->orderBy('name')->get();
        return view('targets.lists')->with('targetLists', $targetLists);
    }
    
    public function addList(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ]);
        $checkList = TargetList::where('name', $request->input('name'))->get();
        if (count($checkList) == 0)
        {
            TargetList::create($request->all());
            return back()->with('success', 'List added successfully');
        }
        else
        {
            return back()->withErrors('List already exists');
        }
    }
    
    public function assign_index()
    {
        $targetUsers = TargetUser::with('lists')->orderBy('last_name')->get();
        return view('targets.assign')->with('targetUsers', $targetUsers);
    }
}
