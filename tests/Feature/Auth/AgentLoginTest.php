<?php

use App\Models\User;
use App\Models\VendorOtp;
use App\Services\WatiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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

it('sends an agent login otp to the agent phone number', function () {
    User::factory()->create([
        'role' => 'agent',
        'email' => 'otp-agent@example.com',
        'phone_number' => '919630884927',
        'crm_status' => 'active',
        'approval_status' => 'approved',
    ]);

    $wati = \Mockery::mock(WatiService::class);
    $wati->shouldReceive('sendOtp')
        ->once()
        ->with('919630884927', \Mockery::on(fn ($otp) => is_string($otp) && strlen($otp) === 6))
        ->andReturnTrue();

    $this->app->instance(WatiService::class, $wati);

    $this->postJson('/api/agent/login/send-otp', [
        'phone_number' => '9630884927',
    ])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('phone', '919630884927');

    expect(VendorOtp::where('phone_number', '919630884927')->where('is_verified', false)->exists())->toBeTrue();
});

it('allows an agent to login with phone number and otp', function () {
    $agent = User::factory()->create([
        'role' => 'agent',
        'email' => 'otp-login-agent@example.com',
        'phone_number' => '919630884928',
        'crm_status' => 'active',
        'approval_status' => 'approved',
    ]);

    VendorOtp::create([
        'phone_number' => '919630884928',
        'otp' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
        'is_verified' => false,
    ]);

    $this->postJson('/api/agent/login/verify', [
        'phone_number' => '9630884928',
        'otp' => '123456',
    ])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('message', 'Agent login successful.')
        ->assertJsonPath('user.id', $agent->id)
        ->assertJsonStructure(['token']);

    expect(VendorOtp::where('phone_number', '919630884928')->where('is_verified', false)->exists())->toBeFalse();
});
