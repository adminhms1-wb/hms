<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of maintenance requests.
     */
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with(['room.roomType', 'reportedBy', 'assignedTo']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $requests]);
    }

    /**
     * Store a newly created maintenance request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'issue' => 'required|string|max:2000',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = 'reported';
        $validated['reported_by'] = auth()->id();

        $maintenanceRequest = MaintenanceRequest::create($validated);
        $maintenanceRequest->load(['room.roomType', 'reportedBy', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request created successfully',
            'data' => $maintenanceRequest
        ], 201);
    }

    /**
     * Display the specified maintenance request.
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['room.roomType', 'reportedBy', 'assignedTo']);
        return response()->json(['data' => $maintenanceRequest]);
    }

    /**
     * Update the specified maintenance request.
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $validated = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'issue' => 'sometimes|string|max:2000',
            'status' => 'sometimes|in:reported,in_progress,resolved,cancelled',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'resolved' && $maintenanceRequest->status !== 'resolved') {
            $validated['resolved_at'] = now();
        }

        $maintenanceRequest->update($validated);
        $maintenanceRequest->load(['room.roomType', 'reportedBy', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request updated successfully',
            'data' => $maintenanceRequest
        ]);
    }

    /**
     * Remove the specified maintenance request.
     */
    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->delete();
        return response()->json([
            'success' => true,
            'message' => 'Maintenance request deleted successfully'
        ]);
    }
}
