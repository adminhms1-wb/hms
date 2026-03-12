<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    /**
     * Display a listing of amenities.
     */
    public function index()
    {
        $amenities = Amenity::all();
        return response()->json(['data' => $amenities]);
    }

    /**
     * Store a newly created amenity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_paid' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
        ]);

        $validated['is_paid'] = $validated['is_paid'] ?? false;
        $validated['price'] = $validated['is_paid'] ? ($validated['price'] ?? 0.00) : 0.00;

        $amenity = Amenity::create($validated);
        return response()->json(['data' => $amenity], 201);
    }

    /**
     * Display the specified amenity.
     */
    public function show(Amenity $amenity)
    {
        return response()->json(['data' => $amenity]);
    }

    /**
     * Update the specified amenity.
     */
    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'is_paid' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['is_paid']) && !$validated['is_paid']) {
            $validated['price'] = 0.00;
        }

        $amenity->update($validated);
        return response()->json(['data' => $amenity]);
    }

    /**
     * Remove the specified amenity.
     */
    public function destroy(Amenity $amenity)
    {
        $amenity->delete();
        return response()->json(['message' => 'Amenity deleted successfully'], 200);
    }
}
