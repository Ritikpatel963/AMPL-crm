<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\VendorOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\WatiService;
use Mockery;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_login_with_valid_credentials()
    {
        $password = 'secret123';
        $agent = User::factory()->create([
            'role' => 'agent',
            'email' => 'agent@test.com',
            'password' => Hash::make($password),
            'crm_status' => 'active',
        ]);

        $response = $this->postJson('/api/agent/login', [
            'login' => 'agent@test.com',
            'password' => $password,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', true)
                 ->assertJsonStructure(['token']);
    }

    public function test_agent_login_fails_with_invalid_credentials()
    {
        $agent = User::factory()->create([
            'role' => 'agent',
            'email' => 'agent@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/agent/login', [
            'login' => 'agent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('status', false);
    }

    public function test_otp_rate_limiting_works()
    {
        $phone = '919876543210';
        $key = 'otp-attempts:' . $phone;

        // Simulate 3 attempts manually
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit($key, 3600);
        }

        $response = $this->postJson('/api/login/send-otp', [
            'phone_number' => $phone,
        ]);

        $response->assertStatus(429)
                 ->assertJsonPath('status', false);
    }
}
