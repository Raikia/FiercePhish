<?php

namespace App\Jobs;

use App\TargetUser;
use App\ActivityLog;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportTargets extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $process_path;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($meta, $path)
    {
        $this->process_path = $path;
        parent::__construct($meta);
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
            $this->cleanup();
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
                    $t = TargetUser::where('first_name', $parts[0])->where('last_name', $parts[1])->where('email', $parts[2])->first();
                    if ($t !== null && $t->hidden)
                    {
                        $t->hidden = false;
                        $t->save();
                    }
                }
                ++$processed;
                $new_progress = round(($processed/$total)*100);
                if ($this->getProgress() != $new_progress)
                {
                    $this->setProgress($new_progress);
                }
            }
        }
        ActivityLog::log("Target User import job completed (".$this->description.")", "Target User");
        unlink($this->process_path);
        $this->cleanup();
    }
}
