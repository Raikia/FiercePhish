<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Email;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'subject', 'template'];
    
    public static $VARS = [
        '[name]' => 'The name of the person (example: "John Doe")',
        '[first_name]' => 'The first name of the person (example: "John")',
        '[last_name]' => 'The last name of the person (example: "Doe")',
        '[username]' => 'Username of the person (example: In "john.doe@domain.com", it would be "john.doe")',
        '[email]' => 'Email of the target person (example: "john.doe@domain.com")', 
        '[uid]' => 'A unique identifier for this user. This is useful in links to identify who clicked on a specific link', 
        '[from_name]' => 'Name of the person sending the email', 
        '[from_email]' => 'Email of the person sending the email', 
        '[extra]' => 'Extra data a campaign might like to customize (such as a signature with titles) - UNUSED CURRENTLY',
    ];
        
    
    public static $DEFAULTS = [
        '[name]' => 'John Doe',
        '[first_name]' => 'John',
        '[last_name]' => 'Doe',
        '[username]' => 'john.doe',
        '[email]' => 'john.doe@domain.com',
        '[uid]' => '0696f64d67415a89782075f1d990b2deb449d5e5',
        '[from_name]' => 'Bill Smith',
        '[from_email]' => 'bsmith@malicious.com',
        '[extra]' => 'Director of IT Security',
    ];
    
    private function parse_variables($text, $campaign, $targetUser)
    {
        $text = str_replace('[name]', $targetUser->full_name(), $text);
        $text = str_replace('[first_name]', $targetUser->first_name, $text);
        $text = str_replace('[last_name]', $targetUser->last_name, $text);
        $text = str_replace('[username]', explode("@",$targetUser->email)[0], $text);
        $text = str_replace('[email]', $targetUser->email, $text);
        $text = str_replace('[uid]', $targetUser->uuid($campaign), $text);
        $text = str_replace('[from_name]', $campaign->from_name, $text);
        $text = str_replace('[from_email]', $campaign->from_email, $text);
        $text = str_replace('[extra]', '', $text);
        return $text;
    }
    
    public function craft_email($campaign, $targetUser)
   { 
        $email = new Email();
        $email->sender_name = $campaign->from_name;
        $email->sender_email = $campaign->from_email;
        $email->target_user_id = $targetUser->id;
        $email->campaign_id = $campaign->id;
        $email->subject = $this->parse_variables($this->subject, $campaign, $targetUser);
        $email->message = $this->parse_variables($this->template, $campaign, $targetUser);
        $email->tls = true; // Maybe change this to be editable in the campaign
        $email->has_attachment = false; // Maybe change this to be editable in the campaign
        $email->status = Email::NOT_SENT;
        $email->uuid = $targetUser->uuid($campaign);
        $email->save();
        return $email;
    }
}
