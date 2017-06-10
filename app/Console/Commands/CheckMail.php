<?php

namespace App\Console\Commands;

use App\ActivityLog;
use App\ReceivedMail;
use Cache;
use Illuminate\Console\Command;

class CheckMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fp:checkmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This connects to the IMAP server and caches the messages in the INBOX.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting email fetch.');
        if (config('fiercephish.IMAP_HOST') !== null && Cache::get('fp:checkmail_error', 0) < 10) {
            $this->info('Running');
            $imap = false;
            try {
                $imap = imap_open('{'.config('fiercephish.IMAP_HOST').':'.config('fiercephish.IMAP_PORT').'}INBOX', config('fiercephish.IMAP_USERNAME'), config('fiercephish.IMAP_PASSWORD'));
            } catch (\Exception $e) {
                $imap = false;
            }
            if ($imap === false) {
                Cache::forever('fp:checkmail_error', Cache::get('fp:checkmail_error', 0) + 1);
                $this->error('Unable to connect to imap server... This was attempt #'.Cache::get('fp:checkmail_error'));
                
                return;
            }
            Cache::forever('fp:checkmail_error', 0);
            $n_msgs = imap_num_msg($imap);
            $this->info("Found ".$n_msgs." emails!");
            if ($n_msgs == 0) {
                imap_alerts();
                imap_errors();
                imap_close($imap);
                
                return;
            }
            for ($x = 1; $x <= $n_msgs; ++$x) {
                $email_header = imap_header($imap, $x);
                $message_id = trim($email_header->message_id, '<>');
                $mail = ReceivedMail::withTrashed()->where('message_id', $message_id)->first();
                if ($mail === null) {
                    $this->info('New mail!');
                    $mail = new ReceivedMail();
                    $mail->message_id = $message_id;
                    if (isset($email_header->sender[0]->personal)) {
                        $mail->sender_name = $email_header->sender[0]->personal;
                    }
                    $mail->sender_email = $email_header->sender[0]->mailbox.'@'.$email_header->sender[0]->host;
                    if (isset($email_header->reply_to[0]->personal)) {
                        $mail->replyto_name = $email_header->reply_to[0]->personal;
                    }
                    $mail->replyto_email = $email_header->reply_to[0]->mailbox.'@'.$email_header->reply_to[0]->host;
                    if (isset($email_header->to[0]->personal)) {
                        $mail->receiver_name = $email_header->to[0]->personal;
                    }
                    $mail->receiver_email = $email_header->to[0]->mailbox.'@'.$email_header->to[0]->host;
                    $mail->subject = $email_header->subject;
                    $mail->received_date = date('Y-m-d H:i:s', strtotime($email_header->date));
                    $mail->message = $this->get_body($imap, $x);
                    $mail->seen = false;
                    $mail->save();
                    $attachments = $this->getAttachments($imap, $x);
                    foreach ($attachments as $attach) {
                        if ($attach['is_attachment']) {
                            $mail->attachments()->create(['name' => $attach['name'], 'content' => base64_encode($attach['attachment'])]);
                        }
                    }
                    $this->info('Saved mail');
                    ActivityLog::log('Received an email from '.$mail->sender_name.' ('.$mail->sender_email.')', 'CheckMail');
                }
            }
            imap_alerts();
            imap_errors();
            imap_close($imap);
        } else {
            $this->error('Inbox feature is disabled, skipping');
        }
    }

    private function getAttachments($connection, $message_number)
    {
        $structure = imap_fetchstructure($connection, $message_number);
        $attachments = [];
        if (isset($structure->parts) && count($structure->parts)) {
            for ($i = 0; $i < count($structure->parts); $i++) {
                $attachments[$i] = [
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => '',
                ];
                
                if ($structure->parts[$i]->ifdparameters) {
                    foreach ($structure->parts[$i]->dparameters as $object) {
                        if (strtolower($object->attribute) == 'filename') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }
                
                if ($structure->parts[$i]->ifparameters) {
                    foreach ($structure->parts[$i]->parameters as $object) {
                        if (strtolower($object->attribute) == 'name') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }
                
                if ($attachments[$i]['is_attachment']) {
                    $attachments[$i]['attachment'] = imap_fetchbody($connection, $message_number, $i + 1);
                    if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    } elseif ($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }
        }
        
        return $attachments;
    }
    
    /* Taken from http://stackoverflow.com/questions/4272551/extract-body-text-from-email-php */
    private function format_html($str)
    {
        $str = htmlentities($str, ENT_COMPAT, 'UTF-8');
        $str = str_replace(chr(10), '<br>', $str);
        
        return $str;
    }
    
    
    private function get_body($imapLink, $num)
    {
        $obj_structure = imap_fetchstructure($imapLink, $num);
        
        $obj_section = $obj_structure;
        $section = '1';
        for ($i = 0; $i < 10; $i++) {
            if ($obj_section->type == 0) {
                break;
            } else {
                $obj_section = $obj_section->parts[0];
                $section .= ($i > 0 ? '.1' : '');
            }
        }
        $text = imap_fetchbody($imapLink, $num, $section);
        if ($obj_section->encoding == 3) {
            $text = imap_base64($text);
        } elseif ($obj_section->encoding == 4) {
            $text = imap_qprint($text);
        }
        foreach ($obj_section->parameters as $obj_param) {
            if (($obj_param->attribute == 'charset') && (mb_strtoupper($obj_param->value) != 'UTF-8')) {
                $text = utf8_encode($text);
                break;
            }
        }
        
        return $text;
    }

}
