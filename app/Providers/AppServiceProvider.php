<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('layouts.app', function ($view) {
            $latest_version = \App\Libraries\CacheHelper::getLatestVersion();
            $current_version = \App\Libraries\CacheHelper::getCurrentVersion();
            $current_jobs = \App\ActivityLog::getJobList();
            $view->with('layout_all_active_campaigns', \App\Campaign::where('status', \App\Campaign::WAITING)->orWhere('status', \App\Campaign::SENDING)->get())
                 ->with('latest_fiercephish_version', $latest_version)
                 ->with('current_fiercephish_version', $current_version)
                 ->with('current_jobs', $current_jobs);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
