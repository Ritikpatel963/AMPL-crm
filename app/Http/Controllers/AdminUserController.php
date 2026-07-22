<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->whereIn('role', ['subadmin', 'agent'])
            ->with('location')
            ->latest()
            ->get();

        $locations = \App\Models\Location::where('is_active', true)->get();

        return view('admin_panel.users.index', compact('users', 'locations'));
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);

        $user = User::whereIn('role', ['subadmin', 'agent'])->findOrFail($request->id);
        $user->delete();

        return redirect()->back()->with('success', 'CRM user deleted successfully');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:30', 'unique:users,phone_number'],
            'password' => ['required', Password::min(4)],
            'role' => ['required', Rule::in(['subadmin', 'agent'])],
            'location_id' => ['nullable', 'exists:locations,id'],
        ]);

        $data['crm_status'] = 'active';
        $data['lead_assignment_enabled'] = true;
        $data['approval_status'] = 'approved';

        User::create($data);

        return redirect()->back()->with('success', 'CRM user created successfully');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);

        $user = User::whereIn('role', ['subadmin', 'agent'])->findOrFail($request->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($user)],
            'password' => ['nullable', Password::min(4)],
            'role' => ['required', Rule::in(['subadmin', 'agent'])],
            'location_id' => ['nullable', 'exists:locations,id'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'CRM user updated successfully');
    }
}
