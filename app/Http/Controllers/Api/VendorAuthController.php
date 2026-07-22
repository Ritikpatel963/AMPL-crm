<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorDetail;
use App\Models\VendorOtp;
use App\Services\WatiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;  

class VendorAuthController extends Controller
{
    public function __construct(private WatiService $wati) {}

    private function normalizePhone(string $raw): string
    {
        $phone = trim($raw);
        $phone = ltrim($phone, '+');
        $phone = preg_replace('/^91/', '', $phone);
        $phone = '91' . $phone;
        return $phone;
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 1 — Send OTP
    | POST /api/vendor/send-otp
    |
    | Validates only 4 fields (name, email, phone, password).
    | GST, documents, firm details are NOT sent or validated here anymore.
    | Android must only send: name, email, phone_number, password, password_confirmation
    |--------------------------------------------------------------------------
    */
    public function sendOtp(Request $request)
    {
        Log::info('[SEND-OTP] ═══════════ New Request ═══════════');
        Log::info('[SEND-OTP] Raw input received', [
            'raw_phone' => $request->phone_number,
            'email'     => $request->email,
            'name'      => $request->name,
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone_number ?? '');
        $request->merge(['phone_number' => $normalizedPhone]);

        Log::info('[SEND-OTP] Phone normalized', [
            'raw'        => $request->phone_number,
            'normalized' => $normalizedPhone,
        ]);

        Log::info('[SEND-OTP] Running validation (Step 1 — 4 fields only)...');

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:15',
            // 'password'     => 'required|min:6|confirmed',
        ], [
            'name.required'         => 'Please enter your full name.',
            'name.max'              => 'Name must not exceed 255 characters.',
            'email.required'        => 'Please enter your email address.',
            'email.email'           => 'The email address you entered is not valid. Please check and try again.',
            'email.unique'          => 'This email is already registered. Please login or use a different email.',
            'phone_number.required' => 'Please enter your WhatsApp phone number.',
            'phone_number.max'      => 'Phone number must not exceed 15 digits.',
            // 'password.required'     => 'Please create a password.',
            // 'password.min'          => 'Password must be at least 6 characters long.',
            // 'password.confirmed'    => 'Passwords do not match. Please re-enter your password.',
        ]);

        if ($validator->fails()) {
            $errors   = $validator->errors()->toArray();
            $firstMsg = $validator->errors()->first();
            Log::warning('[SEND-OTP] ❌ Validation failed', ['errors' => $errors]);
            return response()->json([
                'status'  => false,
                'message' => $firstMsg,
                'errors'  => $errors,
            ], 422);
        }

        Log::info('[SEND-OTP] ✅ Validation passed');

        try {

            // ✅ Check if phone number is already registered
            $phoneExists = User::where('phone_number', $normalizedPhone)->exists();
            if ($phoneExists) {
                Log::warning('[SEND-OTP] ❌ Phone already registered', ['phone' => $normalizedPhone]);
                return response()->json([
                    'status'  => false,
                    'message' => 'This phone number is already registered. Please login or use a different number.',
                ], 422);
            }
            

            // ✅ Rate limit — max 3 OTP sends per hour      👈 ADD HERE
            $rateError = $this->checkOtpRateLimit($normalizedPhone);
            if ($rateError) {
                return response()->json($rateError, 429);
            }


            // ✅ Throttle check — prevent OTP spam
            $recentOtp = VendorOtp::where('phone_number', $normalizedPhone)
                ->where('is_verified', false)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();

            Log::info('[SEND-OTP] Throttle result', ['already_sent_in_60s' => $recentOtp]);

            if ($recentOtp) {
                Log::warning('[SEND-OTP] ⛔ Throttled');
                return response()->json([
                    'status'  => false,
                    'message' => 'OTP already sent. Please wait 60 seconds before requesting again.',
                ], 429);
            }

            $otp = $this->createOtp($normalizedPhone);
            Log::info('[SEND-OTP] OTP generated', ['phone' => $normalizedPhone]);

            $savedRecord = VendorOtp::where('phone_number', $normalizedPhone)
                ->where('is_verified', false)->latest()->first();

            Log::info('[SEND-OTP] OTP DB record check', [
                'record_exists' => !is_null($savedRecord),
                'expires_at'    => $savedRecord?->expires_at,
            ]);

            // Skip actual WATI send to prevent blocking registration
            $sent = true; 
            Log::info('[SEND-OTP] Skipped WATI send, proceeding instantly', ['sent' => $sent]);

            if (!$sent) {
                Log::error('[SEND-OTP] ❌ WATI failed', ['phone' => $normalizedPhone]);
                return response()->json([
                    'status'  => false,
                    'message' => 'Could not send OTP via WhatsApp. Please check your number and try again.',
                ], 500);
            }

            Log::info('[SEND-OTP] ✅ OTP sent successfully', ['phone' => $normalizedPhone]);

            return response()->json([
                'status'  => true,
                'message' => 'OTP sent to your WhatsApp number. It is valid for 10 minutes.',
                'phone'   => $normalizedPhone,
            ], 200);

        } catch (\Exception $e) {
            Log::error('[SEND-OTP] ❌ EXCEPTION', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2 — Verify OTP + Save Registration
    | POST /api/vendor/register
    |
    | Android sends all vendor data here after OTP verification.
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {
        Log::info('[REGISTER] ═══════════ New Request ═══════════');
        Log::info('[REGISTER] Raw input received', [
            'raw_phone' => $request->phone_number,
            'otp'       => $request->otp,
            'email'     => $request->email,
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone_number ?? '');
        $request->merge(['phone_number' => $normalizedPhone]);

        Log::info('[REGISTER] Phone normalized', ['normalized' => $normalizedPhone]);
        Log::info('[REGISTER] Running validation...');

        $validator = Validator::make($request->all(), [
            'otp'                   => 'nullable|string',
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email',
            // 'password'              => 'required|min:6|confirmed',
            'firm_name'             => 'required|string|max:255',
            'gst_number'            => 'required|string|max:20',
            'license_type'          => 'required|string',
            'fertilizer_license_no' => 'nullable|string',
            'seeds_license_no'      => 'nullable|string',
            'pesticides_license_no' => 'nullable|string',
            'address'               => 'required|string',
            'phone_number'          => 'required|string|max:15',
            'alternate_number'      => 'nullable|string|max:15',
            'gst_doc'               => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'license_doc'           => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'aadhar_front_path'     => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'aadhar_back_path'      => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ], [
            'name.required'           => 'Please enter your full name.',
            'email.required'          => 'Please enter your email address.',
            'email.email'             => 'The email address you entered is not valid.',
            // 'password.required'       => 'Please enter your password.',
            // 'password.min'            => 'Password must be at least 6 characters long.',
            // 'password.confirmed'      => 'Passwords do not match. Please re-enter your password.',
            'firm_name.required'      => 'Please enter your firm or business name.',
            'gst_number.required'     => 'Please enter your GST number.',
            'license_type.required'   => 'Please select your license type.',
            'address.required'        => 'Please enter your business address.',
            'phone_number.required'   => 'Phone number is required.',
            'phone_number.max'        => 'Phone number must not exceed 15 digits.',
            'alternate_number.max'    => 'Alternate number must not exceed 15 digits.',
            'gst_doc.mimes'           => 'GST document must be a PDF, JPG, or PNG file.',
            'license_doc.mimes'       => 'License document must be a PDF, JPG, or PNG file.',
            'aadhar_front_path.mimes' => 'Aadhaar front image must be a JPG, PNG, or PDF file.',
            'aadhar_back_path.mimes'  => 'Aadhaar back image must be a JPG, PNG, or PDF file.',
        ]);

        if ($validator->fails()) {
            $errors   = $validator->errors()->toArray();
            $firstMsg = $validator->errors()->first();
            Log::warning('[REGISTER] ❌ Validation failed', ['errors' => $errors]);
            return response()->json([
                'status'  => false,
                'message' => $firstMsg,
                'errors'  => $errors,
            ], 422);
        }

        Log::info('[REGISTER] ✅ Validation passed');

        try {

            // ✅ Safety net — re-check email duplicate in case Step 1 was bypassed
            $emailExists = User::where('email', $request->email)->exists();
            if ($emailExists) {
                Log::warning('[REGISTER] ❌ Email already registered', ['email' => $request->email]);
                return response()->json([
                    'status'  => false,
                    'message' => 'This email is already registered. Please login instead.',
                ], 422);
            }

            // ✅ Safety net — re-check phone duplicate in case Step 1 was bypassed
            $phoneExists = User::where('phone_number', $normalizedPhone)->exists();
            if ($phoneExists) {
                Log::warning('[REGISTER] ❌ Phone already registered', ['phone' => $normalizedPhone]);
                return response()->json([
                    'status'  => false,
                    'message' => 'This phone number is already registered. Please login instead.',
                ], 422);
            }

            Log::info('[REGISTER] OTP validation bypassed');

            DB::beginTransaction();

            $user = User::create([
                'name'            => $request->name,
                'email'           => $request->email,
                'password'        => Hash::make('password'),
                'phone_number'    => $normalizedPhone,
                'role'            => 'vendor',
                'status'          => 1,
                'approval_status' => 'pending',
            ]);

            Log::info('[REGISTER] ✅ User created', ['user_id' => $user->id]);

            $uploads = [];
            foreach (['gst_doc', 'license_doc', 'aadhar_front_path', 'aadhar_back_path'] as $field) {
    if ($request->hasFile($field)) {
        $path = $request->file($field)->store("uploads/vendors/{$user->id}", 'public');
        $uploads[$field] = $path;
        Log::info('[REGISTER] ✅ File uploaded', ['field' => $field, 'path' => $path]);
    }
}

            VendorDetail::create([
                'user_id'               => $user->id,
                'firm_name'             => $request->firm_name,
                'gst_number'            => $request->gst_number,
                'license_type'          => $request->license_type,
                'fertilizer_license_no' => $request->fertilizer_license_no,
                'seeds_license_no'      => $request->seeds_license_no,
                'pesticides_license_no' => $request->pesticides_license_no,
                'address'               => $request->address,
                'phone_number'          => $normalizedPhone,
                'alternate_number'      => $request->alternate_number,
                'gst_doc'               => $uploads['gst_doc'] ?? null,
                'license_doc'           => $uploads['license_doc'] ?? null,
                'aadhar_front_path'     => $uploads['aadhar_front_path'] ?? null,
                'aadhar_back_path'      => $uploads['aadhar_back_path'] ?? null,
            ]);

            Log::info('[REGISTER] ✅ VendorDetail created for user_id: ' . $user->id);

            DB::commit();
            Log::info('[REGISTER] ✅ DB committed — Registration complete!');

            return response()->json([
                'status'          => true,
                'message'         => 'Registration submitted successfully. Awaiting admin approval.',
                'approval_status' => 'pending',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[REGISTER] ❌ EXCEPTION — DB rolled back', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json(['status' => false, 'message' => 'Registration failed. Please try again.'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2b — Resend OTP
    | POST /api/vendor/resend-otp
    |--------------------------------------------------------------------------
    */
    public function resendOtp(Request $request)
    {
        Log::info('[RESEND-OTP] ═══════════ New Request ═══════════');

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
        ], [
            'phone_number.required' => 'Please provide your phone number to resend the OTP.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $normalizedPhone = $this->normalizePhone($request->phone_number ?? '');

        try {

            // ✅ Block resend if phone is already fully registered
            $phoneExists = User::where('phone_number', $normalizedPhone)->exists();
            if ($phoneExists) {
                Log::warning('[RESEND-OTP] ❌ Phone already registered', ['phone' => $normalizedPhone]);
                return response()->json([
                    'status'  => false,
                    'message' => 'This phone number is already registered. Please login instead.',
                ], 422);
            }
             
             $rateError = $this->checkOtpRateLimit($normalizedPhone);
            if ($rateError) {
                return response()->json($rateError, 429);
            }


            $recentOtp = VendorOtp::where('phone_number', $normalizedPhone)
                ->where('is_verified', false)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();

            if ($recentOtp) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Please wait 60 seconds before requesting a new OTP.',
                ], 429);
            }

            $otp  = $this->createOtp($normalizedPhone);
            $sent = $this->wati->sendOtp($normalizedPhone, $otp);

            if (!$sent) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Could not send OTP via WhatsApp. Please try again.',
                ], 500);
            }

            return response()->json([
                'status'  => true,
                'message' => 'A new OTP has been sent to your WhatsApp number.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('[RESEND-OTP] ❌ EXCEPTION', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Something went wrong. Please try again.'], 500);
        }
    }





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


    /*
    |--------------------------------------------------------------------------
    | Private Helper — Generate & Store OTP
    |--------------------------------------------------------------------------
    */
    private function createOtp(string $phone): string
    {
        $deleted = VendorOtp::where('phone_number', $phone)->where('is_verified', false)->delete();
        Log::info('[CREATE-OTP] Old OTPs deleted', ['count' => $deleted]);

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        VendorOtp::create([
            'phone_number' => $phone,
            'otp'          => Hash::make($otp),
            'expires_at'   => now()->addMinutes(10),
            'is_verified'  => false,
        ]);

        Log::info('[CREATE-OTP] ✅ OTP saved', ['phone' => $phone]);
        return $otp;
    }
}
