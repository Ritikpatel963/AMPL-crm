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

Artisan::command('crm:assign-leads {--campaign_id=} {--limit=500} {--force-on-demand}', function () {
    $campaigns = \App\Models\Campaign::query()
        ->when($this->option('campaign_id'), fn ($query, $campaignId) => $query->whereKey($campaignId))
        ->get();

    $service = app(\App\Services\CallingCrm\LeadAssignmentService::class);
    $total = 0;

    foreach ($campaigns as $campaign) {
        $assigned = $service->distributeUnassigned(
            $campaign,
            (int) $this->option('limit'),
            (bool) $this->option('force-on-demand')
        );
        $total += $assigned;
        $this->line("Campaign {$campaign->id} ({$campaign->name}): {$assigned} leads assigned.");
    }

    $this->info("Total assigned: {$total}");
})->purpose('Assign unassigned Calling CRM leads to eligible campaign agents.');

Schedule::job(new MarkMissedFollowUps)->everyFiveMinutes();
Schedule::job(new RefreshCrmAnalyticsCache)->hourly();
Schedule::job(new CloseStaleUserSessions)->hourly();
Schedule::job(new ExpireCrmReportExports)->daily();
