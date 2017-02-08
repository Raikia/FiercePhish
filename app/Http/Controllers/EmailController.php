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
use App\TargetUser;
use App\Campaign;
use \Response;
use Carbon\Carbon;
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
        $actionType='';
        if ($id != '')
        {
            $replyMail = ReceivedMail::findOrFail($id);
            $newSubject = $replyMail->subject;
            $messageLines = explode("\n", $replyMail->message);
            if ($fwd === '')
            {
                $actionType = 'reply';
                if (strpos(strtolower(trim($replyMail->subject)), "re: ") !== 0)
                {
                    $newSubject = 'Re: ' . $replyMail->subject;
                }
                $newMessage = "<br /><br />On ".date("D, M d, Y \a\\t g:i A", strtotime($replyMail->received_date)).", ".$replyMail->sender_name." &lt;".$replyMail->sender_email."&gt; wrote:<br /><br />";
                foreach ($messageLines as $line)
                    $newMessage .= "> ".e($line)."<br />";
            }
            else
            {
                $actionType = 'forward';
                if (strpos(strtolower(trim($replyMail->subject)), "fwd: ") !== 0)
                {
                    $newSubject = "Fwd: " . $replyMail->subject;
                }
                $replyMail->replyto_name = '';
                $replyMail->replyto_email = '';
                $newMessage .= "<br /><br />---------- Forwarded message ----------<br />";
                $newMessage .= "From: ".$replyMail->sender_name." &lt;".$replyMail->sender_email."&gt;<br />";
                $newMessage .= "Date: ".date("D, M d, Y \a\\t h:i A", strtotime($replyMail->received_date))."<br />";
                $newMessage .= "Subject: ".$replyMail->subject."<br />";
                $newMessage .= "To: ".$replyMail->receiver_name." &lt;".$replyMail->receiver_email."&gt;<br /><br />";
                foreach ($messageLines as $line)
                    $newMessage .= $line."<br />";
            }
            
        }
        return view('emails.send_simple')->with('replyMail', $replyMail)->with('newSubject', $newSubject)->with('newMessage', $newMessage)->with('actionType', $actionType);
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
        $redir = redirect()->action('EmailController@send_simple_index');
        if ($request->input('actionType') != '')
        {
            $mail = ReceivedMail::findOrFail($request->input('replyId'));
            if ($request->input('actionType') == 'reply')
            {
                $mail->replied = true;
                $mail->save();
            }
            elseif ($request->input('actionType') == 'forward')
            {
                $mail->forwarded = true;
                $mail->save();
            }
            $redir = redirect()->action('EmailController@inbox_get');
        }
        $target_user_query = TargetUser::query();
        $name_parts = explode(' ', $request->input('sbt_receiver_name'), 2);
        if (count($name_parts) == 1)
            $target_user_query = $target_user_query->where('first_name', $name_parts[0])->where('email', $request->input('sbt_receiver_email'));
        else
            $target_user_query = $target_user_query->where('first_name', $name_parts[0])->where('last_name', $name_parts[1])->where('email', $request->input('sbt_receiver_email'));
        $target_user = $target_user_query->first();
        if ($target_user === null)
        {
            $target_user = new TargetUser();
            $target_user->first_name = $name_parts[0];
            if (count($name_parts) > 1)
                $target_user->last_name = $name_parts[1];
            $target_user->email = $request->input('sbt_receiver_email');
            $target_user->hidden = true;
            $target_user->save();
        }
        $email_obj = new Email();
        $email_obj->sender_name = $request->input('sbt_sender_name');
        $email_obj->sender_email = $request->input('sbt_sender_email');
        $email_obj->target_user_id = $target_user->id;
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
        ActivityLog::log("Queued to send an email (simple send) to \"" . $email_obj->targetuser->email."\"", "Email");
        
        return $redir->with('success', 'Email queued for immediate sending!');
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
        
        return Response::make(base64_decode($attachment->content), '200', [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$attachment->name.'"',
        ]);
    }
    
    public function email_resend(Request $request, $id)
    {
        $email = Email::findorfail($id);
        if ($email->campaign != null && $email->campaign->status == Campaign::CANCELLED)
            return back()->withErrors('Cannot resend an email for a cancelled campaign');
        $new_email = $email->replicate();
        $new_email->status = Email::PENDING_RESEND;
        $new_email->planned_time = Carbon::now();
        $new_email->sent_time = null;
        $new_email->save();
        $new_email->send(-1, 'email');
        return redirect()->action('EmailController@email_log_details', ['id' => $new_email->id])->with('success', 'Email has been queued for resending');
    }
    
    public function email_cancel(Request $request, $id)
    {
        $email = Email::findorfail($id);
        if ($email->status == Email::SENT || $email->status == Email::CANCELLED || $email->status == Email::FAILED)
            return back()->withErrors("You can't cancel a completed email");
        $email->cancel();
        return back()->withSuccess('Email cancelled');
    }
}
