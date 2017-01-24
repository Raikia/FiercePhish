<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\ActivityLog;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StartCampaign implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $process_job;
    protected $campaign;
    protected $list;
    protected $template;
    protected $send_num_emails;
    protected $send_every_minutes;
    protected $start_date;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($process_job, $campaign, $list, $template, $send_num_emails, $send_every_minutes, $start_date)
    {
        $this->process_job = $process_job;
        $this->campaign = $campaign;
        $this->list = $list;
        $this->template = $template;
        $this->send_num_emails = $send_num_emails;
        $this->send_every_minutes = $send_every_minutes;
        $this->start_date = $start_date;
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
        $start_date = $this->start_date;
        $send_all_immediately = false;
        if ($send_num_emails < 0)
            $send_all_immediately = true;
        $original_send_num_emails = $send_num_emails;
        $numUsers = $list->users()->count();
        $numSent = 0;
        $list->users()->chunk(1000, function($users) use($send_all_immediately, &$send_num_emails, $original_send_num_emails, &$counter, &$numSent, $pjob, $campaign, $template, &$start_date, $send_every_minutes, $numUsers) {
            foreach ($users as $user)
            {
                $new_email = $template->craft_email($campaign, $user);
                if ($send_all_immediately)
                {
                    $new_email->send($start_date, 'medium');
                }
                else 
                {
                    $new_email->send($start_date, 'low');
                    --$send_num_emails;
                    if ($send_num_emails == 0)
                    {
                        $start_date = $start_date->addMinutes($send_every_minutes);
                        $send_num_emails = $original_send_num_emails;
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
