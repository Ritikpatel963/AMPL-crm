<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipelines', function (Blueprint $table) {
            if (! Schema::hasColumn('pipelines', 'business_profile_id')) {
                $table->foreignId('business_profile_id')->nullable()->constrained('crm_business_profiles')->nullOnDelete();
            }

            if (! Schema::hasColumn('pipelines', 'color')) {
                $table->string('color', 7)->default('#763abb');
            }

            if (! Schema::hasColumn('pipelines', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('pipelines', 'is_default')) {
                $table->boolean('is_default')->default(false)->index();
            }

            if (! Schema::hasColumn('pipelines', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('pipelines', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->index();
            }

            if (! Schema::hasColumn('pipelines', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('lead_stages', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_stages', 'code')) {
                $table->string('code', 80)->nullable();
            }

            if (! Schema::hasColumn('lead_stages', 'category')) {
                $table->enum('category', ['fresh', 'in_progress', 'closed_won', 'closed_lost'])->default('in_progress')->index();
            }

            if (! Schema::hasColumn('lead_stages', 'is_closed')) {
                $table->boolean('is_closed')->default(false)->index();
            }

            if (! Schema::hasColumn('lead_stages', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('lead_stages', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (! Schema::hasTable('stage_tags')) {
            Schema::create('stage_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stage_id')->constrained('lead_stages')->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('color', 7)->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['stage_id', 'name']);
            });
        }

        Schema::table('campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('campaigns', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('campaigns', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->index();
            }

            if (! Schema::hasColumn('campaigns', 'hide_paused_from_agents')) {
                $table->boolean('hide_paused_from_agents')->default(false);
            }

            if (! Schema::hasColumn('campaigns', 'lead_chunk_size')) {
                $table->unsignedSmallInteger('lead_chunk_size')->default(10);
            }

            if (! Schema::hasColumn('campaigns', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }

            if (! Schema::hasColumn('campaigns', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }

            if (! Schema::hasColumn('campaigns', 'settings')) {
                $table->json('settings')->nullable();
            }

            if (! Schema::hasColumn('campaigns', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('campaign_user', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_user', 'role')) {
                $table->enum('role', ['manager', 'agent', 'viewer'])->default('agent');
            }

            if (! Schema::hasColumn('campaign_user', 'assigned_leads_count')) {
                $table->unsignedInteger('assigned_leads_count')->default(0);
            }

            if (! Schema::hasColumn('campaign_user', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_tags');

        $this->dropColumns('campaign_user', ['role', 'assigned_leads_count', 'is_active']);
        $this->dropColumns('campaigns', [
            'description',
            'is_pinned',
            'hide_paused_from_agents',
            'lead_chunk_size',
            'starts_at',
            'ends_at',
            'settings',
            'deleted_at',
        ]);
        $this->dropColumns('lead_stages', ['code', 'category', 'is_closed', 'is_active', 'deleted_at']);
        $this->dropColumns('pipelines', ['business_profile_id', 'color', 'description', 'is_default', 'is_active', 'sort_order', 'deleted_at']);
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
