<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
use Carbon\Carbon;

class CatchMissed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fp:catchmissedmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This searches for emails that are pending send but are not queued for some reason.';

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
        $emails = Email::where('planned_time', '<', (Carbon::now()->subMinutes(5)))->where('status', Email::NOT_SENT)->get();
        foreach ($emails as $email)
        {
            $this->info("Queueing email " . $email->id);
            $email->send();
        }
    }
}
