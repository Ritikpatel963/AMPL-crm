<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Pipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CallingCrmAdminSessionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_session_can_access_calling_crm_leads_without_sanctum_token(): void
    {
        $admin = Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.test',
            'phone' => '9999999999',
            'password' => Hash::make('password'),
            'is_main_admin' => true,
        ]);

        $pipeline = Pipeline::create(['name' => 'Indore lead']);
        $campaign = Campaign::create([
            'pipeline_id' => $pipeline->id,
            'name' => 'test3',
            'status' => 'active',
            'distribution' => 'equal',
        ]);

        Lead::create([
            'campaign_id' => $campaign->id,
            'pipeline_id' => $pipeline->id,
            'name' => 'anjali',
            'phone' => '6267604854',
            'source' => 'MANUAL',
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson('/admin_panel/admin/api/calling-crm/leads')
            ->assertOk()
            ->assertJsonPath('data.data.0.name', 'anjali')
            ->assertJsonPath('data.data.0.pipeline.name', 'Indore lead');
    }
}
