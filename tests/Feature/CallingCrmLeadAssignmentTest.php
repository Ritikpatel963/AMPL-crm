<?php

use App\Models\AssignmentRule;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\User;
use App\Services\CallingCrm\LeadAssignmentService;
use Laravel\Sanctum\Sanctum;

function crmAgent(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'agent',
        'crm_status' => 'active',
        'lead_assignment_enabled' => true,
    ], $attributes));
}

function crmCampaign(string $distribution, array $agents = []): Campaign
{
    $pipeline = Pipeline::create(['name' => 'Sales']);
    $campaign = Campaign::create([
        'pipeline_id' => $pipeline->id,
        'name' => 'Campaign',
        'distribution' => $distribution,
        'status' => 'active',
    ]);

    foreach ($agents as $agent) {
        $campaign->users()->attach($agent->id, [
            'role' => 'agent',
            'is_active' => true,
            'assigned_leads_count' => 0,
        ]);
    }

    return $campaign->fresh('users');
}

it('leaves on demand leads unassigned until claimed', function () {
    $agent = crmAgent();
    $campaign = crmCampaign('on_demand', [$agent]);

    $lead = Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'name' => 'Lead 1',
        'phone' => '9000000001',
        'status' => 'uncontacted',
    ]);

    app(LeadAssignmentService::class)->assignLead($lead);

    expect($lead->fresh()->assigned_user_id)->toBeNull();
});

it('distributes equal campaign leads across active agents', function () {
    $agentA = crmAgent();
    $agentB = crmAgent();
    $campaign = crmCampaign('equal', [$agentA, $agentB]);
    $service = app(LeadAssignmentService::class);

    foreach (range(1, 4) as $index) {
        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'name' => 'Lead ' . $index,
            'phone' => '900000000' . $index,
            'status' => 'uncontacted',
        ]);
        $service->assignLead($lead);
    }

    $counts = Lead::where('campaign_id', $campaign->id)
        ->selectRaw('assigned_user_id, count(*) as total')
        ->groupBy('assigned_user_id')
        ->pluck('total', 'assigned_user_id');

    expect((int) $counts[$agentA->id])->toBe(2);
    expect((int) $counts[$agentB->id])->toBe(2);
});

it('uses conditional assignment rules before equal fallback', function () {
    $indoreAgent = crmAgent();
    $fallbackAgent = crmAgent();
    $campaign = crmCampaign('conditional', [$indoreAgent, $fallbackAgent]);

    AssignmentRule::create([
        'campaign_id' => $campaign->id,
        'user_id' => $indoreAgent->id,
        'name' => 'Indore source',
        'condition_field' => 'source',
        'condition_operator' => 'equals',
        'condition_value' => 'MANUAL',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $lead = Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'name' => 'Indore Lead',
        'phone' => '9000000010',
        'source' => 'MANUAL',
        'status' => 'uncontacted',
    ]);

    app(LeadAssignmentService::class)->assignLead($lead);

    expect($lead->fresh()->assigned_user_id)->toBe($indoreAgent->id);
});

it('creates conditional campaign rules with the campaign and uses the fallback user', function () {
    $matchedAgent = crmAgent();
    $fallbackAgent = crmAgent();
    $pipeline = Pipeline::create(['name' => 'Conditional Pipeline']);

    Sanctum::actingAs($matchedAgent);

    $response = $this->postJson('/api/calling-crm/campaigns', [
        'name' => 'Conditional Campaign',
        'pipeline_id' => $pipeline->id,
        'distribution' => 'conditional',
        'settings' => [
            'fallback_user_id' => $fallbackAgent->id,
            'conditional_rules' => [
                [
                    'user_id' => $matchedAgent->id,
                    'condition_field' => 'source',
                    'condition_operator' => 'equals',
                    'condition_value' => 'WEB',
                ],
            ],
        ],
    ])->assertCreated();

    $campaign = Campaign::findOrFail($response->json('data.id'));

    expect($campaign->assignmentRules)->toHaveCount(1);
    expect($campaign->users()->whereKey($matchedAgent->id)->exists())->toBeTrue();
    expect($campaign->users()->whereKey($fallbackAgent->id)->exists())->toBeTrue();

    $lead = Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $pipeline->id,
        'name' => 'Fallback Lead',
        'phone' => '9000000011',
        'source' => 'MANUAL',
        'status' => 'uncontacted',
    ]);

    app(LeadAssignmentService::class)->assignLead($lead);

    expect($lead->fresh()->assigned_user_id)->toBe($fallbackAgent->id);
});

