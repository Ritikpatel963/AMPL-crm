<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Campaign;
use App\Models\CommunicationEvent;
use App\Models\CrmUserSession;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadDisposition;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function catalog()
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['name' => 'Lead Disposition Report', 'category' => 'User Reports', 'slug' => 'lead-disposition'],
                ['name' => 'User Call Report', 'category' => 'User Reports', 'slug' => 'user-call'],
                ['name' => 'Follow-Up Report', 'category' => 'User Reports', 'slug' => 'follow-ups'],
                ['name' => 'Campaign Report', 'category' => 'Campaign Reports', 'slug' => 'campaign'],
                ['name' => 'Login Report', 'category' => 'User Reports', 'slug' => 'login'],
                ['name' => 'Hourly Report', 'category' => 'User Reports', 'slug' => 'hourly'],
            ],
        ]);
    }

    public function dashboardOverview(Request $request)
    {
        // Consolidated into a single query instead of 3 separate count queries
        $stats = $this->dateFilteredCalls($request)
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw('SUM(status IN ("connected", "answered")) as connected_calls')
            ->first();

        $totalCalls = (int) ($stats->total_calls ?? 0);
        $connectedCalls = (int) ($stats->connected_calls ?? 0);

        return response()->json([
            'status' => true,
            'data' => [
                'total_calls' => $totalCalls,
                'connected_calls' => $connectedCalls,
                'not_connected_calls' => max($totalCalls - $connectedCalls, 0),
                'connected_percent' => $totalCalls > 0 ? round(($connectedCalls / $totalCalls) * 100, 2) : 0,
            ],
        ]);
    }

    public function agentActivity()
    {
        // Consolidated into fewer queries
        $agentCounts = User::query()
            ->whereIn('role', ['agent', 'subadmin'])
            ->selectRaw('COUNT(*) as total_agents')
            ->selectRaw('SUM(crm_status = "active" AND last_seen_at IS NOT NULL) as active_agents')
            ->first();

        $onBreak = DB::table('user_breaks')
            ->whereNull('ended_at')
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'status' => true,
            'data' => [
                'total_agents' => (int) ($agentCounts->total_agents ?? 0),
                'active_agents' => (int) ($agentCounts->active_agents ?? 0),
                'on_break_agents' => $onBreak,
            ],
        ]);
    }

    public function leadsByStage(Request $request)
    {
        $leads = Lead::query()
            ->select('stage_id', DB::raw('count(*) as total'))
            ->with('stage:id,name,color')
            ->when($request->filled('pipeline_id'), fn ($query) => $query->where('pipeline_id', $request->pipeline_id))
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->groupBy('stage_id')
            ->get();

        return response()->json(['status' => true, 'data' => $leads]);
    }

    public function userCallReport(Request $request)
    {
        $rows = User::query()
            ->select('users.id', 'users.name', 'users.phone_number')
            ->withCount([
                'callLogs as total_calls' => fn ($query) => $this->applyDateFilter($query, $request, 'started_at'),
                'callLogs as connected_calls' => fn ($query) => $this->applyDateFilter($query->connected(), $request, 'started_at'),
                'leadDispositions as disposed_count' => fn ($query) => $this->applyDateFilter($query, $request, 'disposed_at'),
                'assignedLeads as in_progress_leads' => fn ($query) => $query->where('status', 'in_progress'),
                'assignedLeads as converted_leads' => fn ($query) => $query->where('status', 'converted'),
                'assignedLeads as lost_leads' => fn ($query) => $query->where('status', 'lost'),
                'followUps as follow_ups_due_today' => fn ($query) => $query->whereDate('scheduled_at', today()),
            ])
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function followUpReport(Request $request)
    {
        $followUps = FollowUp::query()
            ->select('id', 'lead_id', 'user_id', 'campaign_id', 'scheduled_at', 'status', 'note', 'completed_at', 'created_at')
            ->with([
                'lead:id,name,phone,email,status',
                'user:id,name,phone_number',
                'campaign:id,name',
            ])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->orderBy('scheduled_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $followUps]);
    }

    public function campaignReport(Request $request)
    {
        $campaigns = Campaign::query()
            ->select('id', 'name', 'pipeline_id', 'status', 'created_at')
            ->withCount([
                'leads as total_leads',
                'leads as converted_leads' => fn ($query) => $query->where('status', 'converted'),
                'leads as lost_leads' => fn ($query) => $query->where('status', 'lost'),
                'callLogs as total_calls',
                'callLogs as connected_calls' => fn ($query) => $query->connected(),
            ])
            ->when($request->filled('pipeline_id'), fn ($query) => $query->where('pipeline_id', $request->pipeline_id))
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $campaigns]);
    }

    public function trendWidgets(Request $request)
    {
        // Consolidated call stats into a single query instead of 4 separate queries
        $callStats = $this->dateFilteredCalls($request)
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw('SUM(status IN ("connected", "answered")) as total_calls_connected')
            ->selectRaw('COALESCE(SUM(duration_seconds), 0) as total_call_time_seconds')
            ->first();

        // Consolidated lead stats into a single query instead of 2 separate queries
        $leadStats = Lead::query()
            ->selectRaw('SUM(status = "converted") as total_converted_leads')
            ->selectRaw('SUM(status = "lost") as total_lost_leads')
            ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'total_sms_sent' => CommunicationEvent::where('channel', 'sms')->count(),
                'total_calls' => (int) ($callStats->total_calls ?? 0),
                'total_converted_leads' => (int) ($leadStats->total_converted_leads ?? 0),
                'total_call_time_seconds' => (int) ($callStats->total_call_time_seconds ?? 0),
                'total_calls_connected' => (int) ($callStats->total_calls_connected ?? 0),
                'total_lost_leads' => (int) ($leadStats->total_lost_leads ?? 0),
            ],
        ]);
    }

    public function callsVsConnected(Request $request)
    {
        $rows = $this->dateFilteredCalls($request)
            ->selectRaw('DATE(started_at) as date, COUNT(*) as total_calls, SUM(status in ("connected", "answered")) as connected_calls')
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function leadSources()
    {
        $rows = Lead::query()
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function queueExport(Request $request)
    {
        $data = $request->validate([
            'report_type' => ['required', 'string', 'max:80'],
            'filters' => ['nullable', 'array'],
        ]);

        $export = ReportExport::create([
            'user_id' => auth()->id(),
            'report_type' => $data['report_type'],
            'filters' => $data['filters'] ?? [],
            'status' => 'queued',
            'expires_at' => now()->addDay(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Report export queued successfully',
            'data' => $export,
        ], 201);
    }

    public function showExport(ReportExport $export)
    {
        abort_if($export->user_id !== auth()->id(), 403);

        return response()->json([
            'status' => true,
            'data' => $export,
        ]);
    }

    public function leadDispositionReport(Request $request)
    {
        $rows = LeadDisposition::with(['lead:id,name,phone', 'user:id,name', 'disposition:id,name', 'toStage:id,name'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('disposed_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('disposed_at', '<=', $request->to))
            ->latest('disposed_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function loginReport(Request $request)
    {
        $rows = CrmUserSession::with('user:id,name')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('logged_in_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('logged_in_at', '<=', $request->to))
            ->latest('logged_in_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function hourlyReport(Request $request)
    {
        $rows = CallLog::query()
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as total_calls, SUM(status IN ("connected","answered")) as connected_calls')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('started_at', '<=', $request->to))
            ->groupByRaw('HOUR(started_at)')
            ->orderBy('hour')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function dayReport(Request $request)
    {
        $rows = CallLog::query()
            ->selectRaw('DATE(started_at) as date, COUNT(*) as total_calls, SUM(status IN ("connected","answered")) as connected_calls')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('started_at', '<=', $request->to))
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function callDurationTrend(Request $request)
    {
        $rows = $this->dateFilteredCalls($request)
            ->selectRaw('DATE(started_at) as date, AVG(duration_seconds) as avg_duration, SUM(duration_seconds) as total_duration')
            ->where('duration_seconds', '>', 0)
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function conversionRatioTrend(Request $request)
    {
        $rows = $this->dateFilteredCalls($request)
            ->selectRaw('DATE(started_at) as date, COUNT(*) as total_calls, SUM(status IN ("connected","answered")) as connected_calls')
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total_calls' => (int) $row->total_calls,
                'connected_calls' => (int) $row->connected_calls,
                'conversion_ratio' => $row->total_calls > 0 ? round(($row->connected_calls / $row->total_calls) * 100, 2) : 0,
            ]);

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function leadsAddedTrend(Request $request)
    {
        $rows = Lead::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function lostLeadsTrend(Request $request)
    {
        $rows = LeadDisposition::whereHas('disposition', fn ($q) => $q->where('type', 'closed_lost'))
            ->selectRaw('DATE(disposed_at) as date, COUNT(*) as total')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('disposed_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('disposed_at', '<=', $request->to))
            ->groupByRaw('DATE(disposed_at)')
            ->orderBy('date')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    private function dateFilteredCalls(Request $request)
    {
        return $this->applyDateFilter(CallLog::query(), $request, 'started_at');
    }

    private function applyDateFilter($query, Request $request, string $column)
    {
        return $query
            ->when($request->filled('from'), fn ($query) => $query->whereDate($column, '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate($column, '<=', $request->to));
    }
}
