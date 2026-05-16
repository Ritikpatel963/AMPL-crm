<?php

namespace App\Http\Controllers\Api;

use App\Events\CallAcceptedEvent;
use App\Events\CallEndedEvent;
use App\Events\CallRejectedEvent;
use App\Events\IncomingCallEvent;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Services\AgoraService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CallController extends Controller
{
    /* ================= START CALL ================= */
    public function start(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id'
        ]);

        $caller = $request->user();

        $channel = 'call_' . $caller->id . '_' . $request->receiver_id . '_' . time();

        $call = Call::create([
            'caller_id' => $caller->id,
            'receiver_id' => $request->receiver_id,
            'channel_name' => $channel,
            'status' => 'calling',
        ]);

        // CRITICAL: Use caller's ID as UID
        $uid = $caller->id;
        $token = AgoraService::generateToken($channel, $uid);

        Log::info('Call started', [
            'call_id' => $call->id,
            'channel' => $channel,
            'caller_uid' => $uid,
        ]);

        event(new IncomingCallEvent(
            $call->id,
            $caller->id,
            $call->receiver_id,
            $caller->name,
            $call->channel_name
        ));

        return response()->json([
            'call_id' => $call->id,
            'channel' => $channel,
            'token' => $token,
            'uid' => $uid  // MUST match token generation
        ]);
    }

    /* ================= ACCEPT CALL ================= */
    public function accept(Call $call, Request $request)
    {
        abort_if($call->receiver_id !== $request->user()->id, 403);
        abort_if($call->status !== 'calling', 400, 'Call is not active');

        $call->update([
            'status' => 'connected',
            'started_at' => now()
        ]);

        // CRITICAL: Use receiver's ID as UID
        $uid = $request->user()->id;
        $token = AgoraService::generateToken(
            $call->channel_name,
            $uid
        );

        Log::info('Call accepted', [
            'call_id' => $call->id,
            'channel' => $call->channel_name,
            'receiver_uid' => $uid,
        ]);

        event(new CallAcceptedEvent($call->id));

        return response()->json([
            'channel' => $call->channel_name,
            'token' => $token,
            'uid' => $uid  // MUST match token generation
        ]);
    }

    /* ================= REJECT CALL ================= */
    public function reject(Call $call, Request $request)
    {
        abort_if($call->receiver_id !== $request->user()->id, 403);
        abort_if($call->status !== 'calling', 400, 'Call is not active');

        $call->update([
            'status' => 'rejected',
            'ended_at' => now()
        ]);

        event(new CallRejectedEvent($call->id));

        return response()->json(['success' => true]);
    }

    /* ================= END CALL ================= */
    public function end(Call $call, Request $request)
    {
        abort_if(
            !in_array($request->user()->id, [$call->caller_id, $call->receiver_id]),
            403
        );

        if (in_array($call->status, ['ended', 'rejected', 'missed'])) {
            return response()->json(['success' => true]);
        }

        $call->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $call->started_at
                ? abs(now()->diffInSeconds($call->started_at))
                : null
        ]);

        event(new CallEndedEvent($call->id));

        return response()->json(['success' => true]);
    }
}