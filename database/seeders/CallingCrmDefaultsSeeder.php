<?php

namespace Database\Seeders;

use App\Models\ContactProperty;
use App\Models\CrmBusinessProfile;
use App\Models\Disposition;
use App\Models\LeadPriorityRule;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\RetryReason;
use App\Models\StageTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CallingCrmDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $businessProfile = CrmBusinessProfile::updateOrCreate(
            ['phone' => '9138400002'],
            [
                'business_name' => 'AMPL AGRO MARKET PRIVATE LIMITED',
                'address' => '1261 BEHIND RELEXO SHOWROOM, INFRONT OF SAMDARIYA COMPLEX, Jabalpur, Jabalpur, Madhya Pradesh',
                'state' => 'Madhya Pradesh',
                'pincode' => '482002',
                'gst_number' => '23AAZCA2719G1ZJ',
                'working_days' => 'mon_sat',
                'work_start_time' => '09:30:00',
                'work_end_time' => '18:30:00',
                'timezone' => 'Asia/Kolkata',
                'settings' => [],
            ]
        );

        $this->seedLeadSources();
        $this->seedPipelines($businessProfile);
        $this->seedContactProperties($businessProfile);
        $this->seedLeadPriorityRules($businessProfile);
    }

    private function seedLeadSources(): void
    {
        $sources = [
            ['name' => 'File Upload', 'code' => 'FILE_UPLOAD'],
            ['name' => 'Walk-in Lead', 'code' => 'WALK_IN_LEAD'],
            ['name' => 'Incoming IVR', 'code' => 'INCOMING_IVR'],
            ['name' => 'Workflow', 'code' => 'WORKFLOW'],
            ['name' => 'Google Sheet', 'code' => 'GOOGLE_SHEET'],
            ['name' => 'Manual', 'code' => 'MANUAL'],
            ['name' => 'API', 'code' => 'API'],
            ['name' => 'Webhook', 'code' => 'WEBHOOK'],
        ];

        foreach ($sources as $source) {
            LeadSource::updateOrCreate(
                ['code' => $source['code']],
                ['name' => $source['name'], 'is_active' => true]
            );
        }
    }

    private function seedPipelines(CrmBusinessProfile $businessProfile): void
    {
        $pipelines = [
            ['name' => 'Indore Sales', 'color' => '#763abb', 'is_default' => true, 'sort_order' => 1],
            ['name' => 'Leads', 'color' => '#1094f9', 'is_default' => false, 'sort_order' => 2],
        ];

        foreach ($pipelines as $pipelineData) {
            $pipeline = Pipeline::updateOrCreate(
                ['business_profile_id' => $businessProfile->id, 'name' => $pipelineData['name']],
                [
                    'color' => $pipelineData['color'],
                    'is_default' => $pipelineData['is_default'],
                    'is_active' => true,
                    'sort_order' => $pipelineData['sort_order'],
                ]
            );

            $this->seedStagesAndTags($pipeline);
            $this->seedRetryRules($pipeline);
        }
    }

    private function seedStagesAndTags(Pipeline $pipeline): void
    {
        $stages = [
            [
                'name' => 'Fresh Leads',
                'category' => 'fresh',
                'color' => '#3b82f6',
                'sort_order' => 1,
                'tags' => ['No Season', 'Future Requirement', 'Out of Station'],
            ],
            [
                'name' => 'Follow Up (Mandatory)',
                'category' => 'in_progress',
                'color' => '#7c3aed',
                'sort_order' => 2,
                'tags' => ['No Response', 'Call Back', 'Future Requirement'],
            ],
            [
                'name' => 'Catalog and Price Shared',
                'category' => 'in_progress',
                'color' => '#059669',
                'sort_order' => 3,
                'tags' => ['Catalog Sent', 'Price Shared'],
            ],
            [
                'name' => 'Negotiation or Price Issue',
                'category' => 'in_progress',
                'color' => '#d97706',
                'sort_order' => 4,
                'tags' => ['Discount Request', 'Price Issue'],
            ],
            [
                'name' => 'Closed Won',
                'category' => 'closed_won',
                'color' => '#16a34a',
                'sort_order' => 5,
                'is_closed' => true,
                'tags' => ['Converted', 'Deal Closed', 'Full Payment', 'Order Received'],
            ],
            [
                'name' => 'Closed Lost',
                'category' => 'closed_lost',
                'color' => '#dc2626',
                'sort_order' => 6,
                'is_closed' => true,
                'tags' => ['Not Interested', 'No Shop', 'Invalid Number', 'No Incoming', 'Number Not Available'],
            ],
        ];

        foreach ($stages as $stageData) {
            $stage = LeadStage::updateOrCreate(
                ['pipeline_id' => $pipeline->id, 'name' => $stageData['name']],
                [
                    'code' => Str::slug($stageData['name'], '_'),
                    'category' => $stageData['category'],
                    'color' => $stageData['color'],
                    'is_closed' => $stageData['is_closed'] ?? false,
                    'is_active' => true,
                    'sort_order' => $stageData['sort_order'],
                ]
            );

            foreach ($stageData['tags'] as $index => $tagName) {
                StageTag::updateOrCreate(
                    ['stage_id' => $stage->id, 'name' => $tagName],
                    [
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->seedDispositions($pipeline);
    }

    private function seedDispositions(Pipeline $pipeline): void
    {
        $stageByName = $pipeline->stages()->with('tags')->get()->keyBy('name');

        $dispositions = [
            ['name' => 'Connected - Follow Up', 'type' => 'in_progress', 'stage' => 'Follow Up (Mandatory)', 'tag' => 'Call Back', 'requires_follow_up' => true],
            ['name' => 'Catalog Sent', 'type' => 'in_progress', 'stage' => 'Catalog and Price Shared', 'tag' => 'Catalog Sent'],
            ['name' => 'Price Issue', 'type' => 'in_progress', 'stage' => 'Negotiation or Price Issue', 'tag' => 'Price Issue'],
            ['name' => 'Converted', 'type' => 'closed_won', 'stage' => 'Closed Won', 'tag' => 'Converted', 'marks_lead_closed' => true],
            ['name' => 'Not Interested', 'type' => 'closed_lost', 'stage' => 'Closed Lost', 'tag' => 'Not Interested', 'marks_lead_closed' => true],
            ['name' => 'Not Connected', 'type' => 'not_connected', 'requires_follow_up' => true],
        ];

        foreach ($dispositions as $index => $dispositionData) {
            $stage = isset($dispositionData['stage']) ? $stageByName->get($dispositionData['stage']) : null;
            $tag = $stage && isset($dispositionData['tag'])
                ? $stage->tags->firstWhere('name', $dispositionData['tag'])
                : null;

            Disposition::updateOrCreate(
                ['pipeline_id' => $pipeline->id, 'name' => $dispositionData['name']],
                [
                    'stage_id' => $stage?->id,
                    'tag_id' => $tag?->id,
                    'type' => $dispositionData['type'],
                    'requires_follow_up' => $dispositionData['requires_follow_up'] ?? false,
                    'requires_note' => $dispositionData['requires_note'] ?? false,
                    'marks_lead_closed' => $dispositionData['marks_lead_closed'] ?? false,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedRetryRules(Pipeline $pipeline): void
    {
        $reasons = [
            ['name' => 'Not Connected', 'max_retries' => 5, 'interval_value' => 1, 'interval_unit' => 'hours'],
            ['name' => 'Busy', 'max_retries' => 3, 'interval_value' => 30, 'interval_unit' => 'minutes'],
            ['name' => 'No Answer', 'max_retries' => 4, 'interval_value' => 2, 'interval_unit' => 'hours'],
        ];

        foreach ($reasons as $index => $reasonData) {
            $reason = RetryReason::updateOrCreate(
                ['pipeline_id' => $pipeline->id, 'name' => $reasonData['name']],
                [
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            $reason->rule()->updateOrCreate(
                ['retry_reason_id' => $reason->id],
                [
                    'logic_type' => 'fixed',
                    'max_retries' => $reasonData['max_retries'],
                    'interval_value' => $reasonData['interval_value'],
                    'interval_unit' => $reasonData['interval_unit'],
                    'mark_lost_after_exhausted' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedContactProperties(CrmBusinessProfile $businessProfile): void
    {
        $properties = [
            ['name' => 'Company Name', 'data_type' => 'text', 'sort_order' => 1],
            ['name' => 'Address Line 1', 'data_type' => 'text', 'sort_order' => 2],
            ['name' => 'Address Line 2', 'data_type' => 'text', 'sort_order' => 3],
            ['name' => 'Town/City', 'data_type' => 'text', 'sort_order' => 4],
            ['name' => 'State', 'data_type' => 'text', 'sort_order' => 5],
            ['name' => 'Pincode', 'data_type' => 'number', 'sort_order' => 6],
            ['name' => 'GST', 'data_type' => 'text', 'sort_order' => 7],
        ];

        foreach ($properties as $property) {
            ContactProperty::updateOrCreate(
                ['business_profile_id' => $businessProfile->id, 'slug' => Str::slug($property['name'], '_')],
                [
                    'name' => $property['name'],
                    'data_type' => $property['data_type'],
                    'is_required' => false,
                    'is_active' => true,
                    'sort_order' => $property['sort_order'],
                ]
            );
        }
    }

    private function seedLeadPriorityRules(CrmBusinessProfile $businessProfile): void
    {
        $rules = [
            ['name' => 'Manually Scheduled Leads', 'code' => 'manual_scheduled', 'sort_order' => 1, 'is_locked' => true],
            ['name' => 'Uncontacted Assigned Leads', 'code' => 'assigned_uncontacted', 'sort_order' => 2],
            ['name' => 'Uncontacted Unassigned Leads', 'code' => 'unassigned_uncontacted', 'sort_order' => 3],
            ['name' => 'In-Progress without Follow-Up', 'code' => 'in_progress_no_followup', 'sort_order' => 4],
            ['name' => 'Not Connected Scheduled', 'code' => 'not_connected_scheduled', 'sort_order' => 5],
        ];

        foreach ($rules as $rule) {
            LeadPriorityRule::updateOrCreate(
                ['business_profile_id' => $businessProfile->id, 'code' => $rule['code']],
                [
                    'name' => $rule['name'],
                    'sort_order' => $rule['sort_order'],
                    'is_locked' => $rule['is_locked'] ?? false,
                    'is_active' => true,
                ]
            );
        }
    }
}
