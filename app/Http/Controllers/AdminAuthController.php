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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, false)) {
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();

            // AdminUser records can carry Spatie roles; legacy Admin records do not.
            if (isset($admin->role) && method_exists($admin, 'hasRole') && !$admin->hasRole($admin->role)) {
                $admin->assignRole($admin->role);
            }

            if (method_exists($admin, 'getPermissionsViaRoles')) {
                $admin->getPermissionsViaRoles();
            }

            return redirect()->intended(route('admin_panel.admin.index'));
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
