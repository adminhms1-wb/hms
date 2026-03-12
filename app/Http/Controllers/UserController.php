<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;

class UserController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'view_users');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $query = User::with('role');

        // Filter by role if provided
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);
        
        // Filter out null entries from the data
        $users->getCollection()->transform(function($user) {
            if ($user == null || $user->id == null) {
                return null;
            }
            return $user;
        });
        
        $users->setCollection($users->getCollection()->filter(function($user) {
            return $user != null;
        }));

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'create_users');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'status' => 'nullable|in:active,inactive',
            'mobile_number' => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $validated['status'] ?? 'active';

        $user = User::create($validated);
        $user->load('role');

        // Log activity
        $this->logActivity('create', 'User', $user->id, "User {$user->name} created", 'Users');

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('role');
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'edit_users');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|required|exists:roles,id',
            'status' => 'sometimes|nullable|in:active,inactive',
            'mobile_number' => 'nullable|string|max:20',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('role');

        // Log activity
        $this->logActivity('update', 'User', $user->id, "User {$user->name} updated", 'Users');

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        // Check permission
        $permissionCheck = $this->checkPermission($request, 'delete_users');
        if ($permissionCheck) {
            return $permissionCheck;
        }

        $userName = $user->name;
        $userId = $user->id;
        
        $user->delete();

        // Log activity
        $this->logActivity('delete', 'User', $userId, "User {$userName} deleted", 'Users');

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}

