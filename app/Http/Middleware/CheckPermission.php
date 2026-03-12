<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Get user ID from session
        $userId = $request->session()->get('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        // Get user with role and permissions
        $user = User::with(['role.permissions'])->find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 401);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.'
            ], 403);
        }

        // Check permission
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        // Attach user to request for use in controllers
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
