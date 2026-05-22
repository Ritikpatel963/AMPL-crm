<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE campaigns MODIFY status ENUM('draft', 'active', 'paused', 'completed', 'archived') NOT NULL DEFAULT 'active'");
            DB::statement("ALTER TABLE campaigns MODIFY distribution ENUM('on_demand', 'equal', 'conditional', 'auto_assign') NOT NULL DEFAULT 'on_demand'");
            DB::statement("ALTER TABLE campaigns MODIFY priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium'");

            DB::statement("ALTER TABLE leads MODIFY source ENUM('FILE_UPLOAD', 'WALK_IN_LEAD', 'INCOMING_IVR', 'WORKFLOW', 'GOOGLE_SHEET', 'MANUAL', 'API', 'WEBHOOK') NOT NULL DEFAULT 'MANUAL'");

            DB::statement("ALTER TABLE contact_properties MODIFY data_type ENUM('text', 'number', 'email', 'date', 'dropdown', 'boolean', 'textarea') NOT NULL DEFAULT 'text'");

            DB::statement("ALTER TABLE call_logs MODIFY status ENUM('initiated', 'ringing', 'connected', 'answered', 'not_connected', 'busy', 'no_answer', 'failed', 'missed') NOT NULL DEFAULT 'not_connected'");

            DB::statement("ALTER TABLE dispositions MODIFY type ENUM('fresh', 'in_progress', 'closed_won', 'closed_lost', 'not_connected') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE campaigns MODIFY status ENUM('active', 'paused') NOT NULL DEFAULT 'active'");
            DB::statement("ALTER TABLE campaigns MODIFY distribution ENUM('on_demand', 'auto_assign') NOT NULL DEFAULT 'on_demand'");
            DB::statement("ALTER TABLE campaigns MODIFY priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium'");

            DB::statement("ALTER TABLE leads MODIFY source ENUM('FILE_UPLOAD', 'WALK_IN_LEAD', 'INCOMING_IVR', 'WORKFLOW', 'GOOGLE_SHEET', 'MANUAL') NOT NULL DEFAULT 'MANUAL'");

            DB::statement("ALTER TABLE contact_properties MODIFY data_type ENUM('text', 'number', 'date', 'dropdown') NOT NULL DEFAULT 'text'");

            DB::statement("ALTER TABLE call_logs MODIFY status ENUM('connected', 'not_connected', 'busy', 'no_answer') NOT NULL DEFAULT 'not_connected'");

            DB::statement("ALTER TABLE dispositions MODIFY type ENUM('in_progress', 'closed_won', 'closed_lost') NOT NULL");
        }
    }
};
