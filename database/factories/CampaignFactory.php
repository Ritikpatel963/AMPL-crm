<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Campaign;
use App\Models\Pipeline;
use App\Models\User;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'pipeline_id' => Pipeline::factory(),
            'manager_id' => User::factory(),
            'name' => fake()->company() . ' Campaign',
            'status' => 'active',
            'distribution' => 'auto_assign',
            'priority' => 'medium',
        ];
    }
}
