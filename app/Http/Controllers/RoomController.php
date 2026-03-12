<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotel = Hotel::first();
        if (!$hotel) {
            return response()->json(['data' => []]);
        }

        $query = Room::with(['roomType', 'amenities'])
            ->where('hotel_id', $hotel->id);

        // Include trashed items if requested
        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        // Show only trashed items if requested
        if ($request->has('only_trashed') && $request->only_trashed) {
            $query->onlyTrashed();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")
                  ->orWhere('floor', 'like', "%{$search}%");
            });
        }

        // Filter by room type
        if ($request->has('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // If no pagination requested, return all rooms (for calendar view)
        if ($request->has('all') && $request->all) {
            $rooms = $query->get();
            return response()->json([
                'success' => true,
                'data' => $rooms
            ]);
        }

        $rooms = $query->paginate(15);
        return response()->json($rooms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:255',
            'floor' => 'nullable|string|max:255',
            'max_guests' => 'nullable|integer|min:1',
            'bed_type' => 'nullable|string|max:255',
            'smoking' => 'nullable|boolean',
            'status' => 'nullable|in:available,reserved,checked_in,checked_out,maintenance',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $hotel = Hotel::first();
        if (!$hotel) {
            return response()->json(['error' => 'Hotel not found'], 404);
        }

        $validated['hotel_id'] = $hotel->id;
        $validated['smoking'] = $validated['smoking'] ?? false;
        $validated['status'] = $validated['status'] ?? 'available';

        $amenities = $validated['amenities'] ?? [];
        unset($validated['amenities']);

        $room = Room::create($validated);
        
        if (!empty($amenities)) {
            $room->amenities()->sync($amenities);
        }

        $room->load(['roomType', 'amenities']);
        return response()->json($room, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $room->load(['roomType', 'amenities']);
        return response()->json($room);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_type_id' => 'sometimes|required|exists:room_types,id',
            'room_number' => 'sometimes|required|string|max:255',
            'floor' => 'nullable|string|max:255',
            'max_guests' => 'nullable|integer|min:1',
            'bed_type' => 'nullable|string|max:255',
            'smoking' => 'nullable|boolean',
            'status' => 'nullable|in:available,reserved,checked_in,checked_out,maintenance',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $amenities = $validated['amenities'] ?? null;
        if (isset($validated['amenities'])) {
            unset($validated['amenities']);
        }

        $room->update($validated);
        
        if ($amenities !== null) {
            $room->amenities()->sync($amenities);
        }

        $room->load(['roomType', 'amenities']);
        return response()->json($room);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return response()->json(['message' => 'Room deleted successfully'], 200);
    }

    /**
     * Restore a soft-deleted room.
     */
    public function restore($id)
    {
        $room = Room::withTrashed()->findOrFail($id);
        $room->restore();
        $room->load(['roomType', 'amenities']);
        return response()->json(['message' => 'Room restored successfully', 'data' => $room], 200);
    }

    /**
     * Permanently delete a room.
     */
    public function forceDelete($id)
    {
        $room = Room::withTrashed()->findOrFail($id);
        $room->forceDelete();
        return response()->json(['message' => 'Room permanently deleted'], 200);
    }

    /**
     * Get all amenities
     */
    public function getAmenities()
    {
        $amenities = Amenity::all();
        return response()->json(['data' => $amenities]);
    }

    /**
     * Get room availability for a specific date
     */
    public function getAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date)->format('Y-m-d');
        $hotel = Hotel::first();
        
        if (!$hotel) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $rooms = Room::with(['roomType'])
            ->where('hotel_id', $hotel->id)
            ->get();

        $availability = [];

        foreach ($rooms as $room) {
            // Check if room has a reservation for this date
            $reservation = Reservation::where('room_id', $room->id)
                ->where('check_in_date', '<=', $date)
                ->where('check_out_date', '>', $date)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->first();

            // Check for upcoming reservation (check-out date is today or future)
            $upcomingReservation = Reservation::where('room_id', $room->id)
                ->where('check_out_date', '>=', $date)
                ->where('check_in_date', '>', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('check_in_date', 'asc')
                ->first();

            // Determine status based on room status and reservation
            $status = 'available';
            $guestName = null;
            $reservationId = null;
            $reservationStatus = null;
            $checkOutDate = null;
            $checkInDate = null;

            // Priority: maintenance > active reservation > room status > upcoming reservation
            if ($room->status === 'maintenance') {
                $status = 'maintenance';
            } elseif ($reservation) {
                $status = $reservation->status === 'checked_in' ? 'checked_in' : 'booked';
                $guestName = $reservation->guest_name;
                $reservationId = $reservation->id;
                $reservationStatus = $reservation->status;
                $checkOutDate = $reservation->check_out_date;
                $checkInDate = $reservation->check_in_date;
            } elseif ($room->status === 'reserved') {
                $status = 'reserved';
                if ($upcomingReservation) {
                    $checkOutDate = $upcomingReservation->check_out_date;
                    $checkInDate = $upcomingReservation->check_in_date;
                }
            } elseif ($room->status === 'checked_in') {
                $status = 'checked_in';
            } elseif ($room->status === 'checked_out') {
                $status = 'checked_out';
                // If checked out, show when it becomes available (check-out date or now)
                if ($upcomingReservation) {
                    $checkOutDate = $upcomingReservation->check_out_date;
                    $checkInDate = $upcomingReservation->check_in_date;
                }
            } else {
                $status = 'available';
                if ($upcomingReservation) {
                    $checkOutDate = $upcomingReservation->check_out_date;
                    $checkInDate = $upcomingReservation->check_in_date;
                }
            }

            $availability[$room->id] = [
                'status' => $status,
                'guest_name' => $guestName,
                'reservation_id' => $reservationId,
                'reservation_status' => $reservationStatus,
                'room_status' => $room->status,
                'check_out_date' => $checkOutDate ? Carbon::parse($checkOutDate)->format('Y-m-d') : null,
                'check_in_date' => $checkInDate ? Carbon::parse($checkInDate)->format('Y-m-d') : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $availability
        ]);
    }
}
