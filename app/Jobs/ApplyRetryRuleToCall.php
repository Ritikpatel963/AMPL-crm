<?php

namespace App\Jobs;

use App\Models\CallLog;
use App\Models\FollowUp;
use App\Models\RetryReason;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyRetryRuleToCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public CallLog $callLog) {}

    public function handle(): void
    {
        if (!$this->callLog->lead || !$this->callLog->campaign) {
            return;
        }

        $pipelineId = $this->callLog->campaign->pipeline_id;

        $callStatus = $this->callLog->status;

        $retryReason = match ($callStatus) {
            'busy' => RetryReason::where('pipeline_id', $pipelineId)
                ->where('name', 'like', '%Busy%')
                ->where('is_active', true)->first(),
            'no_answer' => RetryReason::where('pipeline_id', $pipelineId)
                ->where('name', 'like', '%No Answer%')
                ->where('is_active', true)->first(),
            default => RetryReason::where('pipeline_id', $pipelineId)
                ->where('name', 'like', '%Not Connected%')
                ->where('is_active', true)->first(),
        };

        if (!$retryReason || !$retryReason->rule || !$retryReason->rule->is_active) {
            return;
        }

        $rule = $retryReason->rule;

        $existingRetries = FollowUp::where('lead_id', $this->callLog->lead_id)
            ->where('is_system_generated', true)
            ->where('status', 'completed')
            ->count();

        if ($existingRetries >= $rule->max_retries) {
            if ($rule->mark_lost_after_exhausted) {
                $this->callLog->lead->update(['status' => 'lost']);
            }
            return;
        }

        $intervalMinutes = match ($rule->interval_unit) {
            'minutes' => $rule->interval_value,
            'hours' => $rule->interval_value * 60,
            'days' => $rule->interval_value * 1440,
            default => $rule->interval_value,
        };

        $scheduledAt = now()->addMinutes($intervalMinutes);

        $this->callLog->lead->followUps()->create([
            'campaign_id' => $this->callLog->campaign_id,
            'user_id' => $this->callLog->user_id,
            'created_by' => $this->callLog->user_id,
            'call_log_id' => $this->callLog->id,
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
            'is_system_generated' => true,
            'note' => "Auto-retry: {$retryReason->name} (attempt " . ($existingRetries + 1) . "/{$rule->max_retries})",
        ]);

        $this->callLog->lead->update(['next_follow_up_at' => $scheduledAt]);
    }
}
