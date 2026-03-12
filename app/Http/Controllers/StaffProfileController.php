<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffProfileController extends Controller
{
    public function index()
    {
        $profiles = StaffProfile::with(['user', 'role'])->get();
        return response()->json($profiles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => 'nullable|string|unique:staff_profiles,employee_id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile = StaffProfile::create($request->all());
        $profile->load(['user', 'role']);

        return response()->json(['message' => 'Staff profile created successfully', 'profile' => $profile], 201);
    }

    public function update(Request $request, $id)
    {
        $profile = StaffProfile::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => 'nullable|string|unique:staff_profiles,employee_id,' . $id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile->update($request->all());
        $profile->load(['user', 'role']);

        return response()->json(['message' => 'Staff profile updated successfully', 'profile' => $profile]);
    }

    public function destroy($id)
    {
        $profile = StaffProfile::findOrFail($id);
        $profile->delete();

        return response()->json(['message' => 'Staff profile deleted successfully']);
    }
}
