<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Get authenticated user from session
     */
    protected function getAuthenticatedUser(Request $request): ?User
    {
        // Get user ID from session
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return null;
        }

        // Get user with role and permissions
        return User::with(['role.permissions'])->find($userId);
    }

    /**
     * Check if user has permission, return error response if not
     * Only checks if user is authenticated - if no user, allows access (for backward compatibility)
     */
    protected function checkPermission(Request $request, string $permission)
    {
        $user = $this->getAuthenticatedUser($request);

        // If no user found, allow access (for backward compatibility with existing code)
        // This means endpoints work without auth, but can be protected if needed
        if (!$user) {
            return null; // Allow access if no authentication
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.'
            ], 403);
        }

        // Super Admin always has all permissions - bypass check
        if ($user->role && $user->role->slug === 'super_admin') {
            return null; // Permission granted for super admin
        }

        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        return null; // Permission granted
    }
}
