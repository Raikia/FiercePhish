<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Jobs\SendEmail;
use App\Http\Requests;
use App\EmailTemplate;
use App\Libraries\DomainTools;
use App\Email; 
use App\ActivityLog;
use App\ReceivedMail;
use App\ReceivedMailAttachment;
use \Response;
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
        ActivityLog::log("Added a new email template named \"" . $template->name."\"", "Email Template");
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
        ActivityLog::log("Edited the email template named \"" . $template->name ."\"", "Email Template");
        return redirect()->action('EmailController@template_index', ['id' => $template->id])->with('success', 'The template was saved successfully!');
    }
    
    public function deleteTemplate(Request $request)
    {
        $this->validate($request, [
            'deleteId' => 'required|integer'
        ]);
        $template = EmailTemplate::findOrFail($request->input('deleteId'));
        ActivityLog::log("Deleted the email template named \"". $template->name."\"", "Email Template");
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

    public function send_simple_index($id='', $fwd='')
    {
        $replyMail = new ReceivedMail();
        $newSubject = '';
        $newMessage = '';
        if ($id != '')
        {
            $replyMail = ReceivedMail::findOrFail($id);
            $newSubject = $replyMail->subject;
            $messageLines = explode("\n", $replyMail->message);
            if ($fwd === '')
            {
                if (strpos(strtolower(trim($replyMail->subject)), "re: ") !== 0)
                {
                    $newSubject = 'Re: ' . $replyMail->subject;
                }
                $newMessage = "<br /><br />On ".date("D, M d, Y \a\\t h:i A", strtotime($replyMail->received_date))."<br />";
                foreach ($messageLines as $line)
                    $newMessage .= "> ".$line."<br />";
            }
            else
            {
                if (strpos(strtolower(trim($replyMail->subject)), "fwd: ") !== 0)
                {
                    $newSubject = "Fwd: " . $replyMail->subject;
                }
                $replyMail->replyto_name = '';
                $replyMail->replyto_email = '';
                $newMessage .= "<br /><br />---------- Forwarded message ----------<br />";
                $newMessage .= "From: ".$replyMail->sender_name." (".$replyMail->sender_email.")<br />";
                $newMessage .= "Date: ".date("D, M d, Y \a\\t h:i A", strtotime($replyMail->received_date))."<br />";
                $newMessage .= "Subject: ".$replyMail->subject."<br />";
                $newMessage .= "To: ".$replyMail->receiver_name." (".$replyMail->receiver_email.")<br /><br />";
                foreach ($messageLines as $line)
                    $newMessage .= $line."<br />";
            }
            
        }
        return view('emails.send_simple')->with('replyMail', $replyMail)->with('newSubject', $newSubject)->with('newMessage', $newMessage);
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
        $email_obj->send();
        ActivityLog::log("Queued to send an email (simple send) to \"" . $email_obj->receiver_email."\"", "Email");
        // Maybe this should redirect to the list of the queue?
        return redirect()->action('EmailController@send_simple_index')->with('success', 'Email queued for immediate sending!');
    }

    public function email_log()
    {
        return view('emails.email_log');
    }
    
    public function email_log_details($id)
    {
        $email = Email::findorFail($id);
        return view('emails.email_log_details')->with('email', $email);
    }
    
    public function inbox_get()
    {
        $view = view('emails.inbox'); 
        if (\Cache::get('fp:checkmail_error', 0) >= 10)
            $view = $view->withErrors('INBOX feature has been disabled because of too many connection errors! Edit the settings ("Settings" --> "Configuration") to re-enable it.');
        return $view;
    }
    
    public function inbox_download_attachment($id='')
    {
        $attachment = ReceivedMailAttachment::findOrFail($id);
        
        return Response::make(base64_decode($attachment->content), '200', array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$attachment->name.'"'
        ));
    }
}
