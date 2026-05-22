<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->expandActivityTables();
        $this->createSettingsTables();
        $this->createUtilityTables();
        $this->expandAssignmentRulesTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('saved_filters');
        Schema::dropIfExists('timeline_events');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('communication_events');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('user_breaks');
        Schema::dropIfExists('lead_priority_rules');
        Schema::dropIfExists('retry_rules');
        Schema::dropIfExists('retry_reasons');
        Schema::dropIfExists('lead_dispositions');

        $this->dropColumns('assignment_rules', ['name', 'is_active', 'settings']);
        $this->dropColumns('user_sessions', ['status', 'ip_address', 'user_agent']);
        $this->dropColumns('follow_ups', ['campaign_id', 'created_by', 'call_log_id', 'status', 'is_system_generated', 'deleted_at']);
        $this->dropColumns('call_logs', [
            'campaign_id',
            'disposition_id',
            'direction',
            'provider_call_id',
            'phone_number',
            'started_at',
            'answered_at',
            'ended_at',
            'duration_seconds',
            'ring_duration_seconds',
            'recording_url',
            'metadata',
        ]);
        $this->dropColumns('dispositions', ['stage_id', 'tag_id', 'requires_follow_up', 'requires_note', 'marks_lead_closed', 'is_active', 'deleted_at']);
    }

    private function expandActivityTables(): void
    {
        Schema::table('dispositions', function (Blueprint $table) {
            if (! Schema::hasColumn('dispositions', 'stage_id')) {
                $table->foreignId('stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
            }

            if (! Schema::hasColumn('dispositions', 'tag_id')) {
                $table->foreignId('tag_id')->nullable()->constrained('stage_tags')->nullOnDelete();
            }

            if (! Schema::hasColumn('dispositions', 'requires_follow_up')) {
                $table->boolean('requires_follow_up')->default(false);
            }

            if (! Schema::hasColumn('dispositions', 'requires_note')) {
                $table->boolean('requires_note')->default(false);
            }

            if (! Schema::hasColumn('dispositions', 'marks_lead_closed')) {
                $table->boolean('marks_lead_closed')->default(false);
            }

            if (! Schema::hasColumn('dispositions', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('dispositions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('call_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('call_logs', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            }

            if (! Schema::hasColumn('call_logs', 'disposition_id')) {
                $table->foreignId('disposition_id')->nullable()->constrained('dispositions')->nullOnDelete();
            }

            if (! Schema::hasColumn('call_logs', 'direction')) {
                $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing')->index();
            }

            if (! Schema::hasColumn('call_logs', 'provider_call_id')) {
                $table->string('provider_call_id', 160)->nullable()->index();
            }

            if (! Schema::hasColumn('call_logs', 'phone_number')) {
                $table->string('phone_number', 20)->nullable();
            }

            if (! Schema::hasColumn('call_logs', 'started_at')) {
                $table->timestamp('started_at')->nullable()->index();
            }

            if (! Schema::hasColumn('call_logs', 'answered_at')) {
                $table->timestamp('answered_at')->nullable();
            }

            if (! Schema::hasColumn('call_logs', 'ended_at')) {
                $table->timestamp('ended_at')->nullable();
            }

            if (! Schema::hasColumn('call_logs', 'duration_seconds')) {
                $table->unsignedInteger('duration_seconds')->default(0);
            }

            if (! Schema::hasColumn('call_logs', 'ring_duration_seconds')) {
                $table->unsignedInteger('ring_duration_seconds')->default(0);
            }

            if (! Schema::hasColumn('call_logs', 'recording_url')) {
                $table->string('recording_url', 500)->nullable();
            }

            if (! Schema::hasColumn('call_logs', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            if (! Schema::hasColumn('follow_ups', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            }

            if (! Schema::hasColumn('follow_ups', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('follow_ups', 'call_log_id')) {
                $table->foreignId('call_log_id')->nullable()->constrained('call_logs')->nullOnDelete();
            }

            if (! Schema::hasColumn('follow_ups', 'status')) {
                $table->enum('status', ['scheduled', 'due', 'completed', 'missed', 'cancelled', 'rescheduled'])->default('scheduled')->index();
            }

            if (! Schema::hasColumn('follow_ups', 'is_system_generated')) {
                $table->boolean('is_system_generated')->default(false)->index();
            }

            if (! Schema::hasColumn('follow_ups', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('user_sessions', 'status')) {
                $table->enum('status', ['online', 'offline', 'expired'])->default('online')->index();
            }

            if (! Schema::hasColumn('user_sessions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }

            if (! Schema::hasColumn('user_sessions', 'user_agent')) {
                $table->string('user_agent', 500)->nullable();
            }
        });

        if (! Schema::hasTable('lead_dispositions')) {
            Schema::create('lead_dispositions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->foreignId('call_log_id')->nullable()->constrained('call_logs')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
                $table->foreignId('from_stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
                $table->foreignId('to_stage_id')->nullable()->constrained('lead_stages')->nullOnDelete();
                $table->foreignId('tag_id')->nullable()->constrained('stage_tags')->nullOnDelete();
                $table->foreignId('disposition_id')->nullable()->constrained('dispositions')->nullOnDelete();
                $table->enum('call_status', ['connected', 'not_connected', 'missed', 'busy', 'no_answer', 'failed'])->default('connected')->index();
                $table->text('remark')->nullable();
                $table->timestamp('disposed_at')->index();
                $table->timestamps();
            });
        }
    }

    private function createSettingsTables(): void
    {
        if (! Schema::hasTable('retry_reasons')) {
            Schema::create('retry_reasons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pipeline_id')->nullable()->constrained('pipelines')->cascadeOnDelete();
                $table->string('name', 120);
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('retry_rules')) {
            Schema::create('retry_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('retry_reason_id')->constrained('retry_reasons')->cascadeOnDelete();
                $table->enum('logic_type', ['fixed', 'variable'])->default('fixed');
                $table->unsignedTinyInteger('max_retries')->default(5);
                $table->unsignedTinyInteger('interval_value')->default(1);
                $table->enum('interval_unit', ['minutes', 'hours', 'days'])->default('hours');
                $table->boolean('mark_lost_after_exhausted')->default(true);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lead_priority_rules')) {
            Schema::create('lead_priority_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_profile_id')->nullable()->constrained('crm_business_profiles')->nullOnDelete();
                $table->string('name', 120);
                $table->string('code', 80);
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_locked')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();

                $table->unique(['business_profile_id', 'code']);
            });
        }
    }

    private function createUtilityTables(): void
    {
        if (! Schema::hasTable('user_breaks')) {
            Schema::create('user_breaks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('session_id')->nullable()->constrained('user_sessions')->nullOnDelete();
                $table->string('reason', 120)->nullable();
                $table->timestamp('started_at')->index();
                $table->timestamp('ended_at')->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('note');
                $table->enum('visibility', ['private', 'team', 'manager'])->default('team');
                $table->boolean('is_confidential')->default(false)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('communication_events')) {
            Schema::create('communication_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
                $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('channel', ['sms', 'email', 'whatsapp'])->index();
                $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
                $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('sent')->index();
                $table->string('recipient', 180)->nullable();
                $table->string('template_id', 120)->nullable();
                $table->string('body_preview', 500)->nullable();
                $table->timestamp('sent_at')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
                $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
                $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open')->index();
                $table->timestamp('due_at')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('timeline_events')) {
            Schema::create('timeline_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('event_type', [
                    'lead_created',
                    'call_answered',
                    'call_missed',
                    'lead_disposed',
                    'followup_scheduled',
                    'followup_completed',
                    'lead_updated',
                    'lead_reassigned',
                    'message_sent',
                ])->index();
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('saved_filters')) {
            Schema::create('saved_filters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('module', ['leads', 'reports', 'campaigns', 'calls'])->index();
                $table->string('name', 120);
                $table->json('filters');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('report_exports')) {
            Schema::create('report_exports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('report_type', 80)->index();
                $table->json('filters')->nullable();
                $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued')->index();
                $table->string('file_path', 500)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    private function expandAssignmentRulesTable(): void
    {
        Schema::table('assignment_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('assignment_rules', 'name')) {
                $table->string('name', 120)->nullable();
            }

            if (! Schema::hasColumn('assignment_rules', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('assignment_rules', 'settings')) {
                $table->json('settings')->nullable();
            }
        });
    }

    private function dropColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existingColumns = array_filter($columns, fn (string $column): bool => Schema::hasColumn($tableName, $column));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }
};
