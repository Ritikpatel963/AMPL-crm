<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\VendorOtp;
use App\Services\WatiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;


class AdminAuthController extends Controller
{
    public function __construct(private WatiService $wati) {}

    public function showLoginForm()
    {
        return view('admin_panel.admin.login');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if (!$this->isValidPhone($phone)) {
            return back()->withErrors(['phone' => 'Please enter a valid phone number.'])->withInput();
        }

        $admin = Admin::where('phone', $phone)->first();

        if (!$admin) {
            return back()->withErrors(['phone' => 'This phone number is not registered as an admin.'])->withInput();
        }

        $rateKey = 'admin-otp-attempts:' . $phone;
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $minutes = ceil(RateLimiter::availableIn($rateKey) / 60);
            return back()->withErrors(['phone' => "Too many OTP requests. Please try again in {$minutes} minute(s)."])->withInput();
        }

        $recentOtp = VendorOtp::where('phone_number', $phone)
            ->where('is_verified', false)
            ->where('created_at', '>=', now()->subSeconds(60))
            ->exists();

        if ($recentOtp) {
            session(['admin_login_phone' => $phone]);
            return back()->with('success', 'OTP already sent. Please wait 60 seconds before requesting again.');
        }

        RateLimiter::hit($rateKey, 3600);

        VendorOtp::where('phone_number', $phone)
            ->where('is_verified', false)
            ->delete();

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        VendorOtp::create([
            'phone_number' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
            'is_verified' => false,
        ]);

        if (!$this->wati->sendOtp($phone, $otp)) {
            return back()->withErrors(['phone' => 'Could not send OTP via WhatsApp. Please try again.'])->withInput();
        }

        session(['admin_login_phone' => $phone]);

        return back()->with('success', 'OTP sent to your WhatsApp number. It is valid for 10 minutes.');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if (!$this->isValidPhone($phone)) {
            return back()->withErrors(['phone' => 'Please enter a valid phone number.'])->withInput();
        }

        $admin = Admin::where('phone', $phone)->first();

        if (!$admin) {
            return back()->withErrors(['phone' => 'This phone number is not registered as an admin.'])->withInput();
        }

        $otpRecord = VendorOtp::where('phone_number', $phone)
            ->where('otp', $validated['otp'])
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please check and try again.'])->withInput();
        }

        if (now()->gt($otpRecord->expires_at)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.'])->withInput();
        }

        $otpRecord->update(['is_verified' => true]);
        session()->forget('admin_login_phone');

        Auth::guard('admin')->login($admin, true);
        $request->session()->regenerate();

        return redirect()->intended(route('admin_panel.admin.index'));
    }

    public function dashboard()
    {
        return view('admin_panel.index');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin_panel.admin.login');
    }
    public function editProfile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin_panel.admin.edit-profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if (!$this->isValidPhone($phone)) {
            return back()->withErrors(['phone' => 'Please enter a valid phone number.'])->withInput();
        }

        if (Admin::where('phone', $phone)->whereKeyNot($admin->id)->exists()) {
            return back()->withErrors(['phone' => 'This phone number is already registered as an admin.'])->withInput();
        }

        $admin->name = $validated['name'];

        if (!$admin->isMainAdmin()) {
            $admin->phone = $phone;
            $admin->email = $admin->phone . '@admin.local';
        }

        $admin->save();

        return redirect()->route('admin_panel.admin.edit.profile')->with('success', 'Profile updated successfully.');
    }

    public function admins()
    {
        $admins = Admin::orderByDesc('is_main_admin')->latest()->get();

        return view('admin_panel.admin.admins', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if (!$this->isValidPhone($phone)) {
            return back()->withErrors(['phone' => 'Please enter a valid phone number.'])->withInput();
        }

        if (Admin::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'This phone number is already registered as an admin.'])->withInput();
        }

        Admin::create([
            'name' => $validated['name'],
            'phone' => $phone,
            'email' => $phone . '@admin.local',
            'password' => Hash::make(Str::random(32)),
            'is_main_admin' => false,
        ]);

        return back()->with('success', 'Admin created successfully.');
    }

    public function updateAdmin(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        if (!$this->isValidPhone($phone)) {
            return back()->withErrors(['phone' => 'Please enter a valid phone number.'])->withInput();
        }

        if (Admin::where('phone', $phone)->whereKeyNot($admin->id)->exists()) {
            return back()->withErrors(['phone' => 'This phone number is already registered as an admin.'])->withInput();
        }

        $admin->name = $validated['name'];

        if (!$admin->isMainAdmin()) {
            $admin->phone = $phone;
            $admin->email = $admin->phone . '@admin.local';
        }

        $admin->save();

        return back()->with('success', 'Admin updated successfully.');
    }

    public function destroyAdmin(Admin $admin)
    {
        if ($admin->isMainAdmin()) {
            return back()->with('error', 'Main admin cannot be deleted.');
        }

        if (Auth::guard('admin')->id() === $admin->id) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        $admin->delete();

        return back()->with('success', 'Admin deleted successfully.');
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            return substr($phone, 2);
        }

        return $phone;
    }

    private function isValidPhone(string $phone): bool
    {
        return preg_match('/^\d{10,15}$/', $phone) === 1;
    }
}
