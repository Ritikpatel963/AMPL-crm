<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Show permission management page.
     */
    public function index()
    {
        $roles = Role::where('guard_name', 'admin')->get();

        // Group permissions by category
        $permissions = [
            'Users' => ['Manage Users', 'Add Users', 'Edit Users', 'Delete Users'],
            'KYC' => ['Manage KYC', 'Approve KYC', 'Reject KYC'],
            'Product' => ['Manage Products', 'Manage Categories', 'Add Products', 'Edit Products', 'Delete Products'],
            'Payments' => ['Manage Payments'],
            'Chat' => ['Manage Chats'],
            'Orders' => ['Manage Orders', 'Approve Orders', 'Reject Orders', 'Delete Orders'],
            'Stock' => ['Manage Stock', 'Add Stock', 'Edit Stock'],
        ];

        return view('admin_panel.permission.role_permission', compact('roles', 'permissions'));
    }

    /**
     * Store permissions and assign them to the selected role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
        ]);

        $role = Role::where('guard_name', 'admin')->findOrFail($request->role_id);

        // Ensure all permissions exist in DB
        $permissionNames = collect($request->permissions ?? []);
        foreach ($permissionNames as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'admin',
            ]);
        }

        // Sync permissions to role
        $role->syncPermissions($permissionNames);

        return redirect()->back()->with('success', 'Permissions updated successfully for ' . $role->name);
    }

    /**
     * Fetch role permissions via AJAX (optional for dynamic loading)
     */
    public function getRolePermissions($roleId)
    {
        $role = Role::where('guard_name', 'admin')->findOrFail($roleId);
        $permissions = $role->permissions->pluck('name');
        return response()->json($permissions);
    }
}
