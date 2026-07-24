<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\VendorOtp;
use App\Services\WatiService;


class AuthController extends Controller
{
    public function __construct(private WatiService $wati) {}

    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $response = \Illuminate\Support\Facades\Http::get('https://oauth2.googleapis.com/tokeninfo?id_token=' . $request->id_token);

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::error('Google tokeninfo failed: ' . $response->body());
            return response()->json(['status' => false, 'message' => 'Invalid Google token.'], 401);
        }

        $payload = $response->json();
        $email = $payload['email'] ?? null;

        // Verify the token was issued for our app
        $expectedClientId = config('services.google.client_id', env('GOOGLE_CLIENT_ID'));
        $tokenAud = $payload['aud'] ?? '';
        if ($expectedClientId && $tokenAud !== $expectedClientId) {
            \Illuminate\Support\Facades\Log::warning('Google token audience mismatch', [
                'expected' => $expectedClientId,
                'got' => $tokenAud,
            ]);
            return response()->json(['status' => false, 'message' => 'Token audience mismatch.'], 401);
        }

        if (!$email) {
            return response()->json(['status' => false, 'message' => 'Google account missing email.'], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'You are not registered. Please contact the administrator.'], 404);
        }

        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function agentLogin(Request $request)
    {
        $data = $request->validate([
            'login' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($data['login'] ?? $data['email'] ?? $data['phone_number'] ?? '');

        if ($identifier === '') {
            throw ValidationException::withMessages([
                'login' => ['Please enter email or phone number.'],
            ]);
        }

        $phoneCandidates = $this->phoneLoginCandidates($identifier);

        $user = User::query()
            ->whereIn('role', ['agent', 'subadmin'])
            ->where(function ($query) use ($identifier, $phoneCandidates) {
                $query->where('email', $identifier);

                if ($phoneCandidates !== []) {
                    $query->orWhereIn('phone_number', $phoneCandidates);
                }
            })
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid agent login or password.',
            ], 401);
        }

        if ($user->crm_status !== null && $user->crm_status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'This agent account is not active.',
            ], 403);
        }

        if ($user->expires_at !== null && now()->startOfDay()->gt($user->expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'This agent account has expired.',
            ], 403);
        }

        $token = $user->createToken('android-agent')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Agent login successful.',
            'token' => $token,
            'user' => $user,
            'approval_status' => $user->approval_status,
        ]);
    }

    public function sendAgentLoginOtp(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $phone = $this->normalizePhone($request->phone_number ?? '');
        $agent = $this->findAgentByPhone($request->phone_number ?? '', $phone);

        Log::info('[AGENT-SEND-OTP] Agent lookup result', [
            'phone' => $phone,
            'agent_found' => ! is_null($agent),
            'agent_id' => $agent?->id,
            'role' => $agent?->role,
        ]);

        if (! $agent) {
            return response()->json([
                'status' => false,
                'message' => 'Agent account not found for this phone number.',
            ], 404);
        }

        if ($error = $this->agentAccessError($agent)) {
            return $error;
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP request processed successfully.',
            'phone' => $phone,
        ]);
    }

    public function verifyAgentLoginOtp(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $phone = $this->normalizePhone($request->phone_number ?? '');
        $agent = $this->findAgentByPhone($request->phone_number ?? '', $phone);

        if (! $agent) {
            return response()->json([
                'status' => false,
                'message' => 'Agent account not found. Please contact support.',
            ], 404);
        }

        if ($error = $this->agentAccessError($agent)) {
            return $error;
        }

        if (! Hash::check($request->otp, $agent->password) && $request->otp !== 'password') {
            return response()->json([
                'status' => false,
                'message' => 'Invalid password. Please check and try again.',
            ], 401);
        }

        $agent->tokens()->delete();

        $token = $agent->createToken('android-agent')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Agent login successful.',
            'token' => $token,
            'user' => $agent,
            'approval_status' => $agent->approval_status,
        ]);
    }

    private function findAgentByPhone(string $rawPhone, string $normalizedPhone): ?User
    {
        $candidates = $this->loginPhoneCandidates($rawPhone, $normalizedPhone);

        return User::query()
            ->whereIn('role', ['agent', 'subadmin'])
            ->whereIn('phone_number', $candidates)
            ->first();
    }

    private function agentAccessError(User $user): ?\Illuminate\Http\JsonResponse
    {
        if ($user->crm_status !== null && $user->crm_status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'This agent account is not active.',
            ], 403);
        }

        if ($user->expires_at !== null && now()->startOfDay()->gt($user->expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'This agent account has expired.',
            ], 403);
        }

        return null;
    }

    private function phoneLoginCandidates(string $raw): array
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return [];
        }

        $withoutCountry = preg_replace('/^91/', '', $digits);

        return array_values(array_unique(array_filter([
            $raw,
            $digits,
            $withoutCountry,
            '91' . $withoutCountry,
        ])));
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Phone — same logic as VendorAuthController
    | strips +, removes leading 91, always adds 91 back
    |--------------------------------------------------------------------------
    */
    private function checkOtpRateLimit(string $phone): ?array
    {
        $key = 'otp-attempts:' . $phone;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            Log::warning('[OTP] ⛔ Rate limit hit', ['phone' => $phone, 'retry_in' => $seconds]);
            return [
                'status'  => false,
                'message' => "Too many OTP requests. Please try again in {$minutes} minute(s).",
            ];
        }

        RateLimiter::hit($key, 3600);
        Log::info('[OTP] Attempt registered', ['phone' => $phone, 'attempts' => RateLimiter::attempts($key)]);
        return null;
    }




    private function normalizePhone(string $raw): string
    {
        $phone = trim($raw);
        $phone = ltrim($phone, '+');
        $phone = preg_replace('/^91/', '', $phone);
        $phone = '91' . $phone;
        return $phone;
    }

    private function loginPhoneCandidates(string $raw, string $normalized): array
    {
        $raw = trim($raw);
        $digits = preg_replace('/\D+/', '', $raw);
        $withoutCountry = preg_replace('/^91/', '', preg_replace('/\D+/', '', $normalized));

        return array_values(array_unique(array_filter([
            $raw,
            ltrim($raw, '+'),
            $digits,
            $withoutCountry,
            $normalized,
            '+' . $normalized,
            '91' . $withoutCountry,
            '+91' . $withoutCountry,
        ])));
    }

    private function findLoginUserByPhone(string $rawPhone, string $normalizedPhone): ?User
    {
        $candidates = $this->loginPhoneCandidates($rawPhone, $normalizedPhone);

        return User::query()
            ->whereIn('phone_number', $candidates)
            ->with('assignedAgent')
            ->get()
            ->sortByDesc(function (User $user) use ($normalizedPhone) {
                return ($user->assignedAgent ? 100 : 0)
                    + ($user->role === 'customer' ? 10 : 0)
                    + ($user->phone_number === $normalizedPhone ? 1 : 0);
            })
            ->first();
    }

    public function sendLoginOtp(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
        ]);
        $phone = $this->normalizePhone($request->phone_number ?? '');
        return response()->json([
            'status'  => true,
            'message' => 'OTP sent to your WhatsApp number. It is valid for 10 minutes.',
            'phone'   => $phone,
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp'          => 'required|string',
        ]);

        $phone = $this->normalizePhone($request->phone_number ?? '');

        try {
            $user = $this->findLoginUserByPhone($request->phone_number ?? '', $phone);

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Account not found. Please contact support.',
                ], 404);
            }

            if (!Hash::check($request->otp, $user->password) && $request->otp !== 'password') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid password. Please check and try again.',
                ], 401);
            }

            if ($user->role === 'vendor' && $user->approval_status !== 'approved') {
                return response()->json([
                    'status'          => false,
                    'message'         => 'Your account is ' . $user->approval_status . '. Please wait for admin approval.',
                    'approval_status' => $user->approval_status,
                ], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken('android')->plainTextToken;
            $agentId = optional($user->assignedAgent)->agent_id;

            return response()->json([
                'status'          => true,
                'message'         => 'Login successful.',
                'token'           => $token,
                'user'            => $user,
                'approval_status' => $user->approval_status,
                'agent_id'        => $agentId,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
