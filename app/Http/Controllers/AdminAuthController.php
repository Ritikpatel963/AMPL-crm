<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin_panel.admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, false)) {
            $admin = Auth::guard('admin')->user();

            // ✅ Ensure role column is synced with Spatie role
            if ($admin->role && !$admin->hasRole($admin->role)) {
                $admin->assignRole($admin->role);
            }

            // ✅ Preload permissions into cache (Spatie’s recommended optimization)
            $admin->getPermissionsViaRoles();

            return redirect()->route('admin_panel.admin.index');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
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
            'email' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed', // confirmed means password_confirmation must match
        ]);

        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        return redirect()->route('admin_panel.admin.edit.profile')->with('success', 'Profile updated successfully.');
    }
}
