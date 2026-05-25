<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE leads
            SET pipeline_id = (
                SELECT campaigns.pipeline_id
                FROM campaigns
                WHERE campaigns.id = leads.campaign_id
            )
            WHERE leads.pipeline_id IS NULL
              AND EXISTS (
                SELECT 1
                FROM campaigns
                WHERE campaigns.id = leads.campaign_id
                  AND campaigns.pipeline_id IS NOT NULL
              )
        ');
    }

    public function down(): void
    {
        //
    }
};
