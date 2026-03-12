<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::with(['staff', 'shiftSchedule'])->get();
        $staff = User::all();
        
        return response()->json([
            'attendance' => $attendance,
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'shift_schedule_id' => 'nullable|exists:shift_schedules,id',
            'attendance_date' => 'nullable|date',
            'date' => 'nullable|date', // For backward compatibility
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,leave', // Include 'leave' for backward compatibility
            'check_in_type' => 'nullable|in:manual,biometric',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Map 'date' to 'attendance_date' if attendance_date is not provided
        if (empty($data['attendance_date']) && !empty($data['date'])) {
            $data['attendance_date'] = $data['date'];
        }
        
        // Map 'leave' to 'on_leave' for consistency
        if (isset($data['status']) && $data['status'] === 'leave') {
            $data['status'] = 'on_leave';
        }

        $attendance = Attendance::create($data);
        $attendance->load(['staff', 'shiftSchedule']);

        return response()->json(['message' => 'Attendance recorded successfully', 'attendance' => $attendance], 201);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'shift_schedule_id' => 'nullable|exists:shift_schedules,id',
            'attendance_date' => 'nullable|date',
            'date' => 'nullable|date', // For backward compatibility
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half_day,on_leave,leave', // Include 'leave' for backward compatibility
            'check_in_type' => 'nullable|in:manual,biometric',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Map 'date' to 'attendance_date' if attendance_date is not provided
        if (empty($data['attendance_date']) && !empty($data['date'])) {
            $data['attendance_date'] = $data['date'];
        }
        
        // Map 'leave' to 'on_leave' for consistency
        if (isset($data['status']) && $data['status'] === 'leave') {
            $data['status'] = 'on_leave';
        }
        
        // Remove 'date' from data if it exists to avoid conflicts
        unset($data['date']);

        $attendance->update($data);
        $attendance->load(['staff', 'shiftSchedule']);

        return response()->json(['message' => 'Attendance updated successfully', 'attendance' => $attendance]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json(['message' => 'Attendance record deleted successfully']);
    }
}
