<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\LogAggregate;
use Carbon\Carbon;
use App\Email;

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
                        $words = preg_split("/\s+/", $currentLine);
                        if (count($words) < 3)
                            continue; // This means the log was being written to while pulling
                        $strtime = $words[0] . " " . $words[1] . " " . $words[2];
                        //$time = strtotime($strtime.' '.$timezone);
                        $time = Carbon::parse($strtime.' '.$timezone)->timezone("UTC");
                        //$datetime = date("Y-m-d H:i:s", $time);
                        $words_arr = explode(": ", $currentLine, 2);
                        if (count($words_arr) < 2)
                            continue; // This means the log was being written to while pulling
                        $words = $words_arr[1];
                        $newlog = new LogAggregate();
                        $newlog->log_time = $time;
                        $newlog->log_type = $type;
                        $newlog->data = $words;
                        $newlog->hash = LogAggregate::hash($newlog);
                        //echo $newlog."\n";
                        if ($newlog->hash == $latest_hash)
                        {
                            break;
                        }
                        try
                        {
                            $newlog->save();
                        }
                        catch (\Exception $e)
                        {
                        }
                        $currentLine = '';
                    }
                    $pos--;
                }
                fclose($fp);
            }
        }
        $this->info("Completed log aggregation.");
        $this->info("Searching for logs for emails");
        $after_date = Carbon::now()->subMinutes(5);
        $before_date = Carbon::now()->addSeconds(30);
        $emails = Email::where('status', Email::SENT)->where('sent_time', '<=', $before_date)->where('sent_time', '>=', $after_date)->get();
        foreach ($emails as $email)
        {
            $logs = LogAggregate::getSurroundingLogs($email->sent_time, 2, 5, 'smtp');
            $total_str = '';
            foreach ($logs as $log)
            {
                $total_str .= $log->log_time."\t".$log->data."\n";
            }
            if (strlen($total_str) > strlen($email->related_logs))
            {
                $email->related_logs = $total_str;
                $email->save();
            }
        }
        $this->info("Purging logs 20 minutes old");
        $before_date = Carbon::now()->subMinutes(20);
        LogAggregate::where('log_time', '<=', $before_date)->delete();
        $this->info("Completed log aggregation.");
    }
}
