<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskAssignmentController extends Controller
{
    public function index()
    {
        $tasks = TaskAssignment::with(['staff', 'room'])->get();
        $staff = User::all();
        $rooms = Room::all();
        
        return response()->json([
            'tasks' => $tasks,
            'staff' => $staff,
            'rooms' => $rooms
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'task_type' => 'required|in:housekeeping,room_service,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_date' => 'required|date',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'room_id' => 'nullable|exists:rooms,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = TaskAssignment::create($request->all());
        $task->load(['staff', 'room']);

        return response()->json(['message' => 'Task assigned successfully', 'task' => $task], 201);
    }

    public function update(Request $request, $id)
    {
        $task = TaskAssignment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'task_type' => 'required|in:housekeeping,room_service,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_date' => 'required|date',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'room_id' => 'nullable|exists:rooms,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->status === 'completed' && !$task->completed_at) {
            $request->merge(['completed_at' => now()]);
        }

        $task->update($request->all());
        $task->load(['staff', 'room']);

        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    public function destroy($id)
    {
        $task = TaskAssignment::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
