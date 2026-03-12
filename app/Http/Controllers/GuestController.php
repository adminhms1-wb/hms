<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Guest::withCount('reservations');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        // Filter by VIP status
        if ($request->has('is_vip')) {
            $query->where('is_vip', $request->is_vip === 'true' || $request->is_vip === true);
        }

        // Filter by blacklist status
        if ($request->has('is_blacklisted')) {
            $query->where('is_blacklisted', $request->is_blacklisted === 'true' || $request->is_blacklisted === true);
        }

        // Filter by flagged status
        if ($request->has('is_flagged')) {
            if ($request->is_flagged === 'true' || $request->is_flagged === true) {
                $query->whereNotNull('flagged_at');
            } else {
                $query->whereNull('flagged_at');
            }
        }

        $guests = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $guests
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:guests,email',
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:255',
            'id_type' => 'nullable|in:national_id,passport,driving_license,other',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'is_vip' => 'nullable|boolean',
            'allergies' => 'nullable|string',
            'preferences' => 'nullable|array',
            'is_blacklisted' => 'nullable|boolean',
            'blacklist_reason' => 'nullable|string',
            'flagged_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $guest = Guest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guest created successfully',
            'data' => $guest
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $guest = Guest::with(['reservations' => function($query) {
            $query->with(['room.roomType'])
                  ->orderBy('check_in_date', 'desc');
        }])->findOrFail($id);

        // Calculate guest statistics
        $stats = [
            'total_stays' => $guest->reservations()->count(),
            'total_nights' => $guest->reservations()->sum(DB::raw('DATEDIFF(check_out_date, check_in_date)')),
            'total_spent' => $guest->reservations()->sum('total_amount'),
            'last_stay' => $guest->reservations()->orderBy('check_in_date', 'desc')->first()?->check_in_date,
        ];

        return response()->json([
            'success' => true,
            'data' => $guest,
            'stats' => $stats
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255|unique:guests,email,' . $guest->id,
            'phone' => 'nullable|string|max:20',
            'id_number' => 'nullable|string|max:255',
            'id_type' => 'nullable|in:national_id,passport,driving_license,other',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'is_vip' => 'nullable|boolean',
            'allergies' => 'nullable|string',
            'preferences' => 'nullable|array',
            'is_blacklisted' => 'nullable|boolean',
            'blacklist_reason' => 'nullable|string',
            'flagged_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $guest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Guest updated successfully',
            'data' => $guest->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $guest = Guest::findOrFail($id);
        $guest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guest deleted successfully'
        ]);
    }

    /**
     * Get guest history (previous stays)
     */
    public function getHistory($id)
    {
        $guest = Guest::findOrFail($id);

        $reservations = $guest->reservations()
            ->with(['room.roomType'])
            ->orderBy('check_in_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }

    /**
     * Flag a guest
     */
    public function flagGuest(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);

        $validated = $request->validate([
            'flagged_reason' => 'required|string',
        ]);

        $guest->update([
            'flagged_at' => now(),
            'flagged_reason' => $validated['flagged_reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guest flagged successfully',
            'data' => $guest->fresh()
        ]);
    }

    /**
     * Unflag a guest
     */
    public function unflagGuest($id)
    {
        $guest = Guest::findOrFail($id);

        $guest->update([
            'flagged_at' => null,
            'flagged_reason' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guest unflagged successfully',
            'data' => $guest->fresh()
        ]);
    }

    /**
     * Blacklist a guest
     */
    public function blacklistGuest(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);

        $validated = $request->validate([
            'blacklist_reason' => 'required|string',
        ]);

        $guest->update([
            'is_blacklisted' => true,
            'blacklist_reason' => $validated['blacklist_reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guest blacklisted successfully',
            'data' => $guest->fresh()
        ]);
    }

    /**
     * Remove guest from blacklist
     */
    public function unblacklistGuest($id)
    {
        $guest = Guest::findOrFail($id);

        $guest->update([
            'is_blacklisted' => false,
            'blacklist_reason' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guest removed from blacklist successfully',
            'data' => $guest->fresh()
        ]);
    }

    /**
     * Get guests for a specific reservation (multiple guests per room)
     */
    public function getReservationGuests($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        // Get primary guest
        $primaryGuest = $reservation->guest;

        // Get additional guests from reservation_guests pivot table
        $additionalGuests = DB::table('reservation_guests')
            ->where('reservation_id', $reservationId)
            ->join('guests', 'reservation_guests.guest_id', '=', 'guests.id')
            ->select('guests.*')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'primary_guest' => $primaryGuest,
                'additional_guests' => $additionalGuests
            ]
        ]);
    }

    /**
     * Add additional guest to reservation
     */
    public function addGuestToReservation(Request $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
        ]);

        // Check if guest is already added
        $exists = DB::table('reservation_guests')
            ->where('reservation_id', $reservationId)
            ->where('guest_id', $validated['guest_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Guest is already added to this reservation'
            ], 400);
        }

        DB::table('reservation_guests')->insert([
            'reservation_id' => $reservationId,
            'guest_id' => $validated['guest_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guest added to reservation successfully'
        ]);
    }

    /**
     * Remove guest from reservation
     */
    public function removeGuestFromReservation($reservationId, $guestId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        DB::table('reservation_guests')
            ->where('reservation_id', $reservationId)
            ->where('guest_id', $guestId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guest removed from reservation successfully'
        ]);
    }
}
