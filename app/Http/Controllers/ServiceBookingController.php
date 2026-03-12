<?php

namespace App\Http\Controllers;

use App\Models\ServiceBooking;
use App\Models\ServiceTimeSlot;
use App\Models\ServiceUsageLog;
use App\Models\Service;
use App\Models\Guest;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceBookingController extends Controller
{
    /**
     * Display a listing of service bookings.
     */
    public function index(Request $request)
    {
        $query = ServiceBooking::with(['service', 'guest', 'room', 'reservation', 'timeSlot', 'assignedStaff']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by service
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filter by guest
        if ($request->has('guest_id')) {
            $query->where('guest_id', $request->guest_id);
        }

        $bookings = $query->orderBy('date', 'desc')
                         ->orderBy('start_time', 'desc')
                         ->get();

        return response()->json(['data' => $bookings]);
    }

    /**
     * Store a newly created service booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'nullable|exists:rooms,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'time_slot_id' => 'nullable|exists:service_time_slots,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'charge_type' => 'required|in:room,direct',
            'payment_status' => 'nullable|in:pending,paid,refunded',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Get service to calculate amount
        $service = Service::findOrFail($validated['service_id']);
        $validated['amount'] = $service->price;
        $validated['status'] = 'pending';
        $validated['payment_status'] = $validated['payment_status'] ?? 'pending';

        // If charge_type is room, reservation_id is required
        if ($validated['charge_type'] === 'room' && !$validated['reservation_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation ID is required when charging to room.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $booking = ServiceBooking::create($validated);

            // Create usage log
            ServiceUsageLog::create([
                'service_id' => $booking->service_id,
                'service_booking_id' => $booking->id,
                'guest_id' => $booking->guest_id,
                'room_id' => $booking->room_id,
                'reservation_id' => $booking->reservation_id,
                'usage_date' => $booking->date,
                'usage_time' => $booking->start_time ? now()->setTimeFromTimeString($booking->start_time) : now(),
                'amount' => $booking->amount,
                'charge_type' => $booking->charge_type,
                'payment_status' => $booking->payment_status,
                'processed_by' => $this->getAuthenticatedUser()?->id,
            ]);

            DB::commit();

            $booking->load(['service', 'guest', 'room', 'reservation', 'timeSlot', 'assignedStaff']);

            return response()->json([
                'success' => true,
                'message' => 'Service booking created successfully',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified service booking.
     */
    public function show(ServiceBooking $serviceBooking)
    {
        $serviceBooking->load(['service', 'guest', 'room', 'reservation', 'timeSlot', 'assignedStaff']);
        return response()->json(['data' => $serviceBooking]);
    }

    /**
     * Update the specified service booking.
     */
    public function update(Request $request, ServiceBooking $serviceBooking)
    {
        $validated = $request->validate([
            'service_id' => 'sometimes|exists:services,id',
            'guest_id' => 'sometimes|exists:guests,id',
            'room_id' => 'nullable|exists:rooms,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'time_slot_id' => 'nullable|exists:service_time_slots,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
            'date' => 'sometimes|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
            'charge_type' => 'sometimes|in:room,direct',
            'payment_status' => 'nullable|in:pending,paid,refunded',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // If service changed, update amount
        if (isset($validated['service_id']) && $validated['service_id'] != $serviceBooking->service_id) {
            $service = Service::findOrFail($validated['service_id']);
            $validated['amount'] = $service->price;
        }

        // If charge_type is room, reservation_id is required
        if (isset($validated['charge_type']) && $validated['charge_type'] === 'room' && !($validated['reservation_id'] ?? $serviceBooking->reservation_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation ID is required when charging to room.'
            ], 400);
        }

        // If payment status changed to paid, set paid_at
        if (isset($validated['payment_status']) && $validated['payment_status'] === 'paid' && $serviceBooking->payment_status !== 'paid') {
            $validated['paid_at'] = now();
        }

        $serviceBooking->update($validated);

        // Update usage log
        $usageLog = ServiceUsageLog::where('service_booking_id', $serviceBooking->id)->first();
        if ($usageLog) {
            $usageLog->update([
                'service_id' => $serviceBooking->service_id,
                'guest_id' => $serviceBooking->guest_id,
                'room_id' => $serviceBooking->room_id,
                'reservation_id' => $serviceBooking->reservation_id,
                'usage_date' => $serviceBooking->date,
                'amount' => $serviceBooking->amount,
                'charge_type' => $serviceBooking->charge_type,
                'payment_status' => $serviceBooking->payment_status,
            ]);
        }

        $serviceBooking->load(['service', 'guest', 'room', 'reservation', 'timeSlot', 'assignedStaff']);

        return response()->json([
            'success' => true,
            'message' => 'Service booking updated successfully',
            'data' => $serviceBooking
        ]);
    }

    /**
     * Remove the specified service booking.
     */
    public function destroy(ServiceBooking $serviceBooking)
    {
        $serviceBooking->delete();
        return response()->json([
            'success' => true,
            'message' => 'Service booking deleted successfully'
        ]);
    }

    /**
     * Get time slots for a service.
     */
    public function getTimeSlots(Request $request, Service $service)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday

        $timeSlots = ServiceTimeSlot::where('service_id', $service->id)
            ->where('is_available', true)
            ->where(function($query) use ($dayOfWeek) {
                $query->whereNull('available_days')
                      ->orWhereJsonContains('available_days', $dayOfWeek);
            })
            ->get();

        return response()->json(['data' => $timeSlots]);
    }

    /**
     * Get usage logs.
     */
    public function getUsageLogs(Request $request)
    {
        $query = ServiceUsageLog::with(['service', 'guest', 'room', 'reservation', 'processedBy']);

        // Filter by service
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('usage_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('usage_date', '<=', $request->end_date);
        }

        // Filter by guest
        if ($request->has('guest_id')) {
            $query->where('guest_id', $request->guest_id);
        }

        $logs = $query->orderBy('usage_date', 'desc')
                     ->orderBy('usage_time', 'desc')
                     ->get();

        return response()->json(['data' => $logs]);
    }

    /**
     * Post charges to room bill.
     */
    public function postChargesToRoom(ServiceBooking $serviceBooking)
    {
        if ($serviceBooking->charge_type !== 'room' || !$serviceBooking->reservation_id) {
            return response()->json([
                'success' => false,
                'message' => 'This booking is not configured for room charges.'
            ], 400);
        }

        if ($serviceBooking->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Charges have already been posted.'
            ], 400);
        }

        $serviceBooking->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update usage log
        $usageLog = ServiceUsageLog::where('service_booking_id', $serviceBooking->id)->first();
        if ($usageLog) {
            $usageLog->update([
                'payment_status' => 'paid',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Charges posted to room bill successfully',
            'data' => $serviceBooking->load(['service', 'guest', 'room', 'reservation'])
        ]);
    }
}
