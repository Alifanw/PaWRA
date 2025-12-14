<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoleController extends Controller
{
    private $availablePermissions = [
        'dashboard.view',
        'products.view', 'products.create', 'products.edit', 'products.delete',
        'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.delete',
        'ticket-sales.view', 'ticket-sales.create',
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'reports.view',
        'audit-logs.view',
    ];

    public function index(Request $request)
    {
        $query = Role::withCount('users');

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $roles = $query->latest()
            ->paginate(15)
            ->through(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'users_count' => $role->users_count,
                'created_at' => $role->created_at ? $role->created_at->format('Y-m-d H:i') : '-',
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
            'availablePermissions' => $this->availablePermissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            if (!empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $permission) {
                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_name' => $permission,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create role');
        }
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            // Delete old permissions
            RolePermission::where('role_id', $role->id)->delete();

            // Create new permissions
            if (!empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $permission) {
                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_name' => $permission,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update role');
        }
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete role that is assigned to users');
        }

        $role->rolePermissions()->delete();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:roles,id',
        ]);

        // Check which roles are assigned to users
        $rolesInUse = Role::whereIn('id', $validated['ids'])
            ->whereHas('users')
            ->pluck('name')
            ->toArray();

        if (!empty($rolesInUse)) {
            return response()->json([
                'error' => 'Cannot delete roles in use: ' . implode(', ', $rolesInUse)
            ], 422);
        }

        // Bulk delete role permissions and roles
        foreach ($validated['ids'] as $roleId) {
            RolePermission::where('role_id', $roleId)->delete();
        }

        $deletedCount = Role::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => "$deletedCount role(s) deleted successfully",
            'deleted_count' => $deletedCount
        ]);
    }

    public function permissions(Role $role)
    {
        $permissions = RolePermission::where('role_id', $role->id)
            ->pluck('permission_name')
            ->toArray();

        return response()->json([
            'permissions' => $permissions
        ]);
    }
}
