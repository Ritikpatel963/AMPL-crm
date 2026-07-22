<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ContactProperty;
use App\Models\CrmBusinessProfile;
use App\Models\LeadPriorityRule;
use App\Models\LeadSource;
use App\Models\RetryReason;
use App\Models\User;
use App\Services\CrmCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function bootstrap()
    {
        $this->ensureLeadPriorityRules();

        return response()->json([
            'status' => true,
            'data' => CrmCacheService::bootstrap(),
        ]);
    }

    public function profile()
    {
        return response()->json([
            'status' => true,
            'data' => CrmCacheService::bootstrap()['business_profile'] ?? null,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:250'],
            'state' => ['nullable', 'string', 'max:80'],
            'pincode' => ['nullable', 'string', 'max:12'],
            'gst_number' => ['nullable', 'string', 'max:32'],
            'working_days' => ['required', Rule::in(['mon_sat', 'mon_fri', 'all_days', 'custom'])],
            'work_start_time' => ['nullable', 'date_format:H:i'],
            'work_end_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'settings' => ['nullable', 'array'],
        ]);

        $profile = CrmBusinessProfile::first();
        $profile = $profile
            ? tap($profile)->update($data)
            : CrmBusinessProfile::create($data);

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'CRM profile saved successfully',
            'data' => $profile->fresh(),
        ]);
    }

    public function users(Request $request)
    {
        $users = User::query()
            ->select(['id', 'reporting_manager_id', 'location_id', 'name', 'phone_number', 'email', 'role', 'employee_id', 'crm_status', 'lead_assignment_enabled', 'expires_at', 'created_at'])
            ->with(['reportingManager:id,name', 'location:id,name'])
            ->whereIn('role', ['agent', 'subadmin'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('phone_number', 'like', '%' . $request->search . '%')
                        ->orWhere('employee_id', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $users]);
    }

    public function storeUsers(Request $request)
    {
        $data = $request->validate([
            'users' => ['required', 'array', 'min:1', 'max:25'],
            'users.*.name' => ['required', 'string', 'max:255'],
            'users.*.phone_number' => ['required', 'string', 'max:30', 'distinct', 'unique:users,phone_number'],
            'users.*.email' => ['nullable', 'email', 'max:255', 'distinct', 'unique:users,email'],
            'users.*.password' => ['required', Password::min(4)],
            'users.*.role' => ['required', Rule::in(['agent', 'subadmin'])],
            'users.*.employee_id' => ['nullable', 'string', 'max:80', 'distinct'],
            'users.*.reporting_manager_id' => ['nullable', 'exists:users,id'],
            'users.*.state' => ['nullable', 'string', 'max:120'],
            'users.*.city' => ['nullable', 'string', 'max:120'],
            'users.*.expires_at' => ['nullable', 'date'],
        ]);

        $users = DB::transaction(function () use ($data) {
            $created = collect($data['users'])->map(function (array $userData) {
                $userData['email'] = $this->crmUserEmail($userData['email'] ?? null, $userData['phone_number']);
                $userData['crm_status'] = 'active';
                $userData['lead_assignment_enabled'] = true;
                $userData['approval_status'] = $userData['approval_status'] ?? 'approved';

                if (!empty($userData['state']) && !empty($userData['city'])) {
                    $location = \App\Models\Location::firstOrCreate([
                        'state' => $userData['state'],
                        'name' => $userData['city']
                    ], ['is_active' => true]);
                    $userData['location_id'] = $location->id;
                }
                unset($userData['state'], $userData['city']);

                return User::create($userData);
            });
            return new \Illuminate\Database\Eloquent\Collection($created);
        });

        return response()->json([
            'status' => true,
            'message' => 'CRM users created successfully',
            'data' => $users->load('reportingManager:id,name'),
        ], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['agent', 'subadmin'], true), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', Password::min(4)],
            'role' => ['required', Rule::in(['agent', 'subadmin'])],
            'employee_id' => ['nullable', 'string', 'max:80'],
            'reporting_manager_id' => ['nullable', 'exists:users,id'],
            'state' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'expires_at' => ['nullable', 'date'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if (!empty($data['state']) && !empty($data['city'])) {
            $location = \App\Models\Location::firstOrCreate([
                'state' => $data['state'],
                'name' => $data['city']
            ], ['is_active' => true]);
            $data['location_id'] = $location->id;
        }
        unset($data['state'], $data['city']);

        $data['email'] = $this->crmUserEmail($data['email'] ?? null, $data['phone_number']);
        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'CRM user updated successfully',
            'data' => $user->fresh('reportingManager:id,name'),
        ]);
    }

    public function updateUserStatus(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['agent', 'subadmin'], true), 404);

        $data = $request->validate([
            'crm_status' => ['sometimes', Rule::in(['active', 'inactive', 'deactivated'])],
            'lead_assignment_enabled' => ['sometimes', 'boolean'],
        ]);

        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'CRM user status updated successfully',
            'data' => $user->fresh('reportingManager:id,name'),
        ]);
    }

    public function destroyUser(User $user)
    {
        abort_unless(in_array($user->role, ['agent', 'subadmin'], true), 404);

        $user->delete();

        return response()->json(['status' => true, 'message' => 'CRM user deleted successfully']);
    }

    public function updateUserPassword(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['agent', 'subadmin'], true), 404);

        $data = $request->validate([
            'password' => ['required', Password::min(4)],
        ]);

        $user->update(['password' => $data['password']]);

        return response()->json([
            'status' => true,
            'message' => 'User password updated successfully',
        ]);
    }

    public function userCampaigns(User $user)
    {
        return response()->json([
            'status' => true,
            'data' => Campaign::whereHas('users', fn ($q) => $q->where('user_id', $user->id))
                ->orWhere('manager_id', $user->id)
                ->with('pipeline:id,name')
                ->select('id', 'name', 'pipeline_id', 'status')
                ->get(),
        ]);
    }

    public function userReassignCampaigns(User $user)
    {
        $campaigns = Campaign::whereHas('users', fn ($q) => $q->where('user_id', $user->id))
            ->with('pipeline:id,name')
            ->select('id', 'name', 'pipeline_id', 'status')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $campaigns,
        ]);
    }

    public function contactProperties(Request $request)
    {
        $properties = ContactProperty::query()
            ->when($request->boolean('active_only'), fn ($query) => $query->active())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json([
            'status' => true,
            'data' => $properties,
        ]);
    }

    public function storeContactProperty(Request $request)
    {
        $data = $this->validateContactProperty($request);
        $data['slug'] = Str::slug($data['name'], '_');

        $property = ContactProperty::create($data);

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Contact property created successfully',
            'data' => $property,
        ], 201);
    }

    public function updateContactProperty(Request $request, ContactProperty $property)
    {
        $data = $this->validateContactProperty($request);
        $data['slug'] = Str::slug($data['name'], '_');

        $property->update($data);

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Contact property updated successfully',
            'data' => $property->fresh(),
        ]);
    }

    public function toggleContactProperty(ContactProperty $property)
    {
        $property->update(['is_active' => ! $property->is_active]);

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Contact property status updated successfully',
            'data' => $property->fresh(),
        ]);
    }

    public function destroyContactProperty(ContactProperty $property)
    {
        $property->delete();

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Contact property deleted successfully',
        ]);
    }

    public function retryReasons()
    {
        return response()->json([
            'status' => true,
            'data' => RetryReason::with('rule')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function storeRetryReason(Request $request)
    {
        $data = $request->validate([
            'pipeline_id' => ['nullable', 'exists:pipelines,id'],
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['sort_order'] = RetryReason::max('sort_order') + 1;

        $retryReason = RetryReason::create($data);
        $retryReason->rule()->create([]);

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Retry reason created successfully',
            'data' => $retryReason->load('rule'),
        ], 201);
    }

    public function updateRetryReason(Request $request, RetryReason $retryReason)
    {
        $retryReason->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]));

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Retry reason updated successfully',
            'data' => $retryReason->fresh('rule'),
        ]);
    }

    public function destroyRetryReason(RetryReason $retryReason)
    {
        $retryReason->delete();

        CrmCacheService::flushSettings();

        return response()->json(['status' => true, 'message' => 'Retry reason deleted successfully']);
    }

    public function updateRetryRule(Request $request, RetryReason $retryReason)
    {
        $data = $request->validate([
            'logic_type' => ['required', Rule::in(['fixed', 'variable'])],
            'max_retries' => ['required', 'integer', 'min:1', 'max:99'],
            'interval_value' => ['required', 'integer', 'min:1', 'max:99'],
            'interval_unit' => ['required', Rule::in(['minutes', 'hours', 'days'])],
            'mark_lost_after_exhausted' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'apply_to_all' => ['sometimes', 'boolean'],
        ]);

        $ruleData = collect($data)->except('apply_to_all')->all();

        DB::transaction(function () use ($data, $retryReason, $ruleData) {
            $reasons = $data['apply_to_all'] ?? false
                ? RetryReason::query()->get()
                : collect([$retryReason]);

            $reasons->each(function (RetryReason $reason) use ($ruleData) {
                $reason->rule()->updateOrCreate([], $ruleData);
                $reason->update(['is_active' => $ruleData['is_active'] ?? true]);
            });
        });

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Retry logic saved successfully',
            'data' => $retryReason->fresh('rule'),
        ]);
    }

    public function leadPriorityRules()
    {
        return response()->json([
            'status' => true,
            'data' => LeadPriorityRule::orderBy('sort_order')->get(),
        ]);
    }

    public function updateLeadPriority(Request $request)
    {
        $data = $request->validate([
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.id' => ['required', 'exists:lead_priority_rules,id'],
            'rules.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['rules'] as $ruleData) {
            LeadPriorityRule::whereKey($ruleData['id'])
                ->where('is_locked', false)
                ->update(['sort_order' => $ruleData['sort_order']]);
        }

        CrmCacheService::flushSettings();

        return response()->json([
            'status' => true,
            'message' => 'Lead priority saved successfully',
            'data' => LeadPriorityRule::orderBy('sort_order')->get(),
        ]);
    }

    private function validateContactProperty(Request $request): array
    {
        return $request->validate([
            'business_profile_id' => ['nullable', 'exists:crm_business_profiles,id'],
            'name' => ['required', 'string', 'max:60'],
            'data_type' => ['required', Rule::in(['text', 'number', 'email', 'date', 'dropdown', 'boolean', 'textarea'])],
            'options' => ['nullable', 'array'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
    }

    private function crmUserEmail(?string $email, string $phoneNumber): string
    {
        if (filled($email)) {
            return $email;
        }

        return 'crm-' . preg_replace('/[^0-9]+/', '', $phoneNumber) . '@local.invalid';
    }

    private function ensureLeadPriorityRules(): void
    {
        if (LeadPriorityRule::query()->exists()) {
            return;
        }

        $profile = CrmCacheService::bootstrap()['business_profile'] ?? null;
        $rules = [
            ['name' => 'Manually Scheduled Leads', 'code' => 'manual_scheduled', 'is_locked' => true],
            ['name' => 'Uncontacted Assigned Leads', 'code' => 'assigned_uncontacted'],
            ['name' => 'Uncontacted Unassigned Leads', 'code' => 'unassigned_uncontacted'],
            ['name' => 'In-Progress without Follow-Up', 'code' => 'in_progress_no_followup'],
            ['name' => 'Not Connected Scheduled', 'code' => 'not_connected_scheduled'],
        ];

        foreach ($rules as $index => $rule) {
            LeadPriorityRule::create([
                'business_profile_id' => $profile?->id,
                'name' => $rule['name'],
                'code' => $rule['code'],
                'sort_order' => $index + 1,
                'is_locked' => $rule['is_locked'] ?? false,
                'is_active' => true,
            ]);
        }
    }
}
