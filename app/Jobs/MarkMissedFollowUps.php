<?php

namespace App\Jobs;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarkMissedFollowUps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        FollowUp::where('status', 'scheduled')
            ->where('scheduled_at', '<', now())
            ->chunkById(100, function ($followUps) {
                foreach ($followUps as $followUp) {
                    $followUp->update(['status' => 'missed']);
                }
            });
    }
}
