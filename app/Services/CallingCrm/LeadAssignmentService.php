<?php

namespace App\Services\CallingCrm;

use App\Models\AssignmentRule;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeadAssignmentService
{
    public function assignLead(Lead $lead, bool $forceOnDemand = false): ?int
    {
        if ($lead->assigned_user_id) {
            return $lead->assigned_user_id;
        }

        $campaign = $lead->campaign ?: Campaign::find($lead->campaign_id);
        if (!$campaign) {
            return null;
        }

        $userId = null;

        if ($campaign->distribution === 'conditional') {
            $userId = $this->conditionalAgentId($campaign, $lead)
                ?? $this->conditionalFallbackAgentId($campaign)
                ?? $this->nextEqualAgentId($campaign);
        } elseif ($campaign->distribution === 'on_demand') {
            $userId = $forceOnDemand ? $this->nextEqualAgentId($campaign) : null;
        } else {
            if ($lead->location_id) {
                $userId = $this->locationBasedAgentId($campaign, $lead->location_id);
            }
            if (!$userId) {
                $userId = $this->nextEqualAgentId($campaign);
            }
        }

        if (!$userId) {
            return null;
        }

        $lead->forceFill(['assigned_user_id' => $userId])->save();
        $this->refreshCampaignAgentCounts($campaign);

        return $userId;
    }

    public function distributeUnassigned(Campaign $campaign, int $limit = 500, bool $forceOnDemand = false): int
    {
        if ($campaign->distribution === 'on_demand' && ! $forceOnDemand) {
            return 0;
        }

        $updated = 0;

        Lead::where('campaign_id', $campaign->id)
            ->whereNull('assigned_user_id')
            ->where('status', 'uncontacted')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (Lead $lead) use (&$updated, $forceOnDemand) {
                if ($this->assignLead($lead, $forceOnDemand)) {
                    $updated++;
                }
            });

        $this->refreshCampaignAgentCounts($campaign);

        return $updated;
    }

    public function claimNextForUser(Campaign $campaign, User $user, int $chunkSize): Collection
    {
        if (!$this->campaignAgentIds($campaign)->contains($user->id)) {
            return collect();
        }

        return DB::transaction(function () use ($campaign, $user, $chunkSize) {
            $leads = Lead::where('campaign_id', $campaign->id)
                ->whereNull('assigned_user_id')
                ->where('status', 'uncontacted')
                ->orderBy('created_at')
                ->limit($chunkSize)
                ->lockForUpdate()
                ->get();

            if ($leads->isNotEmpty()) {
                Lead::whereIn('id', $leads->pluck('id'))->update(['assigned_user_id' => $user->id]);
                $this->refreshCampaignAgentCounts($campaign);
            }

            return Lead::whereIn('id', $leads->pluck('id'))
                ->with(['campaign:id,name', 'stage:id,name,color', 'tag:id,name,color'])
                ->get();
        });
    }

    public function refreshCampaignAgentCounts(Campaign $campaign): void
    {
        $counts = Lead::where('campaign_id', $campaign->id)
            ->whereNotNull('assigned_user_id')
            ->select('assigned_user_id', DB::raw('count(*) as aggregate'))
            ->groupBy('assigned_user_id')
            ->pluck('aggregate', 'assigned_user_id');

        $campaign->users()->each(function (User $user) use ($campaign, $counts) {
            $campaign->users()->updateExistingPivot($user->id, [
                'assigned_leads_count' => (int) ($counts[$user->id] ?? 0),
            ]);
        });
    }

    private function locationBasedAgentId(Campaign $campaign, int $locationId): ?int
    {
        $agentIds = $this->campaignAgentIds($campaign);
        if ($agentIds->isEmpty()) {
            return null;
        }

        $locationAgentIds = User::whereIn('id', $agentIds)
            ->where('location_id', $locationId)
            ->pluck('id');

        if ($locationAgentIds->isEmpty()) {
            return null;
        }

        $counts = Lead::where('campaign_id', $campaign->id)
            ->whereIn('assigned_user_id', $locationAgentIds)
            ->select('assigned_user_id', DB::raw('count(*) as aggregate'))
            ->groupBy('assigned_user_id')
            ->pluck('aggregate', 'assigned_user_id');

        return $locationAgentIds
            ->sortBy(fn (int $agentId) => (int) ($counts[$agentId] ?? 0))
            ->first();
    }

    private function nextEqualAgentId(Campaign $campaign): ?int
    {
        $agentIds = $this->campaignAgentIds($campaign);
        if ($agentIds->isEmpty()) {
            return null;
        }

        $counts = Lead::where('campaign_id', $campaign->id)
            ->whereIn('assigned_user_id', $agentIds)
            ->select('assigned_user_id', DB::raw('count(*) as aggregate'))
            ->groupBy('assigned_user_id')
            ->pluck('aggregate', 'assigned_user_id');

        return $agentIds
            ->sortBy(fn (int $agentId) => (int) ($counts[$agentId] ?? 0))
            ->first();
    }

    private function conditionalAgentId(Campaign $campaign, Lead $lead): ?int
    {
        $agentIds = $this->campaignAgentIds($campaign);
        if ($agentIds->isEmpty()) {
            return null;
        }

        $lead->loadMissing('propertyValues.property');

        $rules = AssignmentRule::where('campaign_id', $campaign->id)
            ->where('is_active', true)
            ->whereIn('user_id', $agentIds)
            ->orderBy('sort_order')
            ->get();

        foreach ($rules as $rule) {
            if ($this->ruleMatches($rule, $lead)) {
                return $rule->user_id;
            }
        }

        foreach (($campaign->settings['conditional_rules'] ?? []) as $rule) {
            $userId = (int) ($rule['user_id'] ?? 0);
            if ($userId && $agentIds->contains($userId) && $this->arrayRuleMatches($rule, $lead)) {
                return $userId;
            }
        }

        return null;
    }

    private function conditionalFallbackAgentId(Campaign $campaign): ?int
    {
        $fallbackUserId = (int) ($campaign->settings['fallback_user_id'] ?? 0);

        if (! $fallbackUserId) {
            return null;
        }

        return $this->campaignAgentIds($campaign)->contains($fallbackUserId)
            ? $fallbackUserId
            : null;
    }

    private function campaignAgentIds(Campaign $campaign): Collection
    {
        return $campaign->users()
            ->where(function ($query) {
                $query->where('campaign_user.is_active', true)
                    ->orWhereNull('campaign_user.is_active');
            })
            ->where(function ($query) {
                $query->where('campaign_user.role', 'agent')
                    ->orWhereNull('campaign_user.role');
            })
            ->whereIn('users.role', ['agent', 'subadmin'])
            ->where(function ($query) {
                $query->where('users.lead_assignment_enabled', true)
                    ->orWhereNull('users.lead_assignment_enabled');
            })
            ->where(function ($query) {
                $query->whereNull('users.crm_status')->orWhere('users.crm_status', 'active');
            })
            ->pluck('users.id');
    }

    private function ruleMatches(AssignmentRule $rule, Lead $lead): bool
    {
        return $this->matches(
            $this->fieldValue($lead, $rule->condition_field),
            $rule->condition_operator,
            $rule->condition_value
        );
    }

    private function arrayRuleMatches(array $rule, Lead $lead): bool
    {
        return $this->matches(
            $this->fieldValue($lead, (string) ($rule['field'] ?? $rule['condition_field'] ?? '')),
            (string) ($rule['operator'] ?? $rule['condition_operator'] ?? 'equals'),
            (string) ($rule['value'] ?? $rule['condition_value'] ?? '')
        );
    }

    private function fieldValue(Lead $lead, string $field): mixed
    {
        $field = trim($field);

        if (str_starts_with($field, 'property:')) {
            $key = substr($field, 9);
            return $lead->propertyValues->first(fn ($value) => $value->property?->slug === $key || $value->property?->name === $key)?->value_text;
        }

        if (is_numeric($field)) {
            return $lead->propertyValues->firstWhere('property_id', (int) $field)?->value_text;
        }

        return $lead->{$field} ?? data_get($lead->metadata, $field);
    }

    private function matches(mixed $actual, string $operator, string $expected): bool
    {
        $actual = strtolower(trim((string) $actual));
        $expected = strtolower(trim($expected));

        return match ($operator) {
            'contains' => $expected !== '' && str_contains($actual, $expected),
            'starts_with' => $expected !== '' && str_starts_with($actual, $expected),
            'ends_with' => $expected !== '' && str_ends_with($actual, $expected),
            'not_equals' => $actual !== $expected,
            default => $actual === $expected,
        };
    }
}
