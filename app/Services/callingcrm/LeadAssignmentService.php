<?php

namespace App\Services\callingcrm;

use App\Models\callingcrm\Campaign;
use App\Models\callingcrm\Lead;
use Illuminate\Support\Facades\DB;

class LeadAssignmentService
{
    /**
     * Assign a lead to an agent based on the campaign's distribution rules.
     * Neo Dove Style: Condition based -> Round Robin (equal distribution)
     *
     * @param Campaign $campaign
     * @param array $leadData
     * @param array $propertyValues
     * @return int|null Assigned User ID or null if unassigned
     */
    public function determineAgent(Campaign $campaign, array $leadData, array $propertyValues = []): ?int
    {
        // If it's manual / on_demand, return null (unassigned) unless someone manually sets user_id
        if ($campaign->distribution !== Campaign::DISTRIBUTION_AUTO_ASSIGN) {
            return null;
        }

        $agents = $campaign->agents()->pluck('users.id');
        
        if ($agents->isEmpty()) {
            return null;
        }

        // 1. CONDITION-BASED ASSIGNMENT
        // Example: If lead source is Facebook, or area/city (passed via properties or tags)
        $conditionBasedUserId = $this->checkConditions($leadData, $propertyValues, $agents->toArray());
        if ($conditionBasedUserId) {
            return $conditionBasedUserId;
        }

        // 2. EQUAL DISTRIBUTION (ROUND ROBIN)
        return $this->getRoundRobinAgent($campaign->id, $agents->toArray());
    }

    /**
     * Placeholder for condition-based routing.
     * You can expand this to query a `assignment_rules` table.
     */
    protected function checkConditions(array $leadData, array $propertyValues, array $availableAgentIds): ?int
    {
        // Fetch rules for this campaign from the DB, ordered by priority/sort_order
        $rules = \App\Models\callingcrm\AssignmentRule::where('campaign_id', $leadData['campaign_id'] ?? 0)
            ->orderBy('sort_order')
            ->get();

        foreach ($rules as $rule) {
            // Check if the assigned agent in the rule is actually available in the campaign
            if (!in_array($rule->user_id, $availableAgentIds)) {
                continue;
            }

            $fieldValue = null;

            // Determine if the rule field is a standard field ('source', 'tags') or a custom property (like 'property_1')
            if (in_array($rule->condition_field, ['source', 'tags'])) {
                $fieldValue = $leadData[$rule->condition_field] ?? '';
                // Since tags can be an array in some places, flatten it
                if (is_array($fieldValue)) {
                    $fieldValue = implode(',', $fieldValue);
                }
            } else {
                // Assuming condition_field holds property ID for custom properties
                $fieldValue = $propertyValues[$rule->condition_field] ?? '';
            }

            $fieldValue = strtolower(trim((string) $fieldValue));
            $targetValue = strtolower(trim($rule->condition_value));

            // Evaluate the condition
            $isMatch = false;
            if ($rule->condition_operator === 'equals') {
                $isMatch = ($fieldValue === $targetValue);
            } elseif ($rule->condition_operator === 'contains') {
                $isMatch = (strpos($fieldValue, $targetValue) !== false);
            }

            if ($isMatch) {
                return $rule->user_id; // Return the specific agent matched by the rule
            }
        }

        return null; // No rules matched
    }

    /**
     * Get the agent with the lowest number of leads in this campaign.
     * This ensures EQUAL DISTRIBUTION (Round Robin).
     */
    protected function getRoundRobinAgent(int $campaignId, array $agentIds): int
    {
        // Query the number of leads assigned to each agent in this campaign
        $leadCounts = Lead::query()
            ->where('campaign_id', $campaignId)
            ->whereIn('user_id', $agentIds)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->toArray();

        $lowestCount = null;
        $selectedAgentId = $agentIds[0];

        // Find the agent with the minimum count
        // Default to 0 if the agent isn't in the leadCounts array (meaning they have 0 leads)
        foreach ($agentIds as $agentId) {
            $count = $leadCounts[$agentId] ?? 0;

            if ($lowestCount === null || $count < $lowestCount) {
                $lowestCount = $count;
                $selectedAgentId = $agentId;
            }
        }

        return $selectedAgentId;
    }
}
