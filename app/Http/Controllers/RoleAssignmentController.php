<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleAssignmentController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::all();
        
        return response()->json([
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function assignRole(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($userId);
        $user->update(['role_id' => $request->role_id]);
        $user->load('role');

        return response()->json(['message' => 'Role assigned successfully', 'user' => $user]);
    }
}
