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

    /*
    |--------------------------------------------------------------------------
    | STEP 1 — Send Login OTP
    | POST /api/login/send-otp
    |--------------------------------------------------------------------------
    */
    public function sendLoginOtp(Request $request)
    {
        Log::info('[LOGIN-SEND-OTP] ═══════════ New Request ═══════════');
        Log::info('[LOGIN-SEND-OTP] Raw input', [
            'raw_phone' => $request->phone_number,
        ]);

        // ── Validate ──────────────────────────────────────────────────────────
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        // ── Normalize ─────────────────────────────────────────────────────────
        $phone = $this->normalizePhone($request->phone_number ?? '');
        Log::info('[LOGIN-SEND-OTP] Phone normalized', [
            'raw'        => $request->phone_number,
            'normalized' => $phone,
        ]);

        try {

            // ── Check user exists ─────────────────────────────────────────────
            $user = $this->findLoginUserByPhone($request->phone_number ?? '', $phone);
            Log::info('[LOGIN-SEND-OTP] User lookup result', [
                'phone'           => $phone,
                'user_found'      => !is_null($user),
                'user_id'         => $user?->id,
                'role'            => $user?->role,
                'approval_status' => $user?->approval_status,
            ]);

            if (!$user) {
                Log::info('[LOGIN-SEND-OTP] 🆕 Auto-registering new customer', ['phone' => $phone]);
                
                $user = User::create([
                    'phone_number'    => $phone,
                    'email'           => 'customer_' . $phone . '@amplchat.local',
                    'role'            => 'customer',
                    'name'            => 'Customer',
                    'password'        => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'approval_status' => 'approved',
                ]);
            }

            // ── Block unapproved vendors early (no point sending OTP) ─────────
            if ($user->role === 'vendor' && $user->approval_status !== 'approved') {
                Log::warning('[LOGIN-SEND-OTP] ⛔ Vendor not approved — blocking OTP send', [
                    'user_id'         => $user->id,
                    'approval_status' => $user->approval_status,
                ]);
                return response()->json([
                    'status'          => false,
                    'message'         => 'Your account is ' . $user->approval_status . '. You cannot login yet.',
                    'approval_status' => $user->approval_status,
                ], 403);
            }
            
             // ── Rate limit — max 3 OTP sends per hour     👈 ADD HERE
            $rateError = $this->checkOtpRateLimit($phone);
            if ($rateError) {
                return response()->json($rateError, 429);
            }



            // ── Throttle check ────────────────────────────────────────────────
            $recentOtp = VendorOtp::where('phone_number', $phone)
                ->where('is_verified', false)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();

            Log::info('[LOGIN-SEND-OTP] Throttle check', ['throttled' => $recentOtp]);

            if ($recentOtp) {
                Log::warning('[LOGIN-SEND-OTP] ⛔ Throttled — OTP sent within last 60 seconds');
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP already sent. Please wait 60 seconds before requesting again.',
                ], 429);
            }

            // ── Generate OTP ──────────────────────────────────────────────────
            // Delete old unverified OTPs first
            $deleted = VendorOtp::where('phone_number', $phone)
                ->where('is_verified', false)
                ->delete();
            Log::info('[LOGIN-SEND-OTP] Old unverified OTPs deleted', ['count' => $deleted]);

            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            Log::info('[LOGIN-SEND-OTP] OTP generated');

            VendorOtp::create([
                'phone_number' => $phone,
                'otp'          => Hash::make($otp),
                'expires_at'   => now()->addMinutes(10),
                'is_verified'  => false,
            ]);

            // ── Confirm saved in DB ───────────────────────────────────────────
            $savedRecord = VendorOtp::where('phone_number', $phone)
                ->where('is_verified', false)
                ->latest()
                ->first();

            Log::info('[LOGIN-SEND-OTP] OTP saved to DB — verification', [
                'saved_phone'   => $savedRecord?->phone_number,
                'expires_at'    => $savedRecord?->expires_at,
                'record_exists' => !is_null($savedRecord),
            ]);

            // ── Send via WATI ─────────────────────────────────────────────────
            Log::info('[LOGIN-SEND-OTP] Sending OTP via WATI to: ' . $phone);
            $sent = $this->wati->sendOtp($phone, $otp);
            Log::info('[LOGIN-SEND-OTP] WATI send result', ['sent' => $sent]);

            if (!$sent) {
                Log::error('[LOGIN-SEND-OTP] ❌ WATI failed to send OTP', ['phone' => $phone]);
                return response()->json([
                    'status'  => false,
                    'message' => 'Could not send OTP via WhatsApp. Please try again.',
                ], 500);
            }

            Log::info('[LOGIN-SEND-OTP] ✅ OTP sent successfully', ['phone' => $phone]);

            return response()->json([
                'status'  => true,
                'message' => 'OTP sent to your WhatsApp number. It is valid for 10 minutes.',
                'phone'   => $phone,
            ], 200);

        } catch (\Exception $e) {
            Log::error('[LOGIN-SEND-OTP] ❌ EXCEPTION CAUGHT', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2 — Verify OTP + Login
    | POST /api/login
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        Log::info('[LOGIN] ═══════════ New Request ═══════════');
        Log::info('[LOGIN] Raw input received', [
            'raw_phone' => $request->phone_number,
            'otp'       => $request->otp,
        ]);

        // ── Validate ──────────────────────────────────────────────────────────
        $request->validate([
            'phone_number' => 'required|string',
            'otp'          => 'required|string|size:6',
        ]);

        // ── Normalize ─────────────────────────────────────────────────────────
        $phone = $this->normalizePhone($request->phone_number ?? '');
        Log::info('[LOGIN] Phone normalized', [
            'raw'        => $request->phone_number,
            'normalized' => $phone,
        ]);

        try {

            // ── OTP DB Diagnostics ────────────────────────────────────────────
            $allOtps = VendorOtp::where('phone_number', $phone)
                ->orderByDesc('created_at')
                ->get(['phone_number', 'is_verified', 'expires_at', 'created_at'])
                ->toArray();

            Log::info('[LOGIN] All OTP records for phone', [
                'phone'   => $phone,
                'count'   => count($allOtps),
                'records' => $allOtps,
            ]);

            if (empty($allOtps)) {
                Log::warning('[LOGIN] ⚠ NO OTP records at all for this phone — possible phone mismatch between sendLoginOtp and login steps');
            }

            // ── OTP Verification ──────────────────────────────────────────────
            Log::info('[LOGIN] Searching for matching OTP', [
                'phone' => $phone,
            ]);

            $otpRecord = VendorOtp::where('phone_number', $phone)
                ->where('is_verified', false)   // only unused OTPs
                ->latest()
                ->first();

            if ($otpRecord && !Hash::check($request->otp, $otpRecord->otp)) {
                $otpRecord = null; // simulate not found to prevent leaking existence
            }

            Log::info('[LOGIN] OTP record lookup result', [
                'found'        => !is_null($otpRecord),
                'record_phone' => $otpRecord?->phone_number,
                'is_verified'  => $otpRecord?->is_verified,
                'expires_at'   => $otpRecord?->expires_at,
                'now'          => now()->toDateTimeString(),
            ]);

            if (!$otpRecord) {
                Log::warning('[LOGIN] ❌ OTP record not found', [
                    'searched_phone' => $phone,
                    'hint'           => 'Check all OTP records above — if count is 0, phone mismatch. If count > 0, OTP value is wrong or already used.',
                ]);
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid OTP. Please check and try again.',
                ], 401);
            }

            // ── Expiry check ──────────────────────────────────────────────────
            if (now()->gt($otpRecord->expires_at)) {
                Log::warning('[LOGIN] ❌ OTP expired', [
                    'expires_at' => $otpRecord->expires_at,
                    'now'        => now()->toDateTimeString(),
                ]);
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP has expired. Please request a new one.',
                ], 401);
            }

            Log::info('[LOGIN] ✅ OTP valid — marking as used');
            $otpRecord->update(['is_verified' => true]);

            // ── Load user ─────────────────────────────────────────────────────
            $user = $this->findLoginUserByPhone($request->phone_number ?? '', $phone);
            Log::info('[LOGIN] User lookup after OTP verify', [
                'user_found'      => !is_null($user),
                'user_id'         => $user?->id,
                'role'            => $user?->role,
                'approval_status' => $user?->approval_status,
            ]);

            if (!$user) {
                // Extremely rare — user was deleted between sendOtp and login
                Log::error('[LOGIN] ❌ User not found after OTP verified — user may have been deleted', [
                    'phone' => $phone,
                ]);
                return response()->json([
                    'status'  => false,
                    'message' => 'Account not found. Please contact support.',
                ], 404);
            }

            // ── Vendor approval check ─────────────────────────────────────────
            if ($user->role === 'vendor' && $user->approval_status !== 'approved') {
                Log::warning('[LOGIN] ⛔ Vendor account not approved', [
                    'user_id'         => $user->id,
                    'approval_status' => $user->approval_status,
                ]);
                return response()->json([
                    'status'          => false,
                    'message'         => 'Your account is ' . $user->approval_status . '. Please wait for admin approval.',
                    'approval_status' => $user->approval_status,
                ], 403);
            }

            // ── Issue token ───────────────────────────────────────────────────
            // Revoke all old tokens before issuing a new one (prevents token accumulation)
            $user->tokens()->delete();
            Log::info('[LOGIN] Old tokens revoked for user_id: ' . $user->id);

            $token = $user->createToken('android')->plainTextToken;

            // ── Resolve assigned agent (mirrors old email-based AuthController) ─
            $agentId = optional($user->assignedAgent)->agent_id;
            Log::info('[LOGIN] Agent lookup', [
                'user_id'  => $user->id,
                'agent_id' => $agentId,
            ]);

            Log::info('[LOGIN] ✅ Login successful — token issued', [
                'user_id' => $user->id,
                'phone'   => $phone,
                'role'    => $user->role,
            ]);

            return response()->json([
                'status'          => true,
                'message'         => 'Login successful.',
                'token'           => $token,
                'user'            => $user,
                'approval_status' => $user->approval_status,
                'agent_id'        => $agentId,  // ← added: null if no agent assigned
            ], 200);

        } catch (\Exception $e) {
            Log::error('[LOGIN] ❌ EXCEPTION CAUGHT', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
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
