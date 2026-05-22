<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_sources')) {
            Schema::create('lead_sources', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('code', 40)->unique();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('contact_lists')) {
            Schema::create('contact_lists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
                $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('sheet_name', 160)->nullable();
                $table->string('storage_path', 500);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedInteger('file_size')->default(0);
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('created_rows')->default(0);
                $table->unsignedInteger('merged_rows')->default(0);
                $table->unsignedInteger('failed_rows')->default(0);
                $table->enum('status', ['queued', 'processing', 'completed', 'failed', 'partially_failed'])->default('queued')->index();
                $table->json('mapping')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['campaign_id', 'status']);
            });
        }

        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'pipeline_id')) {
                $table->foreignId('pipeline_id')->nullable()->constrained('pipelines')->nullOnDelete();
            }

            if (! Schema::hasColumn('leads', 'tag_id')) {
                $table->foreignId('tag_id')->nullable()->constrained('stage_tags')->nullOnDelete();
            }

            if (! Schema::hasColumn('leads', 'assigned_user_id')) {
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('leads', 'source_id')) {
                $table->foreignId('source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            }

            if (! Schema::hasColumn('leads', 'contact_list_id')) {
                $table->foreignId('contact_list_id')->nullable()->constrained('contact_lists')->nullOnDelete();
            }

            if (! Schema::hasColumn('leads', 'status')) {
                $table->enum('status', ['uncontacted', 'in_progress', 'converted', 'lost', 'closed', 'reopened'])->default('uncontacted')->index();
            }

            if (! Schema::hasColumn('leads', 'priority_bucket')) {
                $table->enum('priority_bucket', [
                    'manual_scheduled',
                    'assigned_uncontacted',
                    'unassigned_uncontacted',
                    'in_progress_no_followup',
                    'not_connected_scheduled',
                    'normal',
                ])->default('normal')->index();
            }

            if (! Schema::hasColumn('leads', 'deal_amount')) {
                $table->decimal('deal_amount', 14, 2)->nullable();
            }

            if (! Schema::hasColumn('leads', 'currency')) {
                $table->string('currency', 3)->default('INR');
            }

            if (! Schema::hasColumn('leads', 'last_call_at')) {
                $table->timestamp('last_call_at')->nullable()->index();
            }

            if (! Schema::hasColumn('leads', 'next_follow_up_at')) {
                $table->timestamp('next_follow_up_at')->nullable()->index();
            }

            if (! Schema::hasColumn('leads', 'total_disposition_count')) {
                $table->unsignedInteger('total_disposition_count')->default(0);
            }

            if (! Schema::hasColumn('leads', 'confidential_remark')) {
                $table->text('confidential_remark')->nullable();
            }

            if (! Schema::hasColumn('leads', 'metadata')) {
                $table->json('metadata')->nullable();
            }

            if (! Schema::hasColumn('leads', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (! Schema::hasTable('contact_list_rows')) {
            Schema::create('contact_list_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_list_id')->constrained('contact_lists')->cascadeOnDelete();
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
                $table->unsignedInteger('row_number');
                $table->json('raw_payload');
                $table->enum('status', ['created', 'merged', 'merged_reopened', 'failed', 'skipped'])->default('created')->index();
                $table->string('failure_reason', 255)->nullable();
                $table->timestamps();

                $table->unique(['contact_list_id', 'row_number']);
                $table->index(['contact_list_id', 'status']);
            });
        }

        if (! Schema::hasTable('lead_phone_numbers')) {
            Schema::create('lead_phone_numbers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->string('phone', 20);
                $table->enum('type', ['primary', 'alternate', 'whatsapp'])->default('alternate');
                $table->boolean('is_primary')->default(false)->index();
                $table->boolean('is_valid')->default(true)->index();
                $table->timestamps();

                $table->unique(['lead_id', 'phone']);
            });
        }

        Schema::table('contact_properties', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_properties', 'business_profile_id')) {
                $table->foreignId('business_profile_id')->nullable()->constrained('crm_business_profiles')->nullOnDelete();
            }

            if (! Schema::hasColumn('contact_properties', 'slug')) {
                $table->string('slug', 80)->nullable()->index();
            }

            if (! Schema::hasColumn('contact_properties', 'options')) {
                $table->json('options')->nullable();
            }

            if (! Schema::hasColumn('contact_properties', 'is_required')) {
                $table->boolean('is_required')->default(false);
            }

            if (! Schema::hasColumn('contact_properties', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('lead_property_values', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_property_values', 'value_text')) {
                $table->text('value_text')->nullable();
            }

            if (! Schema::hasColumn('lead_property_values', 'value_number')) {
                $table->decimal('value_number', 16, 4)->nullable();
            }

            if (! Schema::hasColumn('lead_property_values', 'value_date')) {
                $table->date('value_date')->nullable();
            }

            if (! Schema::hasColumn('lead_property_values', 'value_json')) {
                $table->json('value_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_phone_numbers');
        Schema::dropIfExists('contact_list_rows');
        Schema::dropIfExists('contact_lists');
        Schema::dropIfExists('lead_sources');

        $this->dropColumns('lead_property_values', ['value_text', 'value_number', 'value_date', 'value_json']);
        $this->dropColumns('contact_properties', ['business_profile_id', 'slug', 'options', 'is_required', 'deleted_at']);
        $this->dropColumns('leads', [
            'pipeline_id',
            'tag_id',
            'assigned_user_id',
            'source_id',
            'contact_list_id',
            'status',
            'priority_bucket',
            'deal_amount',
            'currency',
            'last_call_at',
            'next_follow_up_at',
            'total_disposition_count',
            'confidential_remark',
            'metadata',
            'deleted_at',
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
