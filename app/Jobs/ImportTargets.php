<?php

namespace App\Jobs;

use App\TargetUser;
use App\ProcessingJobs;
use App\ActivityLog;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportTargets implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $process_path;
    protected $process_job;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job, $path)
    {
        $this->process_job = $job;
        $this->process_path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!file_exists($this->process_path))
        {
            $this->process_job->delete();
            return;
        }
        $lines = explode("\n", file_get_contents($this->process_path));
        $total = count($lines);
        $processed = 0;
        $errors = [];
        foreach ($lines as $line)
        {
            
            $line = trim($line);
            if ($line == "")
                continue;
            $parts = str_getcsv($line, ",", '"');
            for ($x=0; $x<count($parts); ++$x)
            {
                $parts[$x] = trim(trim($parts[$x]),'"');
            }
            if (count($parts) < 3)
                continue;
            if (strpos($parts[2],'@') !== false)
            {
                $t = new TargetUser();
                $t->first_name = $parts[0];
                $t->last_name = $parts[1];
                $t->email = $parts[2];
                if (count($parts) > 3)
                    $t->notes = $parts[3];
                try
                {
                    $t->save();
                }
                catch (\Illuminate\Database\QueryException $e)
                {
                }
                ++$processed;
                $new_progress = round(($processed/$total)*100);
                if ($this->process_job->progress != $new_progress)
                {
                    $this->process_job->progress = $new_progress;
                    $this->process_job->save();
                }
            }
        }
        ActivityLog::log("Target User import job completed (".$this->process_job->description.")", "Target User");
        $this->process_job->delete();
        unlink($this->process_path);
    }
}
