<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\TargetUser;
use App\TargetList;
use App\ActivityLog;
use App\ProcessingJob;
use App\Jobs\ImportTargets;

use File;

class TargetsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('targets.index');
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
            ActivityLog::log('Added new Target User ("'.$request->input('email').'")', "Target User");
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
        $name = $request->file('import_file')->getClientOriginalName();
        $temp_path = '/tmp/fiercephish_importusers_'.rand().'.dat';
        file_put_contents($temp_path, $content);
        $pj = new ProcessingJob(['name' => 'Import Target Users', 'description' => 'Filename: '.$name, 'progress' => 0, 'icon' => 'users']);
        $pj->save();
        $job = (new ImportTargets($pj, $temp_path))->onQueue('high')->delay(1);
        $this->dispatch($job);
        ActivityLog::log("Started Target User import job, (Filename: ".$name.")", "Target User");
        return back()->with('success', 'Started Target User import job');
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
            ActivityLog::log("Added new Target List named \"" . $request->input('name') . "\"", "Target List");
            return back()->with('success', 'List added successfully');
        }
        else
        {
            return back()->withErrors('List already exists');
        }
    }
    
    public function assign_index($id=null)
    {
        $targetUsers = TargetUser::with('lists')->orderBy('first_name')->get();
        $targetLists = TargetList::with('users')->orderBy('name')->get();
        $selectedList = new TargetList();
        if ($id !== null)
            $selectedList = TargetList::findOrFail($id);
        $ret_obj =  view('targets.assign')->with('targetUsers', $targetUsers)->with('targetLists', $targetLists)->with('selectedList', $selectedList);
        $warn = '';
        if (count($targetUsers) === 0)
            $warn = 'You must add at least one user first!';
        elseif (count($targetLists) === 0)
            $warn = 'You must add at least one list first!';
        if (!empty($warn))
            return $ret_obj->with('warn', $warn);
        return $ret_obj;
    }
    
    public function assignToLists(Request $request)
    {
        $this->validate($request, [
            'listSelection' => 'required',
            'type' => 'required'
            ]);
        if ($request->input('rowsToAdd') == "")
            return back()->withErrors('You need to select some rows to add to the list');
        $ids = explode(',',$request->input('rowsToAdd'));
        foreach ($ids as $id)
            if (!is_numeric($id))
                return back()->withErrors('Invalid selection');
        $list = TargetList::findOrFail($request->input('listSelection'));
        if ($request->input('type') == 'edit')
        {
            $list->users()->sync($ids);
            ActivityLog::log("Modified the users for the list \"" . $list->name ."\", it now has " . count($list->users) ." users", "Target List");
            return back()->with('success', 'List has been edited and now has ' . count($list->users) . ' users');
        }
        else
        {
            $list->users()->syncWithoutDetaching($ids);
            ActivityLog::log("Added users to the list \"".$list->name."\", it now has " . count($list->users) ." users");
            return back()->with('success', 'Users successfully added');
        }
    }
}
