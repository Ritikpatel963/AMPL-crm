<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = AdminUser::latest()->get();
        return view('admin_panel.users.index', compact('users'));
    }
// delete
    public function destroy(Request $request)
{
    $user = AdminUser::findOrFail($request->id);
    $user->delete();

    return redirect()->back()->with('success', 'User Deleted Successfully');
}


    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:adminusers,email',
            'username'  => 'required|unique:adminusers,username',
            'password'  => 'required|min:4',
            'role'      => 'required'
        ]);

        AdminUser::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'status'    => 1,
        ]);

        return redirect()->back()->with('success', 'User Created Successfully');
    }

    // ✅ ADD THIS UPDATE FUNCTION BELOW
    public function update(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'email'     => 'required|email|unique:adminusers,email,' . $request->id,
            'username'  => 'required|unique:adminusers,username,' . $request->id,
            'role'      => 'required'
        ]);

        $user = AdminUser::findOrFail($request->id);

        // Update main fields
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->username = $request->username;
        $user->role     = $request->role;

        // ✅ If password field is filled, update password
        if ($request->password) {
            $request->validate([
                'password' => 'min:4'
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'User Updated Successfully');
    }
}