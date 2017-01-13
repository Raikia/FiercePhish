<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\LogAggregate;

class LogPull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fp:logpull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private $files_to_log = ['smtp' => '/var/log/mail.log', 'imap' => '/var/log/dovecot.log'];
    
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
        $timezone = system("date +%Z");
        $this->info("Running log aggregation");
        foreach ($this->files_to_log as $type => $file)
        {
            $latest = LogAggregate::where('log_type', $type)->orderby('log_time', 'desc')->orderby('id','asc')->first();
            $latest_hash = '';
            if ($latest !== null)
            {
                $latest_hash = $latest->hash;
            }
            if (is_readable($file))
            {
                $fp = fopen($file, 'r');
                
                $pos = -2;
                $currentLine = '';
                while (-1 !== fseek($fp, $pos, SEEK_END)) {
                    $char = fgetc($fp);
                    if (PHP_EOL != $char)
                    {
                        $currentLine = $char . $currentLine;
                        
                    }
                    else
                    {
                        $words = explode(" ", $currentLine);
                        $strtime = $words[0] . " " . $words[1] . " " . $words[2];
                        $time = strtotime($strtime.' '.$timezone);
                        $datetime = date("Y-m-d H:i:s", $time);
                        $words = explode(": ", $currentLine, 2)[1];
                        $newlog = new LogAggregate();
                        $newlog->log_time = $datetime;
                        $newlog->log_type = $type;
                        $newlog->data = $words;
                        $newlog->hash = LogAggregate::hash($newlog);
                        //echo $newlog."\n";
                        if ($newlog->hash == $latest_hash)
                        {
                            break;
                        }
                        $newlog->save();
                        $currentLine = '';
                    }
                    $pos--;
                }
                fclose($fp);
            }
        }
        $this->info("Completed log aggregation.");
    }
}
