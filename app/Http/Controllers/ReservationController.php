<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Guest;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['room.roomType', 'guest', 'guests']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by booking type
        if ($request->has('booking_type')) {
            $query->where('booking_type', $request->booking_type);
        }

        // Filter by date range
        if ($request->has('check_in_date')) {
            $query->where('check_in_date', '>=', $request->check_in_date);
        }
        if ($request->has('check_out_date')) {
            $query->where('check_out_date', '<=', $request->check_out_date);
        }

        // Filter by group
        if ($request->has('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $reservations = $query->orderBy('check_in_date', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    /**
     * Store a newly created reservation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'booking_type' => 'nullable|in:walk_in,advance,online',
            'group_id' => 'nullable|string|max:255',
            'guest_id' => 'nullable|exists:guests,id',
        ]);

        $room = Room::with('roomType')->findOrFail($validated['room_id']);

        // Check if room is available for the selected dates
        $existingReservation = Reservation::where('room_id', $validated['room_id'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('check_in_date', [$validated['check_in_date'], $validated['check_out_date']])
                      ->orWhereBetween('check_out_date', [$validated['check_in_date'], $validated['check_out_date']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('check_in_date', '<=', $validated['check_in_date'])
                            ->where('check_out_date', '>=', $validated['check_out_date']);
                      });
            })
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available for the selected dates.'
            ], 400);
        }

        // Check if room is in maintenance or under cleaning
        if (in_array($room->status, ['maintenance', 'under_cleaning'])) {
            return response()->json([
                'success' => false,
                'message' => 'Room is currently unavailable and cannot be reserved.'
            ], 400);
        }

        // Validate booking type specific rules
        $bookingType = $validated['booking_type'] ?? 'advance';
        $today = Carbon::today();
        $checkInDate = Carbon::parse($validated['check_in_date']);
        
        if ($bookingType === 'walk_in') {
            // Walk-in: Allow same-day or future dates, but not past dates
            if ($checkInDate->lt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Walk-in reservations cannot be made for past dates.'
                ], 400);
            }
        } elseif ($bookingType === 'advance') {
            // Advance booking: Must be at least 1 day in the future
            if ($checkInDate->lte($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Advance bookings must be made for future dates (at least 1 day in advance).'
                ], 400);
            }
        } elseif ($bookingType === 'online') {
            // Online booking: Must be at least 1 day in the future
            if ($checkInDate->lte($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Online bookings must be made for future dates (at least 1 day in advance).'
                ], 400);
            }
        }

        // Calculate total amount
        $roomType = $room->roomType;
        $basePrice = $roomType ? $roomType->base_price : 100;
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $basePrice * $nights;

        // Generate group_id for group bookings
        $groupId = $validated['group_id'] ?? null;
        if ($request->has('is_group_booking') && $request->is_group_booking && !$groupId) {
            $groupId = 'GRP-' . Str::upper(Str::random(8));
        }

        // Create or find guest
        $guest = null;
        if ($validated['guest_id'] ?? null) {
            $guest = Guest::find($validated['guest_id']);
        } elseif ($validated['guest_email'] ?? null) {
            $guest = Guest::where('email', $validated['guest_email'])->first();
            if (!$guest) {
                $guest = Guest::create([
                    'name' => $validated['guest_name'],
                    'email' => $validated['guest_email'],
                    'phone' => $validated['guest_phone'] ?? null,
                ]);
            }
        }

        // Determine initial status based on booking type
        $initialStatus = 'confirmed';
        if ($bookingType === 'online') {
            // Online bookings start as pending until payment is confirmed
            $initialStatus = $request->input('payment_status') === 'paid' ? 'confirmed' : 'pending';
        } elseif ($bookingType === 'advance') {
            // Advance bookings are confirmed immediately
            $initialStatus = 'confirmed';
        } elseif ($bookingType === 'walk_in') {
            // Walk-in bookings are confirmed immediately
            $initialStatus = 'confirmed';
        }

        // Create reservation
        $reservation = Reservation::create([
            'room_id' => $validated['room_id'],
            'guest_id' => $guest?->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'number_of_guests' => $validated['number_of_guests'],
            'total_amount' => $totalAmount,
            'status' => $initialStatus,
            'booking_type' => $bookingType,
            'group_id' => $groupId,
            'special_requests' => $validated['special_requests'] ?? null,
            'external_booking_id' => $request->input('external_booking_id'),
            'webhook_url' => $request->input('webhook_url'),
            'payment_status' => $request->input('payment_status'),
            'payment_method' => $request->input('payment_method'),
            'online_booking_metadata' => $request->input('online_booking_metadata') ? json_encode($request->input('online_booking_metadata')) : null,
        ]);

        // Attach primary guest to reservation_guests pivot table
        if ($guest && $guest->id) {
            $reservation->guests()->attach($guest->id);
        }

        // Update room status
        if ($bookingType === 'walk_in') {
            $room->update(['status' => 'reserved']);
        } elseif ($bookingType === 'advance' || ($bookingType === 'online' && $initialStatus === 'confirmed')) {
            $room->update(['status' => 'reserved']);
        }

        $reservation->load(['room.roomType', 'guest']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully',
            'data' => $reservation
        ], 201);
    }

    /**
     * Display the specified reservation
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['room.roomType', 'guest', 'guests']);
        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }

    /**
     * Update the specified reservation
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'guest_name' => 'sometimes|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'check_in_date' => 'sometimes|date',
            'check_out_date' => 'sometimes|date|after:check_in_date',
            'number_of_guests' => 'sometimes|integer|min:1',
            'special_requests' => 'nullable|string',
            'booking_type' => 'sometimes|in:walk_in,advance,online',
            'group_id' => 'nullable|string|max:255',
        ]);

        // Recalculate amount if dates or room changed
        if (isset($validated['check_in_date']) || isset($validated['check_out_date']) || isset($validated['room_id'])) {
            $roomId = $validated['room_id'] ?? $reservation->room_id;
            $checkIn = Carbon::parse($validated['check_in_date'] ?? $reservation->check_in_date);
            $checkOut = Carbon::parse($validated['check_out_date'] ?? $reservation->check_out_date);
            
            $room = Room::with('roomType')->findOrFail($roomId);
            $roomType = $room->roomType;
            $basePrice = $roomType ? $roomType->base_price : 100;
            $nights = $checkIn->diffInDays($checkOut);
            $validated['total_amount'] = $basePrice * $nights;
        }

        $reservation->update($validated);
        $reservation->load(['room.roomType', 'guest']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation updated successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Check-in a guest
     */
    public function checkIn(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed reservations can be checked in.'
            ], 400);
        }

        $reservation->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        $reservation->room->update(['status' => 'checked_in']);

        // Ensure check-in record exists (for front desk operations / deposit tracking)
        Checkin::firstOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'checkin_time' => now(),
                'deposit' => 0,
            ]
        );

        $reservation->load(['room.roomType', 'guest', 'checkin']);

        return response()->json([
            'success' => true,
            'message' => 'Guest checked in successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Check-out a guest
     */
    public function checkOut(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'Only checked-in reservations can be checked out.'
            ], 400);
        }

        $reservation->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        $reservation->room->update(['status' => 'under_cleaning']);

        $reservation->load(['room.roomType', 'guest', 'checkin']);

        return response()->json([
            'success' => true,
            'message' => 'Guest checked out successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Extend stay
     */
    public function extendStay(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'new_check_out_date' => 'required|date|after:check_in_date',
        ]);

        $newCheckOut = Carbon::parse($validated['new_check_out_date']);
        $oldCheckOut = Carbon::parse($reservation->check_out_date);

        if ($newCheckOut->lte($oldCheckOut)) {
            return response()->json([
                'success' => false,
                'message' => 'New check-out date must be after current check-out date.'
            ], 400);
        }

        // Check room availability for extended period
        $existingReservation = Reservation::where('room_id', $reservation->room_id)
            ->where('id', '!=', $reservation->id)
            ->where(function($query) use ($reservation, $newCheckOut) {
                $query->whereBetween('check_in_date', [$reservation->check_out_date, $newCheckOut])
                      ->orWhereBetween('check_out_date', [$reservation->check_out_date, $newCheckOut]);
            })
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available for the extended period.'
            ], 400);
        }

        // Calculate additional amount
        $roomType = $reservation->room->roomType;
        $basePrice = $roomType ? $roomType->base_price : 100;
        $additionalNights = $oldCheckOut->diffInDays($newCheckOut);
        $additionalAmount = $basePrice * $additionalNights;

        $reservation->update([
            'check_out_date' => $validated['new_check_out_date'],
            'total_amount' => $reservation->total_amount + $additionalAmount,
        ]);

        $reservation->load(['room.roomType', 'guest']);

        return response()->json([
            'success' => true,
            'message' => 'Stay extended successfully',
            'data' => $reservation,
            'additional_amount' => $additionalAmount
        ]);
    }

    /**
     * Early checkout
     */
    public function earlyCheckout(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'new_check_out_date' => 'required|date|after:check_in_date',
        ]);

        $newCheckOut = Carbon::parse($validated['new_check_out_date']);
        $oldCheckOut = Carbon::parse($reservation->check_out_date);
        $checkIn = Carbon::parse($reservation->check_in_date);

        if ($newCheckOut->gte($oldCheckOut)) {
            return response()->json([
                'success' => false,
                'message' => 'New check-out date must be before current check-out date.'
            ], 400);
        }

        if ($newCheckOut->lte($checkIn)) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out date must be after check-in date.'
            ], 400);
        }

        // Calculate refund (if applicable)
        $roomType = $reservation->room->roomType;
        $basePrice = $roomType ? $roomType->base_price : 100;
        $reducedNights = $newCheckOut->diffInDays($oldCheckOut);
        $refundAmount = $basePrice * $reducedNights;

        $reservation->update([
            'check_out_date' => $validated['new_check_out_date'],
            'total_amount' => $reservation->total_amount - $refundAmount,
            'refund_amount' => $refundAmount,
        ]);

        // If currently checked in, check out
        if ($reservation->status === 'checked_in') {
            $reservation->update([
                'status' => 'checked_out',
                'checked_out_at' => now(),
            ]);
            $reservation->room->update(['status' => 'under_cleaning']);
        }

        $reservation->load(['room.roomType', 'guest']);

        return response()->json([
            'success' => true,
            'message' => 'Early checkout processed successfully',
            'data' => $reservation,
            'refund_amount' => $refundAmount
        ]);
    }

    /**
     * Cancel reservation
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        if (in_array($reservation->status, ['checked_out', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a reservation that is already checked out or cancelled.'
            ], 400);
        }

        // Calculate refund based on cancellation policy
        $refundAmount = $this->calculateRefund($reservation);

        $reservation->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            'refund_amount' => $refundAmount,
        ]);

        // Update room status
        if ($reservation->status === 'checked_in') {
            $reservation->room->update(['status' => 'under_cleaning']);
        } else {
            $reservation->room->update(['status' => 'available']);
        }

        $reservation->load(['room.roomType', 'guest']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation cancelled successfully',
            'data' => $reservation,
            'refund_amount' => $refundAmount
        ]);
    }

    /**
     * Calculate refund amount based on cancellation policy
     */
    private function calculateRefund(Reservation $reservation)
    {
        $checkIn = Carbon::parse($reservation->check_in_date);
        $daysUntilCheckIn = Carbon::today()->diffInDays($checkIn, false);

        // Cancellation policy:
        // - More than 7 days: 100% refund
        // - 3-7 days: 50% refund
        // - Less than 3 days: No refund
        if ($daysUntilCheckIn > 7) {
            return $reservation->total_amount;
        } elseif ($daysUntilCheckIn >= 3) {
            return $reservation->total_amount * 0.5;
        } else {
            return 0;
        }
    }

    /**
     * Update room status
     */
    public function updateRoomStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,reserved,checked_in,checked_out,under_cleaning,maintenance',
        ]);

        $room->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully',
            'data' => $room->load('roomType')
        ]);
    }

    /**
     * Get group bookings
     */
    public function getGroupBookings(Request $request, $groupId)
    {
        $reservations = Reservation::where('group_id', $groupId)
            ->with(['room.roomType', 'guest'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    /**
     * Get all group bookings
     */
    public function getAllGroupBookings(Request $request)
    {
        $query = Reservation::whereNotNull('group_id')
            ->with(['room.roomType', 'guest']);

        if ($request->has('group_id')) {
            $query->where('group_id', 'like', '%' . $request->group_id . '%');
        }

        $reservations = $query->orderBy('group_id')->orderBy('check_in_date')->get();

        // Group by group_id
        $grouped = $reservations->groupBy('group_id')->map(function ($group) {
            $first = $group->first();
            return [
                'group_id' => $first->group_id,
                'reservations' => $group->values(),
                'total_amount' => $group->sum('total_amount'),
                'total_guests' => $group->sum('number_of_guests'),
                'check_in_date' => $group->min('check_in_date'),
                'check_out_date' => $group->max('check_out_date'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped
        ]);
    }

    /**
     * Assign guest to room
     */
    public function assignGuestToRoom(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_id' => 'nullable|exists:guests,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);

        $room = Room::with('roomType')->findOrFail($validated['room_id']);

        // Check if room is available
        $existingReservation = Reservation::where('room_id', $validated['room_id'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('check_in_date', [$validated['check_in_date'], $validated['check_out_date']])
                      ->orWhereBetween('check_out_date', [$validated['check_in_date'], $validated['check_out_date']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('check_in_date', '<=', $validated['check_in_date'])
                            ->where('check_out_date', '>=', $validated['check_out_date']);
                      });
            })
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available for the selected dates.'
            ], 400);
        }

        // Create or find guest
        $guest = null;
        if ($validated['guest_id'] ?? null) {
            $guest = Guest::find($validated['guest_id']);
        } elseif ($validated['guest_email'] ?? null) {
            $guest = Guest::where('email', $validated['guest_email'])->first();
            if (!$guest) {
                $guest = Guest::create([
                    'name' => $validated['guest_name'],
                    'email' => $validated['guest_email'],
                    'phone' => $validated['guest_phone'] ?? null,
                ]);
            }
        } else {
            $guest = Guest::create([
                'name' => $validated['guest_name'],
                'email' => $validated['guest_email'] ?? null,
                'phone' => $validated['guest_phone'] ?? null,
            ]);
        }

        // Calculate total amount
        $roomType = $room->roomType;
        $basePrice = $roomType ? $roomType->base_price : 100;
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $basePrice * $nights;

        // Create reservation
        $reservation = Reservation::create([
            'room_id' => $validated['room_id'],
            'guest_id' => $guest->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'number_of_guests' => $validated['number_of_guests'],
            'total_amount' => $totalAmount,
            'status' => 'confirmed',
            'booking_type' => 'advance',
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        // Attach primary guest to reservation_guests pivot table
        if ($guest && $guest->id) {
            $reservation->guests()->attach($guest->id);
        }

        // Update room status
        $room->update(['status' => 'reserved']);

        $reservation->load(['room.roomType', 'guest', 'guests']);

        return response()->json([
            'success' => true,
            'message' => 'Guest assigned to room successfully',
            'data' => $reservation
        ], 201);
    }

    /**
     * Get rooms with current reservations for assignment
     */
    public function getRoomsForAssignment(Request $request)
    {
        try {
            $hotel = \App\Models\Hotel::first();
            if (!$hotel) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $query = Room::with(['roomType', 'reservations' => function($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                  ->orderBy('check_in_date', 'desc');
            }])
            ->where('hotel_id', $hotel->id);

            if ($request->has('room_id') && $request->room_id) {
                $query->where('id', $request->room_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $rooms = $query->get();

            return response()->json([
                'success' => true,
                'data' => $rooms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading rooms: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add additional guests to a reservation
     */
    public function addGuests(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'required|exists:guests,id',
        ]);

        // Get existing guest IDs (including primary guest)
        $existingGuestIds = $reservation->guests()->pluck('guests.id')->toArray();
        if ($reservation->guest_id) {
            $existingGuestIds[] = $reservation->guest_id;
        }

        // Filter out guests that are already associated
        $newGuestIds = array_diff($validated['guest_ids'], $existingGuestIds);

        if (empty($newGuestIds)) {
            return response()->json([
                'success' => false,
                'message' => 'All selected guests are already associated with this reservation.'
            ], 400);
        }

        // Attach new guests
        $reservation->guests()->attach($newGuestIds);

        // Reload with relationships
        $reservation->load(['room.roomType', 'guest', 'guests']);

        return response()->json([
            'success' => true,
            'message' => count($newGuestIds) . ' guest(s) added successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Remove guests from a reservation
     */
    public function removeGuests(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'required|exists:guests,id',
        ]);

        // Prevent removing the primary guest
        if (in_array($reservation->guest_id, $validated['guest_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the primary guest. Please update the reservation to change the primary guest.'
            ], 400);
        }

        // Detach guests
        $reservation->guests()->detach($validated['guest_ids']);

        // Reload with relationships
        $reservation->load(['room.roomType', 'guest', 'guests']);

        return response()->json([
            'success' => true,
            'message' => count($validated['guest_ids']) . ' guest(s) removed successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Get all guests for a reservation (primary + additional)
     */
    public function getReservationGuests(Reservation $reservation)
    {
        $primaryGuest = $reservation->guest;
        $additionalGuests = $reservation->guests;

        $allGuests = collect([$primaryGuest])->merge($additionalGuests)->filter();

        return response()->json([
            'success' => true,
            'data' => [
                'primary_guest' => $primaryGuest,
                'additional_guests' => $additionalGuests,
                'all_guests' => $allGuests->values()
            ]
        ]);
    }

    /**
     * Update security deposit for a reservation (Front Desk Operations)
     */
    public function updateDeposit(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'deposit' => 'required|numeric|min:0',
        ]);

        $checkin = Checkin::firstOrCreate(
            ['reservation_id' => $reservation->id],
            ['checkin_time' => $reservation->checked_in_at ?? now()]
        );

        $checkin->update([
            'deposit' => $validated['deposit'],
        ]);

        $reservation->load(['room.roomType', 'guest', 'checkin']);

        return response()->json([
            'success' => true,
            'message' => 'Security deposit updated successfully',
            'data' => [
                'reservation' => $reservation,
                'checkin' => $checkin,
            ],
        ]);
    }

    /**
     * Guest bill preview with late checkout / extra bed / other charges
     */
    public function billPreview(Request $request, Reservation $reservation)
    {
        $lateFee = (float) $request->input('late_checkout_fee', 0);
        $extraBedFee = (float) $request->input('extra_bed_fee', 0);
        $otherCharges = (float) $request->input('other_charges', 0);
        $notes = $request->input('notes', '');

        $reservation->load(['room.roomType', 'guest', 'checkin']);

        $roomAmount = (float) $reservation->total_amount;
        $deposit = (float) optional($reservation->checkin)->deposit ?? 0;

        $subtotal = $roomAmount + $lateFee + $extraBedFee + $otherCharges;
        $totalPayable = max($subtotal - $deposit, 0);

        return response()->json([
            'success' => true,
            'data' => [
                'reservation' => $reservation,
                'room_amount' => $roomAmount,
                'deposit' => $deposit,
                'late_checkout_fee' => $lateFee,
                'extra_bed_fee' => $extraBedFee,
                'other_charges' => $otherCharges,
                'total_payable' => $totalPayable,
                'notes' => $notes,
            ],
        ]);
    }
}
