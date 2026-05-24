<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an agent to login with email and password', function () {
    $agent = User::factory()->create([
        'role' => 'agent',
        'email' => 'agent@example.com',
        'phone_number' => '919630884927',
        'password' => 'secret123',
        'crm_status' => 'active',
        'approval_status' => 'approved',
    ]);

    $this->postJson('/api/agent/login', [
        'email' => 'agent@example.com',
        'password' => 'secret123',
    ])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('user.id', $agent->id);
});

it('allows an agent to login with phone number and password', function () {
    $agent = User::factory()->create([
        'role' => 'agent',
        'email' => 'phone-agent@example.com',
        'phone_number' => '919630884927',
        'password' => 'secret123',
        'crm_status' => 'active',
        'approval_status' => 'approved',
    ]);

    $this->postJson('/api/agent/login', [
        'phone_number' => '9630884927',
        'password' => 'secret123',
    ])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('user.id', $agent->id);
});
