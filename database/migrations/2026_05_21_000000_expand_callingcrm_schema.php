<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * This migration was originally a large all-in-one Calling CRM schema expansion.
     *
     * It has been intentionally kept as a no-op marker because it was already
     * applied locally before the schema was split into smaller domain migrations.
     * The actual schema work now lives in the following grouped migrations:
     *
     * - 2026_05_21_000010_create_callingcrm_business_profiles_and_teams.php
     * - 2026_05_21_000020_expand_callingcrm_pipelines_stages_and_campaigns.php
     * - 2026_05_21_000030_create_callingcrm_lead_sources_imports_and_lead_extensions.php
     * - 2026_05_21_000040_create_callingcrm_activity_settings_and_utilities.php
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
