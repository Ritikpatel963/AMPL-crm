<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CallingCrmSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CallingCrmDefaultsSeeder::class,
            CallingCrmPermissionSeeder::class,
        ]);
    }
}