it('claim next assigns on demand leads only to campaign agents', function () {
    $agent = crmAgent();
    $outsider = crmAgent();
    $campaign = crmCampaign('on_demand', [$agent]);

    Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'name' => 'Claim Lead',
        'phone' => '9000000020',
        'status' => 'uncontacted',
    ]);

    $service = app(LeadAssignmentService::class);

    expect($service->claimNextForUser($campaign, $outsider, 1))->toHaveCount(0);
    expect($service->claimNextForUser($campaign, $agent, 1))->toHaveCount(1);
    expect(Lead::first()->assigned_user_id)->toBe($agent->id);
});

it('claim next trusts explicit campaign agents even when a manager is set', function () {
    $manager = crmAgent(['role' => 'subadmin']);
    $agent = crmAgent(['reporting_manager_id' => null]);
    $campaign = crmCampaign('on_demand', [$agent]);
    $campaign->update(['manager_id' => $manager->id]);

    Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'name' => 'Managed Campaign Lead',
        'phone' => '9000000021',
        'status' => 'uncontacted',
    ]);

    $claimed = app(LeadAssignmentService::class)->claimNextForUser($campaign->fresh('users'), $agent, 1);

    expect($claimed)->toHaveCount(1);
    expect(Lead::first()->assigned_user_id)->toBe($agent->id);
});

it('can intentionally unassign leads through the reassignment api', function () {
    $agent = crmAgent();
    $campaign = crmCampaign('equal', [$agent]);
    $lead = Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'assigned_user_id' => $agent->id,
        'name' => 'Assigned Lead',
        'phone' => '9000000030',
        'status' => 'uncontacted',
    ]);

    Sanctum::actingAs($agent);

    $this->postJson('/api/calling-crm/leads/reassign', [
        'lead_ids' => [$lead->id],
        'assigned_user_id' => null,
    ])->assertOk();

    expect($lead->fresh()->assigned_user_id)->toBeNull();
});

it('rejects assigning a lead to a user outside the campaign', function () {
    $agent = crmAgent();
    $outsider = crmAgent();
    $campaign = crmCampaign('equal', [$agent]);
    $lead = Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'name' => 'Protected Lead',
        'phone' => '9000000031',
        'status' => 'uncontacted',
    ]);

    Sanctum::actingAs($agent);

    $this->postJson('/api/calling-crm/leads/reassign', [
        'lead_ids' => [$lead->id],
        'assigned_user_id' => $outsider->id,
    ])->assertUnprocessable();

    expect($lead->fresh()->assigned_user_id)->toBeNull();
});

it('keeps existing leads aligned when a campaign pipeline changes', function () {
    $agent = crmAgent();
    $campaign = crmCampaign('equal', [$agent]);
    $oldStage = LeadStage::create([
        'pipeline_id' => $campaign->pipeline_id,
        'name' => 'Old Stage',
        'code' => 'old_stage',
        'category' => 'fresh',
    ]);
    $newPipeline = Pipeline::create(['name' => 'Renewals']);
    $lead = Lead::create([
        'campaign_id' => $campaign->id,
        'pipeline_id' => $campaign->pipeline_id,
        'stage_id' => $oldStage->id,
        'name' => 'Pipeline Lead',
        'phone' => '9000000032',
        'status' => 'uncontacted',
    ]);

    Sanctum::actingAs($agent);

    $this->putJson('/api/calling-crm/campaigns/' . $campaign->id, [
        'name' => $campaign->name,
        'pipeline_id' => $newPipeline->id,
        'distribution' => 'equal',
        'agent_ids' => [$agent->id],
    ])->assertOk();

    $lead->refresh();

    expect($lead->pipeline_id)->toBe($newPipeline->id);
    expect($lead->stage_id)->toBeNull();
});
