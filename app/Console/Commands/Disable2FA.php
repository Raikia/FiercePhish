<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\ActivityLog;

class Disable2FA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fp:disable2fa
                            {--c|confirm : Confirm user creation without prompting} 
                            {username? : Username of the user to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disables the 2FA authentication of a user';

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
        $username = $this->argument('username');
        if ($username == null)
            $username = $this->ask('Enter a username');
        $user = User::where('name', $username)->first();
        if ($user === null)
        {
            $this->error('User "'.$username.'" was not found');
            return;
        }
        if ($user->google2fa_secret == null)
        {
            $this->error('User "'.$username.'" does not have 2FA enabled');
            return;
        }
        if (!$this->option('confirm'))
            if (!$this->confirm("Are you sure you want to disable the 2FA for this account? "))
                return;
        $user->google2fa_secret = null;
        $user->save();
        ActivityLog::log("Disabled 2FA for user named \"".$user->name."\" (via artisan)", "Settings");
        $this->info("2FA disabled successfully!");
    }
}
