<?php

namespace App\Jobs;

use App\TargetList;
use App\TargetUser;
use App\ActivityLog;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AddToList extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $targetlist;
    protected $num_to_add;
    protected $only_unassigned;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($meta, $targetlist, $num_to_add, $only_unassigned)
    {
        $this->targetlist =  $targetlist;
        $this->num_to_add = $num_to_add;
        $this->only_unassigned = $only_unassigned;
        parent::__construct($meta);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Add all to list
        if ($this->num_to_add < 0)
        {
            $list = $this->targetlist;
            $query = $this->targetlist->availableUsers();
            $totalNum = $query->count();
            $count = 0;
            if ($this->only_unassigned)
                $query = $query->has('lists', '<', 1);
            $query->chunk(1000, function($u) use($list, $totalNum, &$count) {
                $list->users()->syncWithoutDetaching($u->pluck('id')->toArray());
                $count += 1000;
                $this->setProgress(round(($count/$totalNum)*100));
            });
            ActivityLog::log("Added All Target Users to the Target List \"".$list->name."\" job completed", "Target List");
        }
        else
        {
            $list = $this->targetlist;
            $totalNum = $this->targetlist->availableUsers()->count();
            $count = 0;
            $num_left = $this->num_to_add;
            while ($num_left > 0)
            {
                $chunk = min($num_left, 1000);
                $query = $list->availableUsers()->inRandomOrder()->take($chunk);
                if ($this->only_unassigned)
                    $query = $query->has('lists', '<', 1);
                $list->users()->syncWithoutDetaching($query->pluck('id')->toArray());
                $num_left -= $chunk;
                $count += $chunk;
                $this->setProgress(round(($count/$totalNum)*100));
            }
            ActivityLog::log("Added Random Target Users to the Target List \"".$list->name."\" job completed", "Target List");
        }
        $this->cleanup();
    }
}
