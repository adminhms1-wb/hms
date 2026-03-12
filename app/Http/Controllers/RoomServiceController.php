<?php

namespace App\Http\Controllers;

use App\Models\RoomServiceOrder;
use App\Models\RoomServiceItem;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomServiceController extends Controller
{
    /**
     * Display a listing of room service orders.
     */
    public function index(Request $request)
    {
        $query = RoomServiceOrder::with(['room', 'guest', 'reservation', 'assignedStaff', 'items.menuItem']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by room number
        if ($request->has('room_number')) {
            $query->where('room_number', 'like', '%' . $request->room_number . '%');
        }

        $orders = $query->orderBy('ordered_at', 'desc')->get();

        return response()->json($orders);
    }

    /**
     * Store a newly created room service order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:255',
            'guest_name' => 'nullable|string|max:255',
            'reservation_id' => 'nullable|exists:reservations,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Find room by room number (with hotel filter)
            $hotel = Hotel::first();
            $room = null;
            if ($hotel) {
                $room = Room::where('hotel_id', $hotel->id)
                    ->where('room_number', $validated['room_number'])
                    ->first();
                
                // Try case-insensitive if exact match fails
                if (!$room) {
                    $room = Room::where('hotel_id', $hotel->id)
                        ->whereRaw('LOWER(room_number) = ?', [strtolower($validated['room_number'])])
                        ->first();
                }
            }
            $roomId = $room ? $room->id : null;

            // Find guest if reservation_id is provided
            $guestId = null;
            if ($validated['reservation_id']) {
                $reservation = Reservation::find($validated['reservation_id']);
                $guestId = $reservation ? $reservation->guest_id : null;
            }

            // Create order
            $order = RoomServiceOrder::create([
                'room_id' => $roomId,
                'guest_id' => $guestId,
                'reservation_id' => $validated['reservation_id'] ?? null,
                'room_number' => $validated['room_number'],
                'guest_name' => $validated['guest_name'] ?? null,
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'total_amount' => $validated['total_amount'],
                'status' => 'NEW',
                'ordered_at' => now(),
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                RoomServiceItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            $order->load(['room', 'guest', 'reservation', 'assignedStaff', 'items.menuItem']);

            return response()->json([
                'success' => true,
                'message' => 'Room service order created successfully',
                'order' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified room service order.
     */
    public function show(RoomServiceOrder $roomServiceOrder)
    {
        $roomServiceOrder->load(['room', 'guest', 'reservation', 'assignedStaff', 'items.menuItem']);
        return response()->json($roomServiceOrder);
    }

    /**
     * Update the specified room service order.
     */
    public function update(Request $request, RoomServiceOrder $roomServiceOrder)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:NEW,KOT,PREPARING,OUT_FOR_DELIVERY,DELIVERED,CANCELLED',
            'assigned_staff_id' => 'nullable|exists:users,id',
        ]);

        // Update status and timestamps
        if (isset($validated['status'])) {
            $roomServiceOrder->status = $validated['status'];
            
            // Update timestamps based on status
            if ($validated['status'] === 'KOT' && !$roomServiceOrder->kitchen_time) {
                $roomServiceOrder->kitchen_time = now();
            } elseif ($validated['status'] === 'PREPARING' && !$roomServiceOrder->kitchen_time) {
                $roomServiceOrder->kitchen_time = now();
            } elseif ($validated['status'] === 'OUT_FOR_DELIVERY') {
                $roomServiceOrder->delivery_time = now();
            } elseif ($validated['status'] === 'DELIVERED') {
                $roomServiceOrder->delivered_time = now();
            }
        }

        if (isset($validated['assigned_staff_id'])) {
            $roomServiceOrder->assigned_staff_id = $validated['assigned_staff_id'];
        }

        $roomServiceOrder->save();
        $roomServiceOrder->load(['room', 'guest', 'reservation', 'assignedStaff', 'items.menuItem']);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'order' => $roomServiceOrder
        ]);
    }

    /**
     * Post charges to room bill.
     */
    public function postCharges(Request $request, RoomServiceOrder $roomServiceOrder)
    {
        if ($roomServiceOrder->charges_posted) {
            return response()->json([
                'success' => false,
                'message' => 'Charges have already been posted to room bill'
            ], 400);
        }

        if ($roomServiceOrder->status !== 'DELIVERED') {
            return response()->json([
                'success' => false,
                'message' => 'Order must be delivered before posting charges'
            ], 400);
        }

        try {
            // Mark charges as posted
            $roomServiceOrder->charges_posted = true;
            $roomServiceOrder->charges_posted_at = now();
            $roomServiceOrder->save();

            // TODO: Add logic to post charges to reservation folio/bill
            // This would typically involve creating a folio item or updating the reservation's charges

            return response()->json([
                'success' => true,
                'message' => 'Charges posted to room bill successfully',
                'order' => $roomServiceOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to post charges: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get room details by room number.
     */
    public function getRoomDetails(Request $request)
    {
        $request->validate([
            'room_number' => 'required|string'
        ]);

        // Get hotel to filter rooms
        $hotel = Hotel::first();
        if (!$hotel) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel not configured'
            ], 404);
        }

        // Search for room by room_number - try multiple approaches
        $roomNumber = trim($request->room_number);
        
        // Build base query
        $baseQuery = Room::where('hotel_id', $hotel->id);
        
        // First try exact match
        $room = (clone $baseQuery)->where('room_number', $roomNumber)
            ->with(['roomType'])
            ->first();
        
        // If not found, try case-insensitive match
        if (!$room) {
            $room = (clone $baseQuery)->whereRaw('LOWER(room_number) = ?', [strtolower($roomNumber)])
                ->with(['roomType'])
                ->first();
        }
        
        // If still not found, try trimmed match (in case database has extra spaces)
        if (!$room) {
            $room = (clone $baseQuery)->whereRaw('TRIM(room_number) = ?', [$roomNumber])
                ->with(['roomType'])
                ->first();
        }
        
        // If still not found, try LIKE match (partial)
        if (!$room) {
            $room = (clone $baseQuery)->where('room_number', 'like', '%' . $roomNumber . '%')
                ->with(['roomType'])
                ->first();
        }
        
        // If still not found, try without hotel filter as last resort (in case hotel_id is wrong or missing)
        if (!$room) {
            // Try exact match without hotel filter
            $room = Room::where('room_number', $roomNumber)
                ->with(['roomType'])
                ->first();
            
            // Try case-insensitive without hotel filter
            if (!$room) {
                $room = Room::whereRaw('LOWER(room_number) = ?', [strtolower($roomNumber)])
                    ->with(['roomType'])
                    ->first();
            }
            
            // Try LIKE without hotel filter
            if (!$room) {
                $room = Room::where('room_number', 'like', '%' . $roomNumber . '%')
                    ->with(['roomType'])
                    ->first();
            }
        }

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found. Please verify the room number exists in the system.'
            ], 404);
        }

        // Find active reservation for this room (checked_in or confirmed status)
        $reservation = Reservation::where('room_id', $room->id)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->with(['guest'])
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'room' => $room,
            'reservation' => $reservation,
            'guest_name' => $reservation ? $reservation->guest_name : null,
            'reservation_id' => $reservation ? $reservation->id : null,
        ]);
    }
}
