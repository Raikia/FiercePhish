<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cache;
use App\HostedFileView;
use App\Geolocation;

class Geolocate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fp:geolocate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs geolocation for all IPs that do not yet have a location';

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
        if (Cache::has('pause_geolocate'))
        {
            $this->info("We are blocked from the geolocate service, so we need to wait...");
            return;
        }
        $all_unknown = HostedFileView::whereDoesntHave('geolocate')->get();
        $this->info("Starting Geolocation on ".count($all_unknown)." IPs!");
        foreach ($all_unknown as $view)
        {
            $this->line('Geolocating ' . $view->ip.'...');
            $content = @file_get_contents('http://freegeoip.net/json/'.$view->ip);
            if ($content === false)
            {
                $this->error('Unable to get geolocation!');
                $headers = @get_headers('http://freegeoip.net/json/'.$view->ip);
                $code = substr($headers[0], 9, 3);
                if ($code == 403)
                {
                    $this->error("We are temporarily blocked!  Waiting for 10 minutes...");
                    Cache::store('pause_geolocate', 10);
                }
                elseif ($code == 404)
                {
                    $this->error("This IP apparently doesn't have any geolocation information.  Setting to blank");
                    $geo = new Geolocation();
                    $geo->ip = $view->ip;
                    $geo->country_code = '';
                    $geo->country_name = '';
                    $geo->region_code = '';
                    $geo->region_name = '';
                    $geo->city = '';
                    $geo->zip_code = '';
                    $geo->time_zone = '';
                    $geo->latitude = 0;
                    $geo->longitude = 0;
                    $geo->metro_code = 0;
                    $geo->save();
                }
            }
            $json = json_decode($content);
            
            $geo = new Geolocation();
            $geo->ip = $json->ip;
            $geo->country_code = $json->country_code;
            $geo->country_name = $json->country_name;
            $geo->region_code = $json->region_code;
            $geo->region_name = $json->region_name;
            $geo->city = $json->city;
            $geo->zip_code = $json->zip_code;
            $geo->time_zone = $json->time_zone;
            $geo->latitude = $json->latitude;
            $geo->longitude = $json->longitude;
            $geo->metro_code = $json->metro_code;
            $geo->save();
            $this->line("Found geolocation!");
        }
        $this->info("Done geolocations");
    }
}
