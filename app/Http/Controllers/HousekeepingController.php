<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingTask;
use App\Models\Room;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    /**
     * Display a listing of housekeeping tasks.
     */
    public function index(Request $request)
    {
        $query = HousekeepingTask::with(['room.roomType', 'staff']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filter by room
        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by staff
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by task type
        if ($request->has('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        $tasks = $query->orderBy('date', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->get();

        return response()->json(['data' => $tasks]);
    }

    /**
     * Store a newly created housekeeping task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'staff_id' => 'nullable|exists:users,id',
            'task_type' => 'required|string|in:cleaning,inspection,linen_change,maintenance,deep_cleaning,turn_down_service',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        $task = HousekeepingTask::create($validated);
        $task->load(['room.roomType', 'staff']);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping task created successfully',
            'data' => $task
        ], 201);
    }

    /**
     * Display the specified housekeeping task.
     */
    public function show(HousekeepingTask $housekeepingTask)
    {
        $housekeepingTask->load(['room.roomType', 'staff']);
        return response()->json(['data' => $housekeepingTask]);
    }

    /**
     * Update the specified housekeeping task.
     */
    public function update(Request $request, HousekeepingTask $housekeepingTask)
    {
        $validated = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'staff_id' => 'nullable|exists:users,id',
            'task_type' => 'sometimes|string|in:cleaning,inspection,linen_change,maintenance,deep_cleaning,turn_down_service',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'date' => 'sometimes|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update timestamps based on status
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_progress' && $housekeepingTask->status !== 'in_progress') {
                $validated['started_at'] = now();
            }
            if ($validated['status'] === 'completed' && $housekeepingTask->status !== 'completed') {
                $validated['completed_at'] = now();
            }
        }

        $housekeepingTask->update($validated);
        $housekeepingTask->load(['room.roomType', 'staff']);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping task updated successfully',
            'data' => $housekeepingTask
        ]);
    }

    /**
     * Remove the specified housekeeping task.
     */
    public function destroy(HousekeepingTask $housekeepingTask)
    {
        $housekeepingTask->delete();
        return response()->json([
            'success' => true,
            'message' => 'Housekeeping task deleted successfully'
        ]);
    }

    /**
     * Get rooms that need housekeeping.
     */
    public function getRoomsNeedingCleaning(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $rooms = Room::with(['roomType', 'reservations' => function($query) use ($date) {
            $query->whereDate('check_out_date', $date)
                  ->whereIn('status', ['checked_out', 'completed']);
        }])
        ->whereIn('status', ['checked_out', 'under_cleaning', 'maintenance'])
        ->get();

        return response()->json(['data' => $rooms]);
    }
}
