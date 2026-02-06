<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Services\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    protected PermissionRegistry $permissionRegistry;

    public function __construct(PermissionRegistry $permissionRegistry)
    {
        $this->permissionRegistry = $permissionRegistry;
    }

    /**
     * Display the merged Role & Permission page.
     */
    public function index(Request $request)
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        
        // Get permissions grouped by source (core vs plugins)
        $groupedPermissions = $this->permissionRegistry->getGroupedBySource();
        
        // Get all active permissions for matrix
        $permissions = Permission::active()
            ->orderBy('sort_order')
            ->orderBy('module')
            ->get()
            ->groupBy('module');
        
        $modules = Permission::getModulesGroupedBySource();
        
        // Get selected role (default to first role if none selected)
        $selectedRoleId = $request->get('role');
        $selectedRole = $selectedRoleId 
            ? Role::with('permissions')->find($selectedRoleId)
            : $roles->first();
            
        if ($selectedRole) {
            $selectedRole->load('permissions');
        }

        return view('admin.role-permission.index', compact(
            'roles',
            'modules',
            'permissions',
            'groupedPermissions',
            'selectedRole'
        ));
    }

    /**
     * Store a newly created role.
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $role = Role::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'role' => $role->load('permissions')->loadCount(['users', 'permissions'])
            ]);
        }

        return redirect()
            ->route('admin.role-permission.index', ['role' => $role->id])
            ->with('success', 'Role created successfully.');
    }

    /**
     * Update the specified role.
     */
    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $role->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'role' => $role->fresh()->load('permissions')->loadCount(['users', 'permissions'])
            ]);
        }

        return redirect()
            ->route('admin.role-permission.index', ['role' => $role->id])
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function deleteRole(Request $request, Role $role)
    {
        // Prevent deleting role if it has users
        if ($role->users()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role that has assigned users.'
                ], 422);
            }
            return redirect()
                ->route('admin.role-permission.index')
                ->with('error', 'Cannot delete role that has assigned users.');
        }

        $role->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.'
            ]);
        }

        return redirect()
            ->route('admin.role-permission.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Toggle a permission for a role (AJAX).
     */
    public function togglePermission(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $permissionId = $validated['permission_id'];
        
        // Check if permission is currently attached
        if ($role->permissions()->where('permissions.id', $permissionId)->exists()) {
            $role->permissions()->detach($permissionId);
            $attached = false;
        } else {
            $role->permissions()->attach($permissionId);
            $attached = true;
        }

        return response()->json([
            'success' => true,
            'attached' => $attached,
            'message' => $attached 
                ? 'Permission granted successfully.' 
                : 'Permission revoked successfully.'
        ]);
    }

    /**
     * Clone a role with all its permissions.
     */
    public function cloneRole(Request $request, Role $role)
    {
        $newRole = Role::create([
            'name' => $role->name . ' (Copy)',
            'slug' => Str::slug($role->name . ' Copy'),
            'description' => $role->description,
            'is_super_admin' => false, // Never clone super admin status
        ]);

        // Copy all permissions
        $newRole->permissions()->sync($role->permissions->pluck('id'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role cloned successfully.',
                'role' => $newRole->load('permissions')->loadCount(['users', 'permissions'])
            ]);
        }

        return redirect()
            ->route('admin.role-permission.index', ['role' => $newRole->id])
            ->with('success', 'Role cloned successfully.');
    }
}
