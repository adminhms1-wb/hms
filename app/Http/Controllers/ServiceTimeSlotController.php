<?php

namespace App\Http\Controllers;

use App\Models\ServiceTimeSlot;
use Illuminate\Http\Request;

class ServiceTimeSlotController extends Controller
{
    /**
     * Display a listing of time slots for a service.
     */
    public function index(Request $request)
    {
        $query = ServiceTimeSlot::with('service');

        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $timeSlots = $query->orderBy('start_time')->get();
        return response()->json(['data' => $timeSlots]);
    }

    /**
     * Store a newly created time slot.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_available' => 'nullable|boolean',
            'max_bookings' => 'nullable|integer|min:1',
            'available_days' => 'nullable|array',
            'available_days.*' => 'integer|min:0|max:6',
        ]);

        $validated['is_available'] = $validated['is_available'] ?? true;
        $validated['max_bookings'] = $validated['max_bookings'] ?? 1;

        // Calculate duration if not provided
        if (!isset($validated['duration_minutes'])) {
            $start = \Carbon\Carbon::parse($validated['start_time']);
            $end = \Carbon\Carbon::parse($validated['end_time']);
            $validated['duration_minutes'] = $start->diffInMinutes($end);
        }

        $timeSlot = ServiceTimeSlot::create($validated);
        $timeSlot->load('service');

        return response()->json([
            'success' => true,
            'message' => 'Time slot created successfully',
            'data' => $timeSlot
        ], 201);
    }

    /**
     * Display the specified time slot.
     */
    public function show(ServiceTimeSlot $serviceTimeSlot)
    {
        $serviceTimeSlot->load('service');
        return response()->json(['data' => $serviceTimeSlot]);
    }

    /**
     * Update the specified time slot.
     */
    public function update(Request $request, ServiceTimeSlot $serviceTimeSlot)
    {
        $validated = $request->validate([
            'service_id' => 'sometimes|exists:services,id',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_available' => 'nullable|boolean',
            'max_bookings' => 'nullable|integer|min:1',
            'available_days' => 'nullable|array',
            'available_days.*' => 'integer|min:0|max:6',
        ]);

        // Recalculate duration if times changed
        if (isset($validated['start_time']) || isset($validated['end_time'])) {
            $start = \Carbon\Carbon::parse($validated['start_time'] ?? $serviceTimeSlot->start_time);
            $end = \Carbon\Carbon::parse($validated['end_time'] ?? $serviceTimeSlot->end_time);
            $validated['duration_minutes'] = $start->diffInMinutes($end);
        }

        $serviceTimeSlot->update($validated);
        $serviceTimeSlot->load('service');

        return response()->json([
            'success' => true,
            'message' => 'Time slot updated successfully',
            'data' => $serviceTimeSlot
        ]);
    }

    /**
     * Remove the specified time slot.
     */
    public function destroy(ServiceTimeSlot $serviceTimeSlot)
    {
        $serviceTimeSlot->delete();
        return response()->json([
            'success' => true,
            'message' => 'Time slot deleted successfully'
        ]);
    }
}
