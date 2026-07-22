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
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function catalog()
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['name' => 'Lead Disposition Report', 'category' => 'User Reports', 'slug' => 'lead-disposition'],
                ['name' => 'User Stage Report', 'category' => 'User Reports', 'slug' => 'user-stage'],
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
            ->selectRaw('SUM(CASE WHEN status IN ("connected", "answered") THEN 1 ELSE 0 END) as connected_calls')
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
            ->selectRaw('SUM(CASE WHEN crm_status = "active" AND last_seen_at IS NOT NULL THEN 1 ELSE 0 END) as active_agents')
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
            ->when($request->filled('campaign_ids'), function ($query) use ($request) {
                $ids = is_array($request->campaign_ids) ? $request->campaign_ids : explode(',', $request->campaign_ids);
                $query->whereIn('campaign_id', array_filter($ids));
            })
            ->when(!$request->filled('campaign_ids') && $request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->groupBy('stage_id')
            ->get();

        return response()->json(['status' => true, 'data' => $leads]);
    }

    public function userCallReport(Request $request)
    {
        $rows = $this->userCallReportQuery($request)
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        $rows->setCollection($this->appendUserReportMetrics($rows->getCollection(), $request));

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function userActivityReport(Request $request)
    {
        return $this->userCallReport($request);
    }

    public function exportUserCallReport(Request $request)
    {
        $users = $this->appendUserReportMetrics($this->userCallReportQuery($request)->get(), $request);
        $rows = $this->formatUserReportExportRows($users, $request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('User Report');

        $headers = [
            'No.',
            'User Name',
            'Reporting Manager',
            'Mobile Number',
            'Date',
            'Total Calls',
            'Total Calls Connected',
            'Total Unconnected Calls',
            'Total Outgoing Calls',
            'Outgoing Connected Calls',
            'Outgoing Unanswered Calls',
            'Avg. Outgoing Call Duration',
            'Total Incoming Calls',
            'Incoming Connected Calls',
            'Incoming Unanswered Calls',
            'Avg. Incoming Call Duration',
            'Total Disposed Count',
            'Disposed Yes Connected Count',
            'Disposed Not Connected Count',
            'Total In-Progress Leads',
            'Total Converted Leads',
            'Total Lost Leads',
            'Follow-Ups Due Today',
            'Avg. Start Calling Time',
            'Avg. Call Duration',
            'Avg. Form Filling Time',
            'Total Call Duration',
            'Total Number of Breaks',
            'Total Break Duration',
            'Total Whatsapp Sent',
            'Total Emails Sent',
            'Total SMS Sent',
        ];

        $sheet->fromArray($headers, null, 'A1');
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $lastColumn = $sheet->getHighestColumn();
        $sheet->freezePane('C2');
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6F42C1']],
        ]);

        for ($column = 1; $column <= count($headers); $column++) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $fileName = sprintf('user-report-%s-to-%s.xlsx', $request->input('from', now()->toDateString()), $request->input('to', now()->toDateString()));

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function userCallReportQuery(Request $request)
    {
        $userIds = $this->idsFromRequest($request, 'user_ids');
        $managerIds = $this->idsFromRequest($request, 'manager_ids');
        $includeNoManager = in_array('none', (array) $request->input('manager_ids', []), true)
            || str_contains((string) $request->input('manager_ids', ''), 'none');

        return User::query()
            ->select('users.id', 'users.reporting_manager_id', 'users.name', 'users.phone_number')
            ->with('reportingManager:id,name')
            ->whereIn('users.role', ['agent', 'subadmin'])
            ->when($userIds, fn ($query) => $query->whereIn('users.id', $userIds))
            ->when($managerIds || $includeNoManager, function ($query) use ($managerIds, $includeNoManager) {
                $query->where(function ($query) use ($managerIds, $includeNoManager) {
                    if ($managerIds) {
                        $query->whereIn('users.reporting_manager_id', $managerIds);
                    }

                    if ($includeNoManager) {
                        $query->orWhereNull('users.reporting_manager_id');
                    }
                });
            })
            ->orderBy('users.name');
    }

    private function appendUserReportMetrics($users, Request $request)
    {
        $pageUserIds = $users->pluck('id')->all();

        if ($pageUserIds) {
            $connected = ['connected', 'answered'];
            $notConnected = ['not_connected', 'busy', 'no_answer', 'failed', 'missed'];
            $sentStatuses = ['sent', 'delivered', 'read'];

            $calls = $this->applyDateFilter(DB::table('call_logs'), $request, 'started_at')
                ->whereIn('user_id', $pageUserIds)
                ->select('user_id')
                ->selectRaw('COUNT(*) as total_calls')
                ->selectRaw('SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as connected_calls', $connected)
                ->selectRaw('SUM(CASE WHEN status IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) as unconnected_calls', $notConnected)
                ->selectRaw('SUM(CASE WHEN direction = "outgoing" THEN 1 ELSE 0 END) as outgoing_calls')
                ->selectRaw('SUM(CASE WHEN direction = "outgoing" AND status IN (?, ?) THEN 1 ELSE 0 END) as outgoing_connected_calls', $connected)
                ->selectRaw('SUM(CASE WHEN direction = "outgoing" AND status IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) as outgoing_unanswered_calls', $notConnected)
                ->selectRaw('AVG(CASE WHEN direction = "outgoing" THEN duration_seconds END) as avg_outgoing_call_duration_seconds')
                ->selectRaw('SUM(CASE WHEN direction = "incoming" THEN 1 ELSE 0 END) as incoming_calls')
                ->selectRaw('SUM(CASE WHEN direction = "incoming" AND status IN (?, ?) THEN 1 ELSE 0 END) as incoming_connected_calls', $connected)
                ->selectRaw('SUM(CASE WHEN direction = "incoming" AND status IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) as incoming_unanswered_calls', $notConnected)
                ->selectRaw('AVG(CASE WHEN direction = "incoming" THEN duration_seconds END) as avg_incoming_call_duration_seconds')
                ->selectRaw('AVG(duration_seconds) as avg_call_duration_seconds')
                ->selectRaw('SUM(duration_seconds) as total_call_duration_seconds')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $dispositions = $this->applyDateFilter(DB::table('lead_dispositions'), $request, 'disposed_at')
                ->whereIn('user_id', $pageUserIds)
                ->select('user_id')
                ->selectRaw('COUNT(*) as disposed_count')
                ->selectRaw('SUM(CASE WHEN call_status IN (?, ?) THEN 1 ELSE 0 END) as disposed_connected_count', $connected)
                ->selectRaw('SUM(CASE WHEN call_status IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) as disposed_not_connected_count', $notConnected)
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $leads = DB::table('leads')
                ->whereIn('assigned_user_id', $pageUserIds)
                ->select('assigned_user_id')
                ->selectRaw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_leads')
                ->selectRaw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted_leads')
                ->selectRaw('SUM(CASE WHEN status = "lost" THEN 1 ELSE 0 END) as lost_leads')
                ->groupBy('assigned_user_id')
                ->get()
                ->keyBy('assigned_user_id');

            $followUps = DB::table('follow_ups')
                ->whereIn('user_id', $pageUserIds)
                ->whereDate('scheduled_at', today())
                ->select('user_id')
                ->selectRaw('COUNT(*) as follow_ups_due_today')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $breaks = $this->applyDateFilter(DB::table('user_breaks'), $request, 'started_at')
                ->whereIn('user_id', $pageUserIds)
                ->select('user_id')
                ->selectRaw('COUNT(*) as total_breaks')
                ->selectRaw('SUM(duration_seconds) as total_break_duration_seconds')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $communications = $this->applyDateFilter(DB::table('communication_events'), $request, 'sent_at')
                ->whereIn('user_id', $pageUserIds)
                ->whereIn('status', $sentStatuses)
                ->select('user_id')
                ->selectRaw('SUM(CASE WHEN channel = "whatsapp" THEN 1 ELSE 0 END) as whatsapp_sent')
                ->selectRaw('SUM(CASE WHEN channel = "email" THEN 1 ELSE 0 END) as emails_sent')
                ->selectRaw('SUM(CASE WHEN channel = "sms" THEN 1 ELSE 0 END) as sms_sent')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $activityMetrics = $this->activityMetricsByUser($pageUserIds, $request);

            $users->transform(function (User $user) use ($calls, $dispositions, $leads, $followUps, $breaks, $communications, $activityMetrics) {
                foreach ([
                    'total_calls', 'connected_calls', 'unconnected_calls', 'outgoing_calls', 'outgoing_connected_calls',
                    'outgoing_unanswered_calls', 'avg_outgoing_call_duration_seconds', 'incoming_calls',
                    'incoming_connected_calls', 'incoming_unanswered_calls', 'avg_incoming_call_duration_seconds',
                    'avg_call_duration_seconds', 'total_call_duration_seconds',
                ] as $field) {
                    $user->{$field} = (float) ($calls[$user->id]->{$field} ?? 0);
                }

                foreach (['disposed_count', 'disposed_connected_count', 'disposed_not_connected_count'] as $field) {
                    $user->{$field} = (int) ($dispositions[$user->id]->{$field} ?? 0);
                }

                foreach (['in_progress_leads', 'converted_leads', 'lost_leads'] as $field) {
                    $user->{$field} = (int) ($leads[$user->id]->{$field} ?? 0);
                }

                $user->follow_ups_due_today = (int) ($followUps[$user->id]->follow_ups_due_today ?? 0);
                $user->total_breaks = (int) ($breaks[$user->id]->total_breaks ?? 0);
                $user->total_break_duration_seconds = (float) ($breaks[$user->id]->total_break_duration_seconds ?? 0);
                $user->whatsapp_sent = (int) ($communications[$user->id]->whatsapp_sent ?? 0);
                $user->emails_sent = (int) ($communications[$user->id]->emails_sent ?? 0);
                $user->sms_sent = (int) ($communications[$user->id]->sms_sent ?? 0);
                $user->avg_start_calling_time = $activityMetrics[$user->id]['avg_start_calling_time'] ?? null;
                $user->avg_form_filling_time_seconds = $activityMetrics[$user->id]['avg_form_filling_time_seconds'] ?? 0;

                return $user;
            });
        }

        return $users;
    }

    private function activityMetricsByUser(array $userIds, Request $request): array
    {
        if (! $userIds) {
            return [];
        }

        $starts = $this->applyDateFilter(DB::table('call_logs'), $request, 'started_at')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('started_at')
            ->select('user_id', 'started_at')
            ->orderBy('started_at')
            ->get();

        $firstCallSeconds = [];

        foreach ($starts as $row) {
            try {
                $startedAt = \Carbon\Carbon::parse($row->started_at);
            } catch (\Throwable $exception) {
                continue;
            }

            $dateKey = $startedAt->toDateString();
            $current = $firstCallSeconds[$row->user_id][$dateKey] ?? null;
            $seconds = ($startedAt->hour * 3600) + ($startedAt->minute * 60) + $startedAt->second;

            if ($current === null || $seconds < $current) {
                $firstCallSeconds[$row->user_id][$dateKey] = $seconds;
            }
        }

        $formRows = $this->applyDateFilter(DB::table('lead_dispositions'), $request, 'lead_dispositions.disposed_at')
            ->leftJoin('call_logs', 'lead_dispositions.call_log_id', '=', 'call_logs.id')
            ->whereIn('lead_dispositions.user_id', $userIds)
            ->whereNotNull('lead_dispositions.disposed_at')
            ->whereNotNull('call_logs.ended_at')
            ->select('lead_dispositions.user_id', 'lead_dispositions.disposed_at', 'call_logs.ended_at')
            ->get();

        $formSeconds = [];

        foreach ($formRows as $row) {
            try {
                $disposedAt = \Carbon\Carbon::parse($row->disposed_at);
                $endedAt = \Carbon\Carbon::parse($row->ended_at);
            } catch (\Throwable $exception) {
                continue;
            }

            $seconds = $endedAt->diffInSeconds($disposedAt, false);

            if ($seconds >= 0) {
                $formSeconds[$row->user_id][] = $seconds;
            }
        }

        return collect($userIds)->mapWithKeys(function ($userId) use ($firstCallSeconds, $formSeconds) {
            $startValues = array_values($firstCallSeconds[$userId] ?? []);
            $formValues = $formSeconds[$userId] ?? [];

            return [
                $userId => [
                    'avg_start_calling_time' => $startValues ? $this->secondsToClock(array_sum($startValues) / count($startValues)) : null,
                    'avg_form_filling_time_seconds' => $formValues ? array_sum($formValues) / count($formValues) : 0,
                ],
            ];
        })->all();
    }

    private function formatUserReportExportRows($users, Request $request): array
    {
        $reportDate = $this->displayDate((string) $request->input('to', now()->toDateString()));

        return $users->values()->map(function (User $user, int $index) use ($reportDate) {
            return [
                $index + 1,
                $user->name ?: '--',
                $user->reportingManager?->name ?: 'No Manager',
                $user->phone_number ?: '--',
                $reportDate,
                (int) ($user->total_calls ?? 0),
                (int) ($user->connected_calls ?? 0),
                (int) ($user->unconnected_calls ?? 0),
                (int) ($user->outgoing_calls ?? 0),
                (int) ($user->outgoing_connected_calls ?? 0),
                (int) ($user->outgoing_unanswered_calls ?? 0),
                $this->secondsToClock($user->avg_outgoing_call_duration_seconds ?? 0),
                (int) ($user->incoming_calls ?? 0),
                (int) ($user->incoming_connected_calls ?? 0),
                (int) ($user->incoming_unanswered_calls ?? 0),
                $this->secondsToClock($user->avg_incoming_call_duration_seconds ?? 0),
                (int) ($user->disposed_count ?? 0),
                (int) ($user->disposed_connected_count ?? 0),
                (int) ($user->disposed_not_connected_count ?? 0),
                (int) ($user->in_progress_leads ?? 0),
                (int) ($user->converted_leads ?? 0),
                (int) ($user->lost_leads ?? 0),
                (int) ($user->follow_ups_due_today ?? 0),
                $user->avg_start_calling_time ?: '--',
                $this->secondsToClock($user->avg_call_duration_seconds ?? 0),
                $this->secondsToClock($user->avg_form_filling_time_seconds ?? 0),
                $this->secondsToClock($user->total_call_duration_seconds ?? 0),
                (int) ($user->total_breaks ?? 0),
                $this->secondsToClock($user->total_break_duration_seconds ?? 0),
                (int) ($user->whatsapp_sent ?? 0),
                (int) ($user->emails_sent ?? 0),
                (int) ($user->sms_sent ?? 0),
            ];
        })->all();
    }

    private function secondsToClock($value): string
    {
        $seconds = max(0, (int) round((float) $value));

        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    private function displayDate(string $value): string
    {
        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $exception) {
            return '--';
        }
    }

    private function reportDateRangeLabel(Request $request): string
    {
        $from = (string) $request->input('from', now()->toDateString());
        $to = (string) $request->input('to', $from);

        return $this->displayDate($from) . ' - ' . $this->displayDate($to);
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
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

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
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $campaigns]);
    }

    public function trendWidgets(Request $request)
    {
        // Consolidated call stats into a single query instead of 4 separate queries
        $callStats = $this->dateFilteredCalls($request)
            ->selectRaw('COUNT(*) as total_calls')
            ->selectRaw('SUM(CASE WHEN status IN ("connected", "answered") THEN 1 ELSE 0 END) as total_calls_connected')
            ->selectRaw('COALESCE(SUM(duration_seconds), 0) as total_call_time_seconds')
            ->first();

        // Consolidated lead stats into a single query instead of 2 separate queries
        $leadStats = Lead::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->selectRaw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as total_converted_leads')
            ->selectRaw('SUM(CASE WHEN status = "lost" THEN 1 ELSE 0 END) as total_lost_leads')
            ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'total_sms_sent' => CommunicationEvent::where('channel', 'sms')
                    ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
                    ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
                    ->count(),
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
            ->selectRaw('DATE(started_at) as date, COUNT(*) as total_calls, SUM(CASE WHEN status in ("connected", "answered") THEN 1 ELSE 0 END) as connected_calls')
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->get();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function leadSources(Request $request)
    {
        $rows = Lead::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
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
        $userIds = $this->idsFromRequest($request, 'user_ids');
        $managerIds = $this->idsFromRequest($request, 'manager_ids');
        $includeNoManager = in_array('none', (array) $request->input('manager_ids', []), true)
            || str_contains((string) $request->input('manager_ids', ''), 'none');

        $rows = LeadDisposition::with([
                'lead:id,name,phone',
                'user:id,name,phone_number,reporting_manager_id',
                'user.reportingManager:id,name',
                'campaign:id,name',
                'disposition:id,name',
                'toStage:id,name',
                'tag:id,name',
                'callLog:id,recording_url,duration_seconds',
            ])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($userIds, fn ($q) => $q->whereIn('user_id', $userIds))
            ->when($managerIds || $includeNoManager, function ($query) use ($managerIds, $includeNoManager) {
                $query->whereHas('user', function ($query) use ($managerIds, $includeNoManager) {
                    $query->where(function ($query) use ($managerIds, $includeNoManager) {
                        if ($managerIds) {
                            $query->whereIn('reporting_manager_id', $managerIds);
                        }

                        if ($includeNoManager) {
                            $query->orWhereNull('reporting_manager_id');
                        }
                    });
                });
            })
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('disposed_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('disposed_at', '<=', $request->to))
            ->latest('disposed_at')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function userStageReport(Request $request)
    {
        $pipelineId = $request->integer('pipeline_id') ?: Pipeline::active()->ordered()->value('id');

        if (! $pipelineId) {
            return response()->json([
                'status' => true,
                'data' => [
                    'pipeline_id' => null,
                    'stages' => [],
                    'rows' => [],
                ],
            ]);
        }

        $userIds = $this->idsFromRequest($request, 'user_ids');
        $campaignIds = $this->idsFromRequest($request, 'campaign_ids');
        if ($request->filled('campaign_id') && is_numeric($request->campaign_id)) {
            $campaignIds[] = (int) $request->campaign_id;
            $campaignIds = array_values(array_unique($campaignIds));
        }

        $managerIds = $this->idsFromRequest($request, 'manager_ids');
        $includeNoManager = in_array('none', (array) $request->input('manager_ids', []), true)
            || str_contains((string) $request->input('manager_ids', ''), 'none');

        $stages = LeadStage::query()
            ->select('id', 'name', 'category', 'color', 'sort_order')
            ->where('pipeline_id', $pipelineId)
            ->active()
            ->ordered()
            ->get();

        $users = User::query()
            ->select('users.id', 'users.reporting_manager_id', 'users.name', 'users.phone_number')
            ->with('reportingManager:id,name')
            ->whereIn('users.role', ['agent', 'subadmin'])
            ->when($userIds, fn ($query) => $query->whereIn('users.id', $userIds))
            ->when($managerIds || $includeNoManager, function ($query) use ($managerIds, $includeNoManager) {
                $query->where(function ($query) use ($managerIds, $includeNoManager) {
                    if ($managerIds) {
                        $query->whereIn('users.reporting_manager_id', $managerIds);
                    }

                    if ($includeNoManager) {
                        $query->orWhereNull('users.reporting_manager_id');
                    }
                });
            })
            ->orderBy('users.name')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        $pageUserIds = $users->getCollection()->pluck('id')->all();
        $stageIds = $stages->pluck('id')->all();
        $closedWonStageIds = $stages->where('category', 'closed_won')->pluck('id')->all();

        $totals = collect();
        $stageCounts = collect();

        if ($pageUserIds) {
            $filteredLeads = DB::table('leads')
                ->where('pipeline_id', $pipelineId)
                ->whereIn('assigned_user_id', $pageUserIds)
                ->when($campaignIds, fn ($query) => $query->whereIn('campaign_id', $campaignIds))
                ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->from))
                ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->to))
                ->whereNull('deleted_at');

            $totals = (clone $filteredLeads)
                ->select('assigned_user_id')
                ->selectRaw('COUNT(*) as total_assigned_leads')
                ->when(
                    $closedWonStageIds,
                    fn ($query) => $query->selectRaw(
                        'SUM(CASE WHEN stage_id IN (' . implode(',', array_fill(0, count($closedWonStageIds), '?')) . ') THEN 1 ELSE 0 END) as converted_leads',
                        $closedWonStageIds
                    ),
                    fn ($query) => $query->selectRaw('0 as converted_leads')
                )
                ->groupBy('assigned_user_id')
                ->get()
                ->keyBy('assigned_user_id');

            $stageCounts = (clone $filteredLeads)
                ->whereIn('stage_id', $stageIds ?: [0])
                ->select('assigned_user_id', 'stage_id')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('assigned_user_id', 'stage_id')
                ->get()
                ->groupBy('assigned_user_id');
        }

        $rows = $users->getCollection()->map(function (User $user) use ($totals, $stageCounts, $stages, $request) {
            $totalAssigned = (int) ($totals[$user->id]->total_assigned_leads ?? 0);
            $converted = (int) ($totals[$user->id]->converted_leads ?? 0);
            $countsByStage = ($stageCounts[$user->id] ?? collect())->keyBy('stage_id');

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'reporting_manager' => $user->reportingManager?->name ?: 'No Manager',
                'phone_number' => $user->phone_number,
                'date' => $this->reportDateRangeLabel($request),
                'conversion_percent' => $totalAssigned > 0 ? round(($converted / $totalAssigned) * 100, 2) : 0,
                'total_assigned_leads' => $totalAssigned,
                'stage_counts' => $stages->mapWithKeys(fn (LeadStage $stage) => [
                    $stage->id => (int) ($countsByStage[$stage->id]->total ?? 0),
                ]),
            ];
        });

        $users->setCollection($rows);

        return response()->json([
            'status' => true,
            'data' => [
                'pipeline_id' => $pipelineId,
                'stages' => $stages,
                'rows' => $users,
            ],
        ]);
    }

    public function loginReport(Request $request)
    {
        $rows = CrmUserSession::with('user:id,name')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('logged_in_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('logged_in_at', '<=', $request->to))
            ->latest('logged_in_at')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

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

    private function idsFromRequest(Request $request, string $key): array
    {
        $value = $request->input($key, []);
        $items = is_array($value) ? $value : explode(',', (string) $value);

        return collect($items)
            ->filter(fn ($item) => is_numeric($item))
            ->map(fn ($item) => (int) $item)
            ->values()
            ->all();
    }
}
