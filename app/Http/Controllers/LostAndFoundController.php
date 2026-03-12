<?php

namespace App\Http\Controllers;

use App\Models\LostAndFound;
use Illuminate\Http\Request;

class LostAndFoundController extends Controller
{
    /**
     * Display a listing of lost and found items.
     */
    public function index(Request $request)
    {
        $query = LostAndFound::with(['room', 'foundBy', 'claimedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $items = $query->orderBy('found_date', 'desc')->get();
        return response()->json(['data' => $items]);
    }

    /**
     * Store a newly created lost and found item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'item' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'found_date' => 'required|date',
            'location' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'found';
        $validated['found_by'] = auth()->id();

        $item = LostAndFound::create($validated);
        $item->load(['room', 'foundBy', 'claimedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Lost and found item created successfully',
            'data' => $item
        ], 201);
    }

    /**
     * Display the specified lost and found item.
     */
    public function show(LostAndFound $lostAndFound)
    {
        $lostAndFound->load(['room', 'foundBy', 'claimedBy']);
        return response()->json(['data' => $lostAndFound]);
    }

    /**
     * Update the specified lost and found item.
     */
    public function update(Request $request, LostAndFound $lostAndFound)
    {
        $validated = $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'item' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'found_date' => 'sometimes|date',
            'status' => 'sometimes|in:found,claimed,unclaimed,discarded',
            'claimed_by' => 'nullable|exists:users,id',
            'claimed_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'claimed' && $lostAndFound->status !== 'claimed') {
            $validated['claimed_date'] = $validated['claimed_date'] ?? now();
        }

        $lostAndFound->update($validated);
        $lostAndFound->load(['room', 'foundBy', 'claimedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Lost and found item updated successfully',
            'data' => $lostAndFound
        ]);
    }

    /**
     * Remove the specified lost and found item.
     */
    public function destroy(LostAndFound $lostAndFound)
    {
        $lostAndFound->delete();
        return response()->json([
            'success' => true,
            'message' => 'Lost and found item deleted successfully'
        ]);
    }
}
