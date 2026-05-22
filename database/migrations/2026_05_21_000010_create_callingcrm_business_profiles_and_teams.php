<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id', 80)->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'reporting_manager_id')) {
                $table->foreignId('reporting_manager_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'crm_status')) {
                $table->enum('crm_status', ['active', 'inactive', 'deactivated'])->default('active')->index();
            }

            if (! Schema::hasColumn('users', 'lead_assignment_enabled')) {
                $table->boolean('lead_assignment_enabled')->default(true)->index();
            }

            if (! Schema::hasColumn('users', 'expires_at')) {
                $table->date('expires_at')->nullable();
            }

            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable();
            }
        });

        if (! Schema::hasTable('crm_business_profiles')) {
            Schema::create('crm_business_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('business_name', 160);
                $table->string('phone', 20)->index();
                $table->text('address')->nullable();
                $table->string('state', 80)->nullable();
                $table->string('pincode', 12)->nullable();
                $table->string('gst_number', 32)->nullable()->index();
                $table->enum('working_days', ['mon_sat', 'mon_fri', 'all_days', 'custom'])->default('mon_sat');
                $table->time('work_start_time')->nullable();
                $table->time('work_end_time')->nullable();
                $table->string('timezone', 64)->default('Asia/Kolkata');
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('crm_teams')) {
            Schema::create('crm_teams', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->foreignId('team_lead_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('crm_team_user')) {
            Schema::create('crm_team_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained('crm_teams')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('role_in_team', ['lead', 'agent', 'observer'])->default('agent');
                $table->timestamps();

                $table->unique(['team_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_team_user');
        Schema::dropIfExists('crm_teams');
        Schema::dropIfExists('crm_business_profiles');

        $this->dropColumns('users', [
            'employee_id',
            'reporting_manager_id',
            'crm_status',
            'lead_assignment_enabled',
            'expires_at',
            'last_seen_at',
        ]);
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
