<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lead;
use App\Models\Campaign;
use App\Models\Pipeline;
use App\Models\User;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'pipeline_id' => Pipeline::factory(),
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'status' => 'uncontacted',
            'deal_amount' => fake()->randomFloat(2, 10, 1000),
        ];
    }
}
