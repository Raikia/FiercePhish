<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\ActivityLog;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StartCampaign extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $process_job;
    protected $campaign;
    protected $list;
    protected $template;
    protected $send_num_emails;
    protected $send_every_minutes;
    protected $seconds_offset_start;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($process_job, $campaign, $list, $template, $send_num_emails, $send_every_minutes, $seconds_offset_start)
    {
        $this->process_job = $process_job;
        $this->campaign = $campaign;
        $this->list = $list;
        $this->template = $template;
        $this->send_num_emails = $send_num_emails;
        $this->send_every_minutes = $send_every_minutes;
        $this->seconds_offset_start = $seconds_offset_start;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $list = $this->list;
        $pjob = $this->process_job;
        $campaign = $this->campaign;
        $template = $this->template;
        $send_num_emails = $this->send_num_emails;
        $send_every_minutes = $this->send_every_minutes;
        $seconds_offset_start = $this->seconds_offset_start;
        $send_all_immediately = false;
        if ($send_num_emails < 0)
            $send_all_immediately = true;
        $counter = 0;
        $original_send_num_emails = $send_num_emails;
        $numUsers = $list->users()->count();
        $numSent = 0;
        $list->users()->chunk(500, function($users) use($send_all_immediately, &$send_num_emails, $original_send_num_emails, &$counter, &$numSent, $pjob, $campaign, $template, $seconds_offset_start, $send_every_minutes, $numUsers) {
            foreach ($users as $user)
            {
                $new_email = $template->craft_email($campaign, $user);
                if ($send_all_immediately)
                {
                    $new_email->send($seconds_offset_start, 'medium');
                }
                else 
                {
                    $new_email->send($seconds_offset_start + ($counter * ($send_every_minutes*60)), 'low');
                    --$send_num_emails;
                    if ($send_num_emails == 0)
                    {
                        $send_num_emails = $original_send_num_emails;
                        ++$counter;
                    }
                }
                ++$numSent;
                $oldProgress = $pjob->progress;
                $newRate = round(($numSent/$numUsers)*100);
                if ($oldProgress != $newRate)
                {
                    $pjob->progress = $newRate;
                    $pjob->save();
                }
            }
        });
        ActivityLog::log("Completed campaign job named \"".$campaign->name."\" to queue ".$list->users()->count()." emails for sending", "Campaign");
        $this->process_job->delete();
    }
}
