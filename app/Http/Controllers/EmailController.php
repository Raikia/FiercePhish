<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\EmailTemplate;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function template_index($id='')
    {
        $all_templates = EmailTemplate::orderBy('name')->get();
        $currentTemplate = new EmailTemplate();
        if ($id !== '')
            $currentTemplate = EmailTemplate::findOrFail($id);
        return view('emails.template_index')->with('allTemplates', $all_templates)->with('currentTemplate', $currentTemplate);
    }
    
    public function addTemplate(Request $request)
    {
        $this->validate($request, [
            'templateName' => 'required|max:255|unique:email_templates,name'
        ]);
        $template = new EmailTemplate();
        $template->name = $request->input('templateName');
        $template->save();
        return back()->with('success', 'Template "'.$request->input('templateName').'" created successfully');
    }
    public function editTemplate(Request $request)
    {
        $this->validate($request, [
            'template_id' => 'required|integer',
            'subject' => 'max:255',
        ]);
        $template = EmailTemplate::findOrFail($request->input('template_id'));
        $template->subject = $request->input('subject');
        $template->template = $request->input('templateData');
        $template->save();
        return redirect()->action('EmailController@template_index', ['id' => $template->id])->with('success', 'The template was saved successfully!');
    }
    
    public function deleteTemplate(Request $request)
    {
        $this->validate($request, [
            'deleteId' => 'required|integer'
        ]);
        $template = EmailTemplate::findOrFail($request->input('deleteId'));
        $template->delete();
        return back()->with('success', 'Template successfully deleted');
    }
    
    public function check_settings_index()
    {
    }
}
