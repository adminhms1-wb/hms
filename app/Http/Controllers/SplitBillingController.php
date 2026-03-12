<?php

namespace App\Http\Controllers;

use App\Models\SplitBilling;
use App\Models\SplitBillingItem;
use Illuminate\Http\Request;

class SplitBillingController extends Controller
{
    public function index()
    {
        $bills = SplitBilling::with(['folio', 'items'])->get();
        return response()->json(['bills' => $bills]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'folio_id' => 'required|exists:folios,id',
            'split_type' => 'required|in:equal,percentage,amount',
            'number_of_splits' => 'required|integer|min:2',
            'total_amount' => 'required|numeric',
            'status' => 'nullable|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $bill = SplitBilling::create($validated);
        $bill->load(['folio', 'items']);

        return response()->json(['message' => 'Split bill created successfully', 'bill' => $bill], 201);
    }

    public function update(Request $request, $id)
    {
        $bill = SplitBilling::findOrFail($id);

        $validated = $request->validate([
            'folio_id' => 'required|exists:folios,id',
            'split_type' => 'required|in:equal,percentage,amount',
            'number_of_splits' => 'required|integer|min:2',
            'total_amount' => 'required|numeric',
            'status' => 'nullable|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $bill->update($validated);
        $bill->load(['folio', 'items']);

        return response()->json(['message' => 'Split bill updated successfully', 'bill' => $bill]);
    }

    public function destroy($id)
    {
        $bill = SplitBilling::findOrFail($id);
        $bill->delete();
        return response()->json(['message' => 'Split bill deleted successfully']);
    }
}
