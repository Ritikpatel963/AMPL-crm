<?php

namespace App\Http\Controllers\callingcrm;

use App\Http\Controllers\Controller;
use App\Models\callingcrm\Campaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pipeline_id' => ['required', 'exists:pipelines,id'],
            'name' => ['required', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', Rule::in(Campaign::STATUS_OPTIONS)],
            'distribution' => ['nullable', Rule::in(Campaign::DISTRIBUTION_OPTIONS)],
            'priority' => ['nullable', Rule::in(Campaign::PRIORITY_OPTIONS)],
            'agent_ids' => ['nullable', 'array'],
            'agent_ids.*' => ['exists:users,id'],
        ]);

        if (!empty($validated['agent_ids']) && isset($validated['manager_id']) && in_array($validated['manager_id'], $validated['agent_ids'], true) === false) {
            $validated['agent_ids'][] = $validated['manager_id'];
        }

        $campaign = Campaign::create([
            'pipeline_id' => $validated['pipeline_id'],
            'name' => $validated['name'],
            'manager_id' => $validated['manager_id'] ?? null,
            'status' => $validated['status'] ?? Campaign::STATUS_ACTIVE,
            'distribution' => $validated['distribution'] ?? Campaign::DISTRIBUTION_ON_DEMAND,
            'priority' => $validated['priority'] ?? Campaign::PRIORITY_MEDIUM,
        ]);

        $campaign->agents()->sync($validated['agent_ids'] ?? []);

        return redirect()
            ->route('callingcrm.pipeline.show', $campaign)
            ->with('success', 'Calling CRM campaign created successfully.');
    }
}
