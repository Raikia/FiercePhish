<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\EmailTemplate;
use App\Libraries\DomainTools;
use App\Email; 
use File;


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
        $settingsCheck = [
            'a_record_primary' => 'Primary A record',
            'a_record_mail'    => 'A record for mail',
            'mx_record'        => 'MX record',
            'spf_record'       => 'SPF record',
        ];
        
        return view('emails.check_settings')->with('settingsCheck', $settingsCheck)->with('server_ip', DomainTools::getServerIP());
    }

    public function send_simple_index()
    {
        return view('emails.send_simple');
    }

    public function send_simple_post(Request $request)
    {
        $this->validate($request, [
            'sbt_sender_name' => 'required',
            'sbt_sender_email' => 'required|email',
            'sbt_receiver_name' => 'required',
            'sbt_receiver_email' => 'required|email',
            'sbt_subject' => 'required',
            'sbt_message' => 'required',
            'sendTLS' =>  'required',
            ]);
        $email_obj = new Email();
        $email_obj->sender_name = $request->input('sbt_sender_name');
        $email_obj->sender_email = $request->input('sbt_sender_email');
        $email_obj->receiver_name = $request->input('sbt_receiver_name');
        $email_obj->receiver_email = $request->input('sbt_receiver_email');
        $email_obj->subject = $request->input('sbt_subject');
        $email_obj->message = $request->input('sbt_message');
        $email_obj->tls = ($request->input('sendTLS') == 'yes');
        $email_obj->has_attachment = $request->hasFile('attachment');
        if ($request->hasFile('attachment'))
        {
            $content = File::get($request->file('attachment')->getRealPath());
            $email_obj->attachment = base64_encode($content);
            $email_obj->attachment_mime = $request->file('attachment')->getMimeType();
            $email_obj->attachment_name = $request->file('attachment')->getClientOriginalName();
        }
        $email_obj->status = Email::NOT_SENT;
        $email_obj->save();
        return redirect()->action('EmailController@send_simple_index')->with('success', 'Email sent!');
    }
}
