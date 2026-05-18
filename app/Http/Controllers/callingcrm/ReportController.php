<?php

namespace App\Http\Controllers\callingcrm;

use App\Http\Controllers\Controller;
use App\Models\callingcrm\CallLog;
use App\Models\callingcrm\Campaign;
use App\Models\callingcrm\FollowUp;
use App\Models\callingcrm\Lead;
use App\Models\callingcrm\UserSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = [
            'User Report',
            'User Activity Report',
            'Lead Disposition Report',
            'User Stage Report',
            'User Call Report',
            'Follow-Up Report',
            'Login Report',
            'Campaign Report',
            'Campaign Lead Report',
            'Campaign Stage Report',
        ];

        [$from, $to] = $this->resolveDateRange($request);
        $selectedReport = $request->get('report', 'User Report');
        $userId = $request->get('user_id');
        $reportData = $this->buildReportData($selectedReport, $from, $to, $userId);

        $agents = User::whereIn('role', ['agent', 'subadmin', 'manager'])->orderBy('name')->get(['id', 'name']);

        return view('callingcrm.reports.index', compact('reports', 'selectedReport', 'reportData', 'from', 'to', 'agents', 'userId'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $selectedReport = $request->get('report', 'User Report');
        $userId = $request->get('user_id');
        $reportData = $this->buildReportData($selectedReport, $from, $to, $userId);

        $filename = 'callingcrm-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($selectedReport, $reportData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$selectedReport]);
            fputcsv($handle, []);

            if (!empty($reportData['headers'])) {
                fputcsv($handle, $reportData['headers']);
            }

            foreach ($reportData['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function resolveDateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->string('to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    protected function buildReportData(string $selectedReport, Carbon $from, Carbon $to, ?string $userId = null): array
    {
        return match ($selectedReport) {
            'User Activity Report' => $this->userActivityReport($from, $to, $userId),
            'Lead Disposition Report' => $this->leadDispositionReport($from, $to, $userId),
            'User Stage Report' => $this->userStageReport($from, $to, $userId),
            'User Call Report' => $this->userCallReport($from, $to, $userId),
            'Follow-Up Report' => $this->followUpReport($from, $to, $userId),
            'Login Report' => $this->loginReport($from, $to, $userId),
            'Campaign Report' => $this->campaignReport($from, $to),
            'Campaign Lead Report' => $this->campaignLeadReport($from, $to),
            'Campaign Stage Report' => $this->campaignStageReport($from, $to),
            default => $this->userReport($from, $to, $userId),
        };
    }

    protected function userReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $query = User::whereIn('role', ['agent', 'subadmin', 'manager'])->orderBy('name');
        if ($userId) {
            $query->where('id', $userId);
        }

        $rows = $query->get()
            ->map(function ($user) use ($from, $to) {
                $calls = CallLog::where('user_id', $user->id)->whereBetween('called_at', [$from, $to]);
                return [
                    $user->name,
                    $user->role,
                    (int) $calls->count(),
                    (int) $calls->where('status', 'connected')->count(),
                    (int) Lead::where('user_id', $user->id)->count(),
                ];
            })
            ->all();

        return [
            'headers' => ['User', 'Role', 'Total Calls', 'Connected Calls', 'Assigned Leads'],
            'rows' => $rows,
        ];
    }

    protected function userActivityReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $query = UserSession::with('user:id,name')
            ->whereBetween('logged_in_at', [$from, $to]);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $rows = $query->get()
            ->groupBy('user_id')
            ->map(function ($sessions, $userId) use ($from, $to) {
                $user = optional($sessions->first()->user)->name ?? 'Unknown';
                $calls = CallLog::where('user_id', $userId)->whereBetween('called_at', [$from, $to]);
                return [
                    $user,
                    $sessions->count(),
                    (int) $sessions->sum('break_minutes'),
                    (int) $calls->count(),
                    (int) $calls->sum('duration'),
                ];
            })
            ->values()
            ->all();

        return [
            'headers' => ['User', 'Login Sessions', 'Break Minutes', 'Calls', 'Call Duration'],
            'rows' => $rows,
        ];
    }

    protected function leadDispositionReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $statusRows = collect(CallLog::STATUS_OPTIONS)->map(function ($status) use ($from, $to, $userId) {
            $query = CallLog::where('status', $status)->whereBetween('called_at', [$from, $to]);
            if ($userId) {
                $query->where('user_id', $userId);
            }
            return [
                ucfirst(str_replace('_', ' ', $status)),
                (int) $query->count(),
            ];
        })->all();

        return [
            'headers' => ['Disposition', 'Count'],
            'rows' => $statusRows,
        ];
    }

    protected function userStageReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $query = Lead::with(['assignedUser:id,name', 'stage:id,name'])
            ->whereBetween('created_at', [$from, $to]);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $rows = $query->get()
            ->groupBy(fn ($lead) => optional($lead->assignedUser)->name ?? 'Unassigned')
            ->map(function ($leads, $user) {
                return [
                    $user,
                    $leads->groupBy(fn ($lead) => optional($lead->stage)->name ?? 'Unassigned')->map->count()->map(
                        fn ($count, $stage) => $stage . ': ' . $count
                    )->implode(', '),
                ];
            })
            ->values()
            ->all();

        return [
            'headers' => ['User', 'Stage Breakdown'],
            'rows' => $rows,
        ];
    }

    protected function userCallReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $query = User::whereIn('role', ['agent', 'subadmin', 'manager'])->orderBy('name');
        if ($userId) {
            $query->where('id', $userId);
        }

        $rows = $query->get()
            ->map(function ($user) use ($from, $to) {
                $calls = CallLog::where('user_id', $user->id)->whereBetween('called_at', [$from, $to]);
                return [
                    $user->name,
                    (int) $calls->count(),
                    (int) $calls->where('status', 'connected')->count(),
                    (int) $calls->sum('duration'),
                ];
            })
            ->all();

        return [
            'headers' => ['User', 'Total Calls', 'Connected', 'Duration'],
            'rows' => $rows,
        ];
    }

    protected function followUpReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $query = FollowUp::with('user:id,name')
            ->whereBetween('scheduled_at', [$from, $to]);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $rows = $query->get()
            ->groupBy(fn ($followUp) => optional($followUp->user)->name ?? 'Unassigned')
            ->map(function ($items, $user) {
                return [
                    $user,
                    $items->where('is_missed', true)->count(),
                    $items->whereNull('completed_at')->count(),
                ];
            })
            ->values()
            ->all();

        return [
            'headers' => ['User', 'Missed Follow Ups', 'Due Follow Ups'],
            'rows' => $rows,
        ];
    }

    protected function loginReport(Carbon $from, Carbon $to, ?string $userId): array
    {
        $query = UserSession::with('user:id,name')
            ->whereBetween('logged_in_at', [$from, $to]);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $rows = $query->get()
            ->map(function ($session) {
                return [
                    optional($session->user)->name ?? 'Unknown',
                    optional($session->logged_in_at)?->format('d M Y H:i'),
                    optional($session->logged_out_at)?->format('d M Y H:i') ?? 'Active',
                    (int) $session->break_minutes,
                ];
            })
            ->all();

        return [
            'headers' => ['User', 'Logged In', 'Logged Out', 'Break Minutes'],
            'rows' => $rows,
        ];
    }

    protected function campaignReport(Carbon $from, Carbon $to): array
    {
        $rows = Campaign::withCount('leads')
            ->with('pipeline:id,name')
            ->get()
            ->map(function ($campaign) use ($from, $to) {
                $callQuery = CallLog::whereHas('lead', fn ($lead) => $lead->where('campaign_id', $campaign->id))
                    ->whereBetween('called_at', [$from, $to]);
                return [
                    $campaign->name,
                    optional($campaign->pipeline)->name,
                    (int) $campaign->leads_count,
                    (int) $callQuery->count(),
                    (int) $callQuery->where('status', 'connected')->count(),
                ];
            })
            ->all();

        return [
            'headers' => ['Campaign', 'Pipeline', 'Leads', 'Calls', 'Connected Calls'],
            'rows' => $rows,
        ];
    }

    protected function campaignLeadReport(Carbon $from, Carbon $to): array
    {
        $rows = Campaign::with(['leads' => function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to])->with('stage:id,name');
            }])
            ->get()
            ->map(function ($campaign) {
                $leads = $campaign->leads;
                return [
                    $campaign->name,
                    $leads->count(),
                    $leads->where('source', 'FILE_UPLOAD')->count(),
                    $leads->where('source', 'MANUAL')->count(),
                ];
            })
            ->all();

        return [
            'headers' => ['Campaign', 'Total Leads', 'Uploaded Leads', 'Manual Leads'],
            'rows' => $rows,
        ];
    }

    protected function campaignStageReport(Carbon $from, Carbon $to): array
    {
        $rows = Campaign::with(['leads' => function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to])->with('stage:id,name');
            }])
            ->get()
            ->map(function ($campaign) {
                return [
                    $campaign->name,
                    $campaign->leads->groupBy(fn ($lead) => optional($lead->stage)->name ?? 'Unassigned')
                        ->map->count()
                        ->map(fn ($count, $stage) => $stage . ': ' . $count)
                        ->implode(', '),
                ];
            })
            ->all();

        return [
            'headers' => ['Campaign', 'Stage Summary'],
            'rows' => $rows,
        ];
    }
}
