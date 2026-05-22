<?php

namespace App\Jobs;

use App\Models\CallLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTelephonyCallStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public CallLog $callLog, public array $providerData) {}

    public function handle(): void
    {
        $updates = [];

        if (isset($this->providerData['status'])) {
            $updates['status'] = $this->providerData['status'];
        }

        if (isset($this->providerData['duration_seconds'])) {
            $updates['duration_seconds'] = (int) $this->providerData['duration_seconds'];
        }

        if (isset($this->providerData['answered_at'])) {
            $updates['answered_at'] = $this->providerData['answered_at'];
        }

        if (isset($this->providerData['ended_at'])) {
            $updates['ended_at'] = $this->providerData['ended_at'];
        }

        if (isset($this->providerData['recording_url'])) {
            $updates['recording_url'] = $this->providerData['recording_url'];
        }

        if (!empty($updates)) {
            $this->callLog->update($updates);
        }

        if ($this->callLog->lead && in_array($this->callLog->status, ['connected', 'answered'])) {
            $this->callLog->lead->update(['last_call_at' => $this->callLog->ended_at ?? now()]);
        }
    }
}
