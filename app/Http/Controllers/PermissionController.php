<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'view_permissions');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $query = Permission::query();

        // Filter by module if provided
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $permissions = $query->get()
            ->filter(function($permission) {
                return $permission != null && $permission->id != null;
            })
            ->values();

        return response()->json($permissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'manage_system');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create($validated);

        return response()->json($permission, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Permission $permission)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'view_permissions');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $permission->load('roles');
        return response()->json($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'manage_system');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:permissions,slug,' . $permission->id,
            'module' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission->update($validated);

        return response()->json($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Permission $permission)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'manage_system');
        if ($permissionCheck) {
            return $permissionCheck;
        }
        
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully'], 200);
    }
}


