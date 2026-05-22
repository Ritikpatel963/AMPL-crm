<?php

namespace App\Jobs;

use App\Models\CrmUserSession;
use App\Models\UserBreak;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseStaleUserSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $staleThreshold = now()->subHours(8);

        CrmUserSession::where('status', 'online')
            ->where('logged_in_at', '<', $staleThreshold)
            ->chunkById(50, function ($sessions) {
                foreach ($sessions as $session) {
                    $session->update([
                        'status' => 'expired',
                        'logged_out_at' => now(),
                    ]);

                    UserBreak::where('session_id', $session->id)
                        ->whereNull('ended_at')
                        ->update([
                            'ended_at' => now(),
                            'duration_seconds' => \Carbon\Carbon::parse('started_at')->diffInSeconds(now()),
                        ]);
                }
            });
    }
}
