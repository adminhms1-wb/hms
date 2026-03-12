<?php

namespace App\Http\Controllers;

use App\Models\PerformanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerformanceLogController extends Controller
{
    public function index()
    {
        $logs = PerformanceLog::with(['staff', 'reviewer'])->get();
        $staff = User::all();
        
        return response()->json([
            'logs' => $logs,
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'reviewed_by' => 'nullable|exists:users,id',
            'review_date' => 'required|date',
            'category' => 'required|in:attendance,task_completion,customer_service,teamwork,punctuality,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'type' => 'required|in:positive,negative,neutral',
            'action_items' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $log = PerformanceLog::create($request->all());
        $log->load(['staff', 'reviewer']);

        return response()->json(['message' => 'Performance log created successfully', 'log' => $log], 201);
    }

    public function update(Request $request, $id)
    {
        $log = PerformanceLog::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:users,id',
            'reviewed_by' => 'nullable|exists:users,id',
            'review_date' => 'required|date',
            'category' => 'required|in:attendance,task_completion,customer_service,teamwork,punctuality,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'type' => 'required|in:positive,negative,neutral',
            'action_items' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $log->update($request->all());
        $log->load(['staff', 'reviewer']);

        return response()->json(['message' => 'Performance log updated successfully', 'log' => $log]);
    }

    public function destroy($id)
    {
        $log = PerformanceLog::findOrFail($id);
        $log->delete();

        return response()->json(['message' => 'Performance log deleted successfully']);
    }
}
