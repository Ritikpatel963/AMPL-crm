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

        $periodFilters = ['Last 30 Days', 'Last 7 Days', 'Custom Range'];
        $callVsConnected = $this->buildWeeklyCallSeries($from, $to);
        $userTrend = User::whereIn('role', ['agent', 'subadmin'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($from, $to) {
                return [
                    'name' => $user->name,
                    'calls' => CallLog::where('user_id', $user->id)->whereBetween('called_at', [$from, $to])->count(),
                    'connected' => CallLog::where('user_id', $user->id)->where('status', 'connected')->whereBetween('called_at', [$from, $to])->count(),
                ];
            });

        return view('callingcrm.trends.index', compact('metrics', 'periodFilters', 'callVsConnected', 'userTrend', 'from', 'to', 'activeFilter'));
    }

    protected function resolveRange(Request $request): array
    {
        $filter = $request->get('range', '30_days');

        if ($filter === '7_days') {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay(), $filter];
        }

        if ($filter === 'custom' && $request->filled('from') && $request->filled('to')) {
            return [
                Carbon::parse($request->string('from'))->startOfDay(),
                Carbon::parse($request->string('to'))->endOfDay(),
                $filter,
            ];
        }

        return [now()->subDays(29)->startOfDay(), now()->endOfDay(), '30_days'];
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
