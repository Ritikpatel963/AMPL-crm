<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CrmUserSession;
use App\Models\UserBreak;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function loginTrack(Request $request)
    {
        $session = CrmUserSession::create([
            'user_id' => auth()->id(),
            'logged_in_at' => now(),
            'status' => 'online',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Login tracked successfully',
            'data' => $session,
        ], 201);
    }

    public function logoutTrack()
    {
        $session = CrmUserSession::where('user_id', auth()->id())
            ->where('status', 'online')
            ->latest('logged_in_at')
            ->first();

        if ($session) {
            $session->update([
                'logged_out_at' => now(),
                'status' => 'offline',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Logout tracked successfully',
        ]);
    }

    public function startBreak(Request $request)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:120'],
        ]);

        $session = CrmUserSession::where('user_id', auth()->id())
            ->where('status', 'online')
            ->latest('logged_in_at')
            ->first();

        $break = UserBreak::create([
            'user_id' => auth()->id(),
            'session_id' => $session?->id,
            'reason' => $data['reason'] ?? null,
            'started_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Break started successfully',
            'data' => $break,
        ], 201);
    }

    public function endBreak(UserBreak $break)
    {
        abort_if($break->user_id !== auth()->id(), 403);

        $duration = $break->started_at->diffInSeconds(now());

        $break->update([
            'ended_at' => now(),
            'duration_seconds' => $duration,
        ]);

        if ($break->session) {
            $breakMin = $break->session->break_minutes + intdiv($duration, 60);
            $break->session->update(['break_minutes' => $breakMin]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Break ended successfully',
            'data' => $break->fresh(),
        ]);
    }

    public function current()
    {
        $session = CrmUserSession::where('user_id', auth()->id())
            ->where('status', 'online')
            ->latest('logged_in_at')
            ->first();

        $activeBreak = UserBreak::where('user_id', auth()->id())
            ->whereNull('ended_at')
            ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'session' => $session,
                'active_break' => $activeBreak,
                'is_online' => (bool) $session,
                'is_on_break' => (bool) $activeBreak,
            ],
        ]);
    }
}
