<?php

namespace App\Http\Controllers;

use App\Models\Folio;
use App\Models\FolioItem;
use App\Models\Reservation;
use App\Models\Guest;
use Illuminate\Http\Request;

class UnifiedGuestFolioController extends Controller
{
    public function index()
    {
        $folios = Folio::with(['reservation', 'guest', 'items'])->get();
        
        // Filter out any null entries and ensure data integrity
        $folios = $folios->filter(function ($folio) {
            return $folio && $folio->id !== null;
        })->values();
        
        return response()->json(['folios' => $folios]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'folio_date' => 'nullable|date',
            'subtotal' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'service_charge' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'status' => 'nullable|in:open,closed,cancelled',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string',
            'items.*.module' => 'nullable|string',
            'items.*.amount' => 'required_with:items|numeric|min:0',
        ]);

        // Calculate subtotal from items if provided
        if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0) {
            $itemsTotal = array_sum(array_column($validated['items'], 'amount'));
            $validated['subtotal'] = $itemsTotal;
        }

        $total = ($validated['subtotal'] ?? 0) + 
                 ($validated['tax_amount'] ?? 0) + 
                 ($validated['service_charge'] ?? 0) - 
                 ($validated['discount'] ?? 0);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $folio = Folio::create([
            ...$validated,
            'total' => $total,
            'paid' => 0,
            'balance' => $total,
        ]);

        // Create folio items
        if (count($items) > 0) {
            foreach ($items as $item) {
                FolioItem::create([
                    'folio_id' => $folio->id,
                    'description' => $item['description'],
                    'module' => $item['module'] ?? null,
                    'amount' => $item['amount'],
                ]);
            }
        }

        $folio->load(['reservation', 'guest', 'items']);

        return response()->json(['message' => 'Folio created successfully', 'folio' => $folio], 201);
    }

    public function show($id)
    {
        $folio = Folio::with(['reservation', 'guest', 'items'])->findOrFail($id);
        return response()->json(['folio' => $folio]);
    }

    public function update(Request $request, $id)
    {
        $folio = Folio::findOrFail($id);

        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'folio_date' => 'nullable|date',
            'subtotal' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'service_charge' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'status' => 'nullable|in:open,closed,cancelled',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:folio_items,id',
            'items.*.description' => 'required_with:items|string',
            'items.*.module' => 'nullable|string',
            'items.*.amount' => 'required_with:items|numeric|min:0',
        ]);

        // Calculate subtotal from items if provided
        if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0) {
            $itemsTotal = array_sum(array_column($validated['items'], 'amount'));
            $validated['subtotal'] = $itemsTotal;
        }

        $total = ($validated['subtotal'] ?? $folio->subtotal) + 
                 ($validated['tax_amount'] ?? $folio->tax_amount) + 
                 ($validated['service_charge'] ?? $folio->service_charge) - 
                 ($validated['discount'] ?? $folio->discount);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $folio->update([
            ...$validated,
            'total' => $total,
            'balance' => $total - $folio->paid,
        ]);

        // Update folio items
        if (isset($request->items)) {
            // Get existing item IDs
            $existingItemIds = collect($items)->pluck('id')->filter()->toArray();
            
            // Delete items that are not in the request
            FolioItem::where('folio_id', $folio->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();
            
            // Update or create items
            foreach ($items as $item) {
                if (isset($item['id']) && $item['id']) {
                    // Update existing item
                    FolioItem::where('id', $item['id'])
                        ->where('folio_id', $folio->id)
                        ->update([
                            'description' => $item['description'],
                            'module' => $item['module'] ?? null,
                            'amount' => $item['amount'],
                        ]);
                } else {
                    // Create new item
                    FolioItem::create([
                        'folio_id' => $folio->id,
                        'description' => $item['description'],
                        'module' => $item['module'] ?? null,
                        'amount' => $item['amount'],
                    ]);
                }
            }
        }

        $folio->load(['reservation', 'guest', 'items']);

        return response()->json(['message' => 'Folio updated successfully', 'folio' => $folio]);
    }

    public function destroy($id)
    {
        $folio = Folio::findOrFail($id);
        $folio->delete();
        return response()->json(['message' => 'Folio deleted successfully']);
    }
}
