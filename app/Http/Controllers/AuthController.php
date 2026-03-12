<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email
        $user = User::with(['role.permissions'])->where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.'
            ], 403);
        }

        // Restrict Super Admin from using normal login form
        if ($user->role && $user->role->slug === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin users must use the Super Admin login page. Please use the Super Admin login form.'
            ], 403);
        }

        // Prepare user data for response
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
            'role' => $user->role ? $user->role->slug : null,
            'roleName' => $user->role ? $user->role->name : 'No Role',
            'permissions' => $user->role && $user->role->permissions 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : []
        ];

        // Store user in session
        $request->session()->put('user', $userData);
        $request->session()->put('user_id', $user->id);

        // Set session lifetime based on remember me
        if ($request->remember) {
            $request->session()->put('remember', true);
            config(['session.lifetime' => 43200]); // 30 days
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $userData
        ], 200);
    }

    /**
     * Handle Super Admin login
     */
    public function superAdminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email
        $user = User::with(['role.permissions'])->where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Restrict non-Super Admin users from using Super Admin login form
        if (!$user->role || $user->role->slug !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'This login form is only for Super Admin users. Please use the regular staff login form.'
            ], 403);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.'
            ], 403);
        }

        // Prepare user data for response
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
            'role' => $user->role ? $user->role->slug : 'super_admin',
            'roleName' => $user->role ? $user->role->name : 'Super Admin (Hotel Owner)',
            'permissions' => $user->role && $user->role->permissions 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : []
        ];

        // Store user in session
        $request->session()->put('user', $userData);
        $request->session()->put('user_id', $user->id);

        // Set session lifetime based on remember me
        if ($request->remember) {
            $request->session()->put('remember', true);
            config(['session.lifetime' => 43200]); // 30 days
        }

        return response()->json([
            'success' => true,
            'message' => 'Super Admin login successful',
            'user' => $userData
        ], 200);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        $request->session()->flush();
        
        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Check if user is authenticated
     */
    public function authCheck(Request $request)
    {
        if ($request->session()->has('user')) {
            $user = $request->session()->get('user');
            return response()->json([
                'success' => true,
                'authenticated' => true,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'success' => false,
            'authenticated' => false,
            'message' => 'Not authenticated'
        ], 401);
    }
}
