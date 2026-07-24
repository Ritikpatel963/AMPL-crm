<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    // ── Admin Web ────────────────────────────────────────────────────────────

    public function index()
    {
        $users = User::with('location')->whereIn('role', ['customer', 'agent', 'subadmin'])
            ->select('id', 'name', 'phone_number', 'role', 'location_id', 'approval_status', 'crm_status')
            ->orderBy('name')
            ->get();

        $locations = $users->pluck('location')->filter()->unique('id')->sortBy('name');

        $history = PushNotification::latest()->get();

        return view('admin_panel.notifications.index', compact('users', 'locations', 'history'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'body'   => 'required|string|max:1000',
            'target' => 'required|string',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $title  = $request->title;
        $body   = $request->body;
        $target = $request->target;

        // Store uploaded image — absolute URL needed for FCM image display
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('notifications', 'public');
            $imageUrl = rtrim(config('app.url'), '/') . Storage::url($path);
        }

        // Build human-readable label for history
        $targetLabel = 'All Users';
        if ($target !== 'all') {
            $targetIds = explode(',', $target);
            $count = count($targetIds);
            if ($count === 1) {
                $user = User::find($targetIds[0]);
                $targetLabel = $user ? "{$user->name} ({$user->role})" : "User #{$targetIds[0]}";
            } else {
                $targetLabel = "{$count} Selected Users";
            }
        }

        // Gather tokens
        $query = FcmToken::query();
        if ($target !== 'all') {
            $targetIds = explode(',', $target);
            $query->whereIn('user_id', $targetIds);
        }
        $tokens = $query->pluck('token')->toArray();

        [$success, $failure] = $tokens ? $this->sendFcm($title, $body, $imageUrl, $tokens) : [0, 0];

        PushNotification::create([
            'title'         => $title,
            'body'          => $body,
            'image_url'     => $imageUrl,
            'target'        => $target,
            'target_label'  => $targetLabel,
            'sent_by'       => Auth::guard('admin')->id(),
            'success_count' => $success,
            'failure_count' => $failure,
        ]);

        $msg = $tokens
            ? "Sent to {$success} device(s). Failures: {$failure}."
            : 'No registered devices found for the selected target.';

        return back()->with('success', $msg);
    }

    // ── API (called by Android app) ──────────────────────────────────────────

    public function registerToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        // ponytail: upsert — one token per user, replace if changed
        FcmToken::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['token'   => $request->token]
        );

        return response()->json(['status' => true]);
    }

    // ── FCM HTTP v1 helper ───────────────────────────────────────────────────

    private function sendFcm(string $title, string $body, ?string $imageUrl, array $tokens): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('FCM: could not obtain access token');
            return [0, count($tokens)];
        }

        $projectId = $this->projectId();
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $success = 0;
        $failure = 0;

        // ponytail: one message per token; fine for admin-volume sends
        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token'        => $token,
                    'data'         => array_filter([
                        'title'     => $title,
                        'body'      => $body,
                        'image_url' => $imageUrl ?? '',
                    ]),
                    'android' => ['priority' => 'high'],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $success++;
            } else {
                $failure++;
                Log::warning('FCM send failed', ['token' => substr($token, 0, 10), 'response' => $response->body()]);
            }
        }

        return [$success, $failure];
    }

    private function getAccessToken(): ?string
    {
        $credentialsPath = config('services.firebase.credentials');
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            Log::error('FCM: credentials file missing at ' . $credentialsPath);
            return null;
        }

        try {
            $credentials = json_decode(file_get_contents($credentialsPath), true);
            $now = time();
            $expiry = $now + 3600;

            $header  = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $expiry,
            ]));

            $signingInput = "{$header}.{$payload}";
            openssl_sign($signingInput, $signature, $credentials['private_key'], 'SHA256');
            $jwt = $signingInput . '.' . base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $response->json('access_token');
        } catch (\Exception $e) {
            Log::error('FCM JWT error: ' . $e->getMessage());
            return null;
        }
    }

    private function projectId(): string
    {
        $credentialsPath = config('services.firebase.credentials');
        $credentials = json_decode(file_get_contents($credentialsPath), true);
        return $credentials['project_id'] ?? '';
    }
}
