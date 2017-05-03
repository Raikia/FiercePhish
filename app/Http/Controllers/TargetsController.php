<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\TargetUser;
use App\TargetList;
use App\ActivityLog;
use App\Jobs\ImportTargets;
use App\Jobs\AddToList;

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
            'email' => 'required|email'
        ]);
        $checkUser = TargetUser::where('first_name', $request->input('first_name'))->where('last_name', $request->input('last_name'))->where('email', $request->input('email'))->get();
        
        if (count($checkUser) == 0)
        {
            TargetUser::create(['first_name' => $request->input('first_name'), 'last_name' => (($request->input('last_name')!==null)?$request->input('last_name'):''), 'email' => $request->input('email')]);
            ActivityLog::log('Added new Target User ("'.$request->input('email').'")', "Target User");
            return back()->with('success', 'Target added successfully');
        }
        elseif ($checkUser[0]->hidden)
        {
            $checkUser[0]->hidden = false;
            $checkUser[0]->save();
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
        $job = (new ImportTargets(['title' => 'Import Target Users', 'description' => 'Filename: '.$name, 'icon' => 'users'], $temp_path))->onQueue('operation')->delay(1);
        $this->dispatch($job);
        ActivityLog::log("Started Target User import job, (Filename: ".$name.")", "Target User");
        return back()->with('success', 'Started Target User import job');
    }
    
    
    public function targetlists_index()
    {
        $targetLists = TargetList::orderBy('name')->get();
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
            $t = TargetList::create($request->all());
            ActivityLog::log("Added new Target List named \"" . $request->input('name') . "\"", "Target List");
            return redirect()->action('TargetsController@targetlists_details', ['id' => $t->id])->with('success', 'List added successfully');
        }
        else
        {
            return back()->withErrors('List already exists');
        }
    }
    
    public function targetlists_details($id)
    {
        $targetList = TargetList::findOrFail($id);
        return view('targets.list_details')->with('targetList', $targetList)->with('numTargetUsers', TargetUser::count());
    }
    
    public function clearList($id)
    {
        $list = TargetList::findOrFail($id);
        $list->users()->detach();
        return back()->with('success', 'Removed all users from the list');
    }
    
    public function addAlltoList(Request $request, $id)
    {
        $list = TargetList::findOrFail($id);
        $job = (new AddToList(['title' => 'Add users to list', 'description' => 'Type: All, List: "' . $list->name.'"', 'icon' => 'list'], $list, -1, $request->has('unusedOnly')))->onQueue('operation')->delay(1);
        $this->dispatch($job);
        return back()->with('success', 'Add users to list job started successfully');
    }
    
    public function addRandomtoList(Request $request, $id)
    {
        $this->validate($request, ['numToSelect' => 'required|integer']);
        $list = TargetList::findOrFail($id);
        $job = (new AddToList(['title' => 'Add users to list', 'description' => 'Type: '.$request->input('numToSelect').', Random, List: "' . $list->name.'"', 'icon' => 'list'], $list, $request->input('numToSelect'), $request->has('unusedOnly')))->onQueue('operation')->delay(1);
        $this->dispatch($job);
        return back()->with('success', 'Add random users to list job started successfully');
    }
    
    public function assign_index($id)
    {
        $selectedList = new TargetList();
        if ($id !== null)
            $selectedList = TargetList::findOrFail($id);
        $ret_obj = view('targets.assign')->with('selectedList', $selectedList)->with('numTargetUsers', TargetUser::count());
        if (TargetUser::count() == 0)
            $ret_obj->with('warn', 'You must add at least one user first!');
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
        $list->users()->syncWithoutDetaching($ids);
        ActivityLog::log("Added users to the list \"".$list->name."\", it now has " . $list->users()->count() ." users");
        return redirect()->action('TargetsController@targetlists_details', ['id' => $list->id])->with('success', 'Users successfully added');
    }
    
    public function removeUser(Request $request, $id='', $user_id='')
    {
        if (!is_numeric($id) || !is_numeric($user_id))
        {
            return back()->withErrors('Unknown user/list');
        }
        $list = TargetList::findOrFail($id);
        $list->users()->detach($user_id);
        return back()->with('success', 'Successfully removed user from list');
    }
}
