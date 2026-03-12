<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftSchedulingController extends Controller
{
    public function index()
    {
        $schedules = ShiftSchedule::with(['staff', 'shift'])->get();
        $shifts = Shift::where('is_active', true)->get();
        $staff = User::all();
        
        return response()->json([
            'schedules' => $schedules,
            'shifts' => $shifts,
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'schedule_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'nullable|in:scheduled,completed,cancelled,absent',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate combination
        $exists = ShiftSchedule::where('staff_id', $request->staff_id)
            ->where('shift_id', $request->shift_id)
            ->where('schedule_date', $request->schedule_date)
            ->exists();

        if ($exists) {
            $validator->errors()->add('schedule_date', 'A schedule already exists for this staff member, shift, and date combination.');
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule = ShiftSchedule::create($request->all());
        $schedule->load(['staff', 'shift']);

        return response()->json(['message' => 'Shift scheduled successfully', 'schedule' => $schedule], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = ShiftSchedule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'schedule_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'nullable|in:scheduled,completed,cancelled,absent',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate combination (excluding current record)
        $exists = ShiftSchedule::where('staff_id', $request->staff_id)
            ->where('shift_id', $request->shift_id)
            ->where('schedule_date', $request->schedule_date)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            $validator->errors()->add('schedule_date', 'A schedule already exists for this staff member, shift, and date combination.');
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule->update($request->all());
        $schedule->load(['staff', 'shift']);

        return response()->json(['message' => 'Shift schedule updated successfully', 'schedule' => $schedule]);
    }

    public function destroy($id)
    {
        $schedule = ShiftSchedule::findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Shift schedule deleted successfully']);
    }
}
