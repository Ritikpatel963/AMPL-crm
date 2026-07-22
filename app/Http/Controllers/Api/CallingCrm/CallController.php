<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\CallingCrm\LeadAccess;

class CallController extends Controller
{
    use LeadAccess;
    public function index(Request $request)
    {
        $calls = CallLog::with(['lead', 'campaign', 'user', 'disposition'])
            ->when($request->user()?->role === 'agent', function ($query) use ($request) {
                $query->where(function ($visible) use ($request) {
                    $visible->where('user_id', $request->user()->id)
                        ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('assigned_user_id', $request->user()->id));
                });
            })
            ->when($request->filled('lead_id'), fn ($query) => $query->where('lead_id', $request->lead_id))
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('started_at', '<=', $request->to))
            ->latest('started_at')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $calls]);
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'phone_number' => ['required', 'string', 'max:20'],
            'direction' => ['sometimes', Rule::in(['outgoing', 'incoming'])],
            'provider_call_id' => ['nullable', 'string', 'max:160'],
        ]);

        $lead = Lead::findOrFail($data['lead_id']);
        abort_if(! $this->canAccessLead($request->user(), $lead), 403);

        $call = CallLog::create([
            'lead_id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'user_id' => auth()->id() ?? $lead->assigned_user_id,
            'direction' => $data['direction'] ?? 'outgoing',
            'status' => 'initiated',
            'provider_call_id' => $data['provider_call_id'] ?? null,
            'phone_number' => $data['phone_number'],
            'started_at' => now(),
            'called_at' => now(),
        ]);

        $lead->update(['last_call_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Call started successfully',
            'data' => $call,
        ], 201);
    }

    public function update(Request $request, CallLog $call)
    {
        abort_if(! $this->canAccessCall($request->user(), $call), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['initiated', 'ringing', 'connected', 'answered', 'not_connected', 'busy', 'no_answer', 'failed', 'missed'])],
            'answered_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'ring_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'recording_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $call->update($data);
        $call->lead?->update(['last_call_at' => $call->ended_at ?? now()]);

        return response()->json([
            'status' => true,
            'message' => 'Call updated successfully',
            'data' => $call->fresh(['lead', 'campaign', 'user']),
        ]);
    }

    public function show(CallLog $call)
    {
        abort_if(! $this->canAccessCall(request()->user(), $call), 403);

        return response()->json([
            'status' => true,
            'data' => $call->load([
                'lead.campaign:id,name,status',
                'lead.assignedUser:id,name,phone_number,email',
                'lead.phoneNumbers:id,lead_id,phone,type,is_primary',
                'lead.propertyValues.property:id,name,slug,data_type',
                'campaign:id,name,status',
                'user:id,name,phone_number,email',
                'disposition:id,name',
                'leadDisposition',
            ]),
        ]);
    }

    public function uploadRecording(Request $request, CallLog $call)
    {
        Log::info('uploadRecording: Start processing upload', [
            'call_id' => $call->id,
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
        ]);

        abort_if(! $this->canAccessCall($request->user(), $call), 403);

        if (!$request->hasFile('recording')) {
            Log::error('uploadRecording: No recording file present in request for call_id: ' . $call->id);
            return response()->json([
                'status' => false,
                'message' => 'No recording file present in request'
            ], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'recording' => ['required', 'file', 'max:51200'],
        ]);

        if ($validator->fails()) {
            Log::error('uploadRecording: Validation failed for call_id ' . $call->id . ': ' . json_encode($validator->errors()->all()));
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('recording');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();

            Log::info('uploadRecording: File details received', [
                'call_id' => $call->id,
                'filename' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
            ]);

            // Store the original file first
            $path = $file->store("calling-crm/recordings/{$call->id}", 'public');
            $storedAbsPath = Storage::disk('public')->path($path);

            // Try to convert to MP3 so browsers can play it
            $mp3RelPath = "calling-crm/recordings/{$call->id}/" . pathinfo($originalName, PATHINFO_FILENAME) . '.mp3';
            $mp3AbsPath = Storage::disk('public')->path($mp3RelPath);

            $ffmpegPath = $this->findFfmpeg();
            $converted  = false;

            if ($ffmpegPath) {
                @mkdir(dirname($mp3AbsPath), 0775, true);
                $cmd = escapeshellarg($ffmpegPath)
                    . ' -y -i ' . escapeshellarg($storedAbsPath)
                    . ' -vn -ar 44100 -ac 1 -ab 64k -f mp3 '
                    . escapeshellarg($mp3AbsPath)
                    . ' 2>&1';
                exec($cmd, $cmdOut, $exitCode);

                if ($exitCode === 0 && file_exists($mp3AbsPath) && filesize($mp3AbsPath) > 0) {
                    // Delete original, use mp3
                    @unlink($storedAbsPath);
                    $path       = $mp3RelPath;
                    $converted  = true;
                    Log::info("uploadRecording: Converted to MP3 for call_id {$call->id}");
                } else {
                    Log::warning("uploadRecording: FFmpeg conversion failed for call_id {$call->id}", ['output' => implode("\n", $cmdOut)]);
                }
            } else {
                Log::info("uploadRecording: FFmpeg not found, saving original format for call_id {$call->id}");
            }

            $url = Storage::disk('public')->url($path);

            // Ensure URL always points to the correct live domain
            // (APP_URL may be set to UAT domain on the server)
            $url = str_replace(
                'https://uatamplchat.agromarket.co.in',
                'https://amplchat.agromarket.co.in',
                $url
            );
            $url = str_replace(
                'http://uatamplchat.agromarket.co.in',
                'https://amplchat.agromarket.co.in',
                $url
            );

            $call->update([
                'recording_url' => $url,
            ]);

            Log::info("uploadRecording: Recording uploaded successfully for call_id {$call->id}", [
                'path'      => $path,
                'url'       => $url,
                'converted' => $converted,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Recording uploaded successfully',
                'data'    => $call->fresh(['lead', 'campaign', 'user']),
            ]);
        } catch (\Exception $e) {
            Log::error("uploadRecording: Error saving recording for call_id {$call->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Internal server error uploading recording'
            ], 500);
        }
    }

    /**
     * Try to find the ffmpeg binary in common locations.
     */
    private function findFfmpeg(): ?string
    {
        $candidates = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/ffmpeg/bin/ffmpeg',
            trim((string) shell_exec('which ffmpeg 2>/dev/null')),
        ];

        foreach ($candidates as $bin) {
            if ($bin && is_executable($bin)) {
                return $bin;
            }
        }

        return null;
    }

    public function webhook(Request $request)
    {
        $expectedSignature = env('TELEPHONY_WEBHOOK_SECRET');
        
        if (empty($expectedSignature) || $request->header('X-Telephony-Signature') !== $expectedSignature) {
            Log::warning('Unauthorized webhook attempt', [
                'ip' => $request->ip(),
                'signature_provided' => $request->hasHeader('X-Telephony-Signature')
            ]);
            return response()->json([
                'status' => false, 
                'message' => 'Unauthorized: Invalid or missing webhook signature'
            ], 401);
        }

        $data = $request->validate([
            'provider_call_id' => ['required', 'string', 'max:160'],
            'status' => ['required', Rule::in(['initiated', 'ringing', 'connected', 'answered', 'not_connected', 'busy', 'no_answer', 'failed', 'missed'])],
            'started_at' => ['nullable', 'date'],
            'answered_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'ring_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'recording_url' => ['nullable', 'string', 'max:500'],
        ]);

        $call = CallLog::where('provider_call_id', $data['provider_call_id'])->first();

        if (!$call) {
            Log::warning('Call webhook received for unknown provider_call_id', ['provider_call_id' => $data['provider_call_id']]);
            return response()->json(['status' => false, 'message' => 'Call not found'], 404);
        }

        $call->update($data);

        if ($call->lead && in_array($data['status'], ['connected', 'answered'])) {
            $call->lead->update(['last_call_at' => $data['ended_at'] ?? now()]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Call webhook processed successfully',
            'data' => $call->fresh(),
        ]);
    }

    public function campaignCallLogs(Request $request, Campaign $campaign)
    {
        $calls = CallLog::where('campaign_id', $campaign->id)
            ->with(['lead:id,campaign_id,assigned_user_id,name,phone,email,status,last_call_at', 'user:id,name,phone_number', 'disposition:id,name'])
            ->when($request->user()?->role === 'agent', function ($query) use ($request) {
                $query->where(function ($visible) use ($request) {
                    $visible->where('user_id', $request->user()->id)
                        ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('assigned_user_id', $request->user()->id));
                });
            })
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('started_at', '<=', $request->to))
            ->latest('started_at')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $calls]);
    }

    public function userCallLogs(Request $request, User $user)
    {
        abort_if($request->user()?->role === 'agent' && $request->user()->id !== $user->id, 403);

        $calls = CallLog::where('user_id', $user->id)
            ->with(['lead:id,campaign_id,assigned_user_id,name,phone,email,status,last_call_at', 'campaign:id,name,status', 'disposition:id,name'])
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('started_at', '<=', $request->to))
            ->latest('started_at')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json(['status' => true, 'data' => $calls]);
    }


    private function canAccessCall($user, CallLog $call): bool
    {
        if (!$user) {
            return Auth::guard('admin')->check();
        }

        if ($user->role === 'subadmin') {
            return true;
        }

        if ($user->role === 'agent') {
            // An agent can ALWAYS access a call they initiated/made themselves
            if ((int) $call->user_id === (int) $user->id) {
                return true;
            }
        }

        $call->loadMissing('lead:id,assigned_user_id');

        return $call->lead ? $this->canAccessLead($user, $call->lead) : false;
    }
}
