<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OnlineBookingController extends Controller
{
    /**
     * Public API endpoint for online bookings
     * This endpoint can be used by external booking systems, websites, mobile apps, etc.
     */
    public function createBooking(Request $request)
    {
        // Validate API key if configured
        $apiKey = $request->header('X-API-Key');
        if (config('app.online_booking_api_key') && $apiKey !== config('app.online_booking_api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'external_booking_id' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:500',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'payment_method' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
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

        if (in_array($room->status, ['maintenance', 'under_cleaning'])) {
            return response()->json([
                'success' => false,
                'message' => 'Room is currently unavailable.'
            ], 400);
        }

        // Calculate total amount
        $roomType = $room->roomType;
        $basePrice = $roomType ? $roomType->base_price : 100;
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $basePrice * $nights;

        // Create or find guest
        $guest = Guest::where('email', $validated['guest_email'])->first();
        if (!$guest) {
            $guest = Guest::create([
                'name' => $validated['guest_name'],
                'email' => $validated['guest_email'],
                'phone' => $validated['guest_phone'] ?? null,
            ]);
        }

        // Determine status based on payment
        $status = ($validated['payment_status'] ?? 'pending') === 'paid' ? 'confirmed' : 'pending';

        // Create reservation
        $reservation = Reservation::create([
            'room_id' => $validated['room_id'],
            'guest_id' => $guest->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'] ?? null,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'number_of_guests' => $validated['number_of_guests'],
            'total_amount' => $totalAmount,
            'status' => $status,
            'booking_type' => 'online',
            'special_requests' => $validated['special_requests'] ?? null,
            'external_booking_id' => $validated['external_booking_id'] ?? null,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'payment_status' => $validated['payment_status'] ?? 'pending',
            'payment_method' => $validated['payment_method'] ?? null,
            'online_booking_metadata' => isset($validated['metadata']) ? json_encode($validated['metadata']) : null,
        ]);

        // Update room status if confirmed
        if ($status === 'confirmed') {
            $room->update(['status' => 'reserved']);
        }

        $reservation->load(['room.roomType', 'guest']);

        // Send webhook notification if URL provided
        if ($reservation->webhook_url) {
            $this->sendWebhook($reservation, 'booking.created');
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => [
                'reservation_id' => $reservation->id,
                'booking_reference' => $reservation->external_booking_id ?? 'BK-' . str_pad($reservation->id, 8, '0', STR_PAD_LEFT),
                'status' => $reservation->status,
                'total_amount' => $reservation->total_amount,
                'check_in_date' => $reservation->check_in_date,
                'check_out_date' => $reservation->check_out_date,
            ]
        ], 201);
    }

    /**
     * Update booking status (for payment confirmations, etc.)
     */
    public function updateBookingStatus(Request $request, $reservationId)
    {
        $apiKey = $request->header('X-API-Key');
        if (config('app.online_booking_api_key') && $apiKey !== config('app.online_booking_api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'external_booking_id' => 'nullable|string|max:255',
        ]);

        $reservation = Reservation::where('id', $reservationId)
            ->orWhere('external_booking_id', $reservationId)
            ->firstOrFail();

        $oldStatus = $reservation->status;
        $oldPaymentStatus = $reservation->payment_status;

        // Update payment status
        $reservation->payment_status = $validated['payment_status'];
        if ($validated['external_booking_id']) {
            $reservation->external_booking_id = $validated['external_booking_id'];
        }

        // Update reservation status based on payment
        if ($validated['payment_status'] === 'paid' && $reservation->status === 'pending') {
            $reservation->status = 'confirmed';
            $reservation->room->update(['status' => 'reserved']);
        } elseif ($validated['payment_status'] === 'failed' && $reservation->status === 'pending') {
            // Keep as pending, can be retried
        }

        $reservation->save();
        $reservation->load(['room.roomType', 'guest']);

        // Send webhook if status changed
        if ($oldStatus !== $reservation->status || $oldPaymentStatus !== $reservation->payment_status) {
            if ($reservation->webhook_url) {
                $this->sendWebhook($reservation, 'booking.updated');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Get available rooms for online booking
     */
    public function getAvailableRooms(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        if (config('app.online_booking_api_key') && $apiKey !== config('app.online_booking_api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        $validated = $request->validate([
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $rooms = Room::with('roomType')
            ->where('status', 'available')
            ->get();

        $availableRooms = [];

        foreach ($rooms as $room) {
            // Check if room has conflicting reservations
            $conflict = Reservation::where('room_id', $room->id)
                ->where(function($query) use ($validated) {
                    $query->whereBetween('check_in_date', [$validated['check_in_date'], $validated['check_out_date']])
                          ->orWhereBetween('check_out_date', [$validated['check_in_date'], $validated['check_out_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('check_in_date', '<=', $validated['check_in_date'])
                                ->where('check_out_date', '>=', $validated['check_out_date']);
                          });
                })
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->exists();

            if (!$conflict) {
                $checkIn = Carbon::parse($validated['check_in_date']);
                $checkOut = Carbon::parse($validated['check_out_date']);
                $nights = $checkIn->diffInDays($checkOut);
                $totalAmount = ($room->roomType->base_price ?? 100) * $nights;

                $availableRooms[] = [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->roomType->name ?? 'N/A',
                    'base_price' => $room->roomType->base_price ?? 100,
                    'nights' => $nights,
                    'total_amount' => $totalAmount,
                    'max_guests' => $room->max_guests,
                    'amenities' => $room->amenities->pluck('name'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $availableRooms
        ]);
    }

    /**
     * Cancel online booking
     */
    public function cancelBooking(Request $request, $reservationId)
    {
        $apiKey = $request->header('X-API-Key');
        if (config('app.online_booking_api_key') && $apiKey !== config('app.online_booking_api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
        }

        $reservation = Reservation::where('id', $reservationId)
            ->orWhere('external_booking_id', $reservationId)
            ->where('booking_type', 'online')
            ->firstOrFail();

        if (in_array($reservation->status, ['checked_out', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a reservation that is already checked out or cancelled.'
            ], 400);
        }

        $reservation->update([
            'status' => 'cancelled',
            'payment_status' => 'refunded',
        ]);

        if ($reservation->status === 'checked_in') {
            $reservation->room->update(['status' => 'under_cleaning']);
        } else {
            $reservation->room->update(['status' => 'available']);
        }

        // Send webhook
        if ($reservation->webhook_url) {
            $this->sendWebhook($reservation, 'booking.cancelled');
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $reservation
        ]);
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(Reservation $reservation, $event)
    {
        try {
            $payload = [
                'event' => $event,
                'reservation_id' => $reservation->id,
                'external_booking_id' => $reservation->external_booking_id,
                'status' => $reservation->status,
                'payment_status' => $reservation->payment_status,
                'guest_name' => $reservation->guest_name,
                'guest_email' => $reservation->guest_email,
                'room_number' => $reservation->room->room_number,
                'check_in_date' => $reservation->check_in_date,
                'check_out_date' => $reservation->check_out_date,
                'total_amount' => $reservation->total_amount,
                'timestamp' => now()->toIso8601String(),
            ];

            Http::timeout(10)->post($reservation->webhook_url, $payload);
        } catch (\Exception $e) {
            Log::error('Webhook failed for reservation ' . $reservation->id . ': ' . $e->getMessage());
        }
    }
}
