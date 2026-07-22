<?php

namespace Tests\Feature;

use App\Models\CallLog;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CallingCrmAgentLeadCallTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_campaign_list_contains_assigned_campaigns_even_before_leads_are_assigned(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $otherAgent = User::factory()->create(['role' => 'agent']);
        $assignedCampaign = $this->campaignForAgents([$agent->id]);
        $otherCampaign = $this->campaignForAgents([$otherAgent->id]);

        Lead::create([
            'campaign_id' => $assignedCampaign->id,
            'pipeline_id' => $assignedCampaign->pipeline_id,
            'assigned_user_id' => null,
            'name' => 'Unassigned Customer',
            'phone' => '9000000099',
            'source' => 'MANUAL',
        ]);

        $calledLead = Lead::create([
            'campaign_id' => $assignedCampaign->id,
            'pipeline_id' => $assignedCampaign->pipeline_id,
            'assigned_user_id' => $agent->id,
            'name' => 'Called Customer',
            'phone' => '9000000100',
            'source' => 'MANUAL',
            'status' => 'in_progress',
        ]);

        CallLog::create([
            'lead_id' => $calledLead->id,
            'campaign_id' => $assignedCampaign->id,
            'user_id' => $agent->id,
            'status' => 'connected',
            'direction' => 'outgoing',
            'phone_number' => '9000000100',
            'duration_seconds' => 70,
            'started_at' => now(),
            'called_at' => now(),
        ]);

        CallLog::create([
            'lead_id' => $calledLead->id,
            'campaign_id' => $assignedCampaign->id,
            'user_id' => $agent->id,
            'status' => 'not_connected',
            'direction' => 'outgoing',
            'phone_number' => '9000000100',
            'duration_seconds' => 0,
            'started_at' => now(),
            'called_at' => now(),
        ]);

        Sanctum::actingAs($agent);

        $this->getJson('/api/calling-crm/campaigns')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $assignedCampaign->id)
            ->assertJsonPath('data.data.0.name', 'May Calling Campaign')
            ->assertJsonPath('data.data.0.total_leads_count', 2)
            ->assertJsonPath('data.data.0.connected_calls_count', 1)
            ->assertJsonPath('data.data.0.disconnected_calls_count', 1)
            ->assertJsonPath('data.data.0.unassigned_leads_count', 1)
            ->assertJsonMissing(['id' => $otherCampaign->id]);
    }

    public function test_agent_lead_list_contains_only_assigned_campaign_leads_with_call_details(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $otherAgent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id, $otherAgent->id]);

        $assignedLead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $agent->id,
            'name' => 'Assigned Customer',
            'phone' => '9000000001',
            'email' => 'assigned@example.test',
            'source' => 'MANUAL',
            'metadata' => ['city' => 'Indore'],
        ]);

        Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $otherAgent->id,
            'name' => 'Other Customer',
            'phone' => '9000000002',
            'email' => 'other@example.test',
            'source' => 'MANUAL',
        ]);

        CallLog::create([
            'lead_id' => $assignedLead->id,
            'campaign_id' => $campaign->id,
            'user_id' => $agent->id,
            'status' => 'answered',
            'direction' => 'outgoing',
            'phone_number' => '9000000001',
            'duration_seconds' => 125,
            'recording_url' => 'https://recordings.example.test/call-1.mp3',
            'called_at' => now()->subMinutes(3),
            'started_at' => now()->subMinutes(3),
            'answered_at' => now()->subMinutes(2),
            'ended_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($agent);

        $this->getJson('/api/calling-crm/leads')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $assignedLead->id)
            ->assertJsonPath('data.data.0.name', 'Assigned Customer')
            ->assertJsonPath('data.data.0.phone', '9000000001')
            ->assertJsonPath('data.data.0.call_summary.total_calls', 1)
            ->assertJsonPath('data.data.0.call_summary.latest_duration_seconds', 125)
            ->assertJsonPath('data.data.0.call_summary.latest_recording_url', 'https://recordings.example.test/call-1.mp3')
            ->assertJsonPath('data.data.0.latest_call.status', 'answered');
    }

    public function test_agent_can_list_unassigned_leads_in_their_campaign(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $otherAgent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id, $otherAgent->id]);
        $otherCampaign = $this->campaignForAgents([$otherAgent->id]);

        $unassignedLead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => null,
            'name' => 'Visible Unassigned Lead',
            'phone' => '9000000010',
            'source' => 'MANUAL',
        ]);

        Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $otherAgent->id,
            'name' => 'Other Agent Assigned Lead',
            'phone' => '9000000011',
            'source' => 'MANUAL',
        ]);

        Lead::create([
            'campaign_id' => $otherCampaign->id,
            'pipeline_id' => $otherCampaign->pipeline_id,
            'assigned_user_id' => null,
            'name' => 'Other Campaign Unassigned Lead',
            'phone' => '9000000012',
            'source' => 'MANUAL',
        ]);

        Sanctum::actingAs($agent);

        $this->getJson('/api/calling-crm/leads?campaign_id=' . $campaign->id)
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $unassignedLead->id)
            ->assertJsonPath('data.data.0.name', 'Visible Unassigned Lead')
            ->assertJsonMissing(['name' => 'Other Agent Assigned Lead'])
            ->assertJsonMissing(['name' => 'Other Campaign Unassigned Lead']);
    }

    public function test_forced_distribution_can_assign_on_demand_campaign_leads(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id]);
        $campaign->update(['distribution' => 'on_demand']);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => null,
            'name' => 'Forced On Demand Lead',
            'phone' => '9000000014',
            'source' => 'MANUAL',
            'status' => 'uncontacted',
        ]);

        $assigned = app(\App\Services\CallingCrm\LeadAssignmentService::class)
            ->distributeUnassigned($campaign, 500, true);

        $this->assertSame(1, $assigned);
        $this->assertSame($agent->id, $lead->fresh()->assigned_user_id);
    }

    public function test_agent_can_view_assigned_lead_with_full_call_history(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id]);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $agent->id,
            'name' => 'Full Detail Customer',
            'phone' => '9000000003',
            'email' => 'full@example.test',
            'source' => 'MANUAL',
        ]);

        CallLog::create([
            'lead_id' => $lead->id,
            'campaign_id' => $campaign->id,
            'user_id' => $agent->id,
            'status' => 'connected',
            'direction' => 'outgoing',
            'provider_call_id' => 'provider-123',
            'phone_number' => '9000000003',
            'duration_seconds' => 240,
            'ring_duration_seconds' => 12,
            'recording_url' => 'https://recordings.example.test/call-2.mp3',
            'notes' => 'Customer interested',
            'called_at' => now(),
            'started_at' => now(),
            'answered_at' => now(),
            'ended_at' => now(),
        ]);

        Sanctum::actingAs($agent);

        $this->getJson('/api/calling-crm/leads/' . $lead->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Full Detail Customer')
            ->assertJsonPath('data.call_logs.0.duration_seconds', 240)
            ->assertJsonPath('data.call_logs.0.ring_duration_seconds', 12)
            ->assertJsonPath('data.call_logs.0.recording_url', 'https://recordings.example.test/call-2.mp3')
            ->assertJsonPath('data.call_logs.0.provider_call_id', 'provider-123');
    }

    public function test_agent_can_start_call_and_fetch_call_details(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id]);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $agent->id,
            'name' => 'Callable Customer',
            'phone' => '9000000015',
            'source' => 'MANUAL',
        ]);

        Sanctum::actingAs($agent);

        $this->postJson('/api/calling-crm/calls/start', [
            'lead_id' => $lead->id,
            'phone_number' => $lead->phone,
        ])
            ->assertCreated()
            ->assertJsonPath('data.lead_id', $lead->id)
            ->assertJsonPath('data.phone_number', '9000000015')
            ->assertJsonPath('data.status', 'initiated');

        $call = CallLog::where('lead_id', $lead->id)->firstOrFail();

        $this->patchJson('/api/calling-crm/calls/' . $call->id, [
            'status' => 'connected',
            'duration_seconds' => 67,
            'ended_at' => now()->toISOString(),
        ])
            ->assertOk()
            ->assertJsonPath('data.duration_seconds', 67);

        $this->getJson('/api/calling-crm/calls?lead_id=' . $lead->id)
            ->assertOk()
            ->assertJsonPath('data.data.0.lead.name', 'Callable Customer')
            ->assertJsonPath('data.data.0.phone_number', '9000000015')
            ->assertJsonPath('data.data.0.status', 'connected')
            ->assertJsonPath('data.data.0.duration_seconds', 67);
    }

    public function test_agent_can_upload_call_recording(): void
    {
        Storage::fake('public');

        $agent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id]);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $agent->id,
            'name' => 'Recording Customer',
            'phone' => '9000000016',
            'source' => 'MANUAL',
        ]);

        $call = CallLog::create([
            'lead_id' => $lead->id,
            'campaign_id' => $campaign->id,
            'user_id' => $agent->id,
            'status' => 'connected',
            'direction' => 'outgoing',
            'phone_number' => $lead->phone,
            'duration_seconds' => 67,
            'called_at' => now()->subMinute(),
            'started_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($agent);

        $this->post('/api/calling-crm/calls/' . $call->id . '/recording', [
            'recording' => UploadedFile::fake()->create('call.mp3', 128, 'audio/mpeg'),
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $call->id);

        $this->assertNotNull($call->fresh()->recording_url);
        Storage::disk('public')->assertExists(
            str_replace('/storage/', '', parse_url($call->fresh()->recording_url, PHP_URL_PATH))
        );
    }

    public function test_agent_cannot_access_other_agents_lead_or_start_call_for_it(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $otherAgent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id, $otherAgent->id]);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $otherAgent->id,
            'name' => 'Other Agent Lead',
            'phone' => '9000000004',
            'source' => 'MANUAL',
        ]);

        Sanctum::actingAs($agent);

        $this->getJson('/api/calling-crm/leads/' . $lead->id)->assertForbidden();

        $this->postJson('/api/calling-crm/calls/start', [
            'lead_id' => $lead->id,
            'phone_number' => $lead->phone,
        ])->assertForbidden();
    }

    public function test_agent_can_fetch_lead_disposition_report_with_call_log(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $campaign = $this->campaignForAgents([$agent->id]);

        $lead = Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $campaign->pipeline_id,
            'assigned_user_id' => $agent->id,
            'name' => 'Report Customer',
            'phone' => '9000000017',
            'source' => 'MANUAL',
        ]);

        $call = CallLog::create([
            'lead_id' => $lead->id,
            'campaign_id' => $campaign->id,
            'user_id' => $agent->id,
            'status' => 'connected',
            'direction' => 'outgoing',
            'phone_number' => $lead->phone,
            'duration_seconds' => 120,
            'recording_url' => 'https://recordings.example.test/call-report.mp3',
            'called_at' => now(),
            'started_at' => now(),
        ]);

        $disposition = \App\Models\Disposition::create([
            'pipeline_id' => $campaign->pipeline_id,
            'name' => 'Interested',
            'type' => 'in_progress',
        ]);

        \App\Models\LeadDisposition::create([
            'lead_id' => $lead->id,
            'call_log_id' => $call->id,
            'user_id' => $agent->id,
            'campaign_id' => $campaign->id,
            'disposition_id' => $disposition->id,
            'call_status' => 'connected',
            'disposed_at' => now(),
        ]);

        Sanctum::actingAs($agent);

        $this->getJson('/api/calling-crm/reports/lead-disposition')
            ->assertOk()
            ->assertJsonPath('data.data.0.call_log.recording_url', 'https://recordings.example.test/call-report.mp3');
    }

    private function campaignForAgents(array $agentIds): Campaign
    {
        $pipeline = Pipeline::create(['name' => 'Sales Pipeline']);
        $campaign = Campaign::create([
            'pipeline_id' => $pipeline->id,
            'name' => 'May Calling Campaign',
            'status' => 'active',
            'distribution' => 'equal',
        ]);

        $sync = [];
        foreach ($agentIds as $agentId) {
            $sync[$agentId] = ['role' => 'agent', 'is_active' => true];
        }

        $campaign->users()->sync($sync);

        return $campaign;
    }
}
