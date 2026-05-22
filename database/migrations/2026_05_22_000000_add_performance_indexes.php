<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── LEADS TABLE — the most heavily queried table ──
        Schema::table('leads', function (Blueprint $table) {
            // LeadController::index filters by campaign_id + status
            $table->index(['campaign_id', 'status'], 'leads_campaign_status_idx');
            // LeadController::index filters by campaign_id + assigned_user_id
            $table->index(['campaign_id', 'assigned_user_id'], 'leads_campaign_assigned_idx');
            // ReportController::userCallReport, LeadController scopes
            $table->index(['assigned_user_id', 'status'], 'leads_assigned_status_idx');
            // ReportController::leadsByStage
            $table->index(['pipeline_id', 'stage_id'], 'leads_pipeline_stage_idx');
            // Dashboard leads-by-stage with campaign filter
            $table->index(['campaign_id', 'stage_id'], 'leads_campaign_stage_idx');
            // General ordering
            $table->index('created_at', 'leads_created_at_idx');
        });

        // ── CALL_LOGS TABLE — reports query this heavily ──
        Schema::table('call_logs', function (Blueprint $table) {
            // ReportController::userCallReport
            $table->index(['user_id', 'started_at'], 'call_logs_user_started_idx');
            // ReportController::campaignReport, dashboardOverview
            $table->index(['campaign_id', 'started_at'], 'call_logs_campaign_started_idx');
            // General lead-level call lookups
            $table->index(['lead_id', 'status'], 'call_logs_lead_status_idx');
        });

        // ── FOLLOW_UPS TABLE ──
        Schema::table('follow_ups', function (Blueprint $table) {
            // FollowUpController::index, ReportController::followUpReport
            $table->index(['user_id', 'scheduled_at'], 'follow_ups_user_scheduled_idx');
            // Campaign-level follow-up queries
            $table->index(['campaign_id', 'status'], 'follow_ups_campaign_status_idx');
            // Lead-level follow-up lookups
            $table->index(['lead_id', 'status'], 'follow_ups_lead_status_idx');
        });

        // ── LEAD_DISPOSITIONS TABLE ──
        Schema::table('lead_dispositions', function (Blueprint $table) {
            // ReportController::userCallReport
            $table->index(['user_id', 'disposed_at'], 'lead_disps_user_disposed_idx');
            // Campaign-level disposition queries
            $table->index(['campaign_id', 'lead_id'], 'lead_disps_campaign_lead_idx');
        });

        // ── COMMUNICATION_EVENTS TABLE ──
        Schema::table('communication_events', function (Blueprint $table) {
            // ReportController::trendWidgets
            $table->index(['lead_id', 'channel'], 'comm_events_lead_channel_idx');
        });

        // ── MESSAGES TABLE — ChatComponent queries ──
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['sender_id', 'receiver_id'], 'messages_sender_receiver_idx');
                $table->index(['receiver_id', 'sender_id'], 'messages_receiver_sender_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_campaign_status_idx');
            $table->dropIndex('leads_campaign_assigned_idx');
            $table->dropIndex('leads_assigned_status_idx');
            $table->dropIndex('leads_pipeline_stage_idx');
            $table->dropIndex('leads_campaign_stage_idx');
            $table->dropIndex('leads_created_at_idx');
        });

        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropIndex('call_logs_user_started_idx');
            $table->dropIndex('call_logs_campaign_started_idx');
            $table->dropIndex('call_logs_lead_status_idx');
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropIndex('follow_ups_user_scheduled_idx');
            $table->dropIndex('follow_ups_campaign_status_idx');
            $table->dropIndex('follow_ups_lead_status_idx');
        });

        Schema::table('lead_dispositions', function (Blueprint $table) {
            $table->dropIndex('lead_disps_user_disposed_idx');
            $table->dropIndex('lead_disps_campaign_lead_idx');
        });

        Schema::table('communication_events', function (Blueprint $table) {
            $table->dropIndex('comm_events_lead_channel_idx');
        });

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('messages_sender_receiver_idx');
                $table->dropIndex('messages_receiver_sender_idx');
            });
        }
    }
};
