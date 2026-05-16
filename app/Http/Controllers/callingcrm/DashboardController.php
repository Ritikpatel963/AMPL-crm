<?php

namespace App\Http\Controllers\callingcrm;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\callingcrm\Campaign;
use App\Models\callingcrm\CallLog;
use App\Models\callingcrm\Lead;
use App\Models\callingcrm\UserSession;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCalls = CallLog::count();
        $connectedCalls = CallLog::where('status', 'connected')->count();
        $connectedPercent = $totalCalls > 0 ? round(($connectedCalls / $totalCalls) * 100, 1) : 0;

        $activeAgents = AdminUser::query()
            ->where(function ($query) {
                $query->where('role', 'callingcrm_manager')
                    ->orWhere('role', 'callingcrm_agent');
            })
            ->count();
        $agentsOnBreak = UserSession::whereNull('logged_out_at')
            ->where('break_minutes', '>', 0)
            ->count();

        $stageStats = Lead::query()
            ->selectRaw('stage_id, count(*) as total')
            ->groupBy('stage_id')
            ->with('stage:id,name,color')
            ->get();

        $totalLeads = max(1, Lead::count());

        $leadsByStage = $stageStats->map(function ($row) use ($totalLeads) {
            return [
                'name' => optional($row->stage)->name ?? 'Unassigned',
                'color' => optional($row->stage)->color ?? '#64748b',
                'total' => $row->total,
                'percentage' => round(($row->total / $totalLeads) * 100, 1),
            ];
        });

        $quickAccess = [
            ['label' => 'User Call Report', 'href' => route('callingcrm.reports.index')],
            ['label' => 'Login Report', 'href' => route('callingcrm.reports.index')],
            ['label' => 'Upload Excel', 'href' => route('callingcrm.contacts.upload')],
            ['label' => 'Create Campaign', 'href' => route('callingcrm.pipeline.index')],
        ];

        $toolsPanel = [
            ['label' => 'User Trends', 'href' => route('callingcrm.trends.index')],
            ['label' => 'Business Trend', 'href' => route('callingcrm.trends.index')],
            ['label' => 'Workflow', 'href' => route('callingcrm.pipeline.index')],
        ];

        $pinnedCampaigns = Campaign::query()
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'status']);

        return view('callingcrm.dashboard.index', compact(
            'activeAgents',
            'agentsOnBreak',
            'connectedCalls',
            'connectedPercent',
            'leadsByStage',
            'pinnedCampaigns',
            'quickAccess',
            'toolsPanel',
            'totalCalls'
        ));
    }
}
