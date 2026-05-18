<?php

namespace App\Http\Controllers\callingcrm;

use App\Http\Controllers\Controller;
use App\Models\callingcrm\Campaign;
use App\Models\callingcrm\Disposition;
use App\Models\callingcrm\Lead;
use App\Models\callingcrm\LeadStage;
use App\Models\callingcrm\Pipeline;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PipelineController extends Controller
{
    public function index()
    {
        $pipelines = Pipeline::with(['campaigns.manager:id,name', 'campaigns.agents:id,name'])
            ->orderBy('name')
            ->get();

        $users = User::whereIn('role', ['subadmin', 'agent', 'manager'])->orderBy('name')->get(['id', 'name', 'role']);

        return view('callingcrm.pipeline.index', compact('pipelines', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stages' => ['nullable', 'string'],
            'dispositions' => ['nullable', 'string'],
        ]);

        $pipeline = DB::transaction(function () use ($validated) {
            $pipeline = Pipeline::create([
                'name' => $validated['name'],
            ]);

            $stages = collect(explode(',', (string) ($validated['stages'] ?? 'Fresh Lead,Follow Up,Closed Won,Closed Lost')))
                ->map(fn ($stage) => trim($stage))
                ->filter()
                ->values();

            $stageColors = ['#0d6efd', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4'];

            foreach ($stages as $index => $stageName) {
                LeadStage::create([
                    'pipeline_id' => $pipeline->id,
                    'name' => $stageName,
                    'color' => $stageColors[$index % count($stageColors)],
                    'sort_order' => $index + 1,
                ]);
            }

            $dispositions = collect(explode(',', (string) ($validated['dispositions'] ?? 'Follow Up:in_progress,Closed Won:closed_won,Closed Lost:closed_lost')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values();

            foreach ($dispositions as $index => $item) {
                [$name, $type] = array_pad(explode(':', $item), 2, 'in_progress');
                $type = trim($type);
                if (!in_array($type, Disposition::TYPE_OPTIONS, true)) {
                    $type = 'in_progress';
                }

                Disposition::create([
                    'pipeline_id' => $pipeline->id,
                    'name' => trim($name),
                    'type' => $type,
                    'sort_order' => $index + 1,
                ]);
            }

            return $pipeline;
        });

        return redirect()
            ->route('callingcrm.pipeline.index')
            ->with('success', "Pipeline {$pipeline->name} created successfully.");
    }

    public function show(Campaign $campaign)
    {
        $campaign->load([
            'pipeline.stages',
            'manager:id,name',
            'agents:id,name',
            'leads.stage:id,name,color',
            'leads.assignedUser:id,name',
            'leads.followUps',
            'callLogs.user:id,name',
        ]);

        $leadCounts = [
            'total' => $campaign->leads->count(),
            'in_progress' => $campaign->leads->filter(fn ($lead) => optional($lead->stage)->name !== 'Closed Won' && optional($lead->stage)->name !== 'Closed Lost')->count(),
            'closed' => $campaign->leads->filter(fn ($lead) => in_array(optional($lead->stage)->name, ['Closed Won', 'Closed Lost'], true))->count(),
        ];

        $stageBreakdown = $campaign->leads
            ->groupBy(fn ($lead) => optional($lead->stage)->name ?? 'Unassigned')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'count' => $items->count(),
                'color' => optional($items->first()->stage)->color ?? '#64748b',
            ])
            ->values();

        $callSummary = [
            'total_calls' => $campaign->callLogs->count(),
            'connected_calls' => $campaign->callLogs->where('status', 'connected')->count(),
            'duration' => (int) $campaign->callLogs->sum('duration'),
            'follow_ups_due' => $campaign->leads->flatMap->followUps->whereNull('completed_at')->count(),
        ];

        $leadDistribution = Lead::query()
            ->where('campaign_id', $campaign->id)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->with('assignedUser:id,name')
            ->get();

        return view('callingcrm.pipeline.show', compact('campaign', 'leadCounts', 'leadDistribution', 'stageBreakdown', 'callSummary'));
    }
}
