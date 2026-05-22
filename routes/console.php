<?php

use App\Jobs\CloseStaleUserSessions;
use App\Jobs\ExpireCrmReportExports;
use App\Jobs\MarkMissedFollowUps;
use App\Jobs\RefreshCrmAnalyticsCache;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new MarkMissedFollowUps)->everyFiveMinutes();
Schedule::job(new RefreshCrmAnalyticsCache)->hourly();
Schedule::job(new CloseStaleUserSessions)->hourly();
Schedule::job(new ExpireCrmReportExports)->daily();
