<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AgentCustomerAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       User::factory()->create([
    'name' => 'User One',
    'email' => 'user1@user.com',
]);
        
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
        ]);
        
        User::factory()->create([
            'name' => 'Test',
            'email' => 'test@test.com',
        ]);

        $subadmin = User::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'subadmin',
        ]);

        $agent = User::create([
            'name' => 'Agent One',
            'email' => 'agent1@example.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
        ]);

        $customer = User::create([
            'name' => 'Customer One',
            'email' => 'customer1@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        AgentCustomerAssignment::create([
            'agent_id' => $agent->id,
            'customer_id' => $customer->id,
        ]);

    }
}


