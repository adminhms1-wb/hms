<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\Hotel;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
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

        $query = RoomType::where('hotel_id', $hotel->id);

        // Include trashed items if requested
        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        // Show only trashed items if requested
        if ($request->has('only_trashed') && $request->only_trashed) {
            $query->onlyTrashed();
        }

        $roomTypes = $query->get();
        return response()->json(['data' => $roomTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'max_guests' => 'nullable|integer|min:1',
        ]);

        $hotel = Hotel::first();
        if (!$hotel) {
            return response()->json(['error' => 'Hotel not found'], 404);
        }

        $validated['hotel_id'] = $hotel->id;
        $validated['base_price'] = $validated['base_price'] ?? 0;
        $validated['max_guests'] = $validated['max_guests'] ?? 1;

        $roomType = RoomType::create($validated);
        return response()->json($roomType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomType $roomType)
    {
        return response()->json($roomType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'max_guests' => 'nullable|integer|min:1',
        ]);

        $roomType->update($validated);
        return response()->json($roomType);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(RoomType $roomType)
    {
        $roomType->delete();
        return response()->json(['message' => 'Room type deleted successfully'], 200);
    }

    /**
     * Restore a soft-deleted room type.
     */
    public function restore($id)
    {
        $roomType = RoomType::withTrashed()->findOrFail($id);
        $roomType->restore();
        return response()->json(['message' => 'Room type restored successfully', 'data' => $roomType], 200);
    }

    /**
     * Permanently delete a room type.
     */
    public function forceDelete($id)
    {
        $roomType = RoomType::withTrashed()->findOrFail($id);
        $roomType->forceDelete();
        return response()->json(['message' => 'Room type permanently deleted'], 200);
    }
}
