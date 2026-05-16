<?php

namespace App\Http\Controllers\callingcrm;

use App\Http\Controllers\Controller;
use App\Models\callingcrm\Campaign;
use App\Models\callingcrm\ContactProperty;
use App\Models\callingcrm\Lead;
use App\Models\callingcrm\LeadPropertyValue;
use App\Models\callingcrm\LeadStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['campaign:id,name', 'assignedUser:id,name', 'stage:id,name,color']);

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source'));
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->integer('campaign_id'));
        }

        $leads = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sources = Lead::SOURCE_OPTIONS;
        $campaigns = Campaign::orderBy('name')->get(['id', 'name']);

        return view('callingcrm.contacts.index', compact('leads', 'sources', 'campaigns'));
    }

    public function create()
    {
        $campaigns = Campaign::orderBy('name')->get(['id', 'name']);
        $properties = ContactProperty::active()->orderBy('sort_order')->get();
        $agents = User::whereIn('role', ['subadmin', 'agent'])->orderBy('name')->get(['id', 'name', 'role']);
        $sources = Lead::SOURCE_OPTIONS;

        return view('callingcrm.contacts.create', compact('campaigns', 'properties', 'agents', 'sources'));
    }

    public function store(Request $request)
    {
        $properties = ContactProperty::active()->orderBy('sort_order')->get();

        $rules = [
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:leads,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'source' => ['required', 'in:' . implode(',', Lead::SOURCE_OPTIONS)],
            'tags' => ['nullable', 'string'],
        ];

        foreach ($properties as $property) {
            $rules['property_' . $property->id] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $properties) {
            $campaign = Campaign::with('pipeline.stages')->findOrFail($validated['campaign_id']);
            $defaultStageId = optional($campaign->pipeline?->stages->sortBy('sort_order')->first())->id;

            $lead = Lead::create([
                'campaign_id' => $campaign->id,
                'user_id' => $validated['user_id'] ?? null,
                'stage_id' => $defaultStageId,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'source' => $validated['source'],
                'tags' => $this->normalizeTags($validated['tags'] ?? null),
            ]);

            foreach ($properties as $property) {
                $value = $validated['property_' . $property->id] ?? null;
                if ($value === null || trim((string) $value) === '') {
                    continue;
                }

                LeadPropertyValue::create([
                    'lead_id' => $lead->id,
                    'property_id' => $property->id,
                    'value' => $value,
                ]);
            }
        });

        return redirect()
            ->route('callingcrm.contacts.index')
            ->with('success', 'Lead created successfully.');
    }

    public function upload()
    {
        $campaigns = Campaign::orderBy('name')->get(['id', 'name']);
        $agents = User::whereIn('role', ['subadmin', 'agent'])->orderBy('name')->get(['id', 'name', 'role']);

        return view('callingcrm.contacts.upload', compact('campaigns', 'agents'));
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'source' => ['required', 'in:' . implode(',', Lead::SOURCE_OPTIONS)],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:3072'],
        ], [
            'file.mimes' => 'CSV import is supported right now. XLS/XLSX parsing needs a spreadsheet package.',
        ]);

        $campaign = Campaign::with('pipeline.stages')->findOrFail($validated['campaign_id']);
        $defaultStageId = optional($campaign->pipeline?->stages->sortBy('sort_order')->first())->id;

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->withErrors(['file' => 'Unable to open uploaded CSV file.'])->withInput();
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return back()->withErrors(['file' => 'Uploaded CSV file is empty.'])->withInput();
        }

        $headerMap = collect($header)->map(fn ($column) => Str::lower(trim((string) $column)))->values();
        $requiredHeaders = ['name', 'phone'];

        foreach ($requiredHeaders as $requiredHeader) {
            if (!$headerMap->contains($requiredHeader)) {
                fclose($handle);
                return back()->withErrors([
                    'file' => 'CSV must contain at least the columns: name, phone. Optional: email, tags.',
                ])->withInput();
            }
        }

        $inserted = 0;
        $skipped = 0;
        $existingPhones = Lead::pluck('phone')->flip();

        DB::transaction(function () use ($handle, $headerMap, $campaign, $validated, $defaultStageId, &$inserted, &$skipped, $existingPhones) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowData = $headerMap->mapWithKeys(function ($column, $index) use ($row) {
                    return [$column => trim((string) ($row[$index] ?? ''))];
                });

                $name = $rowData->get('name');
                $phone = $rowData->get('phone');

                if ($name === '' || $phone === '') {
                    $skipped++;
                    continue;
                }

                if ($existingPhones->has($phone)) {
                    $skipped++;
                    continue;
                }

                Lead::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => $validated['user_id'] ?? null,
                    'stage_id' => $defaultStageId,
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $rowData->get('email') ?: null,
                    'source' => $validated['source'],
                    'tags' => $this->normalizeTags($rowData->get('tags')),
                ]);

                $existingPhones->put($phone, true);
                $inserted++;
            }
        });

        fclose($handle);

        return redirect()
            ->route('callingcrm.contacts.index')
            ->with('success', "Import complete. Added {$inserted} leads, skipped {$skipped} rows.");
    }

    protected function normalizeTags(?string $tags): ?array
    {
        if ($tags === null || trim($tags) === '') {
            return null;
        }

        $values = collect(explode(',', $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        return count($values) > 0 ? $values : null;
    }
}
