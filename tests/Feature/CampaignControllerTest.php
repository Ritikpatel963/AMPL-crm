<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Pipeline;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_campaign()
    {
        $admin = User::factory()->create(['role' => 'subadmin']);
        $pipeline = Pipeline::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/calling-crm/campaigns', [
            'name' => 'New Q3 Campaign',
            'pipeline_id' => $pipeline->id,
            'distribution' => 'auto_assign',
            'priority' => 'medium',
            'status' => 'active'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', true);
        
        $this->assertDatabaseHas('campaigns', ['name' => 'New Q3 Campaign']);
    }

    public function test_admin_can_sync_agents_to_campaign()
    {
        $admin = User::factory()->create(['role' => 'subadmin']);
        $campaign = Campaign::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($admin)->postJson("/api/calling-crm/campaigns/{$campaign->id}/agents", [
            'agent_ids' => [ $agent->id ]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('campaign_user', [
            'campaign_id' => $campaign->id,
            'user_id' => $agent->id
        ]);
    }
}
