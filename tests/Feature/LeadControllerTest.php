<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Lead;
use App\Models\Campaign;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_leads()
    {
        $admin = User::factory()->create(['role' => 'subadmin']);
        Lead::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/calling-crm/leads');

        $response->assertStatus(200)
                 ->assertJsonPath('status', true);
        
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_agent_can_only_bulk_update_assigned_leads()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        // Lead assigned to agent
        $lead1 = Lead::factory()->create(['assigned_user_id' => $agent->id]);
        
        // Lead NOT assigned to agent
        $lead2 = Lead::factory()->create(['assigned_user_id' => null]);

        $response = $this->actingAs($agent)->postJson('/api/calling-crm/leads/bulk/update', [
            'lead_ids' => [$lead1->id, $lead2->id],
            'status' => 'converted'
        ]);

        $response->assertStatus(200);

        // Should only update lead1
        $this->assertEquals('converted', $lead1->fresh()->status);
        
        // lead2 should remain unchanged
        $this->assertEquals('uncontacted', $lead2->fresh()->status);
    }

    public function test_agent_can_only_bulk_delete_assigned_leads()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        $lead1 = Lead::factory()->create(['assigned_user_id' => $agent->id]);
        $lead2 = Lead::factory()->create(['assigned_user_id' => null]);

        $response = $this->actingAs($agent)->postJson('/api/calling-crm/leads/bulk/delete', [
            'lead_ids' => [$lead1->id, $lead2->id]
        ]);

        $response->assertStatus(200);

        // lead1 should be deleted
        $this->assertSoftDeleted('leads', ['id' => $lead1->id]);
        
        // lead2 should NOT be deleted
        $this->assertDatabaseHas('leads', ['id' => $lead2->id, 'deleted_at' => null]);
    }
}
