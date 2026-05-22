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

        User::updateOrCreate(
            ['email' => 'user1@user.com'],
            [
                'name' => 'User One',
                'password' => Hash::make('password'),
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'test@test.com'],
            [
                'name' => 'Test',
                'password' => Hash::make('password'),
            ]
        );

        $subadmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Main Admin',
                'password' => Hash::make('password'),
                'role' => 'subadmin',
            ]
        );

        $agent = User::updateOrCreate(
            ['email' => 'agent1@example.com'],
            [
                'name' => 'Agent One',
                'password' => Hash::make('password'),
                'role' => 'agent',
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer1@example.com'],
            [
                'name' => 'Customer One',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        AgentCustomerAssignment::firstOrCreate([
            'agent_id' => $agent->id,
            'customer_id' => $customer->id,
        ]);

        $this->call(CallingCrmSeeder::class);
    }
}


