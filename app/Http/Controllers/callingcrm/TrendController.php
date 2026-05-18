<?php

namespace App\Http\Controllers\callingcrm;

use App\Http\Controllers\Controller;
use App\Models\callingcrm\CallLog;
use App\Models\callingcrm\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrendController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $activeFilter] = $this->resolveRange($request);

        $callQuery = CallLog::whereBetween('called_at', [$from, $to]);
        $leadQuery = Lead::whereBetween('created_at', [$from, $to]);

        $metrics = [
            'total_sms_sent' => 0,
            'total_calls' => (clone $callQuery)->count(),
            'converted_leads' => (clone $leadQuery)->whereHas('stage', function ($query) {
                $query->where('name', 'Closed Won');
            })->count(),
            'call_duration' => (clone $callQuery)->sum('duration'),
        ];

        $periodFilters = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            '7_days' => 'Last 7 Days',
            '30_days' => 'Last 30 Days',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'all_time' => 'All Time',
            'custom' => 'Custom Range'
        ];

        $callVsConnected = $this->buildWeeklyCallSeries($from, $to);
        $userTrend = User::whereIn('role', ['agent', 'subadmin', 'manager'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($from, $to, $activeFilter) {
                $query = CallLog::where('user_id', $user->id);
                if ($activeFilter !== 'all_time') {
                    $query->whereBetween('called_at', [$from, $to]);
                }

                $calls = (clone $query)->count();
                $connected = (clone $query)->where('status', 'connected')->count();
                $totalDuration = (clone $query)->sum('duration'); // Assuming duration is in seconds
                $averageDuration = $connected > 0 ? round($totalDuration / $connected) : 0;

                return [
                    'name' => $user->name,
                    'calls' => $calls,
                    'connected' => $connected,
                    'total_duration' => $totalDuration,
                    'average_duration' => $averageDuration,
                ];
            });

        return view('callingcrm.trends.index', compact('metrics', 'periodFilters', 'callVsConnected', 'userTrend', 'from', 'to', 'activeFilter'));
    }

    protected function resolveRange(Request $request): array
    {
        $filter = $request->get('range', '30_days');

        switch ($filter) {
            case 'today':
                return [now()->startOfDay(), now()->endOfDay(), $filter];
            case 'yesterday':
                return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), $filter];
            case '7_days':
                return [now()->subDays(6)->startOfDay(), now()->endOfDay(), $filter];
            case 'this_month':
                return [now()->startOfMonth(), now()->endOfMonth(), $filter];
            case 'last_month':
                return [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth(), $filter];
            case 'all_time':
                // Return a very early date
                return [Carbon::create(2000, 1, 1), now()->endOfDay(), $filter];
            case 'custom':
                if ($request->filled('from') && $request->filled('to')) {
                    return [
                        Carbon::parse($request->string('from'))->startOfDay(),
                        Carbon::parse($request->string('to'))->endOfDay(),
                        $filter,
                    ];
                }
                // Fallthrough if custom missing dates
            case '30_days':
            default:
                return [now()->subDays(29)->startOfDay(), now()->endOfDay(), '30_days'];
        }
    }

    protected function buildWeeklyCallSeries(Carbon $from, Carbon $to): array
    {
        $days = collect();
        $cursor = $from->copy();
        while ($cursor <= $to) {
            $days->push([
                'label' => $cursor->format('d M'),
                'calls' => CallLog::whereDate('called_at', $cursor->toDateString())->count(),
                'connected' => CallLog::whereDate('called_at', $cursor->toDateString())->where('status', 'connected')->count(),
            ]);
            $cursor->addDay();
        }

        return $days->all();
    }
}
