<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\ActivityLog;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fp:createuser 
                            {--c|confirm : Confirm user creation without prompting} 
                            {username? : Username of the user to create} 
                            {email? : Email of the user to create} 
                            {password? : Password of the user to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a user for FiercePhish';

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
        $email = $this->argument('email');
        if ($email == null)
            $email = $this->ask('Enter an email address');
        $password = $this->argument('password');
        if ($password == null)
            $password = $this->secret('Enter a password');
        if (!$this->option('confirm'))
            if (!$this->confirm("Are you sure you want to create this account? "))
                return;
        $newUser = new User([
            'name' => $username,
            'email' => $email,
            'password' => bcrypt($password),
            ]);
        $newUser->save();
        ActivityLog::log("Added a new user named \"".$newUser->name."\" (via artisan)", "Settings");
        $this->info("User created successfully!");
    }
}
