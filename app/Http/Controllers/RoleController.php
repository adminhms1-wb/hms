<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'view_roles');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $roles = Role::withCount('users')->with('permissions')->get()
            ->filter(function($role) {
                return $role != null && $role->id != null;
            })
            ->map(function($role) {
                // Filter null permissions
                if ($role->permissions) {
                    $role->permissions = $role->permissions->filter(function($permission) {
                        return $permission != null && $permission->id != null;
                    })->values();
                }
                return $role;
            })
            ->values();
        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'create_roles');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $role = Role::create($validated);
        
        if (!empty($permissions)) {
            $role->permissions()->sync($permissions);
        }

        $role->load('permissions');

        return response()->json($role, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['users', 'permissions']);
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Check permission - need manage_role_permissions if updating permissions
        if ($request->has('permissions')) {
            $permissionCheck = $this->checkPermission($request, 'manage_role_permissions');
        } else {
            $permissionCheck = $this->checkPermission($request, 'edit_roles');
        }
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'sometimes|required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (isset($validated['permissions'])) {
            $permissions = $validated['permissions'];
            unset($validated['permissions']);
            $role->permissions()->sync($permissions);
        }

        if (!empty($validated)) {
            $role->update($validated);
        }

        $role->load('permissions');

        return response()->json($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Role $role)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'delete_roles');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully'], 200);
    }
}

