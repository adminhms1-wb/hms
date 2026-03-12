<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index()
    {
        $services = Service::all();
        return response()->json(['data' => $services]);
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['category'] = $validated['category'] ?? 'other';
        $validated['is_free'] = $validated['is_free'] ?? false;
        $validated['price'] = $validated['is_free'] ? 0.00 : ($validated['price'] ?? 0.00);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $service = Service::create($validated);
        return response()->json(['data' => $service], 201);
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        return response()->json(['data' => $service]);
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:255',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['is_free']) && $validated['is_free']) {
            $validated['price'] = 0.00;
        } elseif (isset($validated['is_free']) && !$validated['is_free'] && !isset($validated['price'])) {
            $validated['price'] = $service->price;
        }

        $service->update($validated);
        return response()->json(['data' => $service]);
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully'], 200);
    }
}
